<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

class Integrations extends Component
{

    #[Title('Integrations')]

    public $integrations;

    public function render()
    {
        return view('livewire.integrations');
    }
}
