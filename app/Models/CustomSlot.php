<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CustomSlot extends Model
{
    use HasFactory;

    protected $table = 'custom_slots';

    protected $fillable = ['team_id','location_id','category_id','user_id','slots_type','selected_date','slot_period','req_per_slot','pax_per_service','allow_req_before','allow_cancel_before','req_accept_mode',
    'business_hours','non_business_hours','slot_type','created_by','created_at','updated_at'];
    
    protected $casts = [
        'business_hours' => 'array',
        'non_business_hours' => 'array',
    ];

    const SERVICE_OPEN ='open';
    const SERVICE_CLOSE ='closed';
    const LOCATION_SLOT ='location';
    const BOOKING_SLOT ='booking';
    const STAFF_SLOT ='staff';
    const CATEGORY_SLOT ='category';
    const TICKET_SLOT ='ticket';
    
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
public static function getAvailableSlots($date, $businessHours, $breakHours = [], $periodOfSlot = 30)
{
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
        ->get(['start_time', 'end_time']);

    foreach ($bookedSlots as $booking) {
        $bookedStart = Carbon::parse($booking->start_time)->format('h:i A');
        $bookedEnd = Carbon::parse($booking->end_time)->format('h:i A');

        $availableSlots = $availableSlots->filter(function ($slot) use ($bookedStart, $bookedEnd) {
            $slotTime = Carbon::parse($slot)->format('h:i A');
            return !($slotTime == $bookedStart);
        });
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
    



}
