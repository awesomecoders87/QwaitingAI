<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SmtpDetails;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class EmailSettings extends Component
{
    #[Title('Email Setting')]

    public $from_name;
    public $from_email;
    public $hostname;
    public $port;
    public $username;
    public $password;
    public $encryption;
    public $teamId;
    public $locationId;
    public $successMessage = null; // For alert handling

    public function mount()
    {

        $user = Auth::user();
        if (!$user->hasPermissionTo('Message Template Edit')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $siteDetail = SmtpDetails::where('team_id', $this->teamId)->where('location_id',$this->locationId)->first();

        if ($siteDetail) {
            $this->from_name = $siteDetail->from_name;
            $this->from_email = $siteDetail->from_email;
            $this->hostname = $siteDetail->hostname;
            $this->port = $siteDetail->port;
            $this->username = $siteDetail->username;
            $this->password = $siteDetail->password;
            $this->encryption = $siteDetail->encryption;
        }
    }

    public function save()
    {
        $this->validate([
            'from_name' => 'required|string|max:50',
            'from_email' => 'required|email|max:50',
            'hostname' => 'required|string|max:100',
            'port' => 'required|numeric|max:65535',
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
            'encryption' => 'nullable|string|max:10',
        ]);

        SmtpDetails::updateOrCreate(
            ['team_id' => $this->teamId,'location_id'=>$this->locationId],
            [
                'from_name' => $this->from_name,
                'from_email' => $this->from_email,
                'hostname' => $this->hostname,
                'port' => $this->port,
                'username' => $this->username,
                'password' => $this->password,
                'encryption' => $this->encryption,
            ]
        );

        // Save settings logic...
        session()->flash('success', 'Settings Updated Successfully.');
        
        // Set success message property
        $this->successMessage = 'Settings Updated Successfully.';

        // Dispatch event to hide the alert after timeout
        $this->dispatch('hide-alert');
    }

    public function render()
    {
        return view('livewire.email-settings');
    }
}
