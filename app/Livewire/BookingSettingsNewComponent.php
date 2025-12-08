<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceSetting;
use App\Models\AccountSetting;
use App\Models\CustomSlot;
use App\Models\ActivityLog;
use App\Models\Team;
use App\Models\SiteDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class BookingSettingsNewComponent extends Component
{
    #[Title('Booking Setting')]

    public $teamId;
    public $locationId;
    public $type;
    public $businessHours = [];
    public $customSlots = [];
    public $showModal = false;
    public $dateRange = [];
    public $start_date;
    public $end_date;
    public $is_closed = 'open';  // Default value for open
    public $customBookingID ='default';
    public $commonTimeSlots = [];
    public bool $isEnabled = false;
    public $mainpage = true;
    public $availabilitySection = false;
    public $schedulingWindow = false;
    public $geofence = false;
    public bool $bookingTimeHold = false;
    public bool $bookingApproval = false;
    public bool $allowReschedulebooking = false;
    public bool $allowCancelbooking = false;
    public bool $allowEditbooking = false;
    public bool $convertAppointmentToQueue = false;
    public bool $bookingConfirmationPage = false;
    public bool $bookingConvertManually = false;
    public bool $googleCalendar = false;
    public bool $outlookCalendar = false;
    public bool $checkinQrCode = false;
    public  $accountsetting = [];
    public $maxBooking = false;
    public $condition = [];
      public $userAuth;
    public function mount()
    {
        $this->userAuth = Auth::user();
        if (!$this->userAuth->hasPermissionTo('Booking Setting')) {
            abort(403);
        }

        $domainSlug = tenant('name');
        $this->teamId = tenant('id');
        $this->locationId = Session::get( 'selectedLocation' );
        $this->type = AccountSetting::BOOKING_SLOT;
        $this->accountsetting = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', $this->type)
        ->first();

        $this->isEnabled = $this->accountsetting?->booking_system ?? false;
        $this->bookingTimeHold = $this->accountsetting?->booking_time_hold ?? false;
        $this->bookingApproval = $this->accountsetting?->booking_approval_by_staff ?? false;
        $this->allowReschedulebooking = $this->accountsetting?->allow_reschedule ?? false;
        $this->allowCancelbooking = $this->accountsetting?->cancel_booking_cus ?? false;
        $this->allowEditbooking = $this->accountsetting?->edit_cancel_book_cus ?? false;
        $this->convertAppointmentToQueue = $this->accountsetting?->show_con_app_form ?? false;
        $this->bookingConfirmationPage = $this->accountsetting?->booking_confirmation_page ?? false;
        $this->customBookingID = $this->accountsetting?->custom_booking_id ?? 'default';
        $this->bookingConvertManually = $this->accountsetting?->booking_convert_manually ?? false;
        $this->googleCalendar = $this->accountsetting?->google_calendar ?? false;
        $this->outlookCalendar = $this->accountsetting?->outlook_calendar ?? false;
        $this->checkinQrCode = $this->accountsetting?->checkin_qrcode ?? false;

        $this->condition = [ 'team_id'=>$this->teamId ,'location_id'=>$this->locationId,'slot_type'=> $this->type];
        $this->loadBusinessHours();

    }

    public function toggle()
    {
        // $this->isEnabled = !$this->isEnabled;
        AccountSetting::updateOrCreate($this->condition,[ 'booking_system'=>$this->isEnabled ]);
       ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch('update');
    }
   public function bookingHoldToggle()
    {

        AccountSetting::updateOrCreate( $this->condition,[ 'booking_time_hold'=>$this->bookingTimeHold ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }
   public function bookingApprovalToggle()
    {

        AccountSetting::updateOrCreate( $this->condition,[ 'booking_approval_by_staff'=>$this->bookingApproval ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }
    public function allowRescheduleBookingToggle()
    {

        AccountSetting::updateOrCreate($this->condition,[ 'allow_reschedule'=>$this->allowReschedulebooking ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }
    public function allowCancelBookingToggle()
    {

        AccountSetting::updateOrCreate($this->condition,[ 'cancel_booking_cus'=>$this->allowCancelbooking ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }
    public function allowEditBookingToggle()
    {

        AccountSetting::updateOrCreate($this->condition,[ 'edit_cancel_book_cus'=>$this->allowEditbooking ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }
    public function convertAppointmentToQueueToggle()
    {

        AccountSetting::updateOrCreate($this->condition,[ 'show_con_app_form'=>$this->convertAppointmentToQueue ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }
    public function bookingConfirmationPageToggle()
    {

        AccountSetting::updateOrCreate($this->condition,[ 'booking_confirmation_page'=>$this->bookingConfirmationPage ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }
    public function customBookingIDToggle()
    {
        AccountSetting::updateOrCreate($this->condition,[ 'custom_booking_id'=>$this->customBookingID ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }

    public function bookingConvertManuallyToggle()
    {

        AccountSetting::updateOrCreate($this->condition,[ 'booking_convert_manually'=>$this->bookingConvertManually ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }

    public function googleCalendarToggle()
    {

        AccountSetting::updateOrCreate($this->condition,[ 'google_calendar'=>$this->googleCalendar ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }

    public function outlookCalendarToggle()
    {

        AccountSetting::updateOrCreate($this->condition,[ 'outlook_calendar'=>$this->outlookCalendar ]);
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    }

    public function showPage($page)
    {
        // List of all section variables
        $sections = ['mainpage', 'availabilitySection','schedulingWindow','geofence','maxBooking'];

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
            ["day" => "Monday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Tuesday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Wednesday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Thursday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Friday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Saturday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Sunday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []]
        ];

        // Load business hours from the service settings table
        $this->businessHours = ($serviceSetting && !empty($serviceSetting->business_hours)) ? json_decode($serviceSetting->business_hours, true) : $defaultBusinessHours;

        // Load custom slots for specific dates
        $customSlots = CustomSlot::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slots_type', CustomSlot::BOOKING_SLOT)
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

    public function checkinQrCodeToggle()
    {
        AccountSetting::updateOrCreate($this->condition,['checkin_qrcode' => $this->checkinQrCode]);
  ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
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
    // AccountSetting::updateOrCreate(
    //     ['team_id' => $this->teamId, 'location_id' => $this->locationId,'slot_type' =>$this->type],
    //     ['business_hours' => json_encode($formattedBusinessHours)]
    // );

    $existingSetting = AccountSetting::where('team_id', $this->teamId)
    ->where('location_id', $this->locationId)
    ->where('slot_type', $this->type)
    ->first();

if ($existingSetting) {
    // ✅ Update only business_hours
    $existingSetting->update([
        'business_hours' => json_encode($formattedBusinessHours),
    ]);
} else {
    // ✅ Insert new record with all settings
    AccountSetting::create([
        'team_id' => $this->teamId,
        'location_id' => $this->locationId,
        'slot_type' => $this->type,
        'business_hours' => json_encode($formattedBusinessHours),
        'booking_system' => 1,
        'booking_time_hold' => 1,
        'booking_approval_by_staff' => 1,
        'allow_reschedule' => 1,
        'cancel_booking_cus' => 1,
        'edit_cancel_book_cus' => 1,
        'show_con_app_form' => 1,
        'booking_confirmation_page' => 1,
        'custom_booking_id' => 'default',
        'booking_convert_manually' => 1,
        'google_calendar' => 1,
        'outlook_calendar' => 1,
        'slot_period' => 30,
        'req_per_slot' => 10,
        'req_per_slot' => 10,
        'allow_req_before' => 150,
        'allow_req_min_before' => 0,
        'req_accept_mode' => 'Auto Confirm',
        'is_geofence' => '0',
        'is_waitlist_limit' => '0',
        'walk_in_label' => 'Walk In',
        'appointment_label' => 'Appointment',
        'created_by' => Auth::id(),
    ]);
}

    CustomSlot::where([
        'team_id' => $this->teamId,
        'location_id' => $this->locationId,
        'slots_type'=>CustomSlot::BOOKING_SLOT,
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
                'slots_type'=>CustomSlot::BOOKING_SLOT,
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
       ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
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
        return view('livewire.booking-settings-new-component');
}

}
