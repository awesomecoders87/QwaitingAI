<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BreakReason as BreakReasonModel;
use App\Models\ActivityLog;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Auth;

class BreakReason extends Component
{
     use WithPagination;

    #[Title('Break Reason')]

    public $team_id;
    public $locationId;
    public $search = '';
    public $selectedId = null;
    public $selectedMultiple= [];
    public $selectAll = false;
    public $userAuth;

     public function mount()
    {
        // $user = Auth::user();
        // if (!$user->hasPermissionTo('Counter Read')) {
        //     abort(403);
        // }
        $this->userAuth = Auth::user();
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
            BreakReasonModel::where('id', $this->selectedId)->delete();

            $this->selectedId = null;
             ActivityLog::storeLog($this->team_id, $this->userAuth->id, null, null, 'Break Delete', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
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
            BreakReasonModel::whereIn('id', $this->selectedMultiple)->delete();

            $this->selectedMultiple = [];
            $this->selectAll = false;
             ActivityLog::storeLog($this->team_id, $this->userAuth->id, null, null, 'Break Delete', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
            $this->dispatch('deleted');
        }
    }

    public function render()
    {
          $reasons = BreakReasonModel::where('team_id',$this->team_id)
        ->whereJsonContains('break_location', "$this->locationId")
        ->when($this->search,function($q){
            $q->where('reason', 'like', '%' . $this->search . '%');
        })
        ->paginate(10);

        return view('livewire.break-reason', [
            'reasons' => $reasons,
        ]);
    }
}
