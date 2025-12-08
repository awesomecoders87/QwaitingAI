<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Auth;

class CreateRole extends Component
{
    #[Title('Role Create')]

    public $name;
    public $teamId;
    public $locationId;
    public $permissions = [];
    public $allPermissions = [];
    public $selectAll = false;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles')->where(function ($query) {
                    return $query->where('team_id', $this->teamId)->where( 'location_id',$this->locationId,);
                })
            ],
            'permissions' => 'array',
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Role Add')) {
            abort(403);
        }

        $this->teamId = tenant('id'); // Ensure `tenant('id')` returns a valid team ID.
        $this->locationId = Session::get('selectedLocation');
       
        $this->allPermissions = Permission::whereNotNull('team_id')->pluck('name', 'id')->toArray();
    }

    public function toggleSelectAll()
{
    if ($this->selectAll) {
        $this->permissions = [];
        $this->selectAll = false;
    } else {
        $this->permissions = array_keys($this->allPermissions);
        $this->selectAll = true;
    }
}

public function updatedPermissions()
{
    // Automatically check/uncheck "selectAll" based on selection count
    $this->selectAll = count($this->permissions) === count($this->allPermissions);
}

    // public function updatedSelectAll($value)
    // {
    //     // If selected, assign all permissions; otherwise, clear selection
    //     $this->permissions = $value ? array_keys($this->allPermissions) : [];
    // }

    public function save()
    {
        $this->validate();

        $role = Role::firstOrCreate([
            'name' => strtolower($this->name),
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            // 'guard_name' => 'web',
        ]);

        $role->permissions()->sync($this->permissions);

        session()->flash('success', 'Role created successfully.');
        return redirect()->route('tenant.roles');
    }

    public function render()
    {
        return view('livewire.create-role', [
            'allPermissions' => Permission::whereNotNull('team_id')->pluck('name', 'id'),
        ]);
    }
}
