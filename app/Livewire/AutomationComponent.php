<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;


class AutomationComponent extends Component
{
    #[Title('Automation')] 

    public $teamId;
    public $locationId;
    public $mainpage = true;
    public $addBookingsToWaitlist = false;

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get( 'selectedLocation');
    }

    public function showPage($page)
    {
        // List of all section variables
        $sections = ['mainpage', 'addBookingsToWaitlist'];
    
        // Set all sections to false
        foreach ($sections as $section) {
            $this->$section = false;
        }
    
        // Set only the requested section to true
        if (in_array($page, $sections)) {
            $this->$page = true;
        }
    }

    public function render()
    {
        return view('livewire.automation-component');
    }
}
