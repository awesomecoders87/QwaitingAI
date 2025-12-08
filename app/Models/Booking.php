<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;
use App\Events\QueueCreated;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
 {
    use HasFactory,SoftDeletes;

    protected $fillable = [ 'team_id',
    'work_permit',
    'reference_number',
    'pax',
    'booking_type',
    'booking_date',
    'booking_time',
    'refID',
    'name',
    'phone',
    'email',
    'category_id',
    'sub_category_id',
    'child_category_id',
    'level_id',
    'location_id',
    'start_time',
    'end_time',
    'created_by',
    'cancel_reason',
    'cancel_remark',
    'interview_mode',
    'meeting_link',
    'json',
    'staff_id',
    'status',
    'is_convert',
    'is_notification_sent',
    'is_rescheduled',
    'suspension_logs_id',
    'created_at',
    'updated_at',
    'campaign_id',
    'phone_code',
     'last_category',
    'count',
];

    const STATUS_PENDING = 'Pending';
    const STATUS_CONFIRMED = 'Confirmed';
    const STATUS_INPROGRESS = 'In Progress';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_COMPLETED = 'Completed';
    const MANUAL_CONFIRM = 'Manual Confirm';
    const AUTO_CONFIRM = 'Auto Confirm';
    const STATUS_YES = 'Yes';
    const STATUS_NO = 'No';
    const ENABLE = '1';
    const DISABLE = '0';

    public $booking_refID;
    public $teamId;
    public $location;
    public $token_start;
    public $siteDetails;
    public $acronym;
    public $showTicketText;
    public $showTicketText_2;

    public $categoryName = '';
    public $secondCategoryName = '';
    public $thirdCategoryName = '';
    public $selectedCategoryId = '';
    public $locationName = '';
    public $secondChildId = '';
    public $thirdChildId = '';
    public $countCatID = 0;
    public $fieldCatName = '';
    public $counterID = 0;

    public function team(): BelongsTo
 {
        return $this->belongsTo(Tenant::class );
    }

    public function location(): BelongsTo
 {
    return $this->belongsTo(Location::class);

    }

    public function categories(): BelongsTo
 {
        return $this->belongsTo(Category::class, 'category_id', 'id')->where( [ 'level_id'=>Level::getFirstRecord()?->id ] )->withTrashed();
}

    public function sub_category(): BelongsTo
 {
        return $this->belongsTo(Category::class, 'sub_category_id', 'id' )->where( [ 'level_id'=>Level::getSecondRecord()?->id ] )->withTrashed();
    }
    public function book_sub_category(): BelongsTo
 {
        return $this->belongsTo(Category::class, 'sub_category_id', 'id' )->withTrashed();
    }
    public function book_child_category(): BelongsTo
 {
        return $this->belongsTo( Category::class, 'child_category_id', 'id' )->withTrashed();
    }

    public function child_category(): BelongsTo
 {
        return $this->belongsTo( Category::class, 'child_category_id', 'id' )->where( [ 'level_id'=>Level::getThirdRecord()?->id ] )->withTrashed();
    }

    public function createdBy(): BelongsTo
 {
        return $this->belongsTo( User::class, 'created_by', 'id' )->withTrashed();
    }
    public function staff(): BelongsTo
 {
        return $this->belongsTo( User::class, 'staff_id','id')->withTrashed();
    }

    public static function checkBooking( $team_id, $appointment_date, $select_time_slot, $location = null )
 {
        $query = self::where( [
            'team_id' => $team_id,
            'booking_date' => $appointment_date,
            'booking_time' => $select_time_slot,
        ] );

        if ( !empty( $location ) ) {
            $query->where( 'location_id', $location );
        }

        return $query->exists();
    }

    public static function maxBookingPerService( $selectedDate = null, $teamID, $locationID, $serviceID, $maxBookings = 1 )
 {
        $carbonDate = $selectedDate ?  $selectedDate : Carbon::now();
        $month = $carbonDate->month;
        $year = $carbonDate->year;
        $datesExceedingLimit = [];
        $startDate = Carbon::createFromDate( $year, $month, 1 )->startOfMonth();
        $endDate = Carbon::createFromDate( $year, $month, 1 )->endOfMonth();
        for ( $date = $startDate; $date->lte( $endDate );
        $date->addDay() ) {

           $bookingsCount = Booking::whereDate('booking_date', $date->toDateString() )
            ->where( [ 'location_id' => $locationID, 'category_id' => $serviceID, 'status' => self::STATUS_CONFIRMED, 'team_id'=> $teamID ] )
            ->count();
            if ( $bookingsCount >= $maxBookings ) {
                $datesExceedingLimit[] = $date->toDateString();
            }
        }

        return $datesExceedingLimit;
    }

    public static function getClosedDates( $startDate, $endDate, $closedDays ) {
        $start = Carbon::parse( $startDate );
        $end = Carbon::parse( $endDate );
        $closedDates = [];

        while ( $start->lte( $end ) ) {
            if ( in_array( $start->format( 'l' ), $closedDays ) ) {
                $closedDates[] = $start->format( 'Y-m-d' );
            }
            $start->addDay();
        }

        return $closedDates;
    }
    public static function viewBooking( $id, $teamId ) {
     return self::with( [ 'location', 'categories', 'sub_category', 'child_category' ] )->where( [ 'team_id' =>$teamId, 'id'=>$id ] )->first();
    }


    public static function getStatus(){

        return[
            'Pending' => __('text.Pending'),
            'In Progress' => __('text.In Progress'),
            'Confirmed' => __('text.Confirmed'),
            'Cancelled' => __('text.Cancelled'),
            'Completed' => __('text.Completed'),
            'Checkin' => __('text.Checkin'),
        ];
    }
    public static function getBookingStatus(){

        return[
            'Confirmed' => __('Confirmed'),
            'Cancelled' => __('Cancelled'),
        ];
    }


    public static function convertToQueue($booking)
    {
        try {
            $dynamicProperties = json_decode($booking->json,true);
            $selectedCategoryId = (int)$booking->category_id;
            $secondChildId = !empty($booking->sub_category_id ) ? (int)$booking->sub_category_id : null;
            $thirdChildId = !empty($booking->child_category_id) ? (int)$booking->child_category_id : null;
            $location = $booking->location_id;
            $teamId = $booking->team_id;

            $siteDetails = SiteDetail::where('team_id', $teamId)
            ->where('location_id',$location)
            ->first();

            $formattedFields = [];
            foreach ($dynamicProperties as $key => $value) {
                $fieldName = preg_replace('/_\d+/', '', $key);
                $formattedFields[$fieldName] = $value;
            }

            $name = $formattedFields['name'] ?? null;
            $phone = $formattedFields['phone'] ?? null;
            $phone_code = $formattedFields['phone_code'] ?? null;
            $email = $formattedFields['email'] ?? ($formattedFields['Email'] ?? ($formattedFields['email_address'] ?? null));
           $jsonDynamicData = json_encode($formattedFields);

            $bookingSetting = $siteDetails->booking_system ?? SiteDetail::STATUS_YES;


           $acronym = ($bookingSetting == Queue::STATUS_NO)
                ? Category::viewAcronym($selectedCategoryId)
                : SiteDetail::DEFAULT_WALKIN_A;

          $lastToken = Queue::getLastToken($teamId, $acronym, $location);

            $tokenDigit = $siteDetails?->token_digit ?? 4;
            $isExistToken = true;

            while ($isExistToken) {
              $newToken = Queue::newGeneratedToken($lastToken, $siteDetails?->token_start, $tokenDigit);
                if (strlen($newToken) > $tokenDigit) {
                    return response()->json(['error' => 'Unable to create more tickets'], 400);
                }

              $isExistToken = Queue::checkToken($teamId, $acronym, $newToken, $location);

                if ($isExistToken) {
                    $lastToken = $newToken;
                } else {
                    $tokenStart = $newToken;
                    $isExistToken = false;
                }
            }

            $nextPrioritySort = QueueStorage::getNextPrioritySort($selectedCategoryId,$teamId,$location);
            $todayDateTime = Carbon::now();

      $storeData = [
                'name' => $name,
                'phone' => $phone,
                'phone_code' => $phone_code,
                'category_id' => $selectedCategoryId,
                'sub_category_id' => $secondChildId,
                'child_category_id' => $thirdChildId,
                'team_id' => $teamId,
                'token' => $tokenStart,
                'token_with_acronym' => $bookingSetting == Queue::STATUS_NO ? Queue::LABEL_YES : Queue::LABEL_NO,
                'json' => $jsonDynamicData,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $acronym,
                'locations_id' => $location,
                'priority_sort' => (int)$nextPrioritySort,
            ];

            $queueCreated = Queue::storeQueue([
                'team_id' => $teamId,
                'token' => $tokenStart,
                'start_acronym' => $acronym,
                'token_with_acronym' => $storeData['token_with_acronym'],
                'locations_id' => $location,
                'arrives_time' => $todayDateTime,
            ]);

            $queueStorage = QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id,'booking_id'=>$booking->id]));

            QueueCreated::dispatch( $queueStorage );

            $fieldCatName = 'category_id';
           $countCatID =  $selectedCategoryId;

            if ( $siteDetails?->category_estimated_time == SiteDetail::STATUS_YES ){
                if ( !empty( $thirdChildId ) ) {
                    if($siteDetails?->category_level_est == 'automatic'){

                        $fieldCatName = 'child_category_id';
                        $countCatID =  $thirdChildId;
                    }elseif($siteDetails?->category_level_est == 'child'){
                        $fieldCatName = 'sub_category_id';
                        $countCatID =  $secondChildId;
                    }else{
                        $fieldCatName = 'category_id';
                        $countCatID =  $selectedCategoryId;
                    }
                } else if ( !empty($secondChildId) ) {

                    if($siteDetails?->category_level_est == 'child'){
                        $fieldCatName = 'sub_category_id';
                        $countCatID =  $secondChildId;
                    }else{
                        $fieldCatName = 'category_id';
                        $countCatID =  $selectedCategoryId;
                    }
                } else {
                    $fieldCatName = 'category_id';
                    $countCatID =  $selectedCategoryId;
                }
            }


            if ( $siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO )
            $counterID  = 0;
           $pendingCount = QueueStorage::countPending($teamId, $queueStorage->id, $countCatID,  $fieldCatName, '', $location);

           $thirdCategoryName = $secondCategoryName = $categoryName =$locationName = '';

           if ( !empty( $thirdChildId ) )
           $thirdCategoryName = Category::viewCategoryName( $thirdChildId );
           if ( !empty( $secondChildId ) )
           $secondCategoryName = Category::viewCategoryName( $secondChildId );
           if ( !empty( $selectedCategoryId ) )
           $categoryName =  Category::viewCategoryName( $selectedCategoryId );
           if ( !empty( $location ) )
           $locationName =  Location::locationName( $location );

        $data = [
                'name' => $queueStorage->name,
                'phone' => $queueStorage->phone,
                'queue_no' => $queueCreated->id,
                'arrives_time' => $todayDateTime->format(AccountSetting::showDateTimeFormat()),
                'category_name' => $categoryName,
                'secondC_name' => $secondCategoryName,
                'thirdC_name' => $thirdCategoryName,
                'pending_count' => $pendingCount,
                'token' => $queueCreated->token,
                'token_with_acronym' => $queueCreated->start_acronym,
                'location_name' =>$locationName,

            ];

            $logo =  SiteDetail::viewImage( SiteDetail::FIELD_LOGO_PRINT_TICKET, $teamId );
            $waitingTime = 0;
            if ( !empty( $siteDetails ) ) {
                if ( $siteDetails->ticket_text_enable == SiteDetail::STATUS_YES ) {
                    $estimate_time = $siteDetails->estimate_time ?? 0 ;

                    $waitingTime =  $estimate_time * $data[ 'pending_count' ];

                    if ( !empty( $siteDetails->ticket_text_2 ) )
                    $showTicketText_2 = str_replace( '{{Waiting Time}}', $waitingTime, $siteDetails->ticket_text_2 );

                    if ( !empty( $siteDetails->ticket_text ) ) {
                        $text = str_replace( '{{QUEUE COUNT}}', $data[ 'pending_count' ], $siteDetails->ticket_text );
                        $showTicketText = str_replace( '{{Waiting Time}}', $waitingTime, $text );
                    }

                }
            }
            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();


            $ticket=[
                'timer'=>4000,
                'html'=>'<div style="padding-top:20px;text-align:center" class="flex content-center gap-4"> <img src="'.asset( $logo ).'" class="w-100 h-100" style="margin:auto;max-width:160px"/></div><div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;"><h3 style="font-size:16px;margin:0">Name: '.$data[ 'name' ].'</h3><div ><h3 style="font-size:16px;margin:0">Queue No. '.$acronym.$data[ 'token' ].'</h3></div><div><h5 style="font-size:16px;margin:0">Arrived:'. $data[ 'arrives_time' ].'</h5></div><div><h3 style="font-size:16px;margin:0">Branch Name: '.$data[ 'location_name' ].'</h3></div>  <div><h3 style="font-size:16px;margin:0">'.$data[ 'category_name' ].'</h3><h3 style="font-size:16px;margin:0">'.$data[ 'secondC_name' ].'</h3><h3 style="font-size:16px;">'.$data[ 'thirdC_name' ].'</h3></div> <div><h4 style="font-size:16px;margin:0">'.$showTicketText .'</h4><h4 style="font-size:16px;margin:0">'.$showTicketText_2 .'</h4></div></div>',
                'confirmButtonText'=>'Thank You',
                'token_notify'=>'The Generated Token Number is '.$acronym.$data[ 'token' ]
            ];

            return (['status' => 'success','ticket'=>$ticket]);
        } catch (\Throwable $ex) {
            Log::error('Error storing queue data: ' . $ex->getMessage());
            return (['status' => 'error','message'=>$ex->getMessage()]);
        }
    }

     public static function checkBookingSlotsLimit($data = [])
    {
        // Validate input
        if (empty($data)) {
            return [
                'status' => false,
                'count' => 0,
                'freeslotId' => null
            ];
        }

        // 1. Check free slot counter table first
        $checkfreeslot = QueueFreeSlotCount::where('booking_date', $data['appointment_date'])
            ->where('team_id', $data['team_id'])
            ->where('location_id', $data['location_id'])
            ->where('sb_start_time', $data['start_time'])
            ->where('sb_end_time', $data['end_time'])
            ->first();

        if ($checkfreeslot) {
            $currentCount = (int) $checkfreeslot->count;

            if ($currentCount < (int) $data['capacity_per_slot']) {
                return [
                    'status' => true,
                    'count' => $currentCount,
                    'freeslotId' => $checkfreeslot->id
                ];
            }

            return [
                'status' => false,
                'count' => $currentCount,
                'freeslotId' => $checkfreeslot->id
            ];
        }

        // 2. Otherwise, check bookings directly
        $booking = Self::where('booking_date', $data['appointment_date'])
            ->where('team_id', $data['team_id'])
            ->where('location_id', $data['location_id'])
            ->where('start_time', $data['start_time'])
            ->where('end_time', $data['end_time'])
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->orderBy('count')
            ->first();

        $currentCount = $booking ? (int) $booking->count : 0;

        if ($currentCount < (int) $data['capacity_per_slot']) {
            return [
                'status' => true,
                'count' => $currentCount + 1,
                'freeslotId' => null
            ];
        }

        return [
            'status' => false,
            'count' => $currentCount,
            'freeslotId' => null
        ];
    }


}
