<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceSetting;
use App\Models\AccountSetting;
use App\Models\CustomSlot;
use App\Models\Category;
use App\Models\SiteDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class CategorySettingV2Component extends Component
{
    #[Title('Category Setting V2')]

    public $level,$categoryId;
    public $teamId;
    public $locationId;
    public $record;
    public $type;
    public $businessHours = [];
    public $customSlots = [];
    public $showModal = false;
    public $dateRange = [];
    public $start_date;
    public $end_date;
    public $is_closed = 'open';  // Default value for open
    public $commonTimeSlots = []; 
    public bool $isEnabled = false;
    public $mainpage = true;
    public $waitlistLimit = false;
    public $geofence = false;
    public $schedulingWindow = false;
    public bool $customersRegisterMultiple = false;
    public bool $allowCancelbooking = false;

    public $accountsetting = [];
    public $condition = [];
    public $weekCalendar = [];
    public $dateCalendar = [];

    public function mount($level =null,$categoryId =null){

        if(is_null($level) || is_null($categoryId)){
           abort(404);
        }

        $this->level = $level;
        $this->categoryId = $categoryId;

        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');
        $this->type = AccountSetting::CATEGORY_SLOT;
        
        // Load the category record
        $this->record = Category::find($this->categoryId);
        
        $this->accountsetting = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where( 'category_id', $this->categoryId)
        ->where('slot_type',$this->type)
        ->first();


        $this->isEnabled =  $this->accountsetting?->booking_system ?? false;
        $this->customersRegisterMultiple =  $this->accountsetting?->customers_register_multiple ?? false;
        $this->allowCancelbooking =  $this->accountsetting?->cancel_booking_cus ?? false;
      
        $this->condition = [ 'team_id'=>$this->teamId ,'location_id'=>$this->locationId,'category_id'=>$this->categoryId,'slot_type'=> $this->type];
        $this->loadBusinessHours();
    }

    public function toggle()
    {
    
        // $this->isEnabled = !$this->isEnabled;
      
        AccountSetting::updateOrCreate($this->condition,[ 'booking_system'=>$this->isEnabled ]);
    }
    public function customRegisterMultipleToggle()
    {
 
        AccountSetting::updateOrCreate( $this->condition,[ 'customers_register_multiple'=>$this->customersRegisterMultiple ]);

        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }

    public function allowCancelBookingToggle()
    {
 
        AccountSetting::updateOrCreate($this->condition,[ 'cancel_booking_cus'=>$this->allowCancelbooking ]);

        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }
   

    public function showPage($page)
    {
        // List of all section variables
        $sections = ['mainpage', 'waitlistLimit','geofence'];
    
        // Set all sections to false
        foreach ($sections as $section) {
            $this->$section = false;
        }
    
        // Set only the requested section to true
        if (in_array($page, $sections)) {
            $this->$page = true;
        }
    }

  
    public function loadBusinessHours()
    {
        // Fetch service settings
        $serviceSetting = $this->accountsetting;

        // Default business hours structure
        $defaultBusinessHours = [
            ["day" => "Monday", "is_closed" => "closed", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Tuesday", "is_closed" => "closed", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Wednesday", "is_closed" => "closed", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Thursday", "is_closed" => "closed", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Friday", "is_closed" => "closed", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Saturday", "is_closed" => "closed", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Sunday", "is_closed" => "closed", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []]
        ];

        // Load business hours from the service settings table
        $this->businessHours = ($serviceSetting && !empty($serviceSetting->business_hours)) ? json_decode($serviceSetting->business_hours, true) : $defaultBusinessHours;

        // Initialize weekCalendar from business_hours
        $this->weekCalendar = [];
        foreach ($this->businessHours as $day) {
            $dayName = $day['day'];
            $slots = [];
            
            // If the day is not closed and has start/end time, add the main slot
            if (isset($day['is_closed']) && $day['is_closed'] === 'open' && !empty($day['start_time']) && !empty($day['end_time'])) {
                $slots[] = [
                    'start' => $day['start_time'],
                    'end' => $day['end_time'],
                    'capacity' => ''
                ];
            }
            
            // Add day_interval slots if they exist
            if (!empty($day['day_interval']) && is_array($day['day_interval'])) {
                foreach ($day['day_interval'] as $interval) {
                    if (!empty($interval['start_time']) && !empty($interval['end_time'])) {
                        $slots[] = [
                            'start' => $interval['start_time'],
                            'end' => $interval['end_time'],
                            'capacity' => ''
                        ];
                    }
                }
            }
            
            $this->weekCalendar[$dayName] = $slots;
        }

        // Load custom slots for specific dates
        $customSlots = CustomSlot::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('category_id', $this->categoryId)
            ->where('slots_type', $this->type)
            ->get();
            
        // Initialize dateCalendar from custom_slots
        $this->dateCalendar = [];
        $this->customSlots = [];
        
        if (!empty($customSlots)) {
            foreach ($customSlots as $slot) {
                $selectedDate = $slot['selected_date'] ?? '';
                if (empty($selectedDate)) {
                    continue;
                }
                
                $slots = [];
                $customBusinessHours = json_decode($slot['business_hours'] ?? '[]', true);
                
                if (!empty($customBusinessHours) && is_array($customBusinessHours)) {
                    foreach ($customBusinessHours as $day) {
                        // If the day is not closed and has start/end time, add the main slot
                        if (isset($day['is_closed']) && $day['is_closed'] === 'open' && !empty($day['start_time']) && !empty($day['end_time'])) {
                            $slots[] = [
                                'start' => $day['start_time'],
                                'end' => $day['end_time'],
                                'capacity' => ''
                            ];
                        }
                        
                        // Add day_interval slots if they exist
                        if (!empty($day['day_interval']) && is_array($day['day_interval'])) {
                            foreach ($day['day_interval'] as $interval) {
                                if (!empty($interval['start_time']) && !empty($interval['end_time'])) {
                                    $slots[] = [
                                        'start' => $interval['start_time'],
                                        'end' => $interval['end_time'],
                                        'capacity' => ''
                                    ];
                                }
                            }
                        }
                    }
                }
                
                $this->dateCalendar[$selectedDate] = $slots;
                
                // Also populate customSlots for backward compatibility
                $this->customSlots[] = [
                    "selected_date" => $selectedDate,
                    "is_closed" => !empty($customBusinessHours) && isset($customBusinessHours[0]['is_closed']) ? $customBusinessHours[0]['is_closed'] : "open",
                    "start_time" => !empty($customBusinessHours) && isset($customBusinessHours[0]['start_time']) ? $customBusinessHours[0]['start_time'] : '',
                    "end_time" => !empty($customBusinessHours) && isset($customBusinessHours[0]['end_time']) ? $customBusinessHours[0]['end_time'] : '',
                    "day_interval" => !empty($customBusinessHours) && isset($customBusinessHours[0]['day_interval']) ? $customBusinessHours[0]['day_interval'] : []
                ];
            }
        } else {
            $this->customSlots[] = [
                "selected_date" => '',
                "is_closed" => "open",
                "start_time" => '',
                "end_time" => '',
                "day_interval" => []
            ];
        }

        // Convert business hours format for backward compatibility
        foreach ($this->businessHours as $index => $day) {
            $this->businessHours[$index]['start_time'] = Carbon::createFromFormat('h:i A', $day['start_time'])->format('H:i');
            $this->businessHours[$index]['end_time'] = Carbon::createFromFormat('h:i A', $day['end_time'])->format('H:i');
            
            // Convert day intervals too
            foreach ($day['day_interval'] as $slotIndex => $slot) {
                $this->businessHours[$index]['day_interval'][$slotIndex]['start_time'] = Carbon::createFromFormat('h:i A', $slot['start_time'])->format('H:i');
                $this->businessHours[$index]['day_interval'][$slotIndex]['end_time'] = Carbon::createFromFormat('h:i A', $slot['end_time'])->format('H:i');
            }
        }
    }

    public function showEditModal()
    {

        $this->showModal = true;
    }
    public function showCloseModal()
    {

        $this->showModal = false;
    }

    public function addSlot($dayIndex)
    {
    
        $this->businessHours[$dayIndex]['day_interval'][] = ['start_time' => '', 'end_time' => ''];
    }
    
    public function addNextCustomSlot()
    {
       $index =count($this->customSlots);
  $this->customSlots[$index] = [
            "selected_date" => '',
             "is_closed" =>"open",
             "start_time" =>'',
             "end_time" => '',
             "day_interval" => []
     ];
 

    }
    public function addCustomSlot($index)
    {

        $this->customSlots[$index]['day_interval'][] = ['start_time' => '', 'end_time' => ''];
 

    }

    
    public function deleteCustomSlot($dayIndex)
    {
        unset($this->customSlots[$dayIndex]);
        $this->customSlots = array_values($this->customSlots); // Re-index array

    }
    public function removeCustomSlot($dayIndex, $slotIndex)
    {
        if (isset($this->customSlots[$dayIndex]['day_interval'][$slotIndex])) {
            unset($this->customSlots[$dayIndex]['day_interval'][$slotIndex]);
            $this->customSlots[$dayIndex]['day_interval'] = array_values($this->customSlots[$dayIndex]['day_interval']); // Re-index array
        }
    }

    public function removeSlot($dayIndex, $slotIndex)
    {
        if (isset($this->businessHours[$dayIndex]['day_interval'][$slotIndex])) {
            unset($this->businessHours[$dayIndex]['day_interval'][$slotIndex]);
            $this->businessHours[$dayIndex]['day_interval'] = array_values($this->businessHours[$dayIndex]['day_interval']); // Re-index array
        }
    }

    public function save()
{
    // dd($this->businessHours,$this->customSlots);
    // Format business hours before saving
    $formattedBusinessHours = array_map(function ($day) {
        return [
            'day' => $day['day'],
            'is_closed' => $day['is_closed'],
            'start_time' => $this->formatTime($day['start_time']),
            'end_time' => $this->formatTime($day['end_time']),
            'day_interval' => array_filter(array_map(function ($interval) {
                return [
                    'start_time' => $this->formatTime($interval['start_time']),
                    'end_time' => $this->formatTime($interval['end_time'])
                ];
            }, $day['day_interval']), function ($interval) {
                return !empty($interval['start_time']) && !empty($interval['end_time']);
            }),
        ];
    }, $this->businessHours);

    // Save business hours to the service settings table
    AccountSetting::updateOrCreate(
        ['team_id' => $this->teamId, 'location_id' => $this->locationId,'category_id'=> $this->categoryId,'slot_type' =>$this->type],
        ['business_hours' => json_encode($formattedBusinessHours)]
    );

    CustomSlot::where([
        'team_id' => $this->teamId,
        'location_id' => $this->locationId,
        'category_id' => $this->categoryId,
        'slots_type'=> $this->type,
    ])->delete();

    // Save custom slots with formatted times
    foreach ($this->customSlots as $slot) {

        if (empty($slot['selected_date'])) {
        continue;
    }
        CustomSlot::create(
            [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'category_id' => $this->categoryId,
                'slots_type'=>$this->type,
                'selected_date' => $slot['selected_date'],
                'business_hours' => json_encode([
                    [
                        "day" => \Carbon\Carbon::parse($slot['selected_date'])->format('l'), // Get day name
                        "is_closed" => $slot['is_closed'],
                        "start_time" => !empty($slot['start_time']) ? $this->formatTime($slot['start_time']): $this->formatTime('12:00 AM'), // Default to 12:00 AM
                        "end_time" => !empty($slot['end_time']) ? $this->formatTime($slot['end_time']): $this->formatTime('12:00 PM'), // Default to 12:00 AM
                        "day_interval" => array_map(function ($interval) {
                            return [
                                "start_time" => !empty($interval['start_time']) ? $this->formatTime($interval['start_time']): $this->formatTime('12:00 AM'), // Default to 12:00 AM
                                "end_time" => !empty($interval['end_time']) ? $this->formatTime($interval['end_time']): $this->formatTime('12:00 PM'), 
                            ];
                        }, $slot['day_interval'])
                    ]
                ])
            ]
        );
    }

    // Reload business hours and calendar data after saving
    $this->accountsetting = AccountSetting::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->where('category_id', $this->categoryId)
        ->where('slot_type', $this->type)
        ->first();
    
    $this->loadBusinessHours();

    $this->showModal = false;
   $this->dispatch('saved', message: 'Opening hours updated successfully.');
    // session()->flash('message', 'Opening hours updated successfully.');
 }

    /**
     * Format time to "h:i A" format (e.g., "09:00 AM")
     */
    private function formatTime($time)
    {
        if (empty($time)) {
            return null;
        }
        return date("h:i A", strtotime($time));
    }

    public function saveWeekData($data)
    {
        // Get current weekCalendar from component
        $stored_value = $this->weekCalendar;
        if (empty($stored_value)) {
            $stored_value = [];
        }
        
        $week_availability = [];
        
        $weekname = date('l', strtotime($data['week_date'] ?? now()));
        
        if (isset($data['week_name']) && !empty($data['week_name'])) {
            $weekNames = is_array($data['week_name']) ? $data['week_name'] : [$data['week_name']];
            
            foreach ($weekNames as $weekname) {
                if (isset($data['start']) && !empty($data['start'])) {
                    $starts = is_array($data['start']) ? $data['start'] : [$data['start']];
                    $ends = isset($data['end']) ? (is_array($data['end']) ? $data['end'] : [$data['end']]) : [];
                    $capacities = isset($data['capacity']) ? (is_array($data['capacity']) ? $data['capacity'] : [$data['capacity']]) : [];
                    
                    // Remove existing entry for this weekday if it exists
                    if (!empty($stored_value) && array_key_exists($weekname, $stored_value)) {
                        unset($stored_value[$weekname]);
                    }
                    
                    // Build new availability array for this weekday with multiple slots
                    for ($i = 0; $i < count($starts); $i++) {
                        $week_availability[$weekname][$i]['start'] = $starts[$i] ?? '';
                        $week_availability[$weekname][$i]['end'] = $ends[$i] ?? '';
                        $week_availability[$weekname][$i]['capacity'] = $capacities[$i] ?? '';
                    }
                } else {
                    // Empty array means unavailable
                    $week_availability[$weekname] = [];
                }
            }
        } else {
            $week_availability[$weekname] = [];
        }
        
        // Merge with existing stored values
        $final = array_merge($stored_value, $week_availability);
        
        // Update component property
        $this->weekCalendar = $final;
        
        // Convert weekCalendar format to business_hours format for database storage
        // Use the merged final array which contains all days
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        // Convert weekCalendar to business_hours format
        $formattedBusinessHours = [];
        foreach ($daysOfWeek as $dayName) {
            $formattedDay = [
                'day' => $dayName,
                'is_closed' => 'closed',
                'start_time' => '09:00 AM',
                'end_time' => '06:00 PM',
                'day_interval' => []
            ];
            
            // If weekCalendar has entries for this day
            if (isset($final[$dayName]) && !empty($final[$dayName]) && is_array($final[$dayName])) {
                $slots = $final[$dayName];
                if (count($slots) > 0) {
                    $formattedDay['is_closed'] = 'open';
                    // First slot becomes the main start_time/end_time
                    $formattedDay['start_time'] = $this->formatTime($slots[0]['start'] ?? '09:00 AM');
                    $formattedDay['end_time'] = $this->formatTime($slots[0]['end'] ?? '06:00 PM');
                    
                    // Remaining slots become day_interval
                    if (count($slots) > 1) {
                        for ($i = 1; $i < count($slots); $i++) {
                            if (!empty($slots[$i]['start']) && !empty($slots[$i]['end'])) {
                                $formattedDay['day_interval'][] = [
                                    'start_time' => $this->formatTime($slots[$i]['start']),
                                    'end_time' => $this->formatTime($slots[$i]['end'])
                                ];
                            }
                        }
                    }
                } else {
                    // Empty array means closed/unavailable
                    $formattedDay['is_closed'] = 'closed';
                }
            }
            
            $formattedBusinessHours[] = $formattedDay;
        }
        
        // Save to account_settings table
        AccountSetting::updateOrCreate(
            [
                'team_id' => $this->teamId, 
                'location_id' => $this->locationId,
                'category_id' => $this->categoryId,
                'slot_type' => $this->type
            ],
            ['business_hours' => json_encode($formattedBusinessHours)]
        );
        
        // Ensure mainpage stays true to show calendar view
        $this->mainpage = true;
        
        // Reload business hours to update component state
        $this->accountsetting = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('category_id', $this->categoryId)
            ->where('slot_type', $this->type)
            ->first();
        
        $this->loadBusinessHours();
        
        $this->dispatch('saved', message: 'Weekly hours updated successfully.');
    }
    
    public function saveDatesData($data)
    {
        // Get current dateCalendar from component
        $stored_value = $this->dateCalendar;
        if (empty($stored_value)) {
            $stored_value = [];
        }
        
        $availability = [];
        $datesToUpdate = [];
        
        if (isset($data['date_array']) && !empty($data['date_array'])) {
            $dates = is_array($data['date_array']) ? $data['date_array'] : [$data['date_array']];
            
            // Normalize dates and remove duplicates
            $uniqueDates = [];
            foreach ($dates as $date) {
                $selected_date = date('Y-m-d', strtotime($date));
                if (!empty($selected_date) && $selected_date != '1970-01-01') {
                    $uniqueDates[$selected_date] = true;
                }
            }
            
            // Process each unique date only once
            foreach ($uniqueDates as $selected_date => $_) {
                $datesToUpdate[] = $selected_date;
                
                if (isset($data['start']) && !empty($data['start'])) {
                    $starts = is_array($data['start']) ? $data['start'] : [$data['start']];
                    $ends = isset($data['end']) ? (is_array($data['end']) ? $data['end'] : [$data['end']]) : [];
                    $capacities = isset($data['capacity']) ? (is_array($data['capacity']) ? $data['capacity'] : [$data['capacity']]) : [];
                    
                    // Remove existing entry for this date if it exists
                    if (!empty($stored_value) && array_key_exists($selected_date, $stored_value)) {
                        unset($stored_value[$selected_date]);
                    }
                    
                    // Build new availability array for this date with multiple slots
                    for ($i = 0; $i < count($starts); $i++) {
                        $availability[$selected_date][$i]['start'] = $starts[$i] ?? '';
                        $availability[$selected_date][$i]['end'] = $ends[$i] ?? '';
                        $availability[$selected_date][$i]['capacity'] = $capacities[$i] ?? '';
                    }
                } else {
                    // Empty array means unavailable
                    $availability[$selected_date] = [];
                }
            }
        }
        
        // Merge with existing stored values
        $final = array_merge($stored_value, $availability);
        
        // Update component property
        $this->dateCalendar = $final;
        
        // Delete existing custom slots for the dates being updated
        if (!empty($datesToUpdate)) {
            // Ensure dates are unique
            $datesToUpdate = array_unique($datesToUpdate);
            
            CustomSlot::where('team_id', $this->teamId)
                ->where('location_id', $this->locationId)
                ->where('category_id', $this->categoryId)
                ->where('slots_type', $this->type)
                ->whereIn('selected_date', $datesToUpdate)
                ->delete();
        }
        
        // Save new custom slots for each date (only unique dates)
        foreach (array_unique($datesToUpdate) as $selectedDate) {
            if (isset($availability[$selectedDate])) {
                $slots = $availability[$selectedDate];
                
                // Convert dateCalendar format to custom_slots business_hours format
                $businessHoursArray = [];
                
                if (!empty($slots) && is_array($slots)) {
                    // Build the business_hours structure
                    $dayInterval = [];
                    
                    // First slot becomes the main start_time/end_time
                    if (count($slots) > 0 && !empty($slots[0]['start']) && !empty($slots[0]['end'])) {
                        $mainStart = $this->formatTime($slots[0]['start']);
                        $mainEnd = $this->formatTime($slots[0]['end']);
                        
                        // Remaining slots become day_interval
                        if (count($slots) > 1) {
                            for ($i = 1; $i < count($slots); $i++) {
                                if (!empty($slots[$i]['start']) && !empty($slots[$i]['end'])) {
                                    $dayInterval[] = [
                                        'start_time' => $this->formatTime($slots[$i]['start']),
                                        'end_time' => $this->formatTime($slots[$i]['end'])
                                    ];
                                }
                            }
                        }
                        
                        $businessHoursArray[] = [
                            'day' => Carbon::parse($selectedDate)->format('l'),
                            'is_closed' => 'open',
                            'start_time' => $mainStart,
                            'end_time' => $mainEnd,
                            'day_interval' => $dayInterval
                        ];
                    } else {
                        // Empty slots means unavailable/closed
                        $businessHoursArray[] = [
                            'day' => Carbon::parse($selectedDate)->format('l'),
                            'is_closed' => 'closed',
                            'start_time' => '12:00 AM',
                            'end_time' => '12:00 PM',
                            'day_interval' => []
                        ];
                    }
                } else {
                    // Empty array means unavailable/closed
                    $businessHoursArray[] = [
                        'day' => Carbon::parse($selectedDate)->format('l'),
                        'is_closed' => 'closed',
                        'start_time' => '12:00 AM',
                        'end_time' => '12:00 PM',
                        'day_interval' => []
                    ];
                }
                
                // Create custom slot record
                CustomSlot::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->locationId,
                    'category_id' => $this->categoryId,
                    'slots_type' => $this->type,
                    'selected_date' => $selectedDate,
                    'business_hours' => json_encode($businessHoursArray)
                ]);
            }
        }
        
        // Ensure mainpage stays true to show calendar view
        $this->mainpage = true;
        
        // Reload business hours to update component state
        $this->loadBusinessHours();
        
        $this->dispatch('saved', message: 'Date hours updated successfully.');
    }
    
    public function resetToWeeklyHours($date)
    {
        $dateFormatted = Carbon::parse($date)->format('Y-m-d');
        
        // Delete the custom slot for this date from database
        CustomSlot::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('category_id', $this->categoryId)
            ->where('slots_type', $this->type)
            ->where('selected_date', $dateFormatted)
            ->delete();
        
        // Reload business hours to update component state
        $this->loadBusinessHours();
        
        $this->dispatch('saved', message: 'Reset to weekly hours successfully.');
    }

    public function render()
    {
        return view('livewire.category-setting-v2-component');
    }
}

