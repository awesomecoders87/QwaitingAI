<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.custom-layout')]
class RatingThankYou extends Component
{
    #[Title('Rating-Thank you')]
    public function render()
    {
        return view('livewire.rating-thankyou');
    }
}
