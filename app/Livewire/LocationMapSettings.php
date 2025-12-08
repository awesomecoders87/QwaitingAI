<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\SiteDetail;

class LocationMapSettings extends Component
{
    #[Title('Location Map Settings')]

    public $teamId;
    public $locationId;
    public $google_map_key = '';
    public $siteDetail;
    public $userAuth;

    protected function rules(): array
    {
        return [
            'google_map_key' => 'nullable|string|max:255',
        ];
    }

    public function mount(): void
    {
        $this->userAuth = Auth::user();
        if (!$this->userAuth || !$this->userAuth->hasPermissionTo('Location')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');

        $this->siteDetail = SiteDetail::getMyDetails($this->teamId, $this->locationId);
        $this->google_map_key = $this->siteDetail->google_map_key ?? '';
    }

    public function save(): void
    {
        $this->validate();

        SiteDetail::updateOrCreate(
            [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
            ],
            [
                'google_map_key' => $this->google_map_key,
            ]
        );

        session()->flash('success', 'Google Map API key updated successfully.');
        $this->dispatch('updated');
    }

    public function render()
    {
        return view('livewire.location-map-settings');
    }
}
