<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\VirtualQueueSetting;
use Illuminate\Support\Facades\Session;

class TicketGenerationSelection extends Component
{
    public $locationId;
    public $teamId;
    public $settings;
    public $selectedOption = null;

    public function mount($location_id = null)
    {
        $this->locationId = $location_id ?? Session::get('selectedLocation');
        $this->teamId = tenant('id');
        
        // Load virtual queue settings
        $this->settings = VirtualQueueSetting::getSettings($this->teamId, $this->locationId);
    }

    public function selectOption($option)
    {
        $this->selectedOption = $option;

        // Redirect based on selection
        switch ($option) {
            case 'qr_code':
                return redirect()->route('tenant.qr-code');
                
            case 'kiosk':
                return redirect()->to('/main/' . base64_encode($this->locationId));
                
            case 'virtual':
                if ($this->settings->isVirtualQueueEnabled()) {
                    return redirect()->route('virtual-queue-type-selection-staff', ['location_id' => base64_encode($this->locationId)]);
                } else {
                    session()->flash('error', 'Virtual queue is not enabled for this location.');
                }
                break;
        }
    }

    public function render()
    {
        return view('livewire.ticket-generation-selection');
    }
}
