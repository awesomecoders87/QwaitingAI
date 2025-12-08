<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Location;
use App\Models\Queue;
use App\Models\SmtpDetails;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Config;

class LocationSelector extends Component
{
    // #[Title('Location Selector')]

    public $selectedLocationUser;
    public $teamId;



    public function mount()
    {
        $this->teamId =tenant('id');
        $user = Auth::user();
        $locations = User::getSelectLocations(); // This should return key => value of active locations
        
        // Check if user has no active locations
        if ($user && !$user->hasRole('Admin')) {
            $userLocationKeys = is_array($user->locations) ? $user->locations : [];

            // Intersect user locations with active ones
            $activeUserLocations = array_intersect(array_keys($locations), $userLocationKeys);

            if (empty($activeUserLocations)) {
                abort(403, 'No active locations assigned to your account.');
            }
        }

       
      if (!Session::has('selectedLocation') || Session::get('selectedLocation') == "") {
        
           Session::put('selectedLocation', User::getDefaultLocation()); 
            $this->selectedLocationUser =User::getDefaultLocation();
           
        }else{

            $this->selectedLocationUser = Session::get('selectedLocation');
        }

        Location::setConfig($this->teamId, $this->selectedLocationUser);

        // Set the initial value of $selectedOption from session
    }
  

    public function updatedselectedLocationUser($value)
    {
        Session::put('selectedLocation', $value);
        $this->selectedLocationUser =$value;
        Location::setConfig($this->teamId, $this->selectedLocationUser);
        $this->dispatch('locationUpdated', $value);
  
    }

    // protected function setConfig(){

    //     Queue::timezoneSet();

    //      $timezone = Session::get('timezone_set') ?? 'UTC';

    //       Config::set('app.timezone', $timezone);
    //     date_default_timezone_set($timezone);

    //    $details = SmtpDetails::where('team_id', $this->teamId)->where('location_id',$this->selectedLocationUser)->first();
    //     if(!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)){
    //                 Config::set('mail.mailers.smtp.transport', 'smtp');
    //                 Config::set('mail.mailers.smtp.host', trim($details->hostname));
    //                 Config::set('mail.mailers.smtp.port', trim($details->port));
    //                 Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
    //                 Config::set('mail.mailers.smtp.username', trim($details->username));
    //                 Config::set('mail.mailers.smtp.password', trim($details->password));
    //                 Config::set('mail.from.address', trim($details->from_email));
    //                 Config::set('mail.from.name', trim($details->from_name));
                       
    //     }



    // }

    #[On('locationUpdated')]
    public function handleLocationUpdated($value)
    {
        $this->selectedLocationUser = $value;
        $this->redirect(request()->header('Referer'));

    }
 
    public function render()
    {
   
        return view('livewire.location-selector', [
            'locations' => User::getSelectLocations(),
        ]);
    }
}

