<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TwilioVideoService;
use Livewire\Attributes\Layout;
use App\Models\QueueStorage;
use App\Models\ReverbDetail;
use App\Models\User;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;

#[Layout('components.layouts.custom-video-layout')]
class VirtualMeeting extends Component
{
    public string $identity;
    public string $room;
    public ?string $queueId;
    public string $token;
    public $queueStorage;
    public $staff;
    public $ticketLink;
    public $showTicketPage = false;
    public $reverbDetails;
    public $reverbKey;
    public $reverbHost, $reverbPort, $reverbScheme;


    public function mount($room, $queueId)
{
    if (!empty($queueId)) {
        $this->queueStorage = QueueStorage::where('queue_id', base64_decode($queueId))->first();

        if ($this->queueStorage) {
            $this->staff = User::where('id', $this->queueStorage->served_by)->first();
            $this->ticketLink = url('visits/' . base64_encode($this->queueStorage->queue_id));
            $this->reverbDetails = ReverbDetail::viewReverbDetails(
                $this->queueStorage->team_id,
                $this->queueStorage->locations_id
            );
            $this->reverbKey = $this->reverbDetails->key ?? env('REVERB_APP_KEY');
            $this->reverbHost = $this->reverbDetails->host ?? env('REVERB_HOST', '127.0.0.1');
            $this->reverbPort = $this->reverbDetails->port ?? env('REVERB_PORT', 8080);
            $this->reverbScheme = $this->reverbDetails->scheme ?? env('REVERB_SCHEME', 'http');

            // ✅ First set the selected location
            Session::put('selectedLocation', $this->queueStorage->locations_id);
        }
    }

    $this->identity = auth()->check()
        ? 'staff_' . uniqid()
        : 'guest_' . uniqid();

    // ✅ Resolve TwilioVideoService after location is set
    $twilio = app(TwilioVideoService::class);

    $this->room = $room;
    $this->token = $twilio->generateToken($this->identity, $this->room);
}

    public function render()
    {
        return view('livewire.virtual-meeting');
    }
}
