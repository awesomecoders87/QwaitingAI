<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ServiceSetting extends Model
{
    use HasFactory;

    protected $fillable = ['team_id','location_id','category_id','slot_period','req_per_slot','pax_per_service','allow_req_before','allow_cancel_before','req_accept_mode',
    'business_hours','non_business_hours','break_monday','break_tuesday','break_wednesday','break_thursday','break_friday','break_saturday','break_sunday','day_off','slot_type','custom_business_hours',
    'created_by','created_at','updated_at'];
    
    protected $casts = [
        'business_hours' => 'array',
        'custom_business_hours' => 'array',
        'non_business_hours' => 'array',
    ];

    const SERVICE_OPEN ='open';
    const SERVICE_CLOSE ='closed';
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public static function getDetails($teamId,$locID,$catID){
        return  self::where( [ 'team_id'=> $teamId, 'location_id'=>$locID,'category_id' => $catID ] )->first();
     }

    public static function storeData($teamId,$locID,$catID){
      $accountSetting = AccountSetting::getDetails($teamId,$locID);
        if ($accountSetting) {
            $businessHours = json_decode($accountSetting->business_hours, true);
            $serviceSetting =  self::getDetails($accountSetting->team_id,$accountSetting->location_id,$catID);
            if(empty($serviceSetting  )){
                return self::create(  [
                    'business_hours' => json_encode($businessHours),
                    'created_by' => Auth::user()->id,
                    'team_id' => $accountSetting->team_id,
                    'location_id' => $accountSetting->location_id,
                    'category_id' =>$catID
                 ]);
            }
         
        }
        
    }

  
public static function generateSlots($start, $end, $interval = 30)
{
    $slots = [];
    $current = Carbon::parse($start);
    $end = Carbon::parse($end);

    while ($current->lt($end)) {
        $start_at = $current->format('h:i A');
        $current->addMinutes($interval);    
        $slots[$start_at] = $start_at;
    }

    return $slots;
}

// Function to get available slots for a specific date
public static function getAvailableSlots($date, $businessHours, $breakHours = [], $servicedetail)
{
    $periodOfSlot = $servicedetail->slot_period ?? '10';
    $reqPerSlot = $servicedetail?->req_per_slot ?? '1';
    $paxPerService = $servicedetail?->pax_per_service ?? '1';
    $categoryId = $servicedetail?->category_id;
    $subCategoryId = $servicedetail?->sub_category_id ?? null;
    $childCategoryId = $servicedetail?->child_category_id ?? null;
    $locationId = $servicedetail?->location_id ?? null;


    // Check for custom slots
    $customSlot = CustomSlot::whereDate('selected_date', $date)
        ->where('category_id', $categoryId)
        ->where(function ($query) use ($locationId) {
            $query->where('location_id', $locationId);
        })
        ->first();

  
    // Use business hours from custom slots if available
    if ($customSlot) {
        $businessHours_get = json_decode($customSlot->business_hours, true);
        $businessHours = $businessHours_get[0];
    }
   
    $availableSlots = new Collection();
    $mainSlots = self::generateSlots($businessHours['start_time'], $businessHours['end_time'], $periodOfSlot);
    $availableSlots = $availableSlots->concat($mainSlots);

    if (!empty($businessHours['day_interval'])) {
        foreach ($businessHours['day_interval'] as $interval) {
            $intervalSlots = self::generateSlots($interval['start_time'], $interval['end_time'], $periodOfSlot);
            $availableSlots = $availableSlots->concat($intervalSlots);
        }
    }

    // Remove slots that overlap with break hours
    foreach ($breakHours as $break) {
        $breakStart = Carbon::parse($break['start_time']);
        $breakEnd = Carbon::parse($break['end_time']);

        $availableSlots = $availableSlots->filter(function ($slot) use ($breakStart, $breakEnd) {
            $slotTime = Carbon::parse($slot);
            return !($slotTime->gte($breakStart) && $slotTime->lt($breakEnd));
        });
    }

    // Remove booked slots
    $bookedSlots = Booking::whereDate('booking_date', $date)
    ->where('status', Booking::STATUS_CONFIRMED)
    ->where('category_id', $categoryId)
    ->where(function ($query) use ($locationId, $subCategoryId, $childCategoryId) {
        $query->where('location_id', $locationId)
              ->orWhere('sub_category_id', $subCategoryId)
              ->orWhere('child_category_id', $childCategoryId);
    })
    ->select('start_time', 'end_time', DB::raw('count(*) as total'))
    ->groupBy('start_time', 'end_time')
    ->get();


 
 
    foreach ($bookedSlots as $booking) {
        $bookedStart = Carbon::parse($booking->start_time)->format('h:i A');
        $bookedEnd = Carbon::parse($booking->end_time)->format('h:i A');
      if($booking->total == $reqPerSlot){
          $availableSlots = $availableSlots->filter(function ($slot) use ($bookedStart, $bookedEnd) {
              $slotTime = Carbon::parse($slot)->format('h:i A');
              return !($slotTime == $bookedStart);
          });
      }
    }


    // Filter out past slots if the date is today
    $current_time = Carbon::now()->format('h:i A');

    if (Carbon::parse($date)->isToday()) {
        $availableSlots = $availableSlots->filter(function ($slot) use ($current_time) {
            return Carbon::parse($slot)->gt(Carbon::parse($current_time));
        });
    }

    return $availableSlots->values();
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

}
