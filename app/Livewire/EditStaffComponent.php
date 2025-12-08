<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use App\Models\{User, Role, Team, Category, Location, Counter,Domain,SiteDetail};
use Auth;
use Livewire\Attributes\Title;

class EditStaffComponent extends Component
{
    #[Title('Edit Staff')]

    public $staffId;
    public $teamId;
    public $locationId;
    public $name;
    public $email;
    public $phone;
    public $username;
    public $password;
    public $password_confirmation;
    public $locations = [];
    public $address;
    public $role;
    public $unique_id;
    public $counter_id;
    public $enable_desktop_notification = false;
    public $show_next_button = true;
    public $enable_hold_queue = false;
    public $hold_queue_feature = false;
    public $categories = [];
    public $selectedAssignCounters = [];
    public $selectedCategories = [];
    public $allLocations = [];
    public $level;
    public $parent_id;
    public $priority;
    public $enablePriority = true;
    public $staffList = [];
    public $allCounters = [];
    public $selectAllCounters = false;
     public $selectAll = false;

    public function mount($staffId)
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Staff Edit')) {
            abort(403);
        }

        $this->staffId = $staffId;
        $staff = User::with('categories')->findOrFail($staffId);

        $this->teamId = $staff->team_id ?? tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->name = $staff->name;
        $this->email = $staff->email;
        $this->phone = $staff->phone;
        $this->username = $staff->username;
        $this->role = $staff->role_id;
        $this->counter_id = $staff->counter_id;
        $this->unique_id = $staff->unique_id;
        $this->locations = $staff->locations ?? [];
        $this->enable_desktop_notification = $staff->enable_desktop_notification;
        $this->enable_hold_queue = $staff->enable_hold_queue;
        $this->show_next_button = $staff->show_next_button;
        $this->address = $staff->address;
        $this->selectedAssignCounters = $staff->assign_counters ?? [];
        $this->level = $staff->level_id ?? 1;
        $this->parent_id = $staff->parent_id ?? '';
        $this->priority = $staff->priority ?? 0;
        $this->selectedCategories = $staff->categories->pluck('id')->toArray();

        $this->enablePriority = true;

        if($this->level == 2){

            $this->staffList = User::where('team_id',$this->teamId)
            ->where('level_id',1)
            ->where('id','!=',$this->staffId)
            ->whereNotNull('locations')
            ->where('locations', '!=', '')
            ->whereRaw("JSON_VALID(locations)")
            ->whereJsonContains('locations', (string) $this->locationId)
            ->orWhereJsonContains('locations', (int) $this->locationId)
            ->select('id','name')
            ->get();

        }elseif($this->level == 3){

            $this->staffList = User::where('team_id',$this->teamId)
            ->where('level_id',2)
            ->where('id','!=',$this->staffId)
            ->whereNotNull('locations')
           ->where('locations', '!=', '')
           ->whereRaw("JSON_VALID(locations)")
           ->whereJsonContains('locations', (string) $this->locationId)
           ->orWhereJsonContains('locations', (int) $this->locationId)
           ->select('id','name')
           ->get();

        }



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

               $domain = Domain::where('team_id',$this->teamId)->first();
               $this->hold_queue_feature = $domain['hold_queue_feature'] == 1 ? true : false;

                $this->enablePriority = SiteDetail::where('team_id', $this->teamId)->where('location_id',$this->locationId)->value('use_staff_priority') ?? false;
                $this->allCounters = Counter::where('team_id',$this->teamId)->whereJsonContains('counter_locations', "$this->locationId")->where('show_checkbox',1)->get();
                $this->selectAllCounters = count($this->selectedAssignCounters) == $this->allCounters->count();

                $this->categories = Category::where('team_id', $this->teamId)
            ->whereJsonContains('category_locations', "$this->locationId")
            ->select('id', 'parent_id', 'level_id', 'name')
            ->get();

             $this->selectAll = count($this->selectedCategories) == $this->categories->count();

            }

    public function updatedLevel($value){
        $this->level =$value;
        if($this->level == 2){
          $this->staffList = User::where('team_id',$this->teamId)
            ->where('level_id',1)
            ->where('id','!=',$this->staffId)
            ->whereNotNull('locations')
           ->where('locations', '!=', '')
           ->whereRaw("JSON_VALID(locations)")
           ->whereJsonContains('locations', (string) $this->locationId)
           ->orWhereJsonContains('locations', (int) $this->locationId)
           ->select('id','name')
           ->get();
        }if($this->level == 3){
           $this->staffList = User::where('team_id',$this->teamId)
            ->where('level_id',2)
            ->where('id','!=',$this->staffId)
            ->whereNotNull('locations')
           ->where('locations', '!=', '')
           ->whereRaw("JSON_VALID(locations)")
           ->whereJsonContains('locations', (string) $this->locationId)
           ->orWhereJsonContains('locations', (int) $this->locationId)
           ->select('id','name')
           ->get();
        }
    }

    protected function rules()
    {
        return [
        //    'name' => ['required', 'regex:/^[a-zA-Z\s]+$/', 'max:255'],
           'name' => ['required',  'regex:/^[\p{L}\p{M}\s]+$/u', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users')->where(fn ($query) =>
                    $query->where('team_id', $this->teamId)->whereNull('deleted_at')
                )->ignore($this->staffId) // Ignore current staff being edited
            ],
            'phone' => ['nullable', 'regex:/^[0-9]{7,15}$/'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->where(fn ($query) =>
                    $query->where('team_id', $this->teamId)->whereNull('deleted_at')
                )->ignore($this->staffId)
            ],
            // 'selectedAssignCounters' => 'nullable|array|min:1',
            'role' => 'required|exists:roles,id',
            'counter_id' => 'required|exists:counters,id',
            'address' => 'nullable|string|max:500',
            'unique_id' => 'nullable|string|max:50',
            'locations' => 'required|array',
            'selectedCategories' => 'nullable|array|exists:categories,id',
            'level' => ['nullable', 'integer', 'min:1'],
            'priority' => 'nullable|integer|min:0',

             // Conditional rule: parent_id required if level > 1
            'parent_id' => [
                Rule::requiredIf(fn () => $this->level > 1),
                'nullable', // for level 1
                'exists:users,id',
            ],
        ];
    }

    public function updateStaff()
    {

        $validatedData = $this->validate($this->rules());
        $staff = User::findOrFail($this->staffId);

        if($this->role == 1){
            $isAdmin = 1;
         }else{
            $isAdmin = 0;
         }

        $staff->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'username' => $this->username,
            'role_id' => $this->role,
            'counter_id' => $this->counter_id,
            'unique_id' => $this->unique_id,
            'locations' => $this->locations ?? [],
            'assign_counters' => $this->selectedAssignCounters ?? [],
            'is_admin' => $isAdmin,
            'enable_desktop_notification' => $this->enable_desktop_notification ? 1 : 0,
            'enable_hold_queue' => $this->enable_hold_queue ? 1 : 0,
            'show_next_button' => $this->show_next_button ? 1 : 0,
            'address' => $this->address,
            'level_id' => !empty($this->level) ? $this->level : 1,
            'parent_id' => $this->parent_id ?? '',
            'priority' => $this->priority ?? 0,
        ]);

          // 🔐 Sync Spatie role by name
            $role = Role::find($this->role);
            if ($role) {
                $staff->syncRoles($role); // replaces previous role
            }

        // if (!empty($this->selectedCategories)) {
        //     $staff->categories()->sync($this->selectedCategories);
        // }
       $staff->categories()->sync($this->selectedCategories ?? []);
        session()->flash('message', 'Staff member updated successfully.');

        $this->dispatch('updated', '/staff');
    }

    private function buildCategoryTree($categories, $parentId = null)
    {
        return $categories->where('parent_id', $parentId)->map(function ($category) use ($categories) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $this->buildCategoryTree($categories, $category->id) // Recursive call
            ];
        });
    }

    public function updatedSelectedAssignCounters()
{
    // Automatically toggle "Select All" if all counters are selected
    $this->selectAllCounters = count($this->selectedAssignCounters) == $this->allCounters->count();
}

public function toggleSelectAllCounters()
{
    if ($this->selectAllCounters) {
        // Select all counters
        $this->selectedAssignCounters = $this->allCounters->pluck('id')->toArray();
    } else {
        // Deselect all counters
        $this->selectedAssignCounters = [];
    }
}


    public function updatedSelectedCategories()
{
    $this->selectAll = count($this->selectedCategories) == $this->categories->count();
}

  public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedCategories = Category::where('team_id', $this->teamId)
                ->whereJsonContains('category_locations', "$this->locationId")
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedCategories = [];
        }
    }

    public function render()
    {

       $allRoles = Role::where(function ($query) {
            $query->where('team_id', $this->teamId)
                  ->orWhere('id', 1);
        })
        ->where('location_id', $this->locationId)
        ->select('name', 'id')
        ->get();


      

        // Structure categories into hierarchy
        $structuredCategories = $this->buildCategoryTree($this->categories);
 
        return view('livewire.edit-staff-component', [
            'allRoles' => $allRoles,
            'getcategories' => $structuredCategories,
        ]);


    }
}
