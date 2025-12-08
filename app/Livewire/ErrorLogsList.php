<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ErrorList;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

   #[Layout('components.layouts.custom-display-layout')]
class ErrorLogsList extends Component
{
    #[Title('Errors List')]

    public function render()
    {
        return view('livewire.error-logs-list' ,[
            'items' => ErrorList::all(),
        ]);
    }
}
