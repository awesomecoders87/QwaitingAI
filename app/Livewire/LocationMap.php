<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use App\Models\SiteDetail;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LocationMap extends Component
{
    #[Title('Location Map')]
    public $locations = [];
    public $googleMapKey;

    public function mount()
    {
        $user = Auth::user();
        if (!$user || !$user->hasPermissionTo('Location')) {
            abort(403);
        }

        $teamId = tenant('id');
        $locationId = Session::get('selectedLocation');

        $this->locations = Location::where('team_id', $teamId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', true)
            ->get(['id', 'location_name', 'address', 'city', 'state', 'country', 'latitude', 'longitude']);

        $siteDetail = SiteDetail::getMyDetails($teamId, $locationId);
        $this->googleMapKey = $siteDetail->google_map_key ?? env('GOOGLE_PLACES_API_KEY');
    }

    public function render()
    {
        return view('livewire.location-map');
    }
}
