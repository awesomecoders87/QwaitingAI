<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Auth;
use DB;
use Log;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class QueueStorage extends Model
{

    use HasFactory;
    protected $table="queues_storage";
    protected $fillable =['id','name','team_id','queue_id','phone','token','token_with_acronym','json','category_id','sub_category_id','child_category_id','status','created_at','updated_at','counter_id','locations_id','closed_by','start_datetime','closed_datetime','transfer_id','called_datetime','is_missed','waiting_time','arrives_time','is_notification_sent','cancelled_datetime','late_duration','is_arrived','served_by','datetime','start_acronym','esitmate_note','reset_call','reset_call_by','rating','ticket_mode','hold_start_datetime','hold_end_datetime','is_hold','temp_hold','forward_counter_id','priority_sort','queue_count','booking_id','created_by','phone_code','suspension_logs_id','assign_staff_id', 'campaign_id','mode','dropoff_position','hold_by','meeting_link','is_virtual_meeting','salesforce_lead','senior_citizen','full_phone_number','called','transfer_by','alert_waiting_show','doc_file','actual_staff_assign_id','is_rest'];

    const STATUS_PENDING = 'Pending';
    const STATUS_START = 'Start';
    const STATUS_READY = 'Ready';
    const STATUS_SKIP = 'Miss';
    const STATUS_PROGRESS = 'Progress';
    const STATUS_PAUSE = 'Pause';
    const STATUS_CLOSE = 'Close';
    const STATUS_RESET = 'Reset';
    const STATUS_MOVE = 'Move';
    const STATUS_CANCELLED ='Cancelled';

   const STATUS_YES = 1;
   const STATUS_NO = 0;
   const LABEL_YES = 'Yes';
   const LABEL_NO ='No';
   const SERVED_MISSED ='Served + Missed Calls';
   const MOVE_BACK_TO_MQ ='Missed Queue List';
   const TICKET_MODE_MOBILE ='Mobile';
   const TICKET_MODE_Walk_IN ='Walk-IN';
   const INITIAL_VISITOR_SHOW_COUNT = 6;
   const MAX_QUEUE_DISPLAY = 20;
   const SORTABLE = "Sort";
   const DEFAULT_QUEUE = "Default";


      public function counter(): BelongsTo
      {
          return $this->belongsTo(Counter::class, 'counter_id', 'id')->withTrashed();
      }

      public function forwardcounter(): BelongsTo
      {
          return $this->belongsTo(Counter::class, 'forward_counter_id', 'id')->withTrashed();
      }

      public function teams(): BelongsTo
      {
          return $this->belongsTo(Tenant::class, 'team_id', 'id')->withTrashed();
      }


    public function transfer()
    {
        return $this->belongsTo(Category::class, 'transfer_id')->withTrashed();
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withTrashed();
    }
    public function location()
    {
        return $this->belongsTo(Location::class, 'locations_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id')->withTrashed();
    }
    public function rating()
    {
        return $this->hasMany(Rating::class);
    }

    public function childCategory()
    {
        return $this->belongsTo(Category::class, 'child_category_id')->withTrashed();
    }
    public function queues()
    {
        return $this->belongsTo(Queue::class);
    }


    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by')->withTrashed();
    }

    public function transferBy()
    {
        return $this->belongsTo(User::class, 'transfer_by')->withTrashed();
    }

    public function assignStaff()
    {
        return $this->belongsTo(User::class, 'assign_staff_id')->withTrashed();
    }

    public function servedBy()
    {
        return $this->belongsTo(User::class, 'served_by')->withTrashed();
    }

    public static function getRatingEmoji($type){

      $data = [
        'Excellent' => 4,
        'Good' => 3,
        'Neutral' => 2,
        'Poor' => 1,
      ];
      if(array_key_exists($type,$data))
          return $data[$type];


          return null;
    }


    public static function getEmojiText(){
      return [
        4=>['emoji'=> '😀','label'=>'Excellent', 'range' => [4, 5]],
        3=>['emoji'=> '😊','label'=>'Good', 'range' => [3, 3.99]],
        2=>['emoji'=> '😐','label'=>'Neutral', 'range' => [2, 2.99]],
        1=>['emoji'=> '🙁','label'=>'Poor', 'range' => [1, 1.99]],
      ];
    }

    public static function getFirstRecord(){
      return self::first();
    }
    public static function getSecondRecord(){
        return self::skip(1)->take(1)->first();
      }
      public static function getThirdRecord(){
        return self::skip(2)->take(1)->first();
      }

      protected $casts = [
        'called_datetime' => 'datetime',
        'arrives_time' => 'datetime',
        'datetime' => 'datetime',
        'closed_datetime' => 'datetime',
        'start_datetime' => 'datetime',
        'hold_start_datetime ' => 'datetime',
        'hold_end_datetime  ' => 'datetime',

    ];


    //   public static function getPendingQueues($conditionTeam, $isFixed = false,$location = null, $page=null, $name= null,$team_id = null,$queueType = null )
    // {
    //   $userAuth = Auth::user(); // Assuming you are using Laravel's Auth facade
    //   $query = Queue::join('categories', 'queues.category_id', '=', 'categories.id')
    //   ->select('queues.*','categories.id as cat_id','categories.visitor_in_queue')
    //   ->where(function ($query) use ($conditionTeam, $userAuth, $location,$name,$team_id) {
    //     $query = $query->where('queues.team_id', $team_id)
    //           ->where('queues.is_missed', self::STATUS_NO)
    //           ->whereNull('queues.start_datetime')
    //           ->whereNull('queues.called_datetime')
    //           ->whereNull('queues.cancelled_datetime')
    //           ->whereNull('queues.closed_datetime')
    //           ->whereDate('queues.arrives_time', Carbon::today());

    //           if(!empty($location)){
    //             $query->where('queues.locations_id', $location)->whereNotNull('queues.locations_id');
    //           }
    //           if(!empty($name)){
    //             $query->where(function( $query ) use($name) {
    //                 $query->where( 'queues.name', 'like', '%' . $name . '%' )
    //                 ->orWhere( function( $query )  use ($name) {
    //                     $query->where( 'queues.token', 'like', '%' . $name . '%' )
    //                     ->orWhere( 'queues.start_acronym', 'like', '%' . $name . '%' );
    //                 }
    //             );
    //          });
    //     }

    //     if (!empty($userAuth) && !$userAuth->hasRole(User::ROLE_ADMIN)) {
    //            self::subQueryCategory($query,$userAuth);
    //       }
    //   });

    //   if($queueType == Queue::SORTABLE){
    //     $query->orderBy('categories.sort', 'asc');
    //   }

    //   $query->orderBy('queues.datetime', 'asc');


    //   if ($isFixed == true ) {
    //     $result =  $query->paginate(10)?->items();
    //   }else{
    //     $result = $query->paginate($page)?->items();
    //   }
    //   return collect($result);
    // }

    public static function getPendingQueues($conditionTeam, $isFixed = false, $location = null, $page = null, $name = null, $team_id = null, $queueType = null)
    {
        $userAuth = Auth::user();

        // Build the initial query
        $query = Queue::join('categories', 'queues.category_id', '=', 'categories.id')
            ->select('queues.*', 'categories.id as cat_id', 'categories.visitor_in_queue', 'categories.sort as cat_sort')
            ->where('queues.team_id', $team_id)
            ->where('queues.is_missed', self::STATUS_NO)
            ->where('queues.is_hold', self::STATUS_NO)
            ->where('queues.temp_hold', self::STATUS_NO)
            ->whereNull(['queues.start_datetime', 'queues.called_datetime', 'queues.cancelled_datetime', 'queues.closed_datetime','queues.hold_start_datetime','queues.hold_end_datetime'])
            ->whereDate('queues.arrives_time', Carbon::today());

        // Apply location filter
        if ($location) {
            $query->where('queues.locations_id', $location)->whereNotNull('queues.locations_id');
        }

        // Apply name filter
        if ($name) {
            $query->where(function ($query) use ($name) {
                $query->where('queues.name', 'like', "%$name%")
                    ->orWhere('queues.token', 'like', "%$name%")
                    ->orWhere('queues.start_acronym', 'like', "%$name%");
            });
        }

        // Apply user role filter
        if ($userAuth && !$userAuth->hasRole(User::ROLE_ADMIN)) {
            self::subQueryCategory($query, $userAuth);
        }

        // Apply sorting
        if ($queueType == Queue::SORTABLE) {
            $query->orderBy('categories.sort', 'asc');
        }
        $query->orderBy('queues.datetime', 'asc');

        // Paginate results
        $result = $isFixed ? $query->paginate(10)?->items() : $query->paginate($page)?->items();
        $allResults = collect($result);
        if ($queueType != Queue::SORTABLE) {
           return $allResults;
        }
        $allResults = collect($result);

        // Initialize collections
        $finalResults = collect();
        $remainingResults = collect();

        // Group by category_id and process each group
        $allResults->groupBy('category_id')->each(function ($items, $categoryId) use (&$finalResults, &$remainingResults) {
            $visibleLimit = intval($items->first()->visitor_in_queue ?? 0);

            if ($visibleLimit > 0) {
                $finalResults = $finalResults->concat($items->take($visibleLimit));
                $remainingResults = $remainingResults->concat($items->slice($visibleLimit));
            } else {
                $finalResults = $finalResults->concat($items);
            }
        });

        // Process remaining results
        $remainingGroupedResults = $remainingResults->groupBy('cat_sort');
        $mergedResults = collect();

        while ($finalResults->isNotEmpty() || $remainingGroupedResults->isNotEmpty()) {
            if ($finalResults->isNotEmpty()) {
                $mergedResults = $mergedResults->concat($finalResults);
                $finalResults = collect();
            }

            $newRemainingResults = collect();
            $remainingGroupedResults->sortBy('cat_sort')->each(function ($items) use (&$mergedResults, &$newRemainingResults) {
                $visibleLimit = intval($items->first()->visitor_in_queue ?? 0);

                if ($visibleLimit > 0) {
                    $mergedResults = $mergedResults->concat($items->take($visibleLimit));
                    $newRemainingResults = $newRemainingResults->concat($items->slice($visibleLimit));
                } else {
                    $mergedResults = $mergedResults->concat($items);
                }
            });

            $remainingGroupedResults = $newRemainingResults->groupBy('cat_sort');
        }

        return $mergedResults->values();
    }
      public static function subQueryCategory($query,$userAuth){
              return   $query->where(function ($subQuery) use ($userAuth) {
                $subQuery->where(function ($checkTransferId) use ($userAuth) {
                    $checkTransferId->whereNotNull('transfer_id')
                        ->whereExists(function ($existsQuery) use ($userAuth) {
                            $existsQuery->select(DB::raw(1))
                                ->from('category_user')
                                ->where('user_id', $userAuth->id)
                                ->whereColumn('category_user.category_id', 'queues.transfer_id');
                        });
                })->orWhere(function ($checkTransferIdEmpty) use ($userAuth) {
                    $checkTransferIdEmpty->whereNull('transfer_id')
                        ->whereExists(function ($existsQuery) use ($userAuth) {
                            $existsQuery->select(DB::raw(1))
                                ->from('category_user')
                                ->where('user_id', $userAuth->id)
                                ->where(function ($query) {
                                    $query->whereColumn('category_user.category_id', 'queues.child_category_id')
                                          ->orWhereColumn('category_user.category_id', 'queues.sub_category_id')
                                          ->orWhereColumn('category_user.category_id', 'queues.category_id');
                                });
                        });
                });
            });
            // ->where('counter_id',$userAuth->counter_id);
      }
    public static function checkCategoryAccess($queueId, $userAuth) {
      $query =  DB::table('queues')
          ->where('id', $queueId);

        self::subQueryCategory($query,$userAuth);

        return  $query->exists();
  }


      public static function getPendingQueuesC($conditionTeam,$userAuth,$location = null){
      return Queue::where(function ($query) use($conditionTeam,$userAuth,$location) {
          $query->where($conditionTeam)
          ->where('is_missed',self::STATUS_NO)
          ->where('is_hold',self::STATUS_NO)
          ->whereNull('start_datetime')
          ->whereNull('called_datetime')
          ->whereNull('cancelled_datetime')
          ->whereNull('closed_datetime')
          ->whereNull('hold_start_datetime')
          ->whereNull('hold_end_datetime');

          if(!empty($location)){
            $query->where('locations_id', $location);
          }

          if (!$userAuth->hasRole(User::ROLE_ADMIN)) {
              self::subQueryCategory($query,$userAuth);
        }
          })->whereDate( 'arrives_time', Carbon::today())->count();
      }

      public static function getMissedCall($conditionTeam)
      {

        $data = self::where(function ($query) use ($conditionTeam) {
                $query->where($conditionTeam)
                    ->where('is_missed', self::STATUS_YES);
            })
            ->whereDate('arrives_time', Carbon::today())

            ->get();

          if ($data->isNotEmpty()) {
              return $data->map(function ($item) {
                  return $item->token_with_acronym . $item->token;
              })->toArray();
          }

          return [];
      }

      public static function updateQueueDateTime($currentVisitorId,$teamId){
        $queue = self::where(['queue_id'=>$currentVisitorId,'team_id'=>$teamId])->first();
        $queue?->update([ 'is_hold' =>Queue::STATUS_NO,
        'hold_start_date' =>null,
        'hold_end_date' =>null]);
    }

      public static function getHoldCall($conditionTeam, $onlyDepartmentQueue = 0, $location = null)
{
    $query = self::where(function ($q) use ($conditionTeam) {
        $q->where($conditionTeam)
          ->where('is_hold', self::STATUS_YES);
    });

    if (Auth::check() && !Auth::user()->hasRole(User::ROLE_ADMIN)) {
        $query->where('hold_by', Auth::user()->id);
    }

    $query->whereDate('hold_start_datetime', Carbon::now());

    if ($location !== null) {
        $query->where('locations_id', $location);
    }

    $data = $query->select('token', 'id', 'start_acronym', 'name')
    ->orderBy('hold_start_datetime', 'desc')
                  ->limit(10)
    ->get()
    ->toArray();

    return $data ?: [];
}



