<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Auth;

class EditRole extends Component
{
    #[Title('Edit Role')]

    public $roleId;
    public $locationId;
    public $name;
    public $permissions = [];
    public $allPermissions = [];
    public $selectAll = false;

    public function mount($id)
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Role Edit')) {
            abort(403);
        }

        $role = Role::findOrFail($id);
        $this->locationId = $role->location_id ?? Session::get('selectedLocation');
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('id')->toArray();
        $this->allPermissions = Permission::whereNotNull('team_id')->pluck('name', 'id')->toArray();
         $this->selectAll = count($this->permissions) === count($this->allPermissions);
    }

    public function updatedSelectAll($value)
    {
        $this->permissions = $value ? array_keys($this->allPermissions) : [];
    }

    public function updatedPermissions()
    {
        $this->selectAll = count($this->permissions) === count($this->allPermissions);
    }

    protected function rules()
   {
    return [
        'name' => [
            'required',
            'string',
            'max:50',
            Rule::unique('roles')
                ->where(function ($query) {
                    return $query->where('team_id', tenant('id'))->where('location_id',$this->locationId);
                })
                ->ignore($this->roleId), // Ignore current role during update
        ],
        'permissions' => 'array',
    ];
}

    public function update()
    {
        $this->validate();

        $role = Role::findOrFail($this->roleId);
        $role->update([
            'name' => strtolower($this->name),
        ]);

        $role->permissions()->sync($this->permissions);

        session()->flash('success', 'Role updated successfully.');
        return redirect()->route('tenant.roles');
    }

    public function render()
    {
        return view('livewire.edit-role', );
    }
}
