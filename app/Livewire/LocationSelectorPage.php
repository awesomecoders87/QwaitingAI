<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Location;
use App\Models\SiteDetail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Config;

#[Layout('components.layouts.custom-display-layout')]
class LocationSelectorPage extends Component
{
    #[Title('Location Select Page')]

    public $teamId;
    public $selectedLocationUser;
    public $allLocations = [];
	public $waitingTimes = [];
	
    public function mount()
    {
        $this->teamId = tenant('id');
        $user = Auth::user();

        // All available locations for user
        $locations = User::getSelectLocations();
        $locationIds = array_keys($locations);

        if (empty($locationIds)) {
            abort(403, 'No locations available.');
        }

        // Base query for locations
        $query = Location::select('id', 'location_name', 'address', 'location_image')
            ->where('team_id', $this->teamId)
            ->where('status', 1);

        // Restrict locations if user is not admin
        if ($user && !$user->hasRole('Admin')) {
            $userLocationIds = is_array($user->locations) ? $user->locations : [];
            $activeUserLocations = array_intersect($locationIds, $userLocationIds);

            if (empty($activeUserLocations)) {
                abort(403, 'No active locations assigned to your account.');
            }

            $query->whereIn('id', $activeUserLocations);
        }

        $this->allLocations = $query->with('siteDetail')->get();

        if ($this->allLocations->isEmpty()) {
            abort(403, 'No active locations found.');
        }

        // ✅ If only one location, set session automatically
        if ($this->allLocations->count() == 1) {

            $locationId = $this->allLocations->first()->id;
            Session::put('selectedLocation', $locationId);
           return redirect()->route('tenant.dashboard');
        }
    }

    public function selectLocation($locationId)
    {
        Session::put('selectedLocation', $locationId);
         $locationId = Session::get('selectedLocation');
                $siteDetails = SiteDetail::where('location_id', $locationId)->first();
                if ($siteDetails && $siteDetails->select_timezone) {
                    $timezone = $siteDetails->select_timezone;
                    Session::put('timezone_set', $timezone);
                }
                Config::set('app.timezone', $timezone);
                date_default_timezone_set($timezone);

        return redirect()->route('tenant.dashboard');
    }

    public function render()
    {
        return view('livewire.location-selector-page');
    }
}
