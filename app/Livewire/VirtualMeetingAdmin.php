<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TwilioVideoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('components.layouts.custom-admin-video-layout')]
class VirtualMeetingAdmin extends Component
{
    public string $identity;
    public string $room;
    public ?string $queueId;
    public string $token;

     public function mount(TwilioVideoService $twilio)
    {
            $this->dispatch('join-call');
        $this->identity = auth()->check()
            ? 'staff_' . uniqid()
            : 'guest_' . uniqid();

        $this->token = $twilio->generateToken($this->identity, $this->room);

      
    }

    public function render()
    {
   
        return view('livewire.virtual-meeting-admin');
    }
}
