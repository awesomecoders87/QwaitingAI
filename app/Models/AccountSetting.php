<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Carbon\Carbon;
use Str;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Config;
  use Illuminate\Support\Facades\Session;

class AccountSetting extends Model
{
    use HasFactory;

    protected $table ='account_settings';

    protected $fillable =['team_id','location_id','category_id','user_id','business_hours', 'show_con_app_form', 'edit_cancel_book_cus', 'cancel_booking_cus', 'booking_confirmation_page', 'smg_integration', 'appointment_integration', 'custom_booking_id', 'booking_convert_manually', 'booking_reminder', 'show_category_per_row', 'calendar_pax_range', 'slot_period','req_per_slot','allow_req_before','allow_cancel_before','req_accept_mode','week_start',
'con_app_input_placeholder','booking_convert_label','menu_online_booking_label','menu_offline_booking_label','google_calendar','outlook_calendar','booking_system','allow_req_min_before','is_geofence','geofence_latitude','geofence_longitude','geofence_max_distance','geofence_max_distance_unit','booking_time_hold','calendar_pax_range_period','booking_approval_by_staff','allow_reschedule','customers_register_multiple','waitlist_limit','is_waitlist_limit','slot_type','walk_in_label','appointment_label','booking_auto_cancel','checkin_qrcode','created_at','updated_at'];

