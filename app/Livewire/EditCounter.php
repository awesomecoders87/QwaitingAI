<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Counter;
use App\Models\Location;
use Livewire\Attributes\Validate;
use Auth;
use Livewire\Attributes\Title;

class EditCounter extends Component
{
    #[Title('Edit Counter')]

    public $counter_id;

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|array|min:1')]
    public $counter_locations = [];

    public $show_on_display = false;
    public $team_id;
    public $allLocations;

    public function mount($counterId)
    {

        $user = Auth::user();
        if (!$user->hasPermissionTo('Counter Edit')) {
            abort(403);
        }
        $this->counter_id = $counterId;
        $this->team_id = tenant('id');
        $this->allLocations = Location::where('team_id', $this->team_id)->select('id', 'location_name')->get();
        if(Auth::user()->is_admin == 1){
            $this->allLocations = Location::where('team_id', tenant('id'))
            ->where('status',1)
            ->select('location_name', 'id')
            ->get();
        }else{
            $this->allLocations = Location::where('team_id', tenant('id'))
            ->where('status',1)
            ->where('id', Auth::user()?->locations)
            ->select('location_name', 'id')
            ->get();
        }
        // Load the existing counter data
        $counter = Counter::findOrFail($this->counter_id);
        $this->name = $counter->name;
        $this->show_on_display = $counter->show_checkbox;
        $this->counter_locations = $counter->counter_locations ?? [];

    }

    public function update()
    {
        $this->validate();

        Counter::where('id', $this->counter_id)->update([
            'name' => $this->name,
            'show_checkbox' => $this->show_on_display,
            'counter_locations' =>$this->counter_locations, // Store as JSON
        ]);

        session()->flash('message', 'Counter updated successfully!');

        return redirect('counters');
    }

    public function render()
    {
        return view('livewire.edit-counter', ['allLocations' => $this->allLocations]);
    }
}
