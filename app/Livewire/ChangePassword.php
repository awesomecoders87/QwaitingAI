<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

class ChangePassword extends Component
{
    #[Title('Change Password')]

    public $current_password;
    public $new_password;
    public $new_password_confirmation;


    public function mount(){
          $checkuser = Auth::user();
        if (!$checkuser->hasPermissionTo('Change Password')) {
            abort(403);
        }
    }
    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|different:current_password', // Ensure it's different
            'new_password_confirmation' => 'required|same:new_password',
        ], [
            'new_password.different' => 'The new password must be different from the current password.',
            'new_password_confirmation.same' => 'The confirmation password does not match.',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($this->current_password, $user->password)) {
            $this->dispatch('swal:error', 'The current password is incorrect.');
            return;
        }
 if($user->must_change_password === 1)
        {
             // Update the password
            $user->update([
                'password' => Hash::make($this->new_password),
                'must_change_password' => 0,
                'password_changed_at' => now()
            ]);
        }
        else
        {
        // Update the password
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);
    }
        // Clear form fields
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        $this->dispatch('swal:success', 'Password updated successfully.');
        // Show success message
    }

    public function render()
    {
       if (!app()->runningInConsole() && request()->is('change-account-password')) {
        return view('livewire.change-password')
            ->layout('components.layouts.custom-change-password-layout');
    }

        return view('livewire.change-password');
    }
}