    protected $casts = [
        'business_hours' => 'array',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATIC_DAY = 3;
    const LOCATION_SLOT ='location';
    const BOOKING_SLOT ='booking';
    const STAFF_SLOT ='staff';
    const TICKET_SLOT ='ticket';
    const CATEGORY_SLOT ='category';
    const AUTO_CONFIRM ='Auto Confirm';
    const MANUAL_CONFIRM ='Manual Confirm';

  protected $timezone = 'UTC'; // default fallback

  protected static function booted()
{
    static::retrieved(function ($model) {
        // Get timezone either from session or related siteSetting
        $timezone = Session::get('timezone_set');

        if (!$timezone && $model->siteSetting) {
            $timezone = $model->siteSetting->select_timezone;
        }

        // Default to UTC if nothing found
        $timezone = $timezone ?: 'UTC';

        // Set Laravel and PHP timezone
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    });
}
    public function getTimezone()
    {
        return $this->timezone ?? 'UTC';
    }

    // // Example method using timezone
    // public function nowWithTimezone()
    // {
    //     return \Carbon\Carbon::now($this->getTimezone());
    // }

    public static  function reqPerSlot($start, $end) {
        $options = [];

        for ($i = $start; $i <= $end; $i++) {
            $options[$i] = (string)$i;
        }
        return $options;
    }

    public static  function reqPerSlotGet($teamId, $location,$type='req_per_slot') {
        $options = [];
        $start = 1;
        $end = 30;
        if($type == 'req_per_slot'){
            $end= self::where(['team_id'=> $teamId, 'location_id'=>"$location" ])->value('req_per_slot');
        }
        if($type == 'pax_per_service'){
            $end= self::where([ 'team_id'=> $teamId, 'location_id'=>"$location" ])->value('calendar_pax_range');
        }

        for ($i = $start; $i <= (int)$end; $i++) {
            $options[$i] = (string)$i;
        }
        return $options;
    }

    public static function reqBeforeDay() {
        return [
            0 =>  __('none'),
            1 => '1 ' . __('text.day'),
            2 => '2 ' . __('text.days'),
            3 => '3 ' . __('text.days'),
            4 => '4 ' . __('text.days'),
            5 => '5 ' . __('text.days'),
            6 => '6 ' . __('text.days'),
            7 => '1 ' . __('text.week'),
            14 => '2 ' . __('text.weeks'),
            21 => '3 ' . __('text.weeks'),
            30 => '1 ' . __('text.month'),
            60 => '2 ' . __('text.months'),
            90 => '3 ' . __('text.months'),
            120 => '4 ' . __('text.months'),
            150 => '5 ' . __('text.months'),
        ];
    }
    public static function distanceUnit() {
        return [
            // 'feet' => 'Feet',
            'meters' => 'Meters',

        ];
    }

    public static function showDateTimeFormat($teamId =null,$location = null) {
      return Auth::user()?->date_format ?? 'Y-m-d H:i:s';// Fallback to default format
     }
    public static function showTimeFormat() {
        return $timeFormat = User::where([ 'id'=> Auth::id()])->value('time_format') ?? 'H:i:s'; // Fallback to default format
     }


public static function periodOfSlot(){
    return  [
        5 => '5 ' . __('text.minutes'),
        10 => '10 ' . __('text.minutes'),
        15 => '15 ' . __('text.minutes'),
        20 => '20 ' . __('text.minutes'),
        25 => '25 ' . __('text.minutes'),
        30 => '30 ' . __('text.minutes'),
        35 => '35 ' . __('text.minutes'),
        40 => '40 ' . __('text.minutes'),
        45 => '45 ' . __('text.minutes'),
        50 => '50 ' . __('text.minutes'),
        55 => '55 ' . __('text.minutes'),
        60 => '60 ' . __('text.minutes')
    ];
}



public static function cancelBefore(){

    return [
        0 => '0',
        1 => '1 ' . __('text.day'),
        2 => '2 ' . __('text.days'),
        3 => '3 ' . __('text.days'),
        4 => '4 ' . __('text.days'),
        5 => '5 ' . __('text.days'),
        6 => '6 ' . __('text.days'),
        7 => '1 ' . __('text.week'),
        14 => '2 ' . __('text.weeks'),
        21 => '3 ' . __('text.weeks'),
        30 => '1 ' . __('text.month')
    ];

}
public static function reqAcceptMode(){
    return [
        'Auto Confirm' => __('text.auto confirm'),
        'Manual Confirm' => __('text.manual confirm')
    ];

}
public static function getWeek($day = null)
{
    $weekDays = [
        __('Monday') => __('Monday'),
        __('Tuesday') => __('Tuesday'),
        __('Wednesday') => __('Wednesday'),
        __('Thursday') => __('Thursday'),
        __('Friday') => __('Friday'),
        __('Saturday') => __('Saturday'),
        __('Sunday') => __('Sunday')
    ];

    if ($day !== null && isset($weekDays[$day])) {
        return $weekDays[$day];
    }

    return $weekDays;
}
public static function getDateTimeFormat()
{
    $dateTimeFormats = [
        'Y-m-d H:i:s' => __('Year-Month-Day Hour:Minute:Second (2024-12-26 11:18:28)'),
        'd-m-Y H:i:s' => __('Day-Month-Year Hour:Minute:Second (26-12-2024 11:18:28)'),
        'm/d/Y H:i:s' => __('Month/Day/Year Hour:Minute:Second (12/26/2024 11:18:28)'),
        'Y-m-d'       => __('Year-Month-Day (2024-12-26)'),
        'd-m-Y'       => __('Day-Month-Year (26-12-2024)'),
        'F j, Y'       => __('Month Day,Year (February 5, 2024)'),
        'd M, Y'       => __('Day Month,Year (05 Feb, 2024)'),
        'H:i:s'       => __('Hour:Minute:Second (11:18:28)'),
        'h:i:s'       => __('12-hour format Hour:Minute:Second (11:18:28 AM/PM)'),
    ];

    return $dateTimeFormats;
}

public static function getTimeOptions($timeGap = '+30 minutes')
{
    $times = [];
    $start = strtotime('12:00 AM');
    $end = strtotime('11:55 PM');

    while ($start <= $end) {
        $time = date('h:i A', $start);
        $times[$time] = $time;
        $start = strtotime($timeGap, $start);
    }

    return $times;
}
public static function statisBusinessHours(){
    return [
        [
            "day" => "Monday",
            "is_closed" => "open",
            "start_time" => "09:00 AM",
            "end_time" => "06:00 PM"
        ],
        [
            "day" => "Tuesday",
            "is_closed" => "open",
            "start_time" => "09:00 AM",
            "end_time" => "06:00 PM"
        ],
        [
            "day" => "Wednesday",
            "is_closed" => "open",
            "start_time" => "09:00 AM",
            "end_time" => "06:00 PM"
        ],
        [
            "day" => "Thursday",
            "is_closed" => "open",
            "start_time" => "09:00 AM",
            "end_time" => "06:00 PM"
        ],
        [
            "day" => "Friday",
            "is_closed" => "open",
            "start_time" => "09:00 AM",
            "end_time" => "06:00 PM"
        ],
        [
            "day" => "Saturday",
            "is_closed" => "closed",
            "start_time" => "09:00 AM",
            "end_time" => "06:00 PM"
        ],
        [
            "day" => "Sunday",
            "is_closed" => "closed",
            "start_time" => "09:00 AM",
            "end_time" => "06:00 PM"
        ]
    ];
}
public static function getDetails($teamId, $location = null)
{
    // Build the query dynamically
    $query = self::where('team_id', $teamId);

    // Add location condition if provided
    if (!is_null($location)) {
        $query->where('location_id', $location);
    }

    // Return the first matching result
    return $query->first();
}


 public static function storeDetail($teamId,$location){
     $businessHours =  self::statisBusinessHours();
           $data = self::updateOrCreate(['team_id' => $teamId,
                'location_id' => $location],
                [
                'created_by' => Auth::user()->id,
                'business_hours' => json_encode($businessHours),
                'created_at'=>Carbon::now()
          ]);
 }


 public static function bookingReminderOptions() {
    return [
        0 =>  __('none'),
        5 => '5 ' . __('text.minutes'),
        10 => '10 ' . __('text.minutes'),
        15 => '15 ' . __('text.minutes'),
        30 => '30 ' . __('text.minutes'),
        60 => '1 ' . __('text.hour'),
        120 => '2 ' . __('text.hours'),
        180 => '3 ' . __('text.hours')
    ];
}
public static function appointmentIntegrationOptions() {
    return [
        2 => __('BookingSG'),
        1 => __('NORAH/Elite'),
        0 => __('No')
    ];
}
public static function maxBookingOptions() {
    return [
        'day' => __('Day'),
        'week' => __('Week'),
        'month' => __('Month')
    ];
}
public static function showCategoryPerRowOptions() {
    return [
        'class-cat-1-row' => '1 '.__('text.Category per Row'),
        'class-cat-2-row' => '2 '.__('text.Categories per Row')
    ];
}

public static function checkBookingSystem(){

    $teamId = tenant('id');
    return Self::where('team_id',$teamId)->value('booking_system');
}

/**
 * check staff assign timeslot and fetch slots
 */

public static function checkStafftimeslot($teamId, $locationId = null, $carbonDate, $categoryId = null, $sitesetting, $staffIds = [])
{

    $bookingSetting = self::where('team_id', $teamId)
        ->where('location_id', $locationId)
        ->where('slot_type', self::BOOKING_SLOT)
        ->first();

    if (!$bookingSetting) {
        return [
            'start_at' => collect(),
            'disabled_date' => []
        ];
    }

    $disabledDates = [];
    $getAdvanceBookingDates = self::datesGet($bookingSetting->allow_req_before ?? 30);
    $serviceTime = Category::where('id',$categoryId)->select('service_time')->first() ?? null;

    $dayOfWeek = Carbon::parse($carbonDate)->format('l');

    // Retrieve all account settings for the staff
    $staffAccounts = self::where('team_id', $teamId)
        ->where('location_id', $locationId)
        ->whereIn('user_id', $staffIds)
        ->where('slot_type', self::STAFF_SLOT)
        ->get();

    $allStaffSlots = [];

    $bookedSlots = Booking::whereDate('booking_date', $carbonDate)
        ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PENDING])
        ->where('team_id', $teamId)
        ->where(function ($query) use ($locationId) {
            $query->where('location_id', $locationId);
        })
        ->select('start_time', 'end_time', DB::raw('count(*) as total'))
        ->groupBy('start_time', 'end_time')
        ->get();