// public static function getMissedCallId($conditionTeam, $onlyDepartmentQueue = 0, $location = null)
// {
//     $userAuth = Auth::user(); // Assuming you are using Laravel's Auth facade

//     $query = self::where($conditionTeam)
//         ->where('is_missed', self::STATUS_YES)
//         ->whereDate('arrives_time', Carbon::today());

//     if ($location !== null) {
//         $query->where('locations_id', $location);
//     }

//     $query->when(!empty($userAuth) && !$userAuth->hasRole(User::ROLE_ADMIN), function ($query) use ($userAuth, $onlyDepartmentQueue) {
//         if ($onlyDepartmentQueue == self::STATUS_YES) {
//             $query->whereExists(function ($subQuery) use ($userAuth) {
//                 $subQuery->select(DB::raw(1))
//                     ->from('category_user')
//                     ->where('user_id', $userAuth->id)
//                     ->where(function ($query) {
//                         $query->whereColumn('category_user.category_id', 'queues.child_category_id')
//                               ->orWhereColumn('category_user.category_id', 'queues.sub_category_id')
//                               ->orWhereColumn('category_user.category_id', 'queues.category_id');
//                     });
//             });
//         }
//     });

//     $data = $query->limit(10)->get();

//     $missedCalls = $data->mapWithKeys(function ($item) {
//         return [$item->id => $item->start_acronym . $item->token];
//     });

