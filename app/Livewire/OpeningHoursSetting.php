<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceSetting;
use App\Models\AccountSetting;
use App\Models\Location;
use App\Models\CustomSlot;
use Carbon\Carbon;
use Livewire\Attributes\Title;

class OpeningHoursSetting extends Component
{
    #[Title('Opening Hours Setting')]

    public $teamId;
    public $locationId;
    public $location;
    public $businessHours = [];
    public $customSlots = [];
    // public $showEditModal = false;
    public $showModal = false;
    public $dateRange = [];
    public $start_date;
    public $end_date;
    public $is_closed = 'open';  // Default value for open
    public $commonTimeSlots = []; 

    public function mount($locationId = null )
    {
        if(Is_null($locationId)){
            abort(404);
        }
        $this->teamId = tenant('id');
        $this->locationId = $locationId;
        $this->location = Location::with('user')->find($locationId);
        // dd( $this->location);
        $this->loadBusinessHours();
      
    }

    public function loadBusinessHours()
    {
        // Fetch service settings
        $serviceSetting = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slot_type', AccountSetting::LOCATION_SLOT)
            ->first();

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
        $this->businessHours = $serviceSetting ? json_decode($serviceSetting->business_hours, true) : $defaultBusinessHours;
        
        // Load custom slots for specific dates
        $customSlots = CustomSlot::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slots_type', CustomSlot::LOCATION_SLOT)
            ->whereNull('category_id')
            ->get();
            

            if (empty($customSlots)) {
                $this->customSlots[] = [
                    "selected_date" => '',
                    "is_closed" => "open",
                    "start_time" => '',
                    "end_time" => '',
                    "day_interval" => []
                ];
            } else {
                foreach ($customSlots as $index => $slot) {
                    // Preserve the selected date
                    $this->customSlots[$index]['selected_date'] = $slot['selected_date'] ?? '';
            
                    // Default is_closed value
                    
                    // Decode business_hours JSON
                    $CustomBusinessHours = json_decode($slot['business_hours'] ?? '[]', true);
                    
                    if (!empty($CustomBusinessHours)) {
                        foreach ($CustomBusinessHours as $key => $day) {
                            // Check if start_time and end_time exist before converting
                            $this->customSlots[$index]['is_closed'] =$day['is_closed'];
                            $this->customSlots[$index]['start_time'] = isset($day['start_time']) 
                                ? Carbon::createFromFormat('h:iA', $day['start_time'])->format('H:i') 
                                : '';
            
                            $this->customSlots[$index]['end_time'] = isset($day['end_time']) 
                                ? Carbon::createFromFormat('h:iA', $day['end_time'])->format('H:i') 
                                : '';
            
                            // Convert day intervals too
                            $this->customSlots[$index]['day_interval'] = [];
                            if (!empty($day['day_interval'])) {
                                foreach ($day['day_interval'] as $slotIndex => $slots) {
                                    $this->customSlots[$index]['day_interval'][$slotIndex] = [
                                        'start_time' => isset($slots['start_time']) 
                                            ? Carbon::createFromFormat('h:iA', $slots['start_time'])->format('H:i') 
                                            : '',
                                        'end_time' => isset($slots['end_time']) 
                                            ? Carbon::createFromFormat('h:iA', $slots['end_time'])->format('H:i') 
                                            : ''
                                    ];
                                }
                            }
                        }
                    } else {
                        // Ensure all required fields exist even when business_hours is empty
                        $this->customSlots[$index] = [
                            "selected_date" => $slot['selected_date'] ?? '',
                            "is_closed" => "open",
                            "start_time" => '',
                            "end_time" => '',
                            "day_interval" => []
                        ];
                    }
                }
            }


            foreach ($this->businessHours as $index => $day) {
                $this->businessHours[$index]['start_time'] = Carbon::createFromFormat('h:iA', $day['start_time'])->format('H:i');
                $this->businessHours[$index]['end_time'] = Carbon::createFromFormat('h:iA', $day['end_time'])->format('H:i');
                
                // Convert day intervals too
                foreach ($day['day_interval'] as $slotIndex => $slot) {
                    $this->businessHours[$index]['day_interval'][$slotIndex]['start_time'] = Carbon::createFromFormat('h:iA', $slot['start_time'])->format('H:i');
                    $this->businessHours[$index]['day_interval'][$slotIndex]['end_time'] = Carbon::createFromFormat('h:iA', $slot['end_time'])->format('H:i');
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
    // public function removeCustomSlot($dayIndex, $slotIndex)
    // {
    //     unset($this->customSlots[$dayIndex]['day_interval'][$slotIndex]);
    //     $this->customSlots[$dayIndex]['day_interval'] = array_values($this->customSlots[$dayIndex]['day_interval']); // Re-index array
    // }

    // public function removeSlot($dayIndex, $slotIndex)
    // {
    //     unset($this->businessHours[$dayIndex]['day_interval'][$slotIndex]);
    //     $this->businessHours[$dayIndex]['day_interval'] = array_values($this->businessHours[$dayIndex]['day_interval']); // Re-index array
    // }

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
        ['team_id' => $this->teamId, 'location_id' => $this->locationId,'slot_type'=> AccountSetting::LOCATION_SLOT],
        ['business_hours' => json_encode($formattedBusinessHours)]
    );

    CustomSlot::where([
        'team_id' => $this->teamId,
        'location_id' => $this->locationId,
        'slots_type'=>CustomSlot::LOCATION_SLOT,
        'category_id' => null,
    ])->delete();

    // Save custom slots with formatted times
    foreach ($this->customSlots as $slot) {
if (empty($slot['selected_date'])) {
        continue; // Skip this slot if no date is selected
    }

        CustomSlot::create(
            [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'slots_type'=>CustomSlot::LOCATION_SLOT,
                'category_id' => null,
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

    $this->showModal = false;
$this->dispatch('saved', message: 'Opening hours updated successfully.');
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


    public function render()
    {
        return view('livewire.opening-hours-setting');
    }
}