    foreach ($staffAccounts as $index => $account) {
        $businessHours = json_decode($account->business_hours, true);
        $indexedBusinessHours = collect($businessHours)->keyBy('day');

        if (!isset($indexedBusinessHours[$dayOfWeek])) {
            continue; // Skip if no working hour for that day
        }

        $dayBusiness = $indexedBusinessHours[$dayOfWeek];
        $breakHours = $dayBusiness['day_interval'] ?? [];
        $staff_id = $account['user_id'];



        $disabledDates[] = self::disabledDates($businessHours, $carbonDate, $getAdvanceBookingDates);

        $availableSlots = self::getStaffAvailableSlots(
            $carbonDate,
            $dayBusiness,
            $breakHours,
            $account,
            $categoryId,
            'staff',
            $bookingSetting,
            $staff_id,
            $index,
            $bookedSlots,
            $getAdvanceBookingDates,
            $serviceTime,
        );

        $allStaffSlots[] = $availableSlots;
    }

    $combinedSlots = collect();

    // Step 1: Merge all staff slots and sum capacities
    foreach ($allStaffSlots as $slotData) {
        $capacity = $slotData['capacity'];
        $slots = $slotData['slots'];

        foreach ($slots as $slot) {
            $combinedSlots[$slot] = ($combinedSlots[$slot] ?? 0) + $capacity;
        }
    }

    // Step 2: Remove slots from $combinedSlots if total bookings >= capacity
    foreach ($bookedSlots as $booking) {
        $slotKey = Carbon::parse($booking->start_time)->format('h:i A') . '-' . Carbon::parse($booking->end_time)->format('h:i A');

        if (isset($combinedSlots[$slotKey]) && $booking->total >= $combinedSlots[$slotKey]) {
            $combinedSlots->forget($slotKey);
        }
    }

    // Step 3: Sort the slots by time
    $allSlotsSorted = $combinedSlots->keys()->sort(function ($a, $b) {
        return Carbon::createFromFormat('h:i A', explode('-', $a)[0])
            ->greaterThan(Carbon::createFromFormat('h:i A', explode('-', $b)[0])) ? 1 : -1;
    });


    $disabledDate = [];

    $disabledDate = collect($disabledDates)->flatten()->unique()->values()->toArray();
 // Step 2: Collect all staff disabled slot arrays
$allDisabledCollections = collect($allStaffSlots)->pluck('disable');

// Step 3: Get common disabled dates (intersection)
$filteredDisables = $allDisabledCollections->filter(function ($arr) {
    return !empty($arr);
});

$commonDisabledDates = [];
if ($filteredDisables->count()) {
    $commonDisabledDates = $filteredDisables->reduce(function ($carry, $item) {
        return $carry === null ? $item : array_values(array_intersect($carry, $item));
    }, null) ?? [];
}

// Step 4: If slots are empty, return only common disables
if ($allSlotsSorted->isEmpty()) {
    $disabledDate = array_values(array_unique($commonDisabledDates));
} else {
    // Merge commonDisabledDates into disabledDate
    $disabledDate = array_values(array_unique(array_merge($disabledDate, $commonDisabledDates)));
}

//  $disabledDates[] = $disabledDate;
    return [
        'start_at' => $allSlotsSorted->values()->toArray(),
        'disabled_date' => $disabledDate,
    ];
}