//     return $missedCalls->toArray();
// }


    // public static function currentVisitorRecord($conditionTeam,$userAuthID,$queueID=null,$location = null )
    // {
    //     return self::with(['category:id,name','SubCategory:id,name','ChildCategory:id,name'])->where($conditionTeam)
    //       ->when(!empty($queueID),function($query) use ($queueID){
    //         $query->where(['id'=>$queueID]);
    //       })->when((!empty($location) && $location != 0),function($query) use ($location){
    //         $query->where(['locations_id'=>$location]);
    //       })
    //       ->whereDate('arrives_time', Carbon::today())
    //         ->where(['status'=> self::STATUS_PROGRESS,'served_by'=>$userAuthID]) ->where(function ($query) {
    //         $query->whereNull('start_datetime')->orwhereNull('closed_datetime');
    //     })->whereNotNull('called_datetime')->whereNull('cancelled_datetime')->first();

    // }


      public static function displayQueue($teamId,$location = null, $limitNumber = 6){
        if($location !== null && $location != 0){
          $queueDisplay =  self::where( [ 'team_id'=>$teamId ] )->whereDate( 'created_at', Carbon::today())->where('is_missed',self::STATUS_NO)->where('is_hold',self::STATUS_NO)->where('locations_id',$location)->whereNotNull('start_datetime')->limit($limitNumber)->orderBy('datetime','desc')->get(['id','team_id','status','counter_id','token','start_acronym','start_datetime']);
        }else{
          $queueDisplay =  self::where( [ 'team_id'=>$teamId ] )->whereDate( 'created_at', Carbon::today())->where('is_missed',self::STATUS_NO)->where('is_hold',self::STATUS_NO)->whereNotNull('start_datetime')->orderBy('datetime','desc')->limit($limitNumber)->get(['id','team_id','status','counter_id','token','start_acronym','start_datetime']);

        }
       $queueDisplay = $queueDisplay->sortBy(function ($item) {
        if ($item->status === self::STATUS_PROGRESS) {
            return -1;
        } else {
            return 0;
        }
      });
       return $queueDisplay;
      }
      public function team(): BelongsTo
      {
          return $this->belongsTo(Tenant::class);
      }


      public static function countPending($teamId, $createdId, $countCatID, $fieldCatName, $counterID, $location = null)
      {

          $queueRecord = self::viewQueue($createdId);
          // If the queue record doesn't exist, return 0
          if (!$queueRecord) {
              return 0;
          }
          $query = self::where('datetime', '<', $queueRecord->datetime)
              ->where(['status' => self::STATUS_PENDING, 'team_id' => $teamId, 'is_missed' => self::STATUS_NO])
              ->whereDate('arrives_time', Carbon::today())
              ->where('id','<',$createdId)
              ->whereNull('cancelled_datetime');

          if ($countCatID != 0 && $fieldCatName != '') {
              $query->where(function ($query) use ($fieldCatName, $countCatID) {
                  $query->where($fieldCatName, $countCatID)
                      ->orWhere('transfer_id', $countCatID);
              });
          }
          if (!empty($counterID) && $counterID != 0) {
              $query->where('counter_id', $counterID);
          }
          if (!empty($location)) {
              $query->where('locations_id', $location);
          }
          return $query->count();
      }




