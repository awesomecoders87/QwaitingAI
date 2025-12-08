<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;
use App\Models\Role;
use App\Models\Permission;


class AddLocation extends Component
{
    #[Title('Add Location')] 

    public $team_id, $location_name, $address, $city, $state, $country, $zip, $latitude, $longitude, $ip_address, $status,$location_image;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Location')) {
            abort(403);
        }
        $this->team_id = tenant('id');
        $this->ip_address = $this->getUserIpAddr();
        $this->status = true;
       
    }

    public function save()
    {
        $this->validate([
            'location_name' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;

    if ($this->location_image) {
        $imagePath = $this->location_image->store('location_images', 'public');
    }
    
         $location = Location::create([
            'team_id' => $this->team_id,
            'user_id' => Auth::user()->id,
            'location_name' => $this->location_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip' => $this->zip ?? '',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ip_address' => $this->ip_address,
            'status' => (bool) $this->status,
              'location_image' => $imagePath,
        ]);

        session()->flash('message', 'Location saved successfully.');
        $this->dispatch('created');
    }

    public static function getUserIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function render()
    {
        return view('livewire.add-location');
    }
}