public static function calculateChunkDuration(array $slots)
{
    if (empty($slots)) return 0;

    [$firstStart, ] = explode('-', $slots[0]);
    [, $lastEnd] = explode('-', end($slots));

    $start = Carbon::createFromFormat('h:i A', trim($firstStart));
    $end = Carbon::createFromFormat('h:i A', trim($lastEnd));

    return $end->diffInMinutes($start);
}

/**
 * check assign timeslot and fetch slots
 */
public static function checktimeslot($teamId,$locationId = null,$carbonDate,$categoryId=null,$sitesetting){
    $type = $sitesetting->choose_time_slot ?? 'category';

    $bookingslot = self::where('team_id',$teamId)
    ->where('location_id',$locationId)
    ->where('slot_type',self::BOOKING_SLOT)
    ->first();

    $availableSlots = [];
    $disabledDate = [];


    //check on public online booking page
    if($type == 'staff' && !Auth::check()){
        $type = 'category';
    }
    $getAdvanceBookingDates = self::datesGet($bookingslot->allow_req_before ?? 30);
    $appointment_date = $carbonDate;
    $carbonDateFormatted = $carbonDate->toDateString();
    $dayOfWeek = $carbonDate->format( 'l' );
    $lDayOfWeek = Str::lower($dayOfWeek);


    // check time slots of staff
    if($type == 'staff'){

      $accountdetail = self::where('team_id',$teamId)
        ->where('location_id',$locationId)
        ->where('user_id',Auth::id())
        ->where('slot_type',self::STAFF_SLOT)
        ->first();

    //  if($accountdetail->booking_system == 1){
     if(isset($accountdetail)){
       $businessHours = json_decode( $accountdetail->business_hours, true );

       $disabledDate[] = self::disabledDates($businessHours,$carbonDate,$getAdvanceBookingDates);

       $datesList =[];
       $breakHours = [];

      $indexedBusinessHours = array_column( $businessHours, null, 'day' );

        // Filter closed days and map them to day indices and check staff slot is open
        if ( isset( $indexedBusinessHours[ $dayOfWeek ])&& ($indexedBusinessHours[ $dayOfWeek ][ 'is_closed' ] === ServiceSetting::SERVICE_OPEN)) {

            if(in_array(date('d-m-Y',strtotime($carbonDate)),$getAdvanceBookingDates) && !in_array(date('d-m-Y',strtotime($carbonDate)),$datesList)){

                $availableSlots[ 'start_at' ] = self::getAvailableSlots( $carbonDate, $indexedBusinessHours[ $dayOfWeek ], $breakHours, $accountdetail,null,$type,$bookingslot);

            }else{
                $availableSlots = [];
            }
        } else {
           // check location time slots if staff day close

            $accountdetail = CustomSlot::whereDate('selected_date', $carbonDate)
       ->where('slot_type',self::STAFF_SLOT)->where('team_id',$teamId)
        ->where('location_id',$locationId)
        ->where('user_id',Auth::id())->first();

        if(empty($accountdetail)){
         $accountdetail = self::where('team_id',$teamId)
        ->where('location_id',$locationId)
        ->where('slot_type',self::LOCATION_SLOT)
        ->first();
        }



        if(isset($accountdetail)){
                $businessHours = json_decode( $accountdetail->business_hours, true );
                $disabledDate[] = self::disabledDates($businessHours,$carbonDate,$getAdvanceBookingDates);

                $datesList =[];
                $breakHours = [];

                $indexedBusinessHours = array_column( $businessHours, null, 'day' );
                if ( isset( $indexedBusinessHours[ $dayOfWeek ])&& ($indexedBusinessHours[ $dayOfWeek ][ 'is_closed' ] === ServiceSetting::SERVICE_OPEN)) {

                    if(in_array(date('d-m-Y',strtotime($carbonDate)),$getAdvanceBookingDates) && !in_array(date('d-m-Y',strtotime($carbonDate)),$datesList)){
                        $availableSlots[ 'start_at' ] = self::getAvailableSlots( $carbonDate, $indexedBusinessHours[ $dayOfWeek ], $breakHours, $accountdetail,null,'location',$bookingslot);


                    }else{
                        $availableSlots = [];
                    }
                }else{
                    $availableSlots = [];
                }
            }else{
                $availableSlots = [];
            }
        }

    }
    // return $availableSlots;

    }elseif($type == 'category'){

        if(is_null($categoryId)){
            return  $availableSlots = [];
        }
        $accountdetail = self::where('team_id',$teamId)
        ->where('location_id',$locationId)
        ->where('category_id',$categoryId)
        ->where('slot_type',self::CATEGORY_SLOT)->first();


    //    if($accountdetail->booking_system == 1){
       if(isset($accountdetail)){
        $businessHours = json_decode($accountdetail->business_hours, true );
        $disabledDate[] = self::disabledDates($businessHours,$carbonDate,$getAdvanceBookingDates);


        $datesList =[];
        $breakHours = [];

       $indexedBusinessHours = array_column( $businessHours, null, 'day' );

         // Filter closed days and map them to day indices and check staff slot is open
         if ( isset( $indexedBusinessHours[ $dayOfWeek ])&& ($indexedBusinessHours[ $dayOfWeek ][ 'is_closed' ] === ServiceSetting::SERVICE_OPEN)) {

            if(in_array(date('d-m-Y',strtotime($carbonDate)),$getAdvanceBookingDates) && !in_array(date('d-m-Y',strtotime($carbonDate)),$datesList)){

                // dd($carbonDate, $indexedBusinessHours[ $dayOfWeek ], $breakHours, $accountdetail ,$categoryId,$type,$bookingslot);
                $availableSlots[ 'start_at' ] = self::getAvailableSlots( $carbonDate, $indexedBusinessHours[ $dayOfWeek ], $breakHours, $accountdetail ,$categoryId,$type,$bookingslot);
                }else{
                 $availableSlots = [];
             }
         } else {
            // check location time slots if staff day close
         $accountdetail = CustomSlot::whereDate('selected_date', $carbonDate)
       ->where('slot_type',self::CATEGORY_SLOT)->where('team_id',$teamId)
        ->where('location_id',$locationId)
        ->first();

        if(empty($accountdetail)){
         $accountdetail = self::where('team_id',$teamId)
        ->where('location_id',$locationId)
        ->where('slot_type',self::LOCATION_SLOT)
        ->first();
        }



         if(isset($accountdetail)){
                 $businessHours = json_decode($accountdetail->business_hours, true );

                 $datesList =[];
                 $breakHours = [];

                 $indexedBusinessHours = array_column( $businessHours, null, 'day' );
                 if ( isset( $indexedBusinessHours[ $dayOfWeek ])&& ($indexedBusinessHours[ $dayOfWeek ][ 'is_closed' ] === ServiceSetting::SERVICE_OPEN)) {
                     if(in_array(date('d-m-Y',strtotime($carbonDate)),$getAdvanceBookingDates) && !in_array(date('d-m-Y',strtotime($carbonDate)),$datesList)){
                         $availableSlots[ 'start_at' ] = self::getAvailableSlots( $carbonDate, $indexedBusinessHours[ $dayOfWeek ], $breakHours, $accountdetail,null,'location',$bookingslot);


                     }else{
                         $availableSlots = [];
                     }
                 }else{
                     $availableSlots = [];
                 }
             }else{
                 $availableSlots = [];
             }
         }

        }

        // return $availableSlots;

    }else{    // type == 'booking' is using  booking time slots

            $accountdetail = $bookingslot;


           if(isset($accountdetail)){
            $businessHours = json_decode( $accountdetail->business_hours, true );

            $disabledDate[] = self::disabledDates($businessHours,$carbonDate,$getAdvanceBookingDates);

            $datesList =[];
            $breakHours = [];
            $indexedBusinessHours = array_column( $businessHours, null, 'day' );

             // Filter closed days and map them to day indices and check staff slot is open
             if ( isset( $indexedBusinessHours[ $dayOfWeek ])&& ($indexedBusinessHours[ $dayOfWeek ][ 'is_closed' ] === ServiceSetting::SERVICE_OPEN)) {

                if(in_array(date('d-m-Y',strtotime($carbonDate)),$getAdvanceBookingDates) && !in_array(date('d-m-Y',strtotime($carbonDate)),$datesList)){

                    $availableSlots[ 'start_at' ] = self::getAvailableSlots( $carbonDate, $indexedBusinessHours[ $dayOfWeek ], $breakHours, $accountdetail ,$categoryId,$type,$bookingslot);

                    }else{
                     $availableSlots = [];
                 }
             } else {
                // check location time slots if staff day close
               $accountdetail = CustomSlot::whereDate('selected_date', $carbonDate)
                ->where('slot_type',self::BOOKING_SLOT)->where('team_id',$teamId)
                ->where('location_id',$locationId)
                ->first();

            if(empty($accountdetail)){
            $accountdetail = self::where('team_id',$teamId)
            ->where('location_id',$locationId)
            ->where('slot_type',self::LOCATION_SLOT)
            ->first();
            }


             if(isset($accountdetail)){
                     $businessHours = json_decode( $accountdetail->business_hours, true );
                     $disabledDate[] = self::disabledDates($businessHours,$carbonDate,$getAdvanceBookingDates);
                     $datesList =[];
                     $breakHours = [];

                     $indexedBusinessHours = array_column( $businessHours, null, 'day' );
                     if ( isset( $indexedBusinessHours[ $dayOfWeek ])&& ($indexedBusinessHours[ $dayOfWeek ][ 'is_closed' ] === ServiceSetting::SERVICE_OPEN)) {
                         if(in_array(date('d-m-Y',strtotime($carbonDate)),$getAdvanceBookingDates) && !in_array(date('d-m-Y',strtotime($carbonDate)),$datesList)){
                             $availableSlots[ 'start_at' ] = self::getAvailableSlots( $carbonDate, $indexedBusinessHours[ $dayOfWeek ], $breakHours, $accountdetail,null,'location',$bookingslot);


                         }else{
                             $availableSlots = [];
                         }
                     }else{
                         $availableSlots = [];
                     }
                 }else{
                     $availableSlots = [];
                 }
             }

            }
        }

        $merged = array_unique(array_merge(...$disabledDate));
        $availableSlots['disabled_date'] = $merged;

        //if appointment date in disabled then $availableSlots['start_at'] set empty
   if (in_array($carbonDateFormatted, $availableSlots['disabled_date'])) {
        $availableSlots['start_at'] = [];
    }

            // ✅ Filter past time slots only if the selected date is today
            if (isset($availableSlots['start_at'])) {
                $availableSlots['start_at'] = is_array($availableSlots['start_at'])
                    ? $availableSlots['start_at']
                    : $availableSlots['start_at']->toArray();

            $now = Carbon::now();

             if ($carbonDate->isToday()) {
                $availableSlots['start_at'] = collect($availableSlots['start_at'])->filter(function ($slot) use ($now) {
                    [$startTime] = explode('-', $slot);
                    try {
                        $slotStart = Carbon::createFromFormat('h:i A', trim($startTime))
                            ->setDate($now->year, $now->month, $now->day);
                        return $slotStart->greaterThanOrEqualTo($now);
                    } catch (\Exception $e) {
                        return false;
                    }
                })->values()->all(); // convert back to array if needed
            }

            }



        return $availableSlots;
    }




