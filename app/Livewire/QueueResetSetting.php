<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SiteDetail;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class QueueResetSetting extends Component
{
    public $queueToken;
    public $queueTokenEndTime;
    public $siteDetail;
    public $teamId;
    public $locationId;


    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->siteDetail = SiteDetail::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->firstOrFail();
        $this->queueToken = $this->siteDetail->queue_token_list ?? 'default';
        $this->queueTokenEndTime = $this->siteDetail->queue_token_end_time ? \Carbon\Carbon::parse($this->siteDetail->queue_token_end_time)->format('H:i') : '';
    }

    public function updatedQueueToken($value)
    {
        $this->queueToken = $value;
        if ($value !== 'custom') {
            $this->queueTokenEndTime = null;
        }
    }

    public function save()
    {
         $this->validate([ 
        'queueToken' => 'required|in:default,custom,never',
    ]);
    
    if ($this->queueToken === 'custom') {
    $this->validate([
        'queueTokenEndTime' => 'required|date_format:H:i',
    ]);
    }
        $this->siteDetail->update([
            'queue_token_list' => $this->queueToken,
            'queue_token_end_time' => $this->queueTokenEndTime ?: null,
        ]);

        // Dispatch events for Pusher if needed
        // event(new DisplayScreenEvent());
        // event(new TicketGenerateCallEvent());

        // session()->flash('message', 'Queue settings updated successfully.');
        $this->dispatch('queueSettingUpdated');
    }

    public function resetToken()
    {
        // Get the latest call and update is_rest
        $latestCall = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->locationId)
            ->latest('id')
            ->first();

        if ($latestCall) {
            $latestCall->update(['is_rest' => 1]);
            $this->dispatch('tokenReset', callId: $latestCall->id);
            session()->flash('message', 'Token reset successfully.');
        } else {
            session()->flash('error', 'No calls found to reset.');
        }
    }

    public function render()
    {
        return view('livewire.queue-reset-setting');
    }
}
