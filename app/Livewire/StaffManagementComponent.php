<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use App\Models\{User, Role, Team, Category, Location, AccountSetting, Counter,Domain, SiteDetail};
use Auth;
use Livewire\Attributes\Title;
use App\Models\Translation;


class StaffManagementComponent extends Component
{
    #[Title('staff')]

    // Public properties for form fields
    public $teamId;
    public $locationId;
    public $name;
    public $email;
    public $phone;
    public $username;
    public $password;
    public $password_confirmation;
    public $locations;
    public $address;
    public $role;
    public $unique_id;
    public $counter_id;
    public $enable_desktop_notification = false;
    public $show_next_button = true;
    public $enable_hold_queue = false;
    public $hold_queue_feature = false;
    public $categories = [];
    public $selectedCategories = [];
    public $selectedAssignCounters = [];
    public $allLocations = [];
    public $selectAll = false;
    public $translations;
    public $language;
    public $level;
    public $parent_id;
    public $priority;
    public $enablePriority = true;
    public $staffList = [];
    public $allCounters = [];
    public $selectAllCounters = false;

    // Mount method to initialize properties
    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Staff Add')) {
            abort(403);
        }
        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');

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

        $domain = Domain::where('team_id',$this->teamId)->first();
        $this->hold_queue_feature = $domain['hold_queue_feature'] == 1 ? true : false;

        $this->language = session('app_locale');

        $this->translations = Translation::where('team_id', $this->teamId)
            ->get()
            ->groupBy('name') // Group by category name
            ->map(function ($items) {
                return $items->pluck('value', 'language'); // ['es' => 'Categoría 1']
            })
            ->toArray();

        $this->level == 1;

        $this->enablePriority = SiteDetail::where('team_id', $this->teamId)->where('location_id',$this->locationId)->value('use_staff_priority') ?? false;
         $this->allCounters = Counter::where('team_id',$this->teamId)->whereJsonContains('counter_locations', "$this->locationId")->where('show_checkbox',1)->get();

    }


        public function updatedLevel($value){
        $this->level =$value;
        if($this->level == 2){
          $this->staffList = User::where('team_id',$this->teamId)
            ->where('level_id',1)
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
            ->whereNotNull('locations')
           ->where('locations', '!=', '')
           ->whereRaw("JSON_VALID(locations)")
           ->whereJsonContains('locations', (string) $this->locationId)
           ->orWhereJsonContains('locations', (int) $this->locationId)
           ->select('id','name')
           ->get();
        }
    }

    // Validation rules for form submission
    protected function rules()
    {
        return [
            // 'name' => ['required', 'regex:/^[a-zA-Z\s]+$/', 'max:255'],
        'name' => ['required',  'regex:/^[\p{L}\p{M}\s]+$/u', 'max:255'],

            // 'email' => [
            //     'required',
            //     'email',
            //     'max:255',
            //     Rule::unique('users', 'email')
            //         ->where(function ($query) {
            //             $query->where('team_id', $this->teamId)
            //                 ->where(function ($q) {
            //                     foreach ($this->allLocations as $locationId) {
            //                         $q->orWhereJsonContains('locations', $locationId);
            //                     }
            //                 });
            //         }),
            // ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where('team_id', $this->teamId)->whereNull('deleted_at'),
            ],

            'phone' => ['nullable', 'regex:/^[0-9]{7,15}$/'],

           'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->where('team_id', $this->teamId)->whereNull('deleted_at'),
            ],
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id',
            'counter_id' => 'required|exists:counters,id',
            'address' => 'nullable|string|max:500',
            'unique_id' => 'nullable|string|max:50',
            'locations' => 'required|array',
            'categories' => 'nullable|array|exists:categories,id',
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

    // Custom validation messages
    protected $messages = [
        'name.required' => 'The name field is required.',
        'name.alpha' => 'The name must only contain letters.',
        'name.max' => 'The name may not be greater than 255 characters.',
        'email.required' => 'The email field is required.',
        'email.unique' => 'The email has already been taken.',
        'phone.required' => 'The phone number is required.',
        'phone.regex' => 'The phone number must be between 7 to 15 digits and contain only numbers.',
        'username.required' => 'The username field is required.',
        'username.unique' => 'The username has already been taken.',
        'password.required' => 'The password field is required.',
        'password.confirmed' => 'The password confirmation does not match.',
        'role.required' => 'The role field is required.',
        'locations.required' => 'The Location is required.',
        'counter_id.required' => 'The counter field is required.',
    ];

    // Form submission handler
    public function submitForm()
    {
        // Validate the form data
        $this->validate();

        $isAdmin = $this->role == 1 ? 1 : 0;

        if ($this->isDuplicateUser('email', $this->email)) {
            $this->addError('email', 'This email already exists in the selected locations.');
            return;
        }

        if ($this->isDuplicateUser('username', $this->username)) {
            $this->addError('username', 'This username already exists in the selected locations.');
            return;
        }



        // Create a new user
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'username' => $this->username,
            'password' => Hash::make($this->password),
            'role_id' => $this->role,
            'unique_id' => $this->unique_id,
            'counter_id' => $this->counter_id,
            'locations' => $this->locations ?? [],
            'assign_counters' => $this->selectedAssignCounters ?? [],
            'is_admin' => $isAdmin,
            'show_next_button' => $this->show_next_button ? 1 : 0,
            'enable_desktop_notification' => $this->enable_desktop_notification ? 1 : 0,
            'enable_hold_queue' => $this->enable_hold_queue ? 1 : 0,
            'team_id' => $this->teamId,
            'level_id' => !empty($this->level) ? $this->level : 1,
            'parent_id' => $this->parent_id ?? '',
            'priority' => $this->priority ?? 0,
        ]);

        // 🔐 Assign role using Spatie
        $role = Role::find($this->role);
        if ($role) {
            $user->assignRole($role); // must use role name
        }

        // Attach categories to the pivot table
        if (!empty($this->selectedCategories)) {
            $user->categories()->sync($this->selectedCategories);
        }

        // Flash a success message
        session()->flash('message', 'Staff member added successfully.');
         $this->dispatch('created', '/staff');

        // Reset the form fields
        $this->resetForm();
    }

    protected function isDuplicateUser($field, $value)
{
    return User::where($field, $value)
        ->where('team_id', $this->teamId)
        ->where(function ($q) {
            foreach ($this->allLocations as $locationId) {
                $q->orWhereJsonContains('locations', "$locationId");
            }
        })
        ->exists();
}

    // Reset form fields
    public function resetForm()
    {
        $this->reset([
            'name',
            'email',
            'phone',
            'username',
            'password',
            'password_confirmation',
            'role',
            'counter_id',
            'address',
            'unique_id',
            'enable_desktop_notification'
        ]);
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

    public function updatedSelectedAssignCounters()
{
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

    // Render the Livewire view
    public function render()
    {
        // Fetch data for dropdowns

        $allRoles = Role::where(function ($query) {
            $query->where('team_id', $this->teamId)
                  ->orWhere('id', 1);
        })
        ->where('location_id', $this->locationId)
        ->select('name', 'id')
        ->get();
        // $allCounters = Counter::where('team_id',$this->teamId)->whereJsonContains('counter_locations', "$this->locationId")->where('show_checkbox',1)->get();
        $categories = Category::where('team_id', $this->teamId)
            ->whereJsonContains('category_locations', "$this->locationId")
            ->select('id', 'parent_id', 'level_id', 'name')
            ->get();

        // Structure categories into hierarchy
        $structuredCategories = $this->buildCategoryTree($categories);

        // Pass data to the view
        return view('livewire.staff-management-component', [
            'allRoles' => $allRoles,
            'getcategories' => $structuredCategories,
        ]);


    }
}