public static function datesGet($adv_date){
    // Initialize an array with today's date
    $datesArray = [];
    // Create a DateTime object for today
    $startDate = new DateTime(); // This sets the start date to today
    if($adv_date == 0){
        $datesArray[] = $startDate->format('d-m-Y');
        return $mergedArray = array_unique($datesArray);
    }

    // Loop to add today’s date and the next $adv_date days
    for ($i = 0; $i <= $adv_date; $i++) {
        $datesArray[] = $startDate->format('d-m-Y');
        // Move to the next day
        $startDate->modify('+1 day');
    }

        // Remove any duplicate dates
        return $mergedArray = array_unique($datesArray);

}

public static function getAvailableSlots($date, $businessHours, $breakHours = [], $accountdetail,$categoryId=null,$type="staff",$bookingSetting)
{

    $periodOfSlot = $bookingSetting->slot_period ?: '10';
    $reqPerSlot = $bookingSetting->req_per_slot ?: '10';
    $allowReqMinDay = $bookingSetting->allow_req_min_before ?: 0;
    $allowReqMaxDay = $bookingSetting->allow_req_before ?: 30;
     $teamId = $bookingSetting->team_id;

    // $categoryId = $accountdetail?->category_id ?? $categoryId;
    // $subCategoryId = $accountdetail->sub_category_id ?? null;
    // $childCategoryId = $accountdetail->child_category_id ?? null;
    $locationId = $accountdetail->location_id ?? $bookingSetting->location_id;
    $availableSlots = [];


    // Check for custom slots
    $customSlotQuery = CustomSlot::whereDate('selected_date', $date)
        ->where('slots_type', $type)->where('team_id', $bookingSetting->team_id)->where('location_id', $locationId);

    // Apply additional filtering based on $type
    if ($type == "staff") {
        $customSlotQuery->where('user_id', Auth::id());
    }

    if ($type == "category") {
        $customSlotQuery->where('category_id', $categoryId);
    }

    $customSlot = $customSlotQuery->first();

    // Use business hours from custom slots if available
    if (isset($customSlot)) {
        $businessHours_get = json_decode($customSlot->business_hours, true);
        $businessHours = $businessHours_get[0];
    }

   if(isset($businessHours)  && $businessHours[ 'is_closed' ] == ServiceSetting::SERVICE_OPEN){
    $availableSlots = new Collection();
    $mainSlots = self::generateSlots($businessHours['start_time'], $businessHours['end_time'], $periodOfSlot);

    $availableSlots = $availableSlots->concat($mainSlots);

    if (!empty($businessHours['day_interval'])) {
        foreach ($businessHours['day_interval'] as $interval) {
            $intervalSlots = self::generateSlots($interval['start_time'], $interval['end_time'], $periodOfSlot);
            $availableSlots = $availableSlots->concat($intervalSlots);
        }
    }
    }

    // Remove booked slots
if(Auth::check() && $type == "staff"){
 $bookedSlots = Booking::whereDate('booking_date', $date)
 ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PENDING])
 ->where('team_id', $teamId)
 ->where(function ($query) use ($locationId) {
     $query->where('location_id', $locationId)
            ->where('staff_id',Auth::id());
 })
 ->select('start_time', 'end_time', DB::raw('count(*) as total'))
 ->groupBy('start_time', 'end_time')
 ->get();
}else{
    $bookedSlots = Booking::whereDate('booking_date', $date)
    ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PENDING])
    ->where('team_id', $teamId)
    ->where(function ($query) use ($locationId, $categoryId) {
        $query->where('location_id', $locationId)
              ->orWhere('category_id', $categoryId)
              ->orWhere('sub_category_id', $categoryId)
              ->orWhere('child_category_id', $categoryId);
    })
    ->select('start_time', 'end_time', DB::raw('count(*) as total'))
    ->groupBy('start_time', 'end_time')
    ->get();

}


    foreach ($bookedSlots as $booking) {
        $bookedStart = Carbon::parse($booking->start_time)->format('h:i A');
        $bookedEnd = Carbon::parse($booking->end_time)->format('h:i A');
      if($booking->total >= $reqPerSlot){
          $availableSlots = $availableSlots->filter(function ($slot) use ($bookedStart, $bookedEnd) {
            [$slotStart, ] = explode('-', $slot); // get only start part
            $slotTime = Carbon::parse(trim($slotStart))->format('h:i A');
            return $slotTime !== $bookedStart;
          });
      }
    }

    $currentTime = Carbon::now(); // or use Carbon::parse($date . ' 09:00 AM') if future day

    $availableSlots = $availableSlots->filter(function ($slot) use ($currentTime, $date) {
        // Ensure $date is in Y-m-d format
        $date = Carbon::parse($date)->format('Y-m-d');

        [$startTime, ] = explode('-', $slot);
        $slotDateTime = Carbon::createFromFormat('Y-m-d h:i A', $date . ' ' . trim($startTime));

        return $slotDateTime->greaterThanOrEqualTo($currentTime);
    })->values(); // Reset array indexes
    // dd($currentTime,$availableSlots);
    return $availableSlots;

}

