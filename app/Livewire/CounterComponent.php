<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Counter;
use App\Models\User;
use App\Models\Location;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Auth;

class CounterComponent extends Component
{
    use WithPagination;

    #[Title('Counter List')]

    public $team_id;
    public $locationId;
    public $search = '';
    public $selectedId = null;
    public $selectedMultiple= [];
    public $selectAll = false;

    public function mount()
    {
        $user = Auth::user();

        if (!$user->hasPermissionTo('Counter Read')) {
            abort(403);
        }
        
        $this->team_id = tenant('id');
     
        $this->locationId = Session::get('selectedLocation');
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination when searching
    }

    public function delete($id)
    {
        $this->selectedId = $id;
        // $this->showDeleteConfirm = true;
        $this->dispatch('confirm-delete');
    }

    #[On('confirmed-delete')]
    public function confirmDelete()
    {
    
        if($this->selectedId){
            Counter::where('id', $this->selectedId)->delete();
            User::where('counter_id',$this->selectedId)->update(['counter_id' =>null]);

                  // Remove deleted counter from assign_counters
        $users = User::whereJsonContains('assign_counters', (string)$this->selectedId)->get();

        foreach ($users as $user) {
            $updatedCounters = array_filter(
                array_diff($user->assign_counters ?? [], [(string)$this->selectedId])
            );
            $user->assign_counters = array_values($updatedCounters); // reindex
            $user->save();
        }

            $this->selectedId = null;
            $this->dispatch('deleted');
        }
    }

    #[On('bulkDelete')]
    public function updatedSelectAll($ids)
    {
        if ($ids) {
            $this->selectedMultiple = $ids;
        } else {
            $this->selectedMultiple = [];
        }
        $this->dispatch('confirm-multiple-delete');
    }

#[On('confirmed-multiple-delete')]
    public function bulkDeleteStaff()
    {

        if (!empty($this->selectedMultiple)) {
            Counter::whereIn('id', $this->selectedMultiple)->delete();
            User::whereIn('counter_id',$this->selectedMultiple)->update(['counter_id' =>null]);

             // Remove deleted counter IDs from assign_counters
        $users = User::where(function ($query) {
            foreach ($this->selectedMultiple as $counterId) {
                $query->orWhereJsonContains('assign_counters', (string) $counterId);
            }
        })->get();

        foreach ($users as $user) {
            $updatedCounters = array_diff($user->assign_counters ?? [], array_map('strval', $this->selectedMultiple));
            $user->assign_counters = array_values($updatedCounters); // reindex
            $user->save();
        }

            $this->selectedMultiple = [];
            $this->selectAll = false;
            // session()->flash('message', 'Selected staff members deleted successfully.');
            $this->dispatch('deleted');
        }
    }
    public function render()
    {
     
        $counters = Counter::where('team_id',$this->team_id)
        ->whereJsonContains('counter_locations', "$this->locationId")
        ->when($this->search,function($q){
            $q->where('name', 'like', '%' . $this->search . '%');
        })
        ->paginate(10);
      
        return view('livewire.counter-component', [
            'counters' => $counters,
        ]);
    }
}
