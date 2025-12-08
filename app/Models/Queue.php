<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\Notifiable;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;


class Queue extends Model
{

    use HasFactory,Notifiable;
    protected $table = "queues";
    protected $fillable = ['id','team_id',  'token', 'token_with_acronym','status', 'created_at', 'updated_at','locations_id',  'arrives_time', 'start_acronym',  'ticket_mode', 'created_by','ticket_date','last_category'
];

    const STATUS_PENDING = 'Pending';
    const STATUS_START = 'Start';
    const STATUS_READY = 'Ready';
    const STATUS_SKIP = 'Miss';
    const STATUS_PROGRESS = 'Progress';
    const STATUS_PAUSE = 'Pause';
    const STATUS_CLOSE = 'Close';
    const STATUS_RESET = 'Reset';
    const STATUS_MOVE = 'Move';
    const STATUS_CANCELLED = 'Cancelled';

    const STATUS_YES = 1;
    const STATUS_NO = 0;
    const LABEL_YES = 'Yes';
    const LABEL_NO = 'No';
    const SERVED_MISSED = 'Served + Missed Calls';
    const MOVE_BACK_TO_MQ = 'Missed Queue List';
    const TICKET_MODE_MOBILE = 'Mobile';
    const TICKET_MODE_Walk_IN = 'Walk-IN';
    const INITIAL_VISITOR_SHOW_COUNT = 6;
    const MAX_QUEUE_DISPLAY = 6;
    const SORTABLE = "Sort";
    const DEFAULT_QUEUE = "Default";


    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class, 'counter_id', 'id');
    }
    public function teams(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }




    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'queues_storage', 'queue_id', 'category_id');
    }
    public function location()
    {
        return $this->belongsTo(Location::class, 'locations_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }
    public function rating()
    {
        return $this->hasMany(Rating::class);
    }

    public function childCategory()
    {
        return $this->belongsTo(Category::class, 'child_category_id');
    }


    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function servedBy()
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public static function getRatingEmoji($type)
    {

        $data = [
            'Excellent' => 4,
            'Good' => 3,
            'Neutral' => 2,
            'Poor' => 1,
        ];
        if (array_key_exists($type, $data))
            return $data[$type];


        return null;
    }


    public static function getEmojiText()
    {
        return [
            4 => ['emoji' => '😀', 'label' => 'Excellent', 'range' => [4, 5]],
            3 => ['emoji' => '😊', 'label' => 'Good', 'range' => [3, 3.99]],
            2 => ['emoji' => '😐', 'label' => 'Neutral', 'range' => [2, 2.99]],
            1 => ['emoji' => '🙁', 'label' => 'Poor', 'range' => [1, 1.99]],
        ];
    }

    public static function getFirstRecord()
    {
        return self::first();
    }
    public static function getSecondRecord()
    {
        return self::skip(1)->take(1)->first();
    }
    public static function getThirdRecord()
    {
        return self::skip(2)->take(1)->first();
    }

    protected $casts = [
        'called_datetime' => 'datetime',
        'arrives_time' => 'datetime',
        'closed_datetime' => 'datetime',
        'start_datetime' => 'datetime',
        'ticket_date' => 'date',

    ];

    protected static function booted()
    {
        static::creating(function ($queue) {
            if (empty($queue->ticket_date)) {
              $timezone = Session::get('timezone_set') ?? config('app.timezone');
            $queue->ticket_date = now($timezone)->toDateString();
            }
        });
    }

    public function queueStorages()
    {
        return $this->hasMany(QueueStorage::class);
    }

//    public static function getPendingQueues($conditionTeam, $isFixed = false, $location = null, $page = null, $name = null, $team_id = null, $queueType = null,$counterId=null,$assign_staff =null,$enable_callDepartment= false)
//     {
//         // dd($conditionTeam, $isFixed , $location , $page , $name , $team_id, $queueType,$counterId,$assign_staff,$enable_callDepartment);

//         $timezone = Session::get('timezone_set') ?? 'UTC';

//         $userAuth = Auth::user();
//         $today = Carbon::today($timezone);

//         if(is_null($team_id)){


//             $team_id = tenant('id');
//         }

//         $enableStaffPriority = SiteDetail::where($conditionTeam)->where('location_id', $location)->value('use_staff_priority') ?? false;

//         if($enableStaffPriority){
//            $assign_staff = $userAuth->id ?? null;
//         }

//         $orderByColumn = $queueType == Queue::SORTABLE ? 'priority_sort' : 'datetime';

//         $query = QueueStorage::where($conditionTeam)
//             ->where('is_hold', self::STATUS_NO)
//             ->where('temp_hold', self::STATUS_NO)
//             ->where('is_missed', self::STATUS_NO)
//             ->whereNull([
//                     'start_datetime',
//                     'called_datetime',
//                     'cancelled_datetime',
//                     'closed_datetime',
//                 ])
//                 ->whereDate('arrives_time', $today->format('Y-m-d'));

//             if ($name) {
//                 $query->where(function ($query) use ($name) {
//                     $query->where('name', 'like', "%$name%")
//                         ->orWhere('token', 'like', "%$name%")
//                         ->orWhere('start_acronym', 'like', "%$name%");
//                 });
//             }

//             if (!empty($counterId)) {
//                 $query->where(function ($subQuery) use ($counterId) {
//                     $subQuery->whereNull('forward_counter_id')
//                              ->orWhere('forward_counter_id', $counterId);
//                 });
//             }

//             if ($userAuth && !$userAuth->hasRole(User::ROLE_ADMIN)) {
//                 self::subQueryCategoryMul($query, $userAuth);
//             }

//             if ($location) {
//                 $query->where('locations_id', $location)
//                     ->whereNotNull('locations_id');
//             }

//             if ($assign_staff && $userAuth && !$userAuth->hasRole(User::ROLE_ADMIN)) {
//                 $query->where('assign_staff_id', $assign_staff);
//             }

//         $pageSize = $isFixed ? ($page ?? 10) : 50; // Default to 15 if $page is null

//         // Join with queueStorages to order by priority_sort
//         $paginatedResults = $query // Select only the columns from the Queue model
//             ->orderBy($orderByColumn, 'asc')
//             ->paginate($pageSize)
//             ->items();

//         return collect($paginatedResults);

//     }

 public static function getwaitingcalls($conditionTeam, $isFixed = false, $location = null, $page = null, $counterId=null,$categories =null)
    {
        // dd($conditionTeam, $isFixed , $location , $page , $name , $team_id, $queueType,$counterId,$assign_staff,$enable_callDepartment);

        $timezone = Session::get('timezone_set') ?? 'UTC';

        $userAuth = Auth::user();
        $today = Carbon::today($timezone);

        $enableStaffPriority = SiteDetail::where($conditionTeam)->where('location_id', $location)->value('use_staff_priority') ?? false;
        $assign_staff = '';
        if($enableStaffPriority){
           $assign_staff = $userAuth->id ?? null;
        }

        $orderByColumn =  'datetime';

        $categories = (array) $categories ?? [];
         $counterId  = (array) $counterId ?? [];

        $query = QueueStorage::where($conditionTeam)
            ->where('is_hold', self::STATUS_NO)
            ->where('temp_hold', self::STATUS_NO)
            ->where('is_missed', self::STATUS_NO)
            ->whereNull([
                    'start_datetime',
                    'called_datetime',
                    'cancelled_datetime',
                    'closed_datetime',
                ])
                ->whereDate('arrives_time', $today->format('Y-m-d'));


    //         if ($counterId !== null && !empty($counterId)) {
    //      $query->whereIn('counter_id', $counterId)
    //             ->orWhereIn('forward_counter_id', $counterId);
    // }

     // Non-admin filtering

        $query->where(function ($q) use ($categories, $counterId) {
            // Case 1: Normal (no transfer/forward)
            $q->where(function ($q2) use ($categories) {
                $q2->whereNull('transfer_id')
                   ->whereNull('forward_counter_id')
                   ->where(function ($cat) use ($categories) {
                       $cat->whereIn('category_id', $categories)
                           ->orWhereIn('sub_category_id', $categories)
                           ->orWhereIn('child_category_id', $categories);
                   });
            })

            // Case 2: Transfer exists
            ->orWhere(function ($q3) use ($categories) {
                $q3->whereNotNull('transfer_id')
                   ->whereIn('transfer_id', $categories);
            })

            // Case 3: Forward exists
            ->orWhere(function ($q4) use ($counterId) {
                $q4->whereNotNull('forward_counter_id');

                if ($counterId) {
                    $q4->where('forward_counter_id', $counterId);
                }
            });
        });


            if ($location) {
                $query->where('locations_id', $location)
                    ->whereNotNull('locations_id');
            }




        // Apply ordering based on queueStorages.priority_sort

        // $pageSize = $isFixed ? 10 : ($page ?? 15); // Default to 15 if $page is null
        $pageSize = $isFixed ? ($page ?? 10) : 50; // Default to 15 if $page is null

        // Join with queueStorages to order by priority_sort
        $paginatedResults = $query // Select only the columns from the Queue model
            ->orderBy($orderByColumn, 'asc')
            ->paginate($pageSize)
            ->items();

        return collect($paginatedResults);

    }

   public static function checkUserAssigned($teamId, $location = null, $type = 'category', $typeValue)
{
    $user = Auth::user();

    // Admin can access all
    if ($user->is_admin == 1) {
        return true;
    }

    if (empty($typeValue)) {
        return false;
    }

    switch ($type) {
        case 'category':
            $assignedCategories = $user->categories?->pluck('id')->toArray() ?? [];
            return in_array($typeValue, $assignedCategories);

        case 'counter':
            $assignedCounter = $user->counter_id ?? null;
            $assignedCounters = $user['assign_counters'] ?? [];
            $allCounters = array_unique(array_merge([$assignedCounter], $assignedCounters));
            return in_array($typeValue, $allCounters);

        default:
            return false;
    }
}

    public static function getPendingQueues($conditionTeam, $isFixed = false, $location = null, $page = null, $name = null, $team_id = null, $queueType = null, $counterId = null, $assign_staff = null, $enable_callDepartment = false)
{
    $timezone = Session::get('timezone_set') ?? 'UTC';
    $userAuth = Auth::user();
    $today = Carbon::today($timezone);

    // User assignments
    $assignedCategories = $userAuth->categories?->pluck('id')->toArray() ?? [];
    $assignedCounter    = $userAuth->counter_id ?? null;
    $assignedCounters   = $userAuth['assign_counters'] ?? [];
    $allCounters = array_values(array_unique(array_merge(["$assignedCounter"], $assignedCounters)));

    if (is_null($team_id)) {
        $team_id = tenant('id');
    }

    $enableStaffPriority = SiteDetail::where($conditionTeam)
        ->where('location_id', $location)
        ->value('use_staff_priority') ?? false;

    if ($enableStaffPriority) {
        $assign_staff = $userAuth->id ?? null;
    }

    $orderByColumn = $queueType == Queue::SORTABLE ? 'priority_sort' : 'datetime';

    $query = QueueStorage::where($conditionTeam)
        ->where('is_hold', self::STATUS_NO)
        ->where('temp_hold', self::STATUS_NO)
        ->where('is_missed', self::STATUS_NO)
        ->whereNull('start_datetime')
        ->whereNull('called_datetime')
        ->whereNull('cancelled_datetime')
        ->whereNull('closed_datetime')
        ->whereDate('arrives_time', $today->format('Y-m-d'));
if ($name) {
    $query->where(function ($query) use ($name) {
        // Extract acronym (letters) and token (numbers)
        $acronym = preg_replace('/[^A-Za-z]/', '', $name);  // FU
        $token   = preg_replace('/[^0-9]/', '', $name);     // 001

        // If input has both acronym and token (like FU001)
        if ($acronym && $token) {
            $query->where(function ($q) use ($acronym, $token) {
                $q->where('start_acronym', 'like', "%$acronym%")
                  ->where('token', 'like', "%$token%");
            });
        } else {
            // Normal search when input doesn't contain both
            $query->where('name', 'like', "%$name%")
                  ->orWhere('token', 'like', "%$name%")
                  ->orWhere('start_acronym', 'like', "%$name%")
                  ->orWhereRaw("CONCAT(start_acronym, token) LIKE ?", ["%$name%"]);
        }
    });
}

    // Non-admin filtering
    if ($userAuth && !$userAuth->hasRole(User::ROLE_ADMIN)) {
        $query->where(function ($q) use ($assignedCategories, $allCounters, $counterId) {
            // Case 1: Normal (no transfer/forward)
            $q->where(function ($q2) use ($assignedCategories) {
                $q2->whereNull('transfer_id')
                   ->whereNull('forward_counter_id')
                   ->where(function ($cat) use ($assignedCategories) {
                       $cat->whereIn('category_id', $assignedCategories)
                           ->orWhereIn('sub_category_id', $assignedCategories)
                           ->orWhereIn('child_category_id', $assignedCategories);
                   });
            })

            // Case 2: Transfer exists
            ->orWhere(function ($q3) use ($assignedCategories) {
                $q3->whereNotNull('transfer_id')
                   ->whereIn('transfer_id', $assignedCategories);
            })

            // Case 3: Forward exists
            ->orWhere(function ($q4) use ($allCounters, $counterId) {
                $q4->whereNotNull('forward_counter_id');

                if ($counterId) {
                    $q4->where('forward_counter_id', $counterId);
                } else {
                    $q4->whereIn('forward_counter_id', $allCounters);
                }
            });
        });
    }

    if ($location) {
        $query->where('locations_id', $location)
              ->whereNotNull('locations_id');
    }

    if ($assign_staff && $userAuth && !$userAuth->hasRole(User::ROLE_ADMIN)) {
        $query->where('assign_staff_id', $assign_staff);
    }

    $pageSize = $isFixed ? ($page ?? 10) : 50;

  $paginatedResults = $query
        ->with(['category', 'subCategory', 'childCategory', 'forwardcounter', 'transfer']) // eager load
        ->orderBy($orderByColumn, 'asc')
        ->paginate($pageSize)
        ->items();

        return collect($paginatedResults);
}


    public static function getPendingQueuesDepartment($conditionTeam, $isFixed = false, $location = null, $page = null, $name = null, $team_id = null, $queueType = null,$counterId=null,$assign_staff =null,$enable_callDepartment= false)
    {

     $timezone = Session::get('timezone_set') ?? 'UTC';
    $userAuth = Auth::user();
    $today = Carbon::today($timezone);

    if (is_null($team_id)) {
        $team_id = tenant('id');
    }

    

    $orderByColumn = $queueType == Queue::SORTABLE ? 'priority_sort' : 'datetime';

    $query = QueueStorage::where($conditionTeam)
        ->where('is_hold', self::STATUS_NO)
        ->where('is_missed', self::STATUS_NO)
        ->whereNull([
            'start_datetime',
            'called_datetime',
            'cancelled_datetime',
            'closed_datetime',
        ])
        ->whereDate('arrives_time', $today->format('Y-m-d'))
        ->where('called', 'yes')
        ->where('assign_staff_id', Auth::id());

    

   if ($name) {
    $query->where(function ($query) use ($name) {

        // Extract acronym (letters) and token (numbers)
        $acronym = preg_replace('/[^A-Za-z]/', '', $name);  // FU
        $token   = preg_replace('/[^0-9]/', '', $name);      // 001

        // If input has both acronym and token (like FU001)
        if ($acronym && $token) {
            $query->where(function ($q) use ($acronym, $token) {
                $q->where('start_acronym', 'like', "%$acronym%")
                  ->where('token', 'like', "%$token%");
            });
        } else {
            // Normal search when input doesn't contain both
            $query->where('name', 'like', "%$name%")
                  ->orWhere('token', 'like', "%$name%")
                  ->orWhere('start_acronym', 'like', "%$name%");
        }
    });
}

    if (!empty($counterId)) {
        $query->where(function ($subQuery) use ($counterId) {
            $subQuery->whereNull('forward_counter_id')
                ->orWhere('forward_counter_id', $counterId);
        });
    }

    if ($userAuth && !$userAuth->hasRole(User::ROLE_ADMIN)) {
        self::subQueryCategoryMul($query, $userAuth);
    }

    if ($location) {
        $query->where('locations_id', $location)
            ->whereNotNull('locations_id');
    }

    // Fetch all pending queues
    $results = $query
    ->with(['category', 'subCategory', 'childCategory', 'forwardcounter', 'transfer']) // eager load relationships
    ->orderBy('token', 'asc')
    ->orderBy('priority_sort', 'asc')
    ->get();

    // Group by token and filter staff turn
    $filteredQueues = $results->groupBy('token')->map(function ($tokenGroup) use ($assign_staff) {
        // Sort by priority_sort
        $sorted = $tokenGroup->sortBy('priority_sort');

        // Pick only the first queue of this token (smallest priority_sort) which is not closed
        $nextTurn = $sorted->first();

        // Return only if current user is the assigned staff
        if ($nextTurn && $nextTurn->assign_staff_id == $assign_staff) {
            return $nextTurn;
        }

        return null;
    })->filter()->values();

    $pageSize = $isFixed ? ($page ?? 10) : 50;

    return $filteredQueues->forPage(1, $pageSize)->values();

}


    public static function getPendingQueuesNumber(
    $conditionTeam,
    $location = null,
    $name = null,
    $team_id = null,
    $queueType = null,
) {
    $timezone = Session::get('timezone_set') ?? 'UTC';
    $userAuth = Auth::user();
    $today = Carbon::today($timezone);

    if (is_null($team_id)) {
        $team_id = tenant('id');
    }

     $assign_staff ='';
    $enableStaffPriority = SiteDetail::where($conditionTeam)->where('location_id', $location)->value('use_staff_priority') ?? false;

    if($enableStaffPriority){
           $assign_staff = $userAuth->id ?? null;
    }

    $orderByColumn = $queueType == Queue::SORTABLE ? 'priority_sort' : 'datetime';

    $query = QueueStorage::select([
            'id', 'phone', 'phone_code', $orderByColumn
        ])
        ->where($conditionTeam)
        ->where('is_hold', self::STATUS_NO)
        ->where('temp_hold', self::STATUS_NO)
        ->where('is_missed', self::STATUS_NO)
        ->whereNull([
            'start_datetime',
            'called_datetime',
            'cancelled_datetime',
            'closed_datetime',
        ])

        ->whereDate('arrives_time', $today->format('Y-m-d'));

   if ($name) {
    $query->where(function ($query) use ($name) {

        // Extract acronym (letters) and token (numbers)
        $acronym = preg_replace('/[^A-Za-z]/', '', $name);  // FU
        $token   = preg_replace('/[^0-9]/', '', $name);      // 001

        // If input has both acronym and token (like FU001)
        if ($acronym && $token) {
            $query->where(function ($q) use ($acronym, $token) {
                $q->where('start_acronym', 'like', "%$acronym%")
                  ->where('token', 'like', "%$token%");
            });
        } else {
            // Normal search when input doesn't contain both
            $query->where('name', 'like', "%$name%")
                  ->orWhere('token', 'like', "%$name%")
                  ->orWhere('start_acronym', 'like', "%$name%");
        }
    });
}

     if ($userAuth && !$userAuth->hasRole(User::ROLE_ADMIN)) {
                self::subQueryCategoryMul($query, $userAuth);
        }


    if ($location) {
        $query->where('locations_id', $location)
              ->whereNotNull('locations_id');
    }
     if ($assign_staff && $userAuth && !$userAuth->hasRole(User::ROLE_ADMIN)) {
                $query->where('assign_staff_id', $assign_staff);
            }

    $results = $query->orderBy($orderByColumn, 'asc')
                     ->get();

     return $results->map(function ($item) {
        return $item->phone_code . $item->phone;
    })->filter()->values()->all();
}


    public static function subQueryCategoryMul($query, $userAuth)
    {
        return $query->where(function ($subQuery) use ($userAuth) {
            $subQuery->where(function ($checkTransferId) use ($userAuth) {
                $checkTransferId->whereNotNull('queues_storage.transfer_id')
                    ->whereExists(function ($existsQuery) use ($userAuth) {
                        $existsQuery->select(DB::raw(1))
                            ->from('category_user')
                            ->where('user_id', $userAuth->id)
                            ->whereColumn('category_user.category_id', 'queues_storage.transfer_id');
                    });
            })->orWhere(function ($checkTransferIdEmpty) use ($userAuth) {
                $checkTransferIdEmpty->whereNull('queues_storage.transfer_id')
                    ->whereExists(function ($existsQuery) use ($userAuth) {
                        $existsQuery->select(DB::raw(1))
                            ->from('category_user')
                            ->where('user_id', $userAuth->id)
                            ->where(function ($query) {
                                $query->whereColumn('category_user.category_id', 'queues_storage.child_category_id')
                                ->orWhereColumn('category_user.category_id', 'queues_storage.sub_category_id')
                                ->orWhereColumn('category_user.category_id', 'queues_storage.category_id');
                            });
                    });
            });
        });
    }



    public static function subQueryCategory($query, $userAuth)
    {
        $categories = $userAuth->categories->pluck('id');

        return $query->where(function ($subQuery) use ($userAuth) {
            $subQuery->where(function ($checkTransferId) use ($userAuth) {
                $checkTransferId->whereNotNull('queues.transfer_id')
                    ->whereExists(function ($existsQuery) use ($userAuth) {
                        $existsQuery->select(DB::raw(1))
                            ->from('category_user')
                            ->where('user_id', $userAuth->id)
                            ->whereColumn('category_user.category_id', 'queues.transfer_id');
                    });
            })->orWhere(function ($checkTransferIdEmpty) use ($userAuth) {
                $checkTransferIdEmpty->whereNull('queues.transfer_id')
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
    }

    public static function checkCategoryAccess($queueId, $userAuth)
    {
        $query = DB::table('queues_storage')
            ->where('id', $queueId);

        self::subQueryCategory($query, $userAuth);

        return $query->exists();
    }


     

 public static function getMissedCallId(
    $conditionTeam,
    $onlyDepartmentQueue = 0,
    $location = null,
    $enabledepartment = false,
    $counterId = null,
    $categories = null
) {

        if(!empty($location)){
                $timezone = Session::has('timezone_set') ? Session::get('timezone_set') : self::timezoneSet($location);
        }else{
                $timezone = Session::get('timezone_set') ?? 'UTC';
        }

        $userAuth = Auth::user();
        $todayDate = Carbon::today($timezone);

    $enableStaffPriority = SiteDetail::where($conditionTeam)
        ->where('location_id', $location)
        ->value('use_staff_priority') ?? false;

    $assign_staff = null;
    if ($enableStaffPriority || $enabledepartment) {
        $assign_staff = $userAuth->id ?? null;
    }

  

    $categories = [];
    $counterId  = [];

if ($userAuth) {
    $categories       = $userAuth->categories?->pluck('id')->toArray() ?? [];
    $assignedCounter  = $userAuth->counter_id ?? null;
    $assignedCounters = $userAuth['assign_counters'] ?? [];
    $counterId        = array_values(array_unique(array_merge(
        [$assignedCounter],
        $assignedCounters
    )));
}

// Always cast into array
$categories = (array) $categories;
$counterId  = (array) $counterId;

    $query = self::select('queues.id', 'start_acronym', 'token')
        ->where($conditionTeam)
        ->whereHas('queueStorages', function ($query) use (
            $conditionTeam,
            $todayDate,
            $onlyDepartmentQueue,
            $userAuth,
            $assign_staff,
            $counterId,
            $categories
        ) {
            $query->where($conditionTeam)
                ->where('is_missed', self::STATUS_YES)
                ->where('assign_staff_id', $assign_staff)
                ->whereDate('arrives_time', $todayDate);

            $query->where(function ($q) use ($categories, $counterId) {
                // Case 1: Normal (no transfer/forward)
                $q->where(function ($q2) use ($categories) {
                    $q2->whereNull('transfer_id')
                       ->whereNull('forward_counter_id')
                       ->where(function ($cat) use ($categories) {
                           if (!empty($categories)) {
                               $cat->whereIn('category_id', $categories)
                                   ->orWhereIn('sub_category_id', $categories)
                                   ->orWhereIn('child_category_id', $categories);
                           }
                       });
                })

                // Case 2: Transfer exists
                ->orWhere(function ($q3) use ($categories) {
                    if (!empty($categories)) {
                        $q3->whereNotNull('transfer_id')
                           ->whereIn('transfer_id', $categories);
                    }
                })

                // Case 3: Forward exists
                ->orWhere(function ($q4) use ($counterId) {
                    $q4->whereNotNull('forward_counter_id');

                    if (!empty($counterId)) {
                        $q4->whereIn('forward_counter_id', $counterId);
                    }
                });
            });
        });

    if ($location) {
        $query->where('locations_id', $location);
    }

    $queues = $query->get();

      // If enabledepartment is true, we need to group by token and queue_id
    if ($enabledepartment) {
        $grouped = [];
        foreach ($queues as $queue) {
            
            if ($queue->queueStorages->isNotEmpty()) {
                $key = $queue->token . '_' . $queue->id;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = $queue;
                }
            }
        }
        // Convert back to collection and take first 10
        $queues = collect($grouped)->values()->take(10);

          return $queues->mapWithKeys(function ($queue) use ($assign_staff) {
        if ($queue->queueStorages->isNotEmpty()) {
            return [
                $queue->id => [
                    'start_acronym' => $queue->start_acronym,
                    'token'         => $queue->token,
                    'queue_storages' => $queue->queueStorages->where('is_missed', self::STATUS_YES)->where('assign_staff_id', $assign_staff)->map(function ($qs) {
                        return [
                            'queue_id' => $qs->id,
                            'name'     => $qs->name,
                        ];
                    })->toArray()
                ]
            ];
        }
        return [];
    })->toArray();
   
    } else {
        // Original behavior - limit to 10
        $queues = $queues->take(10);

          return $queues->mapWithKeys(function ($queue) use ($assign_staff) {
        if ($queue->queueStorages->isNotEmpty()) {
            return [
                $queue->id => [
                    'start_acronym' => $queue->start_acronym,
                    'token'         => $queue->token,
                    'queue_storages' => $queue->queueStorages->map(function ($qs) {
                        return [
                            'queue_id' => $qs->id,
                            'name'     => $qs->name,
                        ];
                    })->toArray()
                ]
            ];
        }
        return [];
    })->toArray();
    }
}




   public static function getMissedCallIdDisplay(
    $conditionTeam,
    $onlyDepartmentQueue = 0,
    $location = null,
    $enabledepartment = false,
    $counterId = null,
    $categories = null
) {
    $timezone = Session::get('timezone_set') ?? 'UTC';

    $userAuth = Auth::user();
    $todayDate = Carbon::today($timezone);

    $enableStaffPriority = SiteDetail::where($conditionTeam)
        ->where('location_id', $location)
        ->value('use_staff_priority') ?? false;

    $assign_staff = null;
    if ($enableStaffPriority || $enabledepartment) {
        $assign_staff = $userAuth->id ?? null;
    }

    // ✅ Always cast categories & counterId into array
    $categories = (array) $categories ?? [];
    $counterId  = (array) $counterId ?? [];

    $query = self::select('queues.id', 'start_acronym', 'token')
        ->where($conditionTeam)
        ->whereHas('queueStorages', function ($query) use (
            $conditionTeam,
            $todayDate,
            $onlyDepartmentQueue,
            $userAuth,
            $assign_staff,
            $counterId,
            $categories
        ) {
            $query->where($conditionTeam)
                ->where('is_missed', self::STATUS_YES)
                ->whereDate('arrives_time', $todayDate);

            $query->where(function ($q) use ($categories, $counterId) {
                // Case 1: Normal (no transfer/forward)
                $q->where(function ($q2) use ($categories) {
                    $q2->whereNull('transfer_id')
                       ->whereNull('forward_counter_id')
                       ->where(function ($cat) use ($categories) {
                           if (!empty($categories)) {
                               $cat->whereIn('category_id', $categories)
                                   ->orWhereIn('sub_category_id', $categories)
                                   ->orWhereIn('child_category_id', $categories);
                           }
                       });
                })

                // Case 2: Transfer exists
                ->orWhere(function ($q3) use ($categories) {
                    if (!empty($categories)) {
                        $q3->whereNotNull('transfer_id')
                           ->whereIn('transfer_id', $categories);
                    }
                })

                // Case 3: Forward exists
                ->orWhere(function ($q4) use ($counterId) {
                    $q4->whereNotNull('forward_counter_id');

                    if (!empty($counterId)) {
                        $q4->whereIn('forward_counter_id', $counterId);
                    }
                });
            });
        });

    if ($location) {
        $query->where('locations_id', $location);
    }

    $queues = $query->limit(10)->get();

    return $queues->mapWithKeys(function ($queue) {
        if ($queue->queueStorages->isNotEmpty()) {
            return [
                $queue->id => [
                    'start_acronym' => $queue->start_acronym,
                    'token'         => $queue->token,
                    'queue_storages' => $queue->queueStorages->map(function ($qs) {
                        return [
                            'queue_id' => $qs->id,
                            'name'     => $qs->name,
                        ];
                    })->toArray()
                ]
            ];
        }
        return [];
    })->toArray();
}


 public static function getHoldCallDisplay(
    $conditionTeam,
    $onlyDepartmentQueue = 0,
    $location = null,
    $enabledepartment = false,
    $counterId = null,
    $categories = null
) {
    $timezone = Session::get('timezone_set') ?? 'UTC';

    $userAuth = Auth::user();
    $todayDate = Carbon::today($timezone);

    $enableStaffPriority = SiteDetail::where($conditionTeam)
        ->where('location_id', $location)
        ->value('use_staff_priority') ?? false;

    $assign_staff = null;
    if ($enableStaffPriority || $enabledepartment) {
        $assign_staff = $userAuth->id ?? null;
    }

    // ✅ Always cast categories & counterId into array
    $categories = (array) $categories ?? [];
    $counterId  = (array) $counterId ?? [];

    $query = QueueStorage::where($conditionTeam)
      ->where(function ($q) use ($conditionTeam) {
            $q->where($conditionTeam)
            ->where('is_hold', self::STATUS_YES);
        });

            if (Auth::check() && !Auth::user()->hasRole(User::ROLE_ADMIN)) {
                $query->where('hold_by', Auth::user()->id);
            }

    $query->whereDate('hold_start_datetime', Carbon::now());


            $query->where(function ($q) use ($categories, $counterId) {
                // Case 1: Normal (no transfer/forward)
                $q->where(function ($q2) use ($categories) {
                    $q2->whereNull('transfer_id')
                       ->whereNull('forward_counter_id')
                       ->where(function ($cat) use ($categories) {
                           if (!empty($categories)) {
                               $cat->whereIn('category_id', $categories)
                                   ->orWhereIn('sub_category_id', $categories)
                                   ->orWhereIn('child_category_id', $categories);
                           }
                       });
                })

                // Case 2: Transfer exists
                ->orWhere(function ($q3) use ($categories) {
                    if (!empty($categories)) {
                        $q3->whereNotNull('transfer_id')
                           ->whereIn('transfer_id', $categories);
                    }
                })

                // Case 3: Forward exists
                ->orWhere(function ($q4) use ($counterId) {
                    $q4->whereNotNull('forward_counter_id');

                    if (!empty($counterId)) {
                        $q4->whereIn('forward_counter_id', $counterId);
                    }
                });
            });



    if ($location) {
        $query->where('locations_id', $location);
    }

    $data = $query->select('token', 'id', 'start_acronym', 'name')->get()->toArray();

    return $data ?: [];
}


 

public static function getAllQueues(
    $teamId,
    $location = null,
    $limitNumber = 6,
    $counterID = null,
    $categories = null,
    $skipClosedCall = null,
    $enableWaiting,
    $enableMissing,
    $enableHold,
) {
    try {
        $timezone   = Config::get('app.timezone') ?? Session::get('timezone_set') ?? 'UTC';
        $today      = Carbon::today($timezone);

        $categories = $categories ? (array) $categories : [];
        $counterID  = $counterID ? (array) $counterID : [];

        /** --------------------------------------------------------
         *  BASE QUERY (EAGER LOADED — SINGLE PLACE)
         * -------------------------------------------------------- */
        $baseQuery = QueueStorage::query()
            ->where('team_id', $teamId)
            ->whereDate('arrives_time', $today)
            ->when($location, fn($q) => $q->where('locations_id', $location))
            ->with([
                'counter:id,name',
                'forwardcounter:id,name',
                'category:id,name',
                'subCategory:id,name',
                'childCategory:id,name',
                'transfer:id,name'
            ]);

        /** --------------------------------------------------------
         *  FILTER BY COUNTERS / CATEGORIES
         * -------------------------------------------------------- */
        if (!empty($categories) || !empty($counterID)) {
            $baseQuery->where(function ($q) use ($categories, $counterID) {

                if (!empty($categories)) {
                    $q->orWhere(function ($catQ) use ($categories) {
                        $catQ->where(function ($w) use ($categories) {
                            $w->whereIn('category_id', $categories)
                                ->orWhereIn('sub_category_id', $categories)
                                ->orWhereIn('child_category_id', $categories);
                        });
                    });
                }

                if (!empty($counterID)) {
                    $q->orWhere(function ($cQ) use ($counterID) {
                        $cQ->whereIn('counter_id', $counterID);
                    });
                }
            });
        }

        /** --------------------------------------------------------
         *  DISPLAY QUEUE
         * -------------------------------------------------------- */
        $displayQueue = (clone $baseQuery)
            ->where('is_missed', self::STATUS_NO)
            ->when($skipClosedCall, fn($q) => $q->where('status', self::STATUS_PROGRESS))
            ->whereNotNull('called_datetime')
            ->orderBy('datetime', 'desc')
            ->limit($limitNumber)
            ->get([
                'id', 'token', 'name', 'start_acronym',
                'counter_id', 'status', 'called_datetime'
            ])
            ->map(fn($item) => [
                'id'      => $item->id,
                'token'   => $item->start_acronym . $item->token,
                'name'    => $item->name,
                'status'  => $item->status,
                'counter' => optional($item->counter)->name,
            ]);

        /** --------------------------------------------------------
         *  WAITING QUEUE
         * -------------------------------------------------------- */
        $waitingCalls = $enableWaiting
            ? (clone $baseQuery)
                ->where('is_hold', self::STATUS_NO)
                ->where('temp_hold', self::STATUS_NO)
                ->where('is_missed', self::STATUS_NO)
                ->whereNull(['start_datetime', 'called_datetime', 'cancelled_datetime', 'closed_datetime'])
                ->orderBy('datetime', 'asc')
                ->limit(10)
                ->get(['id', 'token', 'name', 'start_acronym'])
                ->map(fn($item) => [
                    'token' => $item->start_acronym . $item->token,
                    'name'  => $item->name
                ])
            : collect();

        /** --------------------------------------------------------
         *  MISSED CALLS
         * -------------------------------------------------------- */
        $missedCalls = $enableMissing
            ? (clone $baseQuery)
                ->where('is_missed', self::STATUS_YES)
                ->limit(10)
                ->get(['id', 'token', 'name', 'start_acronym'])
                ->map(fn($item) => [
                    'token' => $item->start_acronym . $item->token,
                    'name'  => $item->name
                ])
            : collect();

        /** --------------------------------------------------------
         *  HOLD CALLS
         * -------------------------------------------------------- */
        $holdCalls = $enableHold
            ? (clone $baseQuery)
                ->where('is_hold', self::STATUS_YES)
                ->whereDate('hold_start_datetime', Carbon::now($timezone))
                ->get(['id', 'token', 'name', 'start_acronym'])
                ->map(fn($item) => [
                    'token' => $item->start_acronym . $item->token,
                    'name'  => $item->name
                ])
            : collect();

        /** --------------------------------------------------------
         *  FINAL RETURN
         * -------------------------------------------------------- */
        return [
            'display' => $displayQueue,
            'waiting' => $waitingCalls,
            'missed'  => $missedCalls,
            'hold'    => $holdCalls,
        ];
    } catch (\Exception $e) {
        Log::error("Queue fetch failed: " . $e->getMessage(), [
            'team_id'  => $teamId,
            'location' => $location
        ]);

        return [
            'display' => collect(),
            'waiting' => collect(),
            'missed'  => collect(),
            'hold'    => collect(),
        ];
    }
}





    public static function currentVisitorRecord($conditionTeam, $userAuthID=null, $queueID = null, $location = null,$storageID = null)
    {

          if(!empty($location)){

              $timezone = Session::has('timezone_set') ? Session::get('timezone_set') : self::timezoneSet($location);
          }else{
   $timezone = Session::get('timezone_set') ?? 'UTC';
          }
        return QueueStorage::with(['category', 'subCategory', 'childCategory'])
			->where($conditionTeam)
			->when(!empty($queueID), function ($query) use ($queueID) {
				$query->where(['queue_id' => $queueID]);
			})
			->when(!empty($storageID), function ($query) use ($storageID) {
				$query->where(['id' => $storageID]);
			})->when((!empty($location) && $location != 0), function ($query) use ($location) {
				$query->where(['locations_id' => $location]);
			})
			->when((!empty($userAuthID)), function ($query) use ($userAuthID) {
				$query->where(['served_by' => $userAuthID]);
			})
			->whereDate('arrives_time', Carbon::today($timezone))

			->where(['status' => self::STATUS_PROGRESS])->where(function ($query) {
				$query->whereNull('start_datetime')->orwhereNull('closed_datetime');
			})->whereNotNull('called_datetime')->whereNull('cancelled_datetime')
		   ->first();

    }

    public static function randomeQueueStorageNextID($conditionTeam, $userAuthID, $queueID = null, $location = null)
    {
        $timezone = Session::get('timezone_set') ?? 'UTC';
        return QueueStorage::where($conditionTeam)
        ->when(!empty($queueID), function ($query) use ($queueID) {
            $query->where(['queue_id' => $queueID]);
        })
        ->when((!empty($location) && $location != 0), function ($query) use ($location) {
            $query->where(['locations_id' => $location]);
        })
        ->whereDate('arrives_time', Carbon::today($timezone))
        ->where(['status' => self::STATUS_PENDING])->where(function ($query) {
            $query->whereNull(['start_datetime','called_datetime','closed_datetime','cancelled_datetime']);
        })
       ->first();

    }

    public static function displayQueue($teamId, $location = null, $limitNumber = 6, $counterID = null,$categories = null, $skipClosedCall = null)
{
    $timezone = Config::get('app.timezone') ?? Session::get('timezone_set') ?? 'UTC';
       $today =Carbon::today($timezone);
        $categories = (array) $categories ?? [];
         $counterID  = (array) $counterID ?? [];
    // Base query with a relationship to 'counter'
    $query = QueueStorage::with(['counter' => function ($query) {
        $query->select('id', 'name'); // Only select the necessary fields from the counters table
    }])
        ->where('team_id', $teamId)
        ->where('is_missed', self::STATUS_NO);

        if(!empty($skipClosedCall) && $skipClosedCall == '1')
        {
            $query->where('status', self::STATUS_PROGRESS);
        }

      $query->whereDate('arrives_time', $today)
     ->whereNotNull('called_datetime');

    // If location is specified
    if ($location !== null && $location != 0) {
        $query->where('locations_id', $location);
    }
if(!empty($categories) || !empty($counterID)){

       $query->where(function ($q) use ($categories, $counterID) {
                // Case 1: Normal (no transfer/forward)
                $q->where(function ($q2) use ($categories) {
                    $q2->where(function ($cat) use ($categories) {
                           if (!empty($categories)) {
                               $cat->whereIn('category_id', $categories)
                                   ->orWhereIn('sub_category_id', $categories)
                                   ->orWhereIn('child_category_id', $categories);
                           }
                       });
                })
                ->orWhere(function ($q2) use ($counterID) {
                    $q2->where(function ($qc) use ($counterID) {
                           if (!empty($counterID)) {
                               $qc->whereIn('counter_id', $counterID);
                           }
                       });
                })

                // Case 2: Transfer exists
                ->orWhere(function ($q3) use ($categories) {
                    if (!empty($categories)) {
                        $q3->whereNotNull('transfer_id')
                           ->whereIn('transfer_id', $categories);
                    }
                })

                // Case 3: Forward exists
                ->orWhere(function ($q4) use ($counterID) {
                    $q4->whereNotNull('forward_counter_id');

                    if (!empty($counterID)) {
                        $q4->whereIn('counter_id', $counterID)
                        ->orWhereIn('forward_counter_id', $counterID);
                    }
                });
            });
        }
    // Get the queue with the necessary fields
    $queueDisplay = $query->orderBy('datetime', 'desc')
        // ->limit($limitNumber)
        ->get(['id', 'team_id', 'locations_id','name', 'status', 'counter_id', 'token', 'start_acronym', 'start_datetime', 'called_datetime']);

    // Sort the collection so that items with 'status' == STATUS_PROGRESS come first
    $queueDisplay = $queueDisplay->sortBy(function ($item) {
        return $item->status === self::STATUS_PROGRESS ? -1 : 0;
    });

    // Include only counter names if counterID is not null or empty
    if ($counterID !== null && !empty($counterID)) {
        $queueDisplay->transform(function ($item) {
            $item->counter_name = optional($item->counter)->name; // Fetch counter name if the relationship exists
            unset($item->counter); // Remove the full relationship data if not needed
            return $item;
        });
    }
    $queueDisplay = $queueDisplay->slice(0,$limitNumber)->values();
    return $queueDisplay;
}

    public static function displayQueueApi($teamId, $location = null, $limitNumber = 6, $counterID = null,$categories = null)
{

  $timezone = Config::get('app.timezone') ?? SiteDetail::where('team_id',$teamId)->where('location_id',$location)->value('select_timezone') ?? 'UTC';

         $categories = (array) $categories ?? [];
         $counterID  = (array) $counterID ?? [];
    // Base query with a relationship to 'counter'
    $query = QueueStorage::with(['counter' => function ($query) {
        $query->select('id', 'name'); // Only select the necessary fields from the counters table
    }])
        ->where('team_id', $teamId)
        ->where('is_missed', self::STATUS_NO)
       ->whereDate('arrives_time', Carbon::today($timezone))
        ->whereNotNull('called_datetime');

    // If location is specified
    if ($location !== null && $location != 0) {
        $query->where('locations_id', $location);
    }

    // If counterID is provided, filter by counter_id
    // if ($counterID !== null && !empty($counterID)) {
    //      $query->whereIn('counter_id', $counterID)
    //             ->orWhereIn('forward_counter_id', $counterID);
    // }


    //    if (!empty($categories)) {
    //     $query->where(function ($query) use ($categories) {
    //         $query->whereIn('category_id', $categories)
    //             ->orWhereIn('sub_category_id', $categories)
    //             ->orWhereIn('child_category_id', $categories)
    //             ->orWhereIn('transfer_id', $categories);
    //     });
    // }

      $query->where(function ($q) use ($categories, $counterID) {
                // Case 1: Normal (no transfer/forward)
                $q->where(function ($q2) use ($categories) {
                    $q2->whereNull('transfer_id')
                       ->whereNull('forward_counter_id')
                       ->where(function ($cat) use ($categories) {
                           if (!empty($categories)) {
                               $cat->whereIn('category_id', $categories)
                                   ->orWhereIn('sub_category_id', $categories)
                                   ->orWhereIn('child_category_id', $categories);
                           }
                       });
                })

                  ->orWhere(function ($q2) use ($counterID) {
                    $q2->whereNull('transfer_id')
                       ->whereNull('forward_counter_id')
                       ->where(function ($qc) use ($counterID) {
                           if (!empty($counterID)) {
                               $qc->whereIn('counter_id', $counterID);
                           }
                       });
                })
                // Case 2: Transfer exists
                ->orWhere(function ($q3) use ($categories) {
                    if (!empty($categories)) {
                        $q3->whereNotNull('transfer_id')
                           ->whereIn('transfer_id', $categories);
                    }
                })

                // Case 3: Forward exists
                ->orWhere(function ($q4) use ($counterID) {
                    $q4->whereNotNull('forward_counter_id');

                    if (!empty($counterID)) {
                        $q4->whereIn('counter_id', $counterID)
                        ->whereIn('forward_counter_id', $counterID);
                    }
                });
            });

    // Get the queue with the necessary fields
   // Get the queue with the necessary fields
    $queueDisplay = $query->orderBy('datetime', 'desc')
        // ->limit($limitNumber)
        ->get(['id', 'team_id', 'status', 'counter_id', 'token', 'start_acronym', 'start_datetime', 'called_datetime']);

    // Sort the collection so that items with 'status' == STATUS_PROGRESS come first
    $queueDisplay = $queueDisplay->sortBy(function ($item) {
        return $item->status === self::STATUS_PROGRESS ? -1 : 0;
    });

    // Include only counter names if counterID is not null or empty
    // if ($counterID !== null && !empty($counterID)) {
        $queueDisplay->transform(function ($item) {
            $item->counter_name = optional($item->counter)->name; // Fetch counter name if the relationship exists
            unset($item->counter); // Remove the full relationship data if not needed
            return $item;
        });
    // }
$queueDisplay = $queueDisplay->slice(0,$limitNumber)->values();
    return $queueDisplay;
}


    public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }


    public static function countPending($teamId, $createdId, $countCatID, $fieldCatName, $counterID, $location = null)
    {
        $timezone = Session::get('timezone_set') ?? 'UTC';
        $queueRecord = self::viewQueue($createdId);
        // If the queue record doesn't exist, return 0
        if (!$queueRecord) {
            return 0;
        }
        $query = self::where('datetime', '<', $queueRecord->datetime)
            ->where(['status' => self::STATUS_PENDING, 'team_id' => $teamId, 'is_missed' => self::STATUS_NO])
            ->whereDate('arrives_time', Carbon::today($timezone))->whereNull('cancelled_datetime');

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


//     public static function getLastToken($teamId, $acronym, $locationId = null,$last_category=null)
// {
//     $timezone = Session::get('timezone_set') ?? 'UTC';

//     // Prepare the base query
//     $query = QueueStorage::where('team_id', $teamId)
//         ->whereDate('arrives_time', Carbon::today($timezone));

//     // Add conditions dynamically
//     if ($acronym) {
//         $query->where('start_acronym', $acronym);
//     }

//     if ($locationId) {
//         $query->where('locations_id', $locationId);
//     }

//     // Retrieve the latest token value
//     return $query->orderByDesc('id')->value('token');
// }

   public static function getLastToken($teamId, $acronym, $locationId = null,$lastcategory =null)
{
    $timezone = Session::get('timezone_set') ?? 'UTC';

    // Prepare the base query
    $query = Queue::where('team_id', $teamId)
        ->whereDate('arrives_time', Carbon::today($timezone));

    // Add conditions dynamically
    if ($acronym) {
        $query->where('start_acronym', $acronym);
    }

    if ($locationId) {
        $query->where('locations_id', $locationId);
    }
    if ($lastcategory) {
        $query->where('last_category', $lastcategory);
    }

    // Retrieve the latest token value
    return $query->orderByDesc('id')->value('token');
}

public static function newGeneratedToken($lastToken, $token, $tokenDigit = 4)
{
    // Determine the starting token
    $tokenStart = $lastToken ? ((int) $lastToken + 1) : (int) $token;

    // Ensure the token is padded to the required digits
    return str_pad($tokenStart, $tokenDigit, '0', STR_PAD_LEFT);
}


    public static function checkToken($teamId, $acronym, $token, $locationID = null)
    {

      $timezone = Session::get('timezone_set') ?? 'UTC';
        $query = self::where('team_id', $teamId)->where('token', $token);

        if ($acronym != SiteDetail::DEFAULT_WALKIN_A) {
            $query->where('start_acronym', $acronym);
        }
        if (!empty($locationID)) {
            $query->where('locations_id', $locationID);
        }
        $query->whereDate('arrives_time', Carbon::today($timezone));

        return $query->exists();
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
        foreach ($counters as $counter) {
            $tasksCount = $counter->queues()->where(['status' => self::STATUS_PENDING, 'locations_id' => (int) $location])->whereNotNull('counter_id')->count();
            if ($tasksCount < $minTasks) {
                $minTasks = $tasksCount;
                $targetCounter = $counter;
            }
        }
        $queue = QueueStorage::find($queueID);
        $queue->counter_id = $targetCounter->id;
        $queue->save();

        return $queue->counter_id;
    }

    public static function viewCounterID($queueID)
    {
        return Queue::find($queueID)?->counter_id;
    }



    public static function smsReminderNumber($currentVisitorId,$nextStorageId,$teamId)
    {
          Log::info('reminder start');
        $timezone = Session::get('timezone_set') ?? 'UTC';
        $userAuth = Auth::user();
        $today = Carbon::today($timezone);

        $reminderBefore = $user->sms_reminder_queue ?? 1;

         $orderByColumn =  'datetime';

        $queue = QueueStorage::where('id','>',$nextStorageId)
            ->where('team_id', $teamId)
             ->where('is_hold', self::STATUS_NO)
            ->where('temp_hold', self::STATUS_NO)
            ->where('is_missed', self::STATUS_NO)
            ->whereNull([
                    'start_datetime',
                    'called_datetime',
                    'cancelled_datetime',
                    'closed_datetime',
                ])
            ->whereDate('arrives_time', $today->format('Y-m-d'))
            ->orderBy($orderByColumn, 'asc')
            ->skip($reminderBefore)
            ->take(1)
            ->first();
            // dd($queue);
        if (!empty($queue)){

            $dateformat = AccountSetting::showDateTimeFormat();

            if ( !empty( $queue['child_category_id'] ) )
            $thirdCategoryName = Category::viewCategoryName( $queue['child_category_id'] );
            if ( !empty( $queue['sub_category_id']) )
            $secondCategoryName = Category::viewCategoryName($queue['sub_category_id'] );
            if ( !empty( $queue['category_id'] ) )
            $categoryName =  Category::viewCategoryName( $queue['category_id'] );
            $formattedFields = json_decode($queue['json'],true) ?? [];
            Log::info($formattedFields);
            $email =isset( $formattedFields[ 'email' ] ) ? $formattedFields[ 'email' ] : ( isset( $formattedFields[ 'Email' ] ) ? $formattedFields[ 'Email' ] : null );
          $locationName= Location::locationName($queue['locations_id']);

            $data = [
                'name' => $queue['name'],
                'phone' => $queue['phone'],
                'phone_code' =>  $queue['phone_code'] ?? '91',
                'queue_no' => $queue['queue_id'],
                'queue_storage_id' => $queue['id'],
                'arrives_time' => Carbon::parse($queue['arrives_time'])->format($dateformat),
                'category_name' => $categoryName,
                'thirdC_name' => $thirdCategoryName ?? '',
                'secondC_name' => $secondCategoryName ?? '',
                'pending_count' => '',
                'token' =>($queue['start_acronym']?? ''). $queue['token'],
                'token_with_acronym' => $queue['start_acronym'] ?? '',
                'to_mail' => $email ?? '',
                'locations_id' => $queue['locations_id'],
                'location_name' => $locationName,
                'priority_sort' => 0,
                 'team_id' => $queue->team_id,
            ];


                 $logData = [
                'team_id' => $queue->team_id,
                'location_id' => $queue->locations_id,
                'user_id' =>  $queue->served_by,
                'customer_id' =>  $queue->created_by,
                'queue_id' =>  $queue->queue_id,
                'queue_storage_id' =>  $queue->id,
                'booking_id' =>  null,
                'email' => $email,
                'contact' => $queue['phone'],
                'type' => MessageDetail::AUTOMATIC_TYPE,
                'event_name' => 'Reminder for Call Turn',
            ];

            Log::info($data);
            self::sendNotification($data, 'reminder', $logData);
                 Log::info('reminder end');

        }else{
            Log::info('no sms-'.$currentVisitorId);
        }
    }

       public static function sendNotification($data, $type, $logData = null)
    {

        if (isset($data['to_mail']) && $data['to_mail'] != '') {
            SmtpDetails::sendMail($data, $type, '',  $data['team_id'],$logData);
        }
        if (!empty($data['phone'])) {
            SmsAPI::sendSms($data['team_id'], $data, $type, $type, $logData);
        }
    }



    public static function sendSMSReminder($queue, $teamId)
    {
        $dateformat = AccountSetting::showDateTimeFormat();

        if ( !empty( $queue['child_category_id'] ) )
        $thirdCategoryName = Category::viewCategoryName( $queue['child_category_id'] );
        if ( !empty( $queue['sub_category_id']) )
        $secondCategoryName = Category::viewCategoryName($queue['sub_category_id'] );
        if ( !empty( $queue['category_id'] ) )
        $categoryName =  Category::viewCategoryName( $queue['category_id'] );
        $formattedFields = json_decode($queue['json'],true) ?? [];

        $email =isset( $formattedFields[ 'email' ] ) ? $formattedFields[ 'email' ] : ( isset( $formattedFields[ 'Email' ] ) ? $formattedFields[ 'Email' ] : null );
        $data = [
            'name' => $queue['name'],
            'phone' => $queue['phone'],
            'phone_code' => $queue['phone'],
            'token' => $queue['token'],
            'token_with_acronym' => $queue['token_with_acronym'],
            'queue_no' => $queue['queue_id'],
            'arrives_time' => Carbon::parse( $queue['created_at'] )->format($dateformat),
            'category_name' => $categoryName,
            'thirdC_name' => $thirdCategoryName ?? '',
            'secondC_name' => $secondCategoryName ?? '',
            'to_mail'=>$email ?? '',

        ];
        Log::info($data);
        if ( isset( $data[ 'to_mail' ] ) && $data[ 'to_mail' ] != '' ){
            SmtpDetails::sendMail( $data, 'reminder', 'reminder', $teamId );
        }
        if ( isset( $data[ 'phone' ] )  ){
        SmsAPI::sendSms($teamId, $data, 'reminder','reminder');
        }
    }


    public static function getProgressRecord($conditionTeam, $userAuthId, $locationID)
    {
        // $timezone = Session::get('timezone_set') ?? 'UTC';
         $timezone = Session::has('timezone_set') ? Session::get('timezone_set') : self::timezoneSet($locationID);
        // return QueueStorage::where(array_merge($conditionTeam, ['status' => Queue::STATUS_PROGRESS, 'served_by' => $userAuthId, 'locations_id' => $locationID]))->whereDate('arrives_time', Carbon::today())
        //     ->whereNotNull('called_datetime')->whereNull('closed_datetime')->whereNull('cancelled_datetime')->first();
        return QueueStorage::where(array_merge($conditionTeam, ['status' => Queue::STATUS_PROGRESS, 'served_by' => $userAuthId, 'locations_id' => $locationID]))
              ->whereDate('arrives_time', Carbon::today($timezone))
              ->whereNotNull('called_datetime')
              ->first();
    }
    public static function getProgressRecordExist($conditionTeam, $userAuthId, $locationID, $counter = null)
{
    $timezone = Session::get('timezone_set') ?? 'UTC';

    return QueueStorage::where($conditionTeam)
        ->where('status', self::STATUS_PROGRESS)
        ->where('locations_id', $locationID)
        ->whereDate('arrives_time', Carbon::today($timezone))
        ->when($counter, fn($q) => $q->where('counter_id', $counter))
        ->whereNotNull('called_datetime')
        ->exists();
}
    public static function getHoldRecord($conditionTeam,$queueId, $storageId =null , $locationID)
    {
        return QueueStorage::where('id',$storageId )
        ->where('temp_hold', self::STATUS_YES)
        ->first();
    }

    public static function getHoldRecordExist($conditionTeam, $queueId, $storageId = null, $locationID)
    {

        return QueueStorage::where('id', $storageId)
            ->where('temp_hold', self::STATUS_YES)
            ->exists(); // Correct method
    }



    // public static function nextCalledField($conditionTeam, $currentVisitorId, $selectedCounter, $userAuthId, $showStartBtn, $locationID,$currentStorageID = null)
    // {
    //   $todayDate =  Carbon::now();
    //     self::updateQueueDateTime($currentVisitorId,$conditionTeam['team_id']);

    //     QueueStorage::where(['id'=>$currentStorageID, 'queue_id'=>$currentVisitorId])?->update([
    //         'status' => self::STATUS_PROGRESS, 'counter_id' => $selectedCounter, 'called_datetime' => $todayDate, 'is_missed' => self::STATUS_NO, 'served_by' => $userAuthId,'datetime' =>$todayDate
    //     ]);

    //     ActivityLog::storeLog($conditionTeam['team_id'], $userAuthId, $currentVisitorId,$currentStorageID, ActivityLog::QUEUE_CALLED, $locationID);

    // }

    public static function nextCalledField($conditionTeam, $currentVisitorId, $selectedCounter, $userAuthId, $showStartBtn, $locationID, $currentStorageID = null,$checkCounter = null)
{
    // $timezone = self::timezoneSet() ?? 'UTC';
     $timezone = Session::has('timezone_set') ? Session::get('timezone_set') : self::timezoneSet($locationID);
     $todayDate = Carbon::now($timezone);

    // Perform both updates in a single query if possible
    $updated = QueueStorage::where(['id' => $currentStorageID, 'queue_id' => $currentVisitorId])
        ->update([
            'status' => self::STATUS_PROGRESS,
            'counter_id' => $selectedCounter,
            'called_datetime' => $todayDate,
            'is_missed' => self::STATUS_NO,
            'served_by' => $userAuthId,
            'datetime' => $todayDate
        ]);

    if ($updated) {

        // Log activity only if update was successful
        ActivityLog::storeLog($conditionTeam['team_id'], $userAuthId, $currentVisitorId, $currentStorageID, ActivityLog::QUEUE_CALLED, $locationID);
    }
}


 public static function nextCalled($nextcalldata)
{
    // $timezone = Session::get('timezone_set') ?? 'UTC';
    $queueStorage = $nextcalldata['queueStorage'];
    $selectedCounter = $nextcalldata['selectedCounter'];
    $userAuth = $nextcalldata['userAuth'];

    // Set timezone
    $locationID = $queueStorage->locations_id;
    $timezone = Session::has('timezone_set') ? Session::get('timezone_set') : self::timezoneSet($locationID);
    $todayDate = Carbon::now($timezone);

    // Perform both updates in a single query if possible


    $queueStorage->updateQuietly([
        'status' => self::STATUS_PROGRESS,
        'counter_id' => $selectedCounter,
        'called_datetime' => $todayDate,
        'is_missed' => self::STATUS_NO,
        'served_by' => $userAuth->id,
        'datetime' => $todayDate,
    ]);

    // Log activity
    ActivityLog::storeLog(
        $queueStorage->team_id,
        $userAuth->id,
        $queueStorage->queue_id,
        $queueStorage->id,
        ActivityLog::QUEUE_CALLED,
        $locationID
    );

    return $queueStorage;
}


public static function startCalled(array $nextcalldata = [])
{
    //  $timezone = Session::get('timezone_set') ?? 'UTC';

    $queueStorage = $nextcalldata['queueStorage'];
    $selectedCounter = $nextcalldata['selectedCounter'];
    $userAuth = $nextcalldata['userAuth'];
    $isStartBtn = $nextcalldata['isStartBtn'] ?? false;

    $locationID = $queueStorage->locations_id;
    $timezone = Session::has('timezone_set') ? Session::get('timezone_set') : self::timezoneSet($locationID);
    $todayDate = Carbon::now($timezone);

     $userAuthId = $userAuth->id; // Optimized user authentication call

        if (!$queueStorage || $queueStorage->temp_hold == self::STATUS_YES) {
        Log::warning("queueStorage not found or on hold: ID {$queueStorage->id}");
        return "hold on";
    }

$queueStorage->updateQuietly([
        'status' => self::STATUS_PROGRESS,
        'is_missed' => self::STATUS_NO,
        'served_by' => $userAuth->id,
        'counter_id' => $selectedCounter,
        'called_datetime' => $isStartBtn ? $queueStorage->called_datetime : $todayDate,
        'start_datetime' => $todayDate,
    ]);

    // Hold remaining calls for the same queue
    QueueStorage::where('queue_id', $queueStorage->queue_id)
        ->where('id', '!=', $queueStorage->id)
        ->update(['temp_hold' => self::STATUS_YES]);

    // Log activity
    ActivityLog::storeLog(
        $queueStorage->team_id,
        $userAuth->id,
        $queueStorage->queue_id,
        $queueStorage->id,
        $isStartBtn ? ActivityLog::QUEUE_STARTED : ActivityLog::QUEUE_CALLED,
        $locationID
    );

      QueueStorage::where('queue_id', $queueStorage->queue_id)
        ->where('id', '!=', $queueStorage->id)
        ->update(['temp_hold' => self::STATUS_YES]);

    return $queueStorage;

}


public static function startCalledField($conditionTeam, $currentVisitorId, $selectedCounter, $isStartBtn, $locationID, $currentStorageID = null,$checkCounter=null)
{
    //  $timezone = Session::get('timezone_set') ?? 'UTC';
      $timezone = Session::has('timezone_set') ? Session::get('timezone_set') : self::timezoneSet($locationID);
     $todayDate = Carbon::now($timezone);
     $userAuthId = Auth::id(); // Optimized user authentication call

    // Fetch queue storage directly without an extra check
    $queueStorage = QueueStorage::where([
        'id' => $currentStorageID,
        'queue_id' => $currentVisitorId,
        'temp_hold' => self::STATUS_NO
    ])->first();

    if (!$queueStorage) {
        Log::warning("queueStorage not found");
        return "hold on";
    }

     // ✅ Counter check logic
     if ($checkCounter) {
        // if (is_null($queueStorage->counter_id)) {
        //     // Assign selectedCounter if not already set
        //     $queueStorage->counter_id = $selectedCounter;
        //     $queueStorage->save();
        // }
        // elseif ($queueStorage->counter_id != $selectedCounter) {
        //     // ❌ Different counter - don't start the call
        //     Log::warning("Counter mismatch: Visitor ID {$currentVisitorId} is assigned to counter {$queueStorage->counter_id}, but tried to call from counter {$selectedCounter} by user ID {$userAuthId}");
        //     return "hold on";
        // }
    }

     $queueStorage->counter_id = $selectedCounter;
    $queueStorage->save();
    // Determine activity type
    $activityType = $isStartBtn ? ActivityLog::QUEUE_STARTED : ActivityLog::QUEUE_CALLED;
    // ActivityLog::storeLog($conditionTeam['team_id'], $userAuthId, $currentVisitorId, $currentStorageID, $activityType, $locationID);

    // Optimize status update (remove unnecessary ternary operator)
    $updateData = [
        'status' => self::STATUS_PROGRESS,
        'is_missed' => self::STATUS_NO,
        'served_by' => $userAuthId
    ];

    // Add additional fields only when required
    if (!$isStartBtn) {
        $updateData['counter_id'] = $selectedCounter;
        $updateData['called_datetime'] = $todayDate;
    }
    $updateData['start_datetime'] = $todayDate;

    // Update the queue storage in one go
    $queueStorage->update($updateData);

    // Hold remaining calls of the same queue ID until the current call is closed
    QueueStorage::where('queue_id', $currentVisitorId)
        ->where('id', '!=', $currentStorageID)
        ->update(['temp_hold' => self::STATUS_YES]);
}





public static function totalTokenServed($conditionTeam, $userAuthID, $location=null)
{
    // $timezone  = Session::get('timezone_set') ?? 'UTC';
  if(!empty($location)){

        $timezone = Session::has('timezone_set') ? Session::get('timezone_set') : self::timezoneSet($location);
    }else{
         $timezone  = Session::get('timezone_set') ?? 'UTC';

    }
    $todayDate = Carbon::today($timezone);

    $tokensWithQueueStorages = Queue::select('queues.id', 'queues.locations_id', 'queues.start_acronym', 'queues.token')
        ->where($conditionTeam)
        ->whereDate('arrives_time', $todayDate)
        ->whereHas('queueStorages', function ($query) use ($todayDate, $userAuthID) {
            $query->where('is_missed', self::STATUS_NO)
                ->whereIn('status', [self::STATUS_CLOSE, self::STATUS_RESET])
                ->where(function ($q) use ($todayDate) {
                    $q->whereDate('arrives_time', $todayDate)
                      ->orWhereDate('reset_call', $todayDate);
                })
                ->where(function ($q) use ($userAuthID) {
                    $q->where('closed_by', $userAuthID)
                      ->orWhere('reset_call_by', $userAuthID);
                });
        })
        ->when($location, fn ($q) => $q->where('locations_id', $location))
        ->with(['queueStorages' => function ($q) {
            $q->select('id', 'queue_id', 'name', 'is_missed', 'status', 'arrives_time', 'reset_call', 'closed_by', 'reset_call_by', 'updated_at');
        }])
        ->get();

    $totalServed = $tokensWithQueueStorages->mapWithKeys(function ($queue) use ($userAuthID, $todayDate) {
        $filtered = $queue->queueStorages->filter(function ($qs) use ($userAuthID) {
            return $qs->is_missed == self::STATUS_NO
                && ($qs->closed_by == $userAuthID || $qs->reset_call_by == $userAuthID)
                && in_array($qs->status, [self::STATUS_CLOSE, self::STATUS_RESET], true);
        });

        if ($filtered->isEmpty()) {
            return [];
        }

        $relevantStorage = $filtered->sortByDesc('updated_at')->first();

        return [
            $queue->id => [
                'id'            =>   $relevantStorage->queue_id ?? '',
                'name'            => $relevantStorage->name ?? '',
                'token'           => ($queue->start_acronym ?? '') . $queue->token,
                'updated_at'      => $relevantStorage->updated_at,
                'queue_storages'  => $filtered->map(fn ($qs) => ['queue_id' => $qs->id])->values()->toArray(),
            ],
        ];
    });

    // Now sort by updated_at and take top 10
    return $totalServed->sortByDesc('updated_at')->map(function ($item) {
        unset($item['updated_at']); // remove this if not needed in final output
        return $item;
    })->toArray();
}

    // public static function agoTimeFormat($datetime)
    // {
    //     $arrivesTime = Carbon::parse($datetime);

    //     $diffInHours = $arrivesTime->diffInHours();
    //     $diffInDays = $arrivesTime->diffInDays();
    //     $diffInMinutes = $arrivesTime->diffInMinutes();

    //     if ($diffInHours > 0) {
    //         $arrivesTime = $diffInHours . ' hrs';
    //     } elseif ($diffInDays > 0) {
    //         $arrivesTime = $diffInDays . ' days';
    //     } else {
    //         $arrivesTime = $diffInMinutes . ' mins';
    //     }

    //     return $arrivesTime;
    // }

    public static function agoTimeFormat($datetime)
{
    $arrivesTime = Carbon::parse($datetime);
    $diffInMinutes = $arrivesTime->diffInMinutes();

    return number_format($diffInMinutes, 0) . ' mins';
}
    public static function storeQueue($data)
    {
        return self::create($data);
    }




    public static function viewQueue($queueID)
    {
        return self::find($queueID);
    }
    // public static function isBookExist($bookedID)
    // {
    //     return self::where(['booking_id' => $bookedID])->exists();
    // }

    public static function filterSettingExcel($selectedLocation, $filters)
    {
        $data = [];
        $data['Branch Name'] = Location::locationName($selectedLocation);
        $data['Created From'] = (!empty($filters['created_at']['created_from'])) ? Carbon::parse($filters['created_at']['created_from'])->format('d-m-Y') : '';
        $data['Created Until'] = (!empty($filters['created_at']['created_until'])) ? Carbon::parse($filters['created_at']['created_until'])->format('d-m-Y') : '';
        $data['Closed By'] = (!empty($filters['closed_by']) && !empty($filters['closed_by']['values'])) ? $filters['closed_by']['values'] : [];
        $data['Counter'] = (!empty($filters['counter_id']) && !empty($filters['counter_id']['values'])) ? $filters['counter_id']['values'] : [];
        $data['Status'] = (!empty($filters['status']) && !empty($filters['status']['values'])) ? $filters['status']['values'] : [];
        $data['Ticket Mode'] = (!empty($filters['ticket_mode']['values']['0'])) ? $filters['ticket_mode']['values']['0'] : '';

        $counterNames = '';
        if (!empty($data['Counter'])) {
            $counters = Counter::whereIn('id', $data['Counter'])->pluck('name', 'id')->toArray();
            $counterNames = array_map(fn($id) => $counters[$id] ?? 'Unknown', $data['Counter']);
            $counterNames = implode(', ', $counterNames);
        }

        $data['Counter'] = $counterNames;

        $closedNamed = '';
        if (!empty($data['Closed By'])) {
            $users = User::whereIn('id', $data['Closed By'])->pluck('name', 'id')->toArray();
            $closedNamed = array_map(fn($id) => $users[$id] ?? 'Unknown', $data['Closed By']);
            $closedNamed = implode(', ', $closedNamed);

        }
        $data['Closed By'] = $closedNamed;

        if (!empty($data['Status'])) {
            $data['Status'] = implode(', ', $data['Status']);

        } else {
            $data['Status'] = '';

        }

        return $data;
    }


    public static function nextStorage($queue,$userAuth,$userCategories){

        return $queue;
        //     if (!empty($userAuth) && !$userAuth->hasRole(User::ROLE_ADMIN)) {
        //         $innerQueue = $queue?->queueStorages?->where('status', self::STATUS_PENDING)->whereIn('sub_category_id',$userCategories)->first() ?? null;
        //        }
        //       else {
        //          $innerQueue =$queue?->queueStorages?->where('status', self::STATUS_PENDING)->first() ?? null;
        //    }




    //    return $innerQueue;
    }

    public static function nextStorageGet($id){
        $innerQueue = QueueStorage::where('queue_id', $id)->get() ?? null;
    return $innerQueue;
    }

    public static function timezoneSet($locationID=null){
        $location = Session::get('selectedLocation') ?? $locationID;
        if(!empty($location)){
            $siteDetail = SiteDetail::where('location_id',$location)->first();

          if ($siteDetail && $siteDetail->select_timezone) {
            Config::set('app.timezone', $siteDetail->select_timezone);
            date_default_timezone_set($siteDetail->select_timezone);

            $timezone = $siteDetail->select_timezone ?? 'UTC';
            Session::put('timezone_set', $timezone);
        }else{
            Config::set('app.timezone','UTC');
            // date_default_timezone_set($siteDetail->select_timezone);

            $timezone ='UTC';
            Session::put('timezone_set', $timezone);
        }
        return $timezone;
        }

    }
}