public static function getStaffAvailableSlots($date, $businessHours, $breakHours = [], $accountdetail,$categoryId=null,$type="staff",$bookingSetting,$staffId = null,$index=0,$bookedSlots,$getAdvanceBookingDates,$slotperiod = 10)
{

$disabledDates = [];
   if($type =="staff" && !empty($slotperiod['service_time'])){
    $periodOfSlot = (int)$slotperiod['service_time'] ?: '10';
    }else{
        $periodOfSlot = $bookingSetting->slot_period ?: '10';
    }

    $reqPerSlot = $bookingSetting->req_per_slot ?: '10';
    $allowReqMinDay = $bookingSetting->allow_req_min_before ?: 0;
    $allowReqMaxDay = $bookingSetting->allow_req_before ?: 30;
     $teamId = $bookingSetting->team_id;


    $locationId = $accountdetail->location_id ?? $bookingSetting->location_id;
    $availableSlots = [];


    // Check for custom slots
    $customSlotQuery = CustomSlot::whereDate('selected_date', $date)
        ->where('slots_type', $type)->where('team_id', $teamId)->where('location_id', $locationId);

    // Apply additional filtering based on $type
    if ($type == "staff") {
        $customSlotQuery->where('user_id',$staffId);
    }

    if ($type == "category") {
        $customSlotQuery->where('category_id', $categoryId);
    }

    $customSlot = $customSlotQuery->first();

    // Use business hours from custom slots if available
    if (isset($customSlot)) {
        $businessHours_get = json_decode($customSlot->business_hours, true);
        $businessHours = $businessHours_get[0];
    }

   if(isset($businessHours)  && $businessHours[ 'is_closed' ] == ServiceSetting::SERVICE_OPEN){
    $availableSlots = new Collection();
    $mainSlots = self::generateSlots($businessHours['start_time'], $businessHours['end_time'], $periodOfSlot);

    $availableSlots = $availableSlots->concat($mainSlots);

    if (!empty($businessHours['day_interval'])) {
        foreach ($businessHours['day_interval'] as $interval) {
            $intervalSlots = self::generateSlots($interval['start_time'], $interval['end_time'], $periodOfSlot);
            $availableSlots = $availableSlots->concat($intervalSlots);
        }
    }
    }else{
         $disabledDates = self::disabledDates($businessHours,$date,$getAdvanceBookingDates);
    }
if (!empty($availableSlots)) {
    $currentTime = Carbon::now();

    $availableSlots = $availableSlots->filter(function ($slot) use ($currentTime, $date) {
        $date = Carbon::parse($date)->format('Y-m-d');
        [$startTime, ] = explode('-', $slot);
        $slotDateTime = Carbon::createFromFormat('Y-m-d h:i A', $date . ' ' . trim($startTime));
        return $slotDateTime->greaterThanOrEqualTo($currentTime);
    })->values(); // reset keys

       // 🔽 Add this block here
    // if ($serviceTimeInMinutes > 0) {
    //     $validSlots = collect();
    //     $slotChunks = [];
    //     $currentChunk = [];
    //     $previousEndTime = null;

    //     foreach ($availableSlots as $slot) {
    //         [$start, $end] = explode('-', $slot);
    //         $startTime = Carbon::createFromFormat('h:i A', trim($start));
    //         $endTime = Carbon::createFromFormat('h:i A', trim($end));

    //         if ($previousEndTime && !$previousEndTime->equalTo($startTime)) {
    //             // Not continuous, reset
    //             if (self::calculateChunkDuration($currentChunk) >= $serviceTimeInMinutes) {
    //                 $slotChunks[] = $currentChunk;
    //             }
    //             $currentChunk = [];
    //         }

    //         $currentChunk[] = $slot;
    //         $previousEndTime = $endTime;
    //     }

    //     // Final chunk
    //     if (self::calculateChunkDuration($currentChunk) >= $serviceTimeInMinutes) {
    //         $slotChunks[] = $currentChunk;
    //     }

    //     // Flatten valid chunks
    //     $availableSlots = collect($slotChunks)->flatten()->unique()->values();
    // }
}

//disbale closed custom dates of staff
$customslots = CustomSlot::where('slots_type', "staff")
    ->where('team_id', $teamId)
    ->where('location_id', $locationId)
    ->where('user_id', $staffId)
    ->select('id', 'selected_date', 'business_hours')
    ->get();



if ($customslots->isNotEmpty()) {
    foreach ($customslots as $customslot) {
        $getcustomslot = json_decode($customslot->business_hours, true);
        $getbusinessHours = $getcustomslot[0] ?? null;

        if (
            isset($getbusinessHours) &&
            isset($getbusinessHours['is_closed']) &&
            $getbusinessHours['is_closed'] == ServiceSetting::SERVICE_CLOSE
        ) {
            $disabledDates[] = $customslot->selected_date;
        }
    }
}
return [
    'staff_id' => $staffId,
    'capacity' => $reqPerSlot,
    'slots' => $availableSlots,
    'disable' => $disabledDates,
];
    // return $availableSlots;

}


