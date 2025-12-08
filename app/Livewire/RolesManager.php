<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Role;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class RolesManager extends Component
{
    use WithPagination;
    #[Title('Role')]
    
    public $teamId;
    public $locationId;
    public $search = '';
    public $selectedId = '';

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Role Read')) {
            abort(403);
        }
        

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
      
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteconfirmation($id)
    {
       $this->selectedId = $id;
       $this->dispatch('confirm-delete');
    }

    #[On('confirmed-delete')]
    public function delete()
    {
        if ($this->selectedId) {
            Role::findOrFail($this->selectedId)->delete();
            $this->dispatch('deleted');
        }

      
    }

    public function render()
    {
        $roles = Role::where('team_id',$this->teamId)->where('location_id',$this->locationId)
            ->where('name', 'like', "%{$this->search}%")
            ->paginate(10);

        return view('livewire.roles-manager', compact('roles'));
    }
}
