<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Location;
use App\Models\ActivityLog;
use App\Models\LocationsGrouping;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class LocationGrouping extends Component
{
    public $groups;
    public $name, $description, $selectedLocations = [], $groupId;
    public $showModal = false;
    public $teamId;
    public $selectedId;

    public function mount()
    {
        $this->teamId = auth()->user()->team_id ?? tenant('id');
        $this->loadGroups();
    }

    public function loadGroups()
    {
        $this->groups = LocationsGrouping::with('locations')
            ->where('team_id', $this->teamId)
            ->get();
    }

    public function create()
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $group = LocationsGrouping::with('locations')->findOrFail($id);
        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->description = $group->description;
        $this->selectedLocations = $group->locations->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $group = LocationsGrouping::updateOrCreate(
            ['id' => $this->groupId],
            ['team_id' => $this->teamId, 'name' => $this->name, 'description' => $this->description]
        );

        $group->locations()->sync($this->selectedLocations);

        $this->showModal = false;
        $this->resetInput();
        $this->loadGroups();
         $this->dispatch('create');
    }

        public function deleteconfirmation($id)
    {
        $this->selectedId = $id;

        $this->dispatch('confirmation-delete');
        // $this->resetPage();
    }

     #[On('confirmed-delete')]
    public function delete($id)
    {
        LocationsGrouping::findOrFail($id)->delete();
          // $this->resetPage();
        $this->reset('selectedId');

        // Dispatch event to notify frontend
        $this->dispatch('deleted');
        $this->loadGroups();
    }

    private function resetInput()
    {
        $this->groupId = null;
        $this->name = '';
        $this->description = '';
        $this->selectedLocations = [];
    }

    public function render()
    {
        return view('livewire.location-grouping', [
            'locations' => Location::where('team_id', $this->teamId)->get()
        ]);
    }
}