// Function to filter slots based on existing appointments
public static function  filterSlots($availableSlots, $appointments)
{
    return $availableSlots->filter(function ($slot) use ($appointments) {
        foreach ($appointments as $appointment) {
            $appointmentStart = Carbon::parse($appointment['start_time']);
            $appointmentEnd = Carbon::parse($appointment['end_time']);

            if (Carbon::parse($slot)->between($appointmentStart, $appointmentEnd)) {
                return false;
            }
        }
        return true;
    });
}

public static function disabledDates($businessHours,$selecteddate,$getAdvanceBookingDates){

    $selecteddate = Carbon::parse($selecteddate);

    $month = $selecteddate->month ?? Carbon::now()->month;
    $year = $selecteddate->year ?? Carbon::now()->year;

    $closedDays = collect($businessHours)
    ->where('is_closed', 'closed')
    ->pluck('day')
    ->map(fn($day) => strtolower($day)) // Convert to lowercase for Carbon compatibility
    ->toArray(); // e.g., ['monday', 'sunday']

    $disabledDates = [];

    // Start and end of selected month
    $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
    $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();


    $period = CarbonPeriod::create($start, $end);

    $today = Carbon::today();

    foreach ($period as $date) {
        if ($date->lessThan($today)) {
            continue; // Skip past dates
        }

        if (in_array($date->format('l'), array_map('ucfirst', $closedDays))) {
            $disabledDates[] = $date->format('Y-m-d');
        }
    }
    return $disabledDates;
}

