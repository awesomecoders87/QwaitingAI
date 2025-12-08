<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Counter;
use App\Models\Location;
use App\Models\ActivityLog;
use Livewire\Attributes\Validate;
use Auth;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;

class AddCounter extends Component
{
    #[Title('Add Counter')]

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|array')]
    public $counter_locations = [];

    public $show_on_display = false; // Default to false if not checked
    public $team_id;
    public $allLocations;
    public $userAuth;
    public $locationId;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Counter Add')) {
            abort(403);
        }
        $this->team_id = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();
        $this->show_on_display= true;

        // $this->allLocations = Location::where('team_id', $this->team_id)->select('id', 'location_name')->get();
        if(Auth::user()->is_admin == 1){
            $this->allLocations = Location::where('team_id', tenant('id'))
            ->where('status',1)
            ->select('location_name', 'id')
            ->get();
        }else{
            $this->allLocations = Location::where('team_id', tenant('id'))
            ->where('id', Auth::user()?->locations)
            ->where('status',1)
            ->select('location_name', 'id')
            ->get();
        }
    }

    public function save()
    {

        $this->validate();

        // dd([
        //     'team_id' => $this->team_id,
        //     'name' => $this->name,
        //     'show_checkbox' => $this->show_on_display,
        //     'counter_locations' => $this->counter_locations, // Store as JSON
        // ]);
        Counter::create([
            'team_id' => $this->team_id,
            'name' => $this->name,
            'show_checkbox' => $this->show_on_display,
            'counter_locations' => $this->counter_locations, // Store as JSON
        ]);

        ActivityLog::storeLog($this->team_id, $this->userAuth->id, null, null, ActivityLog::ADD, $this->locationId, ActivityLog::COUNTER, null, $this->userAuth);

        session()->flash('message', 'Counter created successfully!');
        $this->reset(); // Clear form after save
  $this->dispatch('created');
        // return redirect('counters');
    }

    public function render()
    {
        return view('livewire.add-counter', ['allLocations' => $this->allLocations]);
    }
}
