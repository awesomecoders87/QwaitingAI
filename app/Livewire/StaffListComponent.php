<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use App\Models\{User, Role, Team, Category, Location, AccountSetting, Counter};
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Auth;
use Livewire\Attributes\Title;

class StaffListComponent extends Component
{
    use WithPagination;
    #[Title('Staff List')]

    public $teamId;
    public $locationId;
    public $selectedStaff;
    // public $oldPassword;
    public $newPassword;
    public $confirmPassword;
    public $showPasswordModal = false;
    public $showDeleteConfirm = false;
    public $search = '';
    public $closed_by = [];
    public $selectedMultipleStaff = [];
    public $selectAll = false;



    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Staff Read')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
    }

    public function openPasswordModal($staffId)
    {

        $this->selectedStaff = User::find($staffId);
        // $this->oldPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->showPasswordModal = true;
    }

    public function updatePassword()
    {
        $this->validate([
            // 'oldPassword' => 'required',
            'newPassword' => 'required|min:6|different:oldPassword',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        // if (!Hash::check($this->oldPassword, $this->selectedStaff->password)) {
        //     throw ValidationException::withMessages(['oldPassword' => 'Old password is incorrect.']);
        // }

        $this->selectedStaff->update([
            'password' => Hash::make($this->newPassword),
        ]);

        session()->flash('message', 'Password updated successfully.');
        $this->dispatch('updated');
        $this->showPasswordModal = false;
    }

    public function deleteStaff($staffId)
    {
        $this->selectedStaff = User::find($staffId);
        // $this->showDeleteConfirm = true;
        $this->dispatch('confirm-delete');
    }

    #[On('confirmed-delete')]
    public function confirmDelete()
    {
        if ($this->selectedStaff) {
            $this->selectedStaff->delete();
            $this->selectedStaff=null;
            // session()->flash('message', 'Staff deleted successfully.');
            $this->dispatch('deleted');
        }

        // $this->showDeleteConfirm = false;
    }

    #[On('bulkDeleteStaff')]
    public function updatedSelectAll($ids)
    {

        if ($ids) {
            $this->selectedMultipleStaff = $ids;
        } else {
            $this->selectedMultipleStaff = [];
        }
        $this->dispatch('confirm-multiple-delete');
    }

#[On('confirmed-multiple-delete')]
    public function bulkDeleteStaff()
    {

        if (!empty($this->selectedMultipleStaff)) {
            User::whereIn('id', $this->selectedMultipleStaff)->delete();
            $this->selectedMultipleStaff = [];
            $this->selectAll = false;
            // session()->flash('message', 'Selected staff members deleted successfully.');
            $this->dispatch('deleted');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination when search is updated
    }

    public function render()
    {
        $staffs = User::where('team_id', $this->teamId)
            //  ->where(function ($query) {
            //     $query->whereJsonContains('locations', "$this->locationId")
            //         ->orWhereRaw("JSON_LENGTH(locations) = 0")
            //         ->orWhereNull('locations'); // strict empty []
            // })
            ->when(auth()->user()->is_admin != 1, function ($query) {
            // Apply location filter only for non-admin users
                $query->where(function ($q) {
                    $q->whereJsonContains('locations', "$this->locationId")
                    ->orWhereRaw("JSON_LENGTH(locations) = 0")
                    ->orWhereNull('locations'); // strict empty []
                });
            })
            ->whereNot('id', auth()->user()->id)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'superadmin');
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->closed_by, function ($query) {
                $query->where(function ($q) {
                    $q->where('id',  $this->closed_by);
                });
            })
            ->paginate(10); // Pagination should be used here

            $users = User::where(function ($query) {
                $query->where('team_id', $this->teamId)
                      ->where('id','!=',Auth::id());
            })
            ->whereNotNull('locations')
            ->whereJsonContains('locations', "$this->locationId")
            ->pluck('name', 'id');

        return view('livewire.staff-list-component', compact('staffs','users'));
    }
}