public static function dayOffList($dayOffs){
    $datesList =[];
    foreach ($dayOffs as $dayOff) {
        $startDate = Carbon::createFromFormat('d/m/Y', $dayOff['start_date']);
        $endDate = Carbon::createFromFormat('d/m/Y', $dayOff['end_date']);

        while ($startDate->lte($endDate)) {
            $datesList[] = $startDate->format('Y-m-d');
            $startDate->addDay();
        }
    }
  return $datesList;
}

// public static function generateSlots($start, $end, $interval = 30)
// {
//     $slots = [];
//     $current = Carbon::parse($start);
//     $end = Carbon::parse($end);

//     while ($current->lt($end)) {
//         $start_at = $current->format('h:i A');
//         $current->addMinutes((int)$interval);
//         $slots[$start_at] = $start_at;
//     }
//     dd($slots);
//     return $slots;
// }

public static function generateSlots($start, $end, $interval = 10)
{
    $slots = [];
    $current = Carbon::parse($start);
    $end = Carbon::parse($end);

    while ($current->lt($end)) {
        $start_at = $current->format('h:i A');
        $current->addMinutes((int)$interval);
        $end_at = $current->format('h:i A');

        if ($current->lte($end)) {
            $label = "$start_at-$end_at";
            $slots[$label] = $label;
        }
    }


    return $slots;
}


}
