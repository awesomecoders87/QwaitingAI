<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EditLocation extends Component
{
    use WithFileUploads;

    #[Title('Edit Location')]

    public $location_id, $team_id, $location_name, $address, $city, $state, $country, $zip, $latitude, $longitude, $ip_address, $status;
     public $location_image; // New image upload
    public $existing_image; // Store current image

    public function mount(Location $location)
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Location Add')) {
            abort(403);
        }
        
        $this->location_id = $location->id;
        $this->team_id = $location->team_id;
        $this->location_name = $location->location_name;
        $this->address = $location->address;
        $this->city = $location->city;
        $this->state = $location->state;
        $this->country = $location->country;
        $this->zip = $location->zip;
        $this->latitude = $location->latitude;
        $this->longitude = $location->longitude;
        $this->ip_address = $location->ip_address;
        $this->status = $location->status;
        $this->existing_image = $location->location_image; 
    }

    public function updateLocation()
    {
        $this->validate([
            'location_name' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
             'location_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $location = Location::find($this->location_id);
        if (!$location) {
            session()->flash('error', 'Location not found.');
            return;
        }

        if ($this->location_image) {
            // Delete old image if exists
            if ($location->location_image && Storage::disk('public')->exists($location->location_image)) {
                Storage::disk('public')->delete($location->location_image);
            }

            $imagePath = $this->location_image->store('location_images', 'public');
        } else {
            $imagePath = $location->location_image;
        }


        $location->update([
            'location_name' => $this->location_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip' => $this->zip,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ip_address' => $this->ip_address,
            'status' => (bool) $this->status,
             'location_image' => $imagePath,
        ]);

        // session()->flash('message', 'Location updated successfully.');

        $this->dispatch('updated');
    }

    public function render()
    {
        return view('livewire.edit-location');
    }
}