public static function countPendingByCategorywithstaff(
    $teamId,
    $createdId,
    $countCatID,
    $fieldCatName,
    $counterID,
    $location = null
) {
    $today = Carbon::today();

    // ✅ Load only required category details + staff
    $category = Category::select('id', 'service_time')
        ->with(['users' => function ($q) use ($location) {
            $q->select('users.id', 'users.name', 'users.locations')
              ->where('locations', '!=', '')
              ->where('is_login', 1)
              ->whereRaw("JSON_VALID(locations)")
              ->where(function ($q2) use ($location) {
                  $q2->whereJsonContains('locations', (string) $location)
                     ->orWhereJsonContains('locations', (int) $location);
              });
        }])
        ->findOrFail($countCatID);

    $serviceTime = $category->service_time;
    if (empty($serviceTime)) {
        return false;
    }

    $staffMembers = $category->users;
    if ($staffMembers->isEmpty()) {
        return false; // No staff assigned
    }

    $staffIds = $staffMembers->pluck('id');

    // ✅ Preload in-progress queues for staff
    $inProgressBookings = QueueStorage::select('id', 'assign_staff_id', 'called_datetime', $fieldCatName)
        ->with(['category:id,service_time', 'subCategory:id,service_time', 'childCategory:id,service_time'])
        ->whereIn('assign_staff_id', $staffIds)
        ->where([
            'status' => self::STATUS_PROGRESS,
            'team_id' => $teamId,
            'locations_id' => $location,
            'is_missed' => self::STATUS_NO,
        ])
        ->whereDate('datetime', $today)
        ->whereNull('cancelled_datetime')
        ->whereNotNull('called_datetime')
        ->get()
        ->groupBy('assign_staff_id');

    // ✅ Preload pending queues for staff
    $pendingBookings = QueueStorage::select('id', 'assign_staff_id', 'arrives_time', $fieldCatName)
        ->with(['category:id,service_time', 'subCategory:id,service_time', 'childCategory:id,service_time'])
        ->whereIn('assign_staff_id', $staffIds)
        ->where([
            'status' => self::STATUS_PENDING,
            'team_id' => $teamId,
            'locations_id' => $location,
            'is_missed' => self::STATUS_NO,
        ])
        ->whereDate('arrives_time', $today)
        ->whereNull('cancelled_datetime')
        ->get()
        ->groupBy('assign_staff_id');

    // ✅ Calculate load per staff
    $staffLoad = [];
    foreach ($staffMembers as $staff) {
        $totalLoad = 0;
        $remainingTime = 0;

        // In-progress booking
        $inProgress = $inProgressBookings->get($staff->id)?->first();
        if ($inProgress) {
            $serviceTimeInProgress = match ($fieldCatName) {
                'category_id'      => $inProgress->category->service_time,
                'sub_category_id'  => $inProgress->subCategory->service_time,
                'child_category_id'=> $inProgress->childCategory->service_time,
                default            => $serviceTime,
            };

            $elapsed = now()->diffInMinutes(Carbon::parse($inProgress->called_datetime));
            $remainingTime = max(0, $serviceTimeInProgress - $elapsed);
            $totalLoad += $remainingTime;
        }

        // Pending bookings
        $staffPending = $pendingBookings->get($staff->id) ?? collect();
        foreach ($staffPending as $booking) {
            $bookingServiceTime = match ($fieldCatName) {
                'category_id'      => $booking->category->service_time,
                'sub_category_id'  => $booking->subCategory->service_time,
                'child_category_id'=> $booking->childCategory->service_time,
                default            => $serviceTime,
            };
            $totalLoad += (int) $bookingServiceTime;
        }

        $staffLoad[$staff->id] = [
            'total_load'     => $totalLoad,
            'remaining_time' => $remainingTime,
            'pending_count'  => $staffPending->count(),
        ];
    }

    // ✅ Assign staff with least load
    uasort($staffLoad, fn($a, $b) => $a['total_load'] <=> $b['total_load']);
    $assignedStaffId = array_key_first($staffLoad);
    $assigned = $staffLoad[$assignedStaffId];

    // ✅ Get pending queues for assigned staff + category
    $pendingForCategory = QueueStorage::select('id', 'arrives_time', 'assign_staff_id')
        ->where([
            'status' => self::STATUS_PENDING,
            'team_id' => $teamId,
            'locations_id' => $location,
            'is_missed' => self::STATUS_NO,
            'assign_staff_id' => $assignedStaffId,
        ])
        ->whereDate('arrives_time', $today)
        ->where(function ($query) use ($fieldCatName, $countCatID) {
            $query->where($fieldCatName, $countCatID)
                  ->orWhere('transfer_id', $countCatID);
        })
        ->whereNull('cancelled_datetime')
        ->orderBy('arrives_time', 'asc')
        ->get();

    // ✅ Find position in queue
    $beforeCount = 0;
    foreach ($pendingForCategory as $pending) {
        if ((int) $pending->id === (int) $createdId) {
            break;
        }
        $beforeCount++;
    }

    if (!$pendingForCategory->contains('id', $createdId)) {
        $beforeCount = $pendingForCategory->count();
    }

    // ✅ Corrected: Use only total_load (avoid double counting)
    $estimatedWaitTime = (int) $assigned['total_load'];

    $queueCount = QueueStorage::where([
        'status' => self::STATUS_PENDING,
        'team_id' => $teamId,
        'locations_id' => $location,
        'is_missed' => self::STATUS_NO,
        'assign_staff_id' => $assignedStaffId,
    ])
    ->whereDate('arrives_time', $today)
    ->whereNull('cancelled_datetime')
    ->count();

    return [
        'estimated_wait_time' => $estimatedWaitTime,
        'customers_before_me' => $queueCount,
        'assigned_staff_id'   => $assignedStaffId,
    ];
}

public static function countPendingByCategory(
    $teamId,
    $createdId,
    $countCatID,
    $fieldCatName = 'category_id',
    $location = null
) {
    $today = Carbon::today();

    // Get category service time
    $category = Category::select('id', 'service_time')->find($countCatID);

    if (!$category || empty($category->service_time)) {
        return [
            'estimated_wait_time' => 0,
            'customers_before_me' => 0,
        ];
    }

    $serviceTime = !empty($category->service_time) ? (int) $category->service_time : 0;

    // Get all pending tickets of this category for today
   $pendingTickets = QueueStorage::where([
        'status' => self::STATUS_PENDING,
        'team_id' => $teamId,
        'locations_id' =>$location,
        'is_missed' => self::STATUS_NO,
    ])
    ->whereDate('arrives_time', $today)
    ->where(function ($query) use ($fieldCatName, $countCatID) {
        $query->where($fieldCatName, $countCatID)
               ->orWhere('transfer_id', $countCatID);
    })
    ->whereNull('cancelled_datetime')
    ->orderBy('arrives_time', 'asc')
    ->get();

    // Count tickets before current
    $beforeCount = 0;
    foreach ($pendingTickets as $ticket) {
        if ((int)$ticket->id === (int)$createdId) break;
        $beforeCount++;
    }

    // If first ticket, wait time = 0
    if ($beforeCount === 0) {
        return [
            'estimated_wait_time' => 0,
            'customers_before_me' => 0,
        ];
    }

    // Check if first ticket is in progress
    $firstTicket = $pendingTickets->first();
    $elapsedTime = 0;
    if (!empty($firstTicket->called_datetime)) {
        $elapsedTime = Carbon::parse($firstTicket->called_datetime)->diffInMinutes(now());
    }

    // Calculate estimated wait time
    $estimatedWaitTime = max(0, ($beforeCount * $serviceTime) - $elapsedTime);

    return [
        'estimated_wait_time' => $estimatedWaitTime,
        'customers_before_me' => $beforeCount,
    ];
}

