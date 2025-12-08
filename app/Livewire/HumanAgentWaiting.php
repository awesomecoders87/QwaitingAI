<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\VirtualQueue;
use App\Models\VirtualQueueSetting;
use App\Models\User;
use App\Models\SmsAPI;
use Illuminate\Support\Str;

class HumanAgentWaiting extends Component
{
    public $virtualQueueId;
    public $virtualQueue;
    public $settings;
    public $status = 'waiting'; // waiting, connected, completed
    public $position;
    public $estimatedWaitTime;
    public $assignedAgent;
    public $meetingLink;

    protected $listeners = ['agentAssigned', 'positionUpdated'];

    public function mount($virtualQueueId)
    {
        $this->virtualQueueId = base64_decode($virtualQueueId);
        $this->virtualQueue = VirtualQueue::findOrFail($this->virtualQueueId);
        $this->settings = VirtualQueueSetting::getSettings($this->virtualQueue->team_id, $this->virtualQueue->location_id);

        // Calculate queue position
        $this->calculatePosition();

        // Check if agent is already assigned
        if ($this->virtualQueue->human_agent_id) {
            $this->assignedAgent = User::find($this->virtualQueue->human_agent_id);
            $this->status = 'connected';
            $this->generateMeetingLink();
        }
    }

    protected function calculatePosition()
    {
        // Count how many virtual queues are ahead
        $this->position = VirtualQueue::where('team_id', $this->virtualQueue->team_id)
            ->where('location_id', $this->virtualQueue->location_id)
            ->where('queue_type', 'human_agent')
            ->where('status', 'pending')
            ->where('created_at', '<', $this->virtualQueue->created_at)
            ->count() + 1;

        // Estimate wait time (5 minutes per person ahead)
        $this->estimatedWaitTime = $this->position * 5;
    }

    protected function generateMeetingLink()
    {
        if (!$this->virtualQueue->meeting_link) {
            $sessionId = Str::uuid()->toString();
            
            $meetingLink = route('virtual-meeting', [
                'room' => $sessionId,
                'queueId' => $this->virtualQueue->id
            ]);

            $this->virtualQueue->update([
                'session_id' => $sessionId,
                'meeting_link' => $meetingLink,
            ]);

            $this->meetingLink = $meetingLink;

            // Send notification
            $this->sendAgentAssignedNotification();
        } else {
            $this->meetingLink = $this->virtualQueue->meeting_link;
        }
    }

    protected function sendAgentAssignedNotification()
    {
        $agentName = $this->assignedAgent ? $this->assignedAgent->name : 'Agent';
        $message = "You have been assigned to {$agentName}. Join the call: {$this->meetingLink}";

        // Send SMS
        if ($this->settings->send_sms_notification && $this->virtualQueue->customer_phone) {
            SmsAPI::currentQueueSms(
                $this->virtualQueue->customer_phone,
                $message,
                $this->virtualQueue->team_id,
                'virtual_queue'
            );
        }

        // Send Email
        if ($this->settings->send_email_notification && $this->virtualQueue->customer_email) {
            // Email sending logic
        }
    }

    public function joinCall()
    {
        if ($this->meetingLink) {
            return redirect($this->meetingLink);
        }
    }

    public function cancelQueue()
    {
        $this->virtualQueue->update([
            'status' => 'cancelled',
        ]);

        session()->flash('success', 'Queue cancelled successfully.');
        return redirect()->route('queue', ['location_id' => $this->virtualQueue->location_id]);
    }

    public function agentAssigned($agentId)
    {
        $this->assignedAgent = User::find($agentId);
        $this->status = 'connected';
        $this->generateMeetingLink();
    }

    public function positionUpdated()
    {
        $this->calculatePosition();
    }

    public function render()
    {
        return view('livewire.human-agent-waiting');
    }
}
