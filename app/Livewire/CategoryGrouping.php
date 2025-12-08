<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\QueueCategoryGrouping;
use App\Models\Category;
use App\Models\Counter;
use App\Models\User;
use Auth;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class CategoryGrouping extends Component
{
    public $team_id;
    public $locationId;
    public $level1Categories = [];
    public $selectedLevel1;
    public $groupingData = [
        'data' => [],
        'selected' => []
    ];

    public function mount()
    {
        $this->team_id = tenant('id');
        $this->locationId = Session::get('selectedLocation');

        // load top-level categories
        $this->level1Categories = Category::where('team_id', $this->team_id)
        ->whereJsonContains('category_locations', (string) $this->locationId)
        ->where('level_id',1)
        ->get();

    }

 public function updatedSelectedLevel1($val)
{
    if (empty($val)) {
        $this->groupingData = ['data' => [], 'selected' => []];
        return;
    }
    $this->groupingData = ['data' => [], 'selected' => []];
    // 1. Load all child categories (departments) with users
    $categories = Category::with(['users' => function ($query) {
            $query->whereJsonContains('locations', (string) $this->locationId); // ✅ filter users by location
        }])
        ->select('id', 'name', 'parent_id')
        ->where('parent_id', $val)
        ->whereJsonContains('category_locations', (string) $this->locationId)
        ->whereNotNull('parent_id')
        ->get();

    $responseData = [];
    foreach ($categories as $category) {
        $staffData = [];

        foreach ($category->users as $user) {
            $counters = [];

            if (!empty($user->assign_counters)) {
                $ids = is_array($user->assign_counters)
                    ? $user->assign_counters
                    : json_decode($user->assign_counters, true);

                // ✅ filter counters by location too
                $userCounters = Counter::whereJsonContains('counter_locations', (string) $this->locationId)
                    ->whereIn('id', $ids ?? [])
                    ->where('show_checkbox', 1)
                    ->pluck('name', 'id'); // [id => name]

                $counters = $userCounters->toArray();
            }

            $staffData[$user->id] = [
                'staffName' => $user->name,
                'counters'  => $counters
            ];
        }

        $responseData[$category->id] = [
            'categoryName' => $category->name,
            'staff'        => $staffData,
            'counters'     => []
        ];
    }

    // 2. Check if grouping exists in DB for this category
    $existing = QueueCategoryGrouping::where('team_id', $this->team_id)
        ->where('location_id', $this->locationId)
        ->where('category_id', $val)
        ->first();

    $selected = [];
    if ($existing && $existing->grouping_data) {
        $selected = json_decode($existing->grouping_data, true) ?? [];
    }

    // 3. Save into groupingData
    $this->groupingData = [
        'data'     => $responseData,
        'selected' => $selected
    ];
}


public function save()
{
    // Validation rules
    $this->validate([
        'groupingData.selected' => 'required|array',
        'groupingData.selected.*.staff' => 'required|integer|exists:users,id',
        'groupingData.selected.*.counter' => 'required|integer|exists:counters,id',
        'groupingData.selected.*.priority' => 'required|integer|min:1',
    ], [
        'groupingData.selected.*.staff.required' => 'Staff is required for each department.',
        'groupingData.selected.*.counter.required' => 'Counter is required for each department.',
        'groupingData.selected.*.priority.required' => 'Meeting sequence is required for each department.',
    ]);

    // Save or update
    QueueCategoryGrouping::updateOrCreate(
        [
            'team_id'     => $this->team_id,
            'location_id' => $this->locationId,
            'category_id' => $this->selectedLevel1,
        ],
        [
            'grouping_data' => json_encode($this->groupingData['selected']),
            'created_by'    => auth()->id(),
            'status'        => true,
        ]
    );

    $this->reset(['selectedLevel1', 'groupingData']);
    // $this->dispatch('notify', 'Category grouping saved successfully!');
    $this->dispatch('created');
}

    public function render()
    {
        return view('livewire.category-grouping');
    }
}