public static function countAllPendingQueues(
    $teamId,
    $createdId,
    $countCatID,
    $location = null
) {
    $today = Carbon::today();

    // Get all pending tickets for today (no category filter)
    $pendingTickets = QueueStorage::where([
            'status' => self::STATUS_PENDING,
            'team_id' => $teamId,
            'is_missed' => self::STATUS_NO,
            'is_hold' => self::STATUS_NO,
        ])
        ->when($location, function ($query) use ($location) {
            $query->where('locations_id', $location);
        })
        ->whereDate('arrives_time', $today)
        ->whereNull('cancelled_datetime')
        ->orderBy('arrives_time', 'asc')
        ->get();

    // Count how many tickets are before the current one
    $beforeCount = 0;
    foreach ($pendingTickets as $ticket) {
        if ((int)$ticket->id === (int)$createdId) break;
        $beforeCount++;
    }

    // Get service time from current ticket’s category

    $serviceTime = 0;

    if ($countCatID) {
        $category = Category::select('service_time')->find($countCatID);
        $serviceTime = $category ? (int)$category->service_time : 0;
    }

    // If no service time or first ticket
    if ($beforeCount === 0 || $serviceTime === 0) {
        return [
            'estimated_wait_time' => 0,
            'customers_before_me' => $beforeCount,
        ];
    }

    // Check elapsed time for first ticket (if already called)
    $firstTicket = $pendingTickets->first();
    $elapsedTime = 0;

    if (!empty($firstTicket->called_datetime)) {
        $elapsedTime = Carbon::parse($firstTicket->called_datetime)->diffInMinutes(now());
    }

    // Calculate estimated wait time
    $estimatedWaitTime = max(0, ($beforeCount * $serviceTime) - $elapsedTime);

    return [
        'estimated_wait_time' => $estimatedWaitTime,
        'customers_before_me' => $beforeCount,
    ];
}


public static function countPendingByStaff($teamId, $createdId, $countCatID = 2, $location) {
    $today = Carbon::today();

    // Get service time for category
    $category = Category::select('id', 'service_time')->find($countCatID);
    $serviceTime = $category?->service_time ?? 0;

    if ($serviceTime <= 0) {
        return [
            'estimated_wait_time' => 0,
            'customers_before_me' => 0,
            'assigned_staff_id' => null,
        ];
    }

    // Get active staff for location
    $staffMembers = User::where('is_login', 1)
        ->whereNotNull('locations')
        ->where(function($q) use ($location) {
            $q->whereJsonContains('locations', (string)$location)
              ->orWhereJsonContains('locations', (int)$location);
        })
        ->get();

    if ($staffMembers->isEmpty()) {
        return [
            'estimated_wait_time' => 0,
            'customers_before_me' => 0,
            'assigned_staff_id' => null,
        ];
    }

    $staffIds = $staffMembers->pluck('id');

    // Pending tickets per staff for today
    $pendingBookings = QueueStorage::whereIn('assign_staff_id', $staffIds)
        ->where([
            'status' => self::STATUS_PENDING,
            'team_id' => $teamId,
            'locations_id' => $location,
            'is_missed' => self::STATUS_NO,
        ])
        ->whereDate('arrives_time', $today)
        ->whereNull('cancelled_datetime')
        ->orderBy('arrives_time', 'asc')
        ->get()
        ->groupBy('assign_staff_id');

    // In-progress tickets per staff for today
    $inProgressBookings = QueueStorage::whereIn('assign_staff_id', $staffIds)
        ->where([
            'status' => self::STATUS_PROGRESS,
            'team_id' => $teamId,
            'locations_id' => $location,
            'is_missed' => self::STATUS_NO,
        ])
        ->whereDate('datetime', $today)
        ->whereNotNull('called_datetime')
        ->whereNull('cancelled_datetime')
        ->get()
        ->groupBy('assign_staff_id');

    // Calculate total load per staff
    $staffLoad = [];
    foreach ($staffMembers as $staff) {
        $totalLoad = 0;
        $remainingTime = 0;

        // Earliest in-progress ticket
        $inProgress = $inProgressBookings->get($staff->id)?->first();
        if ($inProgress) {
            $elapsed = now()->diffInMinutes(Carbon::parse($inProgress->called_datetime));
            $remainingTime = max(0, $serviceTime - $elapsed);
            $totalLoad += $remainingTime;
        }

        // Pending tickets
        $staffPending = $pendingBookings->get($staff->id) ?? collect();
        $totalLoad += $staffPending->count() * $serviceTime;

        $staffLoad[$staff->id] = [
            'total_load' => $totalLoad,
            'remaining_time' => $remainingTime,
            'pending_count' => $staffPending->count(),
        ];
    }

    // Assign staff with least load
    uasort($staffLoad, fn($a,$b) => $a['total_load'] <=> $b['total_load']);
    $assignedStaffId = array_key_first($staffLoad);
    $assigned = $staffLoad[$assignedStaffId];

    // Tickets pending for assigned staff
    $pendingForStaff = $pendingBookings->get($assignedStaffId) ?? collect();

    // Count tickets before the current ticket
    $beforeCount = 0;
    foreach ($pendingForStaff as $ticket) {
        if ((int)$ticket->id === (int)$createdId) break;
        $beforeCount++;
    }

    // Determine elapsed time of first in-progress ticket
    $firstInProgress = $inProgressBookings->get($assignedStaffId)?->first();
    $elapsed = $firstInProgress ? now()->diffInMinutes(Carbon::parse($firstInProgress->called_datetime)) : 0;
    $elapsed = min($elapsed, $serviceTime);

    // Calculate estimated wait time
    if ($beforeCount === 0) {
        // First ticket → wait = 0
        $estimatedWaitTime = 0;
    } else {
        // Subsequent tickets
        $estimatedWaitTime = ($beforeCount * $serviceTime) - $elapsed;
        $estimatedWaitTime = max(0, $estimatedWaitTime);
    }

    return [
        'estimated_wait_time' => $estimatedWaitTime,
        'customers_before_me' => $beforeCount,
        'assigned_staff_id' => $assignedStaffId,
    ];
}

