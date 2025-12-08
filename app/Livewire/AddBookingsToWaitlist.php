<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AutomationSetting;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Translation;
use Auth;

class AddBookingsToWaitlist extends Component
{

    public $teamId;
    public $locationId;
    public $is_add_bookings_to_waitlist;
    public $when_to_move_booking;
    public $move_to_position;
    public $is_close_waitlist_early;
    public $is_assign_visit_on_alert;
    public $is_waitlist_prioritization;
    public $waitlist_prioritization;
    public $firstCategories = [];
    public $secondCategories = [];
    public $thirdCategories = [];
    public $selectedCategoryId, $secondChildId, $thirdChildId;
    public $translations;
    public $language;
    public $userAuth;

    public array $rules = [
    [
        'type' => '',
        'category' => '',
        'subcategory' => '',
        'childcategory' => ''
    ]
];

    public $showAddBookingsToWaitlistModal = false;
    public $showWaitlistPrioritizationModal = false;
  
    public function mount($teamId,$locationId)
    {
        $this->teamId = $teamId;
        $this->locationId = $locationId;

        $automationSettings = AutomationSetting::where('team_id', $this->teamId)->where('location_id', $this->locationId)->first();
        
        $this->is_add_bookings_to_waitlist = isset($automationSettings->is_add_bookings_to_waitlist) ? (bool)$automationSettings->is_add_bookings_to_waitlist : ''; 

        $this->when_to_move_booking = isset($automationSettings->when_to_move_booking) ? $automationSettings->when_to_move_booking : ''; 

        $this->move_to_position = isset($automationSettings->move_to_position) ? $automationSettings->move_to_position : ''; 

        $this->is_close_waitlist_early = isset($automationSettings->is_close_waitlist_early) ? $automationSettings->is_close_waitlist_early : ''; 

        $this->is_assign_visit_on_alert = isset($automationSettings->is_assign_visit_on_alert) ? $automationSettings->is_assign_visit_on_alert : ''; 

        $this->is_waitlist_prioritization = isset($automationSettings->is_waitlist_prioritization) ? (bool)$automationSettings->is_waitlist_prioritization : '';

        $this->rules = json_decode($automationSettings->waitlist_prioritization ?? '[]', true);

        $this->firstCategories = Category::getFirstCategoryN($this->teamId, $this->locationId);
        foreach ($this->rules as $index => $rule) {
            // Fill selected values
            $this->selectedCategoryId[$index] = $rule['category'] ?? null;
            $this->secondChildId[$index] = $rule['subcategory'] ?? null;
            $this->thirdChildId[$index] = $rule['childcategory'] ?? null;

            // Load options for subcategory & childcategory dropdowns
            $this->updateCategories('secondChildId', $this->selectedCategoryId[$index], $index);
            $this->updateCategories('thirdChildId', $this->secondChildId[$index], $index);
        }

        $this->language = session('app_locale');

        $this->translations = Translation::where('team_id', $this->teamId)
            ->get()
            ->groupBy('name') // Group by category name
            ->map(function ($items) {
                return $items->pluck('value', 'language'); // ['es' => 'Categoría 1']
            })
            ->toArray();

        $this->userAuth = Auth::user();    
    }
    

    public function openAddBookingsWaitlistModal()
    {
        $this->showAddBookingsToWaitlistModal = true;
    }

    public function closeAddBookingsWaitlistModal()
    {
        $this->showAddBookingsToWaitlistModal = false;
    }

    public function openWaitlistPrioritizationModal()
    {
        $this->showWaitlistPrioritizationModal = true;
    }

    public function closeWaitlistPrioritizationModal()
    {
        $this->showWaitlistPrioritizationModal = false;
    }

    public function closeWaitlistEarly()
    {
         $getCurrentSetting= AutomationSetting::where('team_id', $this->teamId)->where('location_id', $this->locationId)->first();

        if($getCurrentSetting->is_close_waitlist_early === 1)
        {
            $value = 0;
        }
        else
        {
            $value = 1;
        }

        $saveAutomationSetting = AutomationSetting::updateOrCreate([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId
        ],
        [
            'is_close_waitlist_early' => $value
        ]);

        if($saveAutomationSetting)
        {
             ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Automation Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
             $this->dispatch('updated');
        }
    }

    public function assignVisitOnAlert()
    {

        $getCurrentSetting= AutomationSetting::where('team_id', $this->teamId)->where('location_id', $this->locationId)->first();

        if($getCurrentSetting->is_assign_visit_on_alert === 1)
        {
            $value = 0;
        }
        else
        {
            $value = 1;
        }

        $saveAutomationSetting = AutomationSetting::updateOrCreate([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId
        ],
        [
            'is_assign_visit_on_alert' => $value
        ]);

        if($saveAutomationSetting)
        {
             ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Automation Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
             $this->dispatch('updated');
        }
    }

    public function addRule()
    {
        $this->rules[] = [
            'type' => '',
            'category' => '',
            'subcategory' => '',
            'childcategory' => ''
        ];
    }

    public function removeRule($index)
    {
        unset($this->rules[$index]);
        $this->rules = array_values($this->rules); // reindex after unset
    }

    public function onCategoryChanged($value, $index)
{
    $this->selectedCategoryId[$index] = $value;
    $this->secondChildId[$index] = null;
    $this->thirdChildId[$index] = null;

    $this->secondCategories[$index] = [];
    $this->thirdCategories[$index] = [];

    $this->updateCategories('secondChildId', $value, $index);
}

public function onSubcategoryChanged($value, $index)
{
    $this->secondChildId[$index] = $value;
    $this->thirdChildId[$index] = null;
    $this->thirdCategories[$index] = [];

    $this->updateCategories('thirdChildId', $value, $index);
}


    private function updateCategories($childProperty, $parentId, $index)
{
    $categories = [];

    if (!is_null($parentId)) {
        $categories = Category::getPluckNames($parentId, $this->locationId)?->toArray();
    }

    $propertyMap = [
        'secondChildId' => 'secondCategories',
        'thirdChildId' => 'thirdCategories'
    ];

    $targetProperty = $propertyMap[$childProperty];
    $this->{$targetProperty}[$index] = $categories;

    if (count($categories) === 1) {
        $this->{$childProperty}[$index] = array_key_first($categories);

        // Handle cascading to next level if required
        if ($childProperty === 'secondChildId') {
            $this->updateCategories('thirdChildId', $this->{$childProperty}[$index], $index);
        }
    } else {
        // Reset next child category if necessary
        if ($childProperty === 'secondChildId') {
            $this->thirdChildId[$index] = null;
            $this->thirdCategories[$index] = [];
        }
    }
}


    public function saveAddBookingsToWaitlist()
    {

        $saveAutomationSetting = AutomationSetting::updateOrCreate([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId
        ],
        [
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'is_add_bookings_to_waitlist' => $this->is_add_bookings_to_waitlist,
            'when_to_move_booking' => $this->when_to_move_booking,
            'move_to_position' => $this->move_to_position
        ]);

        if($saveAutomationSetting)
        {
             ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Automation Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
             $this->dispatch('updated');
        }
    }

    public function saveWaitlistPrioritization()
    {
        AutomationSetting::updateOrCreate(
        [
            'team_id' => $this->teamId,
            'location_id' => $this->locationId
        ],
        [
            'is_waitlist_prioritization' => $this->is_waitlist_prioritization,
            'waitlist_prioritization' => json_encode($this->rules)
        ]
    );

    ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Automation Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);

    $this->dispatch('updated');
    }

    public function render()
    {
        return view('livewire.add-bookings-to-waitlist');
    }
}
