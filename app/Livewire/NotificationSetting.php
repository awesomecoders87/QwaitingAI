<?php

namespace App\Livewire;

use Livewire\Component;
use Auth;
use Livewire\Attributes\Title;

class NotificationSetting extends Component
{

     #[Title('Notification Setting')]

      public function mount()
    {

        $user = Auth::user();
        if (!$user->hasPermissionTo('Message Template Edit')) {
            abort(403);
        }

    }

    public function render()
    {
        return view('livewire.notification-setting');
    }
}