public static function countPendingByCategoryMobile($teamId, $createdId, $countCatID, $fieldCatName, $counterID, $location = null, $staffId)
{

    $today = Carbon::today();

    if (!$staffId) {
        return false; // No staff assigned
    }

    $totalLoad = 0;
    $remainingTime = 0;

    // In-progress booking for the specific staff
    $inProgress = QueueStorage::where([
            'status' => self::STATUS_PROGRESS,
            'team_id' => $teamId,
            'locations_id' => $location,
            'is_missed' => self::STATUS_NO,
            'assign_staff_id' => $staffId,
        ])
        ->whereDate('datetime', $today)
        ->where('id', '<', $createdId)
        ->whereNull('cancelled_datetime')
        ->whereNotNull('called_datetime')
        ->orderBy('called_datetime', 'asc')
        ->first();

    if ($inProgress) {
        if ($fieldCatName == 'category_id') {
            $serviceTimeInProgress = $inProgress->category->service_time;
        } elseif ($fieldCatName == 'sub_category_id') {
            $serviceTimeInProgress = $inProgress->subCategory->service_time;
        } elseif ($fieldCatName == 'child_category_id') {
            $serviceTimeInProgress = $inProgress->childCategory->service_time;
        }
        $elapsed = now()->diffInMinutes(Carbon::parse($inProgress->called_datetime));
       $remainingTime = max(0, (float)$serviceTimeInProgress + (float)$elapsed);
        $totalLoad += $remainingTime;

    }
    // Pending bookings for this staff
    $pendingBookings = QueueStorage::where([
            'status' => self::STATUS_PENDING,
            'team_id' => $teamId,
            'locations_id' => $location,
            'is_missed' => self::STATUS_NO,
            'assign_staff_id' => $staffId,
        ])
        ->whereDate('arrives_time', $today)
        ->where('id', '<', $createdId)
        ->whereNull('cancelled_datetime')
        ->get();

    foreach ($pendingBookings as $booking) {
        if ($fieldCatName == 'category_id') {
            $bookingServiceTime = $booking->category->service_time;
        } elseif ($fieldCatName == 'sub_category_id') {
            $bookingServiceTime = $booking->subCategory->service_time;
        } elseif ($fieldCatName == 'child_category_id') {
            $bookingServiceTime = $booking->childCategory->service_time;
        }
        $totalLoad += (int)$bookingServiceTime;
    }

   

    // $estimatedWaitTime = (int)$totalLoad + (int)($customersBeforeMe * $serviceTime);
    $estimatedWaitTime = (int)$totalLoad ;

    // Queue count before this booking
    $queueCount = QueueStorage::where([
            'status' => self::STATUS_PENDING,
            'team_id' => $teamId,
            'locations_id' => $location,
            'is_missed' => self::STATUS_NO,
            'assign_staff_id' => $staffId,
        ])
        ->whereDate('arrives_time', $today)
        ->where('id', '<', $createdId)
        ->whereNull('cancelled_datetime')
        ->count();

    return [
        'estimated_wait_time' => round($estimatedWaitTime),
        'customers_before_me' => $queueCount,
        'assigned_staff_id' => $staffId,
    ];
}






      public static function getLastToken($teamId,$acronym,$locationId = null){
          $condition = [];
          if($acronym != SiteDetail::DEFAULT_WALKIN_A)
          $condition  = ['start_acronym'=>$acronym,'locations_id'=>$locationId];
            return self::where(array_merge(['team_id' =>$teamId],$condition) )
            ->whereDate( 'arrives_time', Carbon::today())
            ->whereNull('created_by')
            ->orderBy('arrives_time','desc')
            ->first()?->token;
    }
    public static function newGeneratedToken($lastToken,$token,$tokenDigit){
      $tokenStart = $lastToken ?? $token;

      $tokenStart = ( int ) $tokenStart;

      if ( $lastToken ) {
          $tokenStart += 1;
      }

      $tokenDigit = $tokenDigit ?? 4;

      return str_pad( $tokenStart, $tokenDigit, '0', STR_PAD_LEFT );
    }

     public static function checkToken($teamId,$acronym,$token){
      $query = self::where('team_id', $teamId)  ->where('token', $token);

    if ($acronym!= SiteDetail::DEFAULT_WALKIN_A) {
        $query->where('start_acronym', $acronym);
    }

    $query->whereDate('arrives_time',  Carbon::today());

         return  $query->exists();
     }



     public static function assignCounterToQueue($queueID, $location = null)
     {
         $query = Counter::where(['show_checkbox' => self::STATUS_YES]);

         if ($location !== null) {
             $query->whereJsonContains('counter_locations', $location);
         }
         $counters = $query->get();

         $minTasks = PHP_INT_MAX;
         $targetCounter = null;

         if ($counters->isNotEmpty()) {
             foreach ($counters as $counter) {
                 $tasksCount = $counter->queues()
                     ->where(['status' => self::STATUS_PENDING, 'locations_id' => (int) $location])
                     ->whereNotNull('counter_id')
                     ->count();
                 if ($tasksCount < $minTasks) {
                     $minTasks = $tasksCount;
                     $targetCounter = $counter;
                 }
             }

             if ($targetCounter !== null) {
                 $queue = self::find($queueID);
                 if ($queue) {
                     $queue->counter_id = $targetCounter->id;
                     $queue->save();
                     return $queue->counter_id;
                 }
             }
         }

         // If no counters are available or the queue is not found
         return 0;
     }

  public static function viewCounterID($queueID){
    return  Queue::find($queueID)?->counter_id;
  }


  public static function smsReminderNumber($currentVisitorId,$teamId){
    $user =  Auth::user();
    $reminderBefore = $user->sms_reminder_queue ?? 1;
    $queue = Queue::where('id', '>', $currentVisitorId)
    ->where('team_id',$teamId)
    ->orderBy('id')
    ->skip($reminderBefore)
    ->take(1)
    ->first();
    if(!empty($queue) && !empty($queue->phone))
     self::sendSMSReminder($queue,$teamId);
  }

  public static function nextCallReminder($currentVisitorId,$teamId){
    $queue = Queue::where('id', '>', $currentVisitorId)->where('team_id',$teamId)
    ->orderBy('id')
    ->first();
    if(!empty($queue) && !empty($queue->phone))
    self::sendSMSReminder($queue,$teamId);
  }
    public static function sendSMSReminder($queue,$teamId){
      $data = [
        'name' => $queue->name,
        'phone' => $queue->phone,
        'token' =>$queue->token,
        'token_with_acronym'=>$queue->token_with_acronym,
      ];
      SmsAPI::sendSms( $teamId, $data,'reminder','reminder');
    }




    public static function getProgressRecord($conditionTeam,$userAuthId,$locationID){
     return self::where( array_merge( $conditionTeam, [ 'status'=>Queue::STATUS_PROGRESS,'served_by' =>$userAuthId,'locations_id'=> $locationID  ] ) )->whereDate('arrives_time',Carbon::today())
            ->whereNotNull('called_datetime')->whereNull('closed_datetime')->whereNull('cancelled_datetime')->first();
    }


    public static function nextCalledField($conditionTeam,$currentVisitorId,$selectedCounter,$userAuthId,$showStartBtn,$locationID){
      self::where( array_merge( $conditionTeam, [ 'id'=> $currentVisitorId ] ) )
      ->update( [ 'status'=>self::STATUS_PROGRESS, 'counter_id' => $selectedCounter, 'called_datetime'=>Carbon::now(),'is_missed'=>self::STATUS_NO,'served_by' =>$userAuthId ] );

      ActivityLog::storeLog($conditionTeam['team_id'],$userAuthId,$currentVisitorId,ActivityLog::QUEUE_CALLED,$locationID);

    }


    public static function startCalledField($conditionTeam,$currentVisitorId,$selectedCounter,$isStartBtn,$locationID){
       $userAuthId =   Auth::user()->id;

    $queue =  self::where( array_merge( $conditionTeam, [ 'id'=> $currentVisitorId ] ) )->first();
        if($queue->is_missed == self::STATUS_NO){
            if($isStartBtn == false)
                ActivityLog::storeLog($conditionTeam['team_id'],$userAuthId,$currentVisitorId,ActivityLog::QUEUE_CALLED,$locationID);
            else
                ActivityLog::storeLog($conditionTeam['team_id'],$userAuthId,$currentVisitorId,ActivityLog::QUEUE_STARTED,$locationID);
        }

      if($isStartBtn == false)
         $queue
          ->update( [ 'status'=>self::STATUS_PROGRESS, 'counter_id' => $selectedCounter, 'called_datetime'=>Carbon::now(),'start_datetime'=>Carbon::now(),'is_missed'=>self::STATUS_NO,'served_by' =>$userAuthId ] );
    else
     $queue->update(
            [ 'status'=>self::STATUS_PROGRESS, 'start_datetime'=>Carbon::now(),'is_missed'=>self::STATUS_NO,'served_by' =>$userAuthId
          ]);

    }


  

  public static function totalTokenServed($conditionTeam, $userAuthID, $location = null) {
    $todayDate = Carbon::today();

    $query = Queue::select('id', 'start_acronym', 'token','locations_id')
        ->where($conditionTeam)
        ->where('is_missed', self::STATUS_NO)
        ->whereIn('status', [self::STATUS_CLOSE, self::STATUS_RESET])
        ->where(function($query) use ($todayDate) {
            $query->whereDate('arrives_time', $todayDate)
                  ->orWhereDate('reset_call', $todayDate);
        })
        ->where(function($query) use ($userAuthID) {
            $query->where('closed_by', $userAuthID)
                  ->orWhere('reset_call_by', $userAuthID);
        });

    if ($location !== null) {
        $query->where('locations_id', $location);
    }

    $data = $query->get();

    return $data->pluck('id')->combine(
        $data->map(fn($item) => $item->start_acronym . $item->token)
    )->toArray();
}


    public static function agoTimeFormat($datetime){
      $arrivesTime = Carbon::parse($datetime);

      $diffInHours = $arrivesTime->diffInHours();
      $diffInDays = $arrivesTime->diffInDays();
      $diffInMinutes = $arrivesTime->diffInMinutes();

      if ($diffInHours > 0) {
          $arrivesTime = $diffInHours . ' hrs';
      } elseif ($diffInDays > 0) {
          $arrivesTime = $diffInDays . ' days';
      } else {
          $arrivesTime = $diffInMinutes . ' mins';
      }

      return $arrivesTime;
    }

    public static function storeQueue($data){

      return self::create($data);
    }

  public static function viewQueue($queueID){
      return  self::with([
        'category:id,name',
        'subCategory:id,name',
        'childCategory:id,name',
        'counter:id,name'
    ])->find($queueID);
    }

    public static function isBookExist($bookedID){
     return self::where( [ 'booking_id' =>$bookedID] )->exists();
    }

    public static function filterSettingExcel($selectedLocation,$filters){
      $data = [];
      $data['Branch Name'] = Location::locationName($selectedLocation);
      $data['Created From']  = (!empty($filters['created_at']['created_from'])) ? Carbon::parse($filters['created_at']['created_from'])->format('d-m-Y'):'';
      $data['Created Until']  = (!empty($filters['created_at']['created_until'])) ?  Carbon::parse($filters['created_at']['created_until'])->format('d-m-Y'):'';
      $data['Closed By']  = (!empty($filters['closed_by']) && !empty($filters['closed_by']['values'])) ? $filters['closed_by']['values']:[];
      $data['Counter']  = (!empty($filters['counter_id']) && !empty($filters['counter_id']['values'])) ? $filters['counter_id']['values']:[];
      $data['Status']  = (!empty($filters['status']) && !empty($filters['status']['values'])) ? $filters['status']['values']:[];
      $data['Ticket Mode']  = (!empty($filters['ticket_mode']['values']['0'])) ? $filters['ticket_mode']['values']['0']:'';

          $counterNames ='';
          if(!empty( $data['Counter'])){
              $counters = Counter::whereIn('id', $data['Counter'])->pluck('name', 'id')->toArray();
              $counterNames = array_map(fn($id) => $counters[$id] ?? 'Unknown',$data['Counter']);
              $counterNames = implode(', ', $counterNames);
          }

          $data['Counter']  = $counterNames;

          $closedNamed ='';
          if(!empty( $data['Closed By'])){
              $users = User::whereIn('id', $data['Closed By'])->pluck('name', 'id')->toArray();
              $closedNamed = array_map(fn($id) => $users[$id] ?? 'Unknown',$data['Closed By']);
              $closedNamed = implode(', ', $closedNamed);

          }
          $data['Closed By']  = $closedNamed;

              if(!empty($data['Status'])){
                  $data['Status']  = implode(', ', $data['Status']);

              }else{
                  $data['Status']  = '';

              }

              return $data;
    }

    public static function countQueueStorage($queueID){
        return self::where(['queue_id'=>$queueID,'status'=>self::STATUS_PENDING,'is_missed'=>self::STATUS_NO])->count();
    }

    public static function viewQueueStorage($queueID, $userAuth, $userCategories) {
        $query = self::where('queue_id', $queueID)
        ->where('status', self::STATUS_PENDING)
        // ->where('is_missed', self::STATUS_NO)
        ->orderBy('waiting_time', 'asc');

        if (!$userAuth->hasRole(User::ROLE_ADMIN)) {
            $query->whereIn('sub_category_id', $userCategories)
                ->orWhereIn('category_id', $userCategories);
        }

        return $query->first();
    }

    protected static function generateSequencePattern($teamId,$locationId)
    {

          return $cateogry =  Category::where('team_id', $teamId)
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhere('parent_id', '');
            })
            ->whereJsonContains('category_locations', "$locationId")
            ->orderBy('sort')
            ->pluck('visitor_in_queue', 'id');
    }

    // Method to get next priority sort for the queue
    public static function getNextPrioritySort($categoryId,$teamId,$locationId)
    {
        $category = Category::find($categoryId);
        $nextserial = 1;

     $sequencePattern =self::generateSequencePattern($teamId,$locationId);
     if(count($sequencePattern) == 0){
         return null;
     }
     $filteredCategories = $sequencePattern->except($category->id);

       $sumVisitorInQueue = $filteredCategories->sum() + $sequencePattern[$category->id];

      $queues = QueueStorage::where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->where('category_id', $category->id)
            ->whereNotNull('priority_sort')
            ->whereDate('created_at', Carbon::today())
            ->pluck('priority_sort')
            ->toArray();

        $maxValue = !empty($queues) ? max($queues) : $nextserial;

        if (empty($queues) || $maxValue == 0) {
            $maxValue = $nextserial;
            $queues = [];
        }

        if ($sequencePattern[$category->id] == 1) {
            if (!empty($queues)) {
                return $nextserial = $maxValue + $sumVisitorInQueue;
            } else {
                $sumBefore = self::sumCategoriesBefore($category->id, $sequencePattern);
                return $nextserial = $maxValue + $sumBefore;
            }
        }

        if ($sequencePattern[$category->id] > 1) {
            if (!empty($queues)) {
                $countserial = self::countAssignedPrioritySorts($category->id, $maxValue,$teamId,$locationId);

                if ($countserial == $sequencePattern[$category->id]) {
                    return $nextserial = $maxValue + $sumVisitorInQueue - 1;
                } else {
                    return $nextserial = $maxValue + 1;
                }
            } else {
                $sumBefore =self::sumCategoriesBefore($category->id, $sequencePattern);
                return $nextserial = $maxValue + $sumBefore;
            }
        }
    }

    // Helper method to calculate sum of all categories before a given category in the sequence
    public static function sumCategoriesBefore($categoryId, $sequencePattern)
    {
        $categoriesArray = $sequencePattern->toArray();
        $slicedArray = array_slice($categoriesArray, 0, array_search($categoryId, array_keys($categoriesArray)));
        return array_sum($slicedArray);
    }

    // Helper method to count assigned priority_sort values
    public static function countAssignedPrioritySorts($categoryId, $maxValue,$teamId,$locationId)
    {
        $countserial = 0;
        for ($i = $maxValue; $i >= 1; $i--) {
            $checkSort = QueueStorage::where('team_id', $teamId)
                ->where('locations_id',$locationId)
                ->where('category_id', $categoryId)
                ->whereNotNull('priority_sort')
                ->whereDate('created_at', Carbon::today())
                ->where('priority_sort', $i)
                ->exists();

            if ($checkSort) {
                $countserial++;
            } else {
                break;
            }
        }
        return $countserial;
    }

     public static function getPendingDetails(
    int $teamId,
    int $queueId,
    ?int $categoryId = null,
    ?string $fieldCatName = null,
    ?int $location = null,
    $siteDetails = null,
    bool $enablePriority = false,
)
{
    $pendingCount = 0;
    $pendingWaiting = 0;
    $assignedStaffId = null;
    $estimateTime = $siteDetails->estimate_time ?? 0;

    if ($siteDetails && $siteDetails->category_estimated_time == SiteDetail::STATUS_YES) {

        switch ($siteDetails->estimate_time_mode) {
            case 1: // By service + staff availability
                $estimateDetail = self::countPendingByCategorywithstaff(
                    $teamId, $queueId, $categoryId, $fieldCatName, '', $location
                );

                if (!$estimateDetail) {
                    $pendingCount = self::countPending($teamId, $queueId, $categoryId, $fieldCatName, '', $location);
                } else {
                    $pendingCount = $estimateDetail['customers_before_me'] ?? 0;
                    $pendingWaiting = $estimateDetail['estimated_wait_time'] ?? 0;
                    if (!$enablePriority) {
                        $assignedStaffId = $estimateDetail['assigned_staff_id'] ?? null;
                    }
                }
                break;

            case 2: // By service only
                if ($siteDetails->count_all_services == 2) {
                    $estimateDetail = self::countAllPendingQueues($teamId, $queueId, $categoryId, $location);
                    $pendingCount = $estimateDetail['customers_before_me'] ?? 0;
                } else {
                    $estimateDetail = self::countPendingByCategory($teamId, $queueId, $categoryId, $fieldCatName, $location);
                    $pendingCount = $estimateDetail['customers_before_me'] ?? 0;
                    $pendingWaiting = $estimateDetail['estimated_wait_time'] ?? 0;
                }
                break;

            default: // By staff only
                $estimateDetail = self::countPendingByStaff($teamId, $queueId, $categoryId, $location);
                if ($estimateDetail) {
                    $pendingCount = $estimateDetail['customers_before_me'] ?? 0;
                    $pendingWaiting = $estimateDetail['estimated_wait_time'] ?? 0;
                    if (!$enablePriority) {
                        $assignedStaffId = $estimateDetail['assigned_staff_id'] ?? null;
                    }
                }
                break;
        }

        // Calculate waiting time
        if ($siteDetails->estimate_time_mode == 2 && $siteDetails->count_all_services == 2) {
            $waitingTime = $estimateTime * $pendingCount;
        } else {
            $waitingTime = $pendingWaiting ?: ($estimateTime * $pendingCount);
        }

    } else {
        // Default logic without estimated time
        $pendingCountGet = (int) self::countPending($teamId, $queueId, '', '', '', $location);
        $counterCount = Counter::where('team_id', $teamId)
            ->whereJsonContains('counter_locations', (string)$location)
            ->where('show_checkbox', 1)
            ->count();

        $pendingCount = ($pendingCountGet > 0 && $counterCount > 0)
            ? floor($pendingCountGet / $counterCount)
            : 0;

        $waitingTime = $estimateTime * $pendingCount;
    }

    return [
        'pending_count' => $pendingCount,
        'pending_waiting' => $pendingWaiting,
        'assigned_staff_id' => $assignedStaffId,
        'waiting_time' => $waitingTime,
    ];
}

	public function assignedStaff()
	{
		return $this->belongsTo(User::class, 'staff_id');
	}
   }
