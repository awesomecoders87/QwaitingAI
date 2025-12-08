<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\QueueStorage;
use App\Models\Booking;
use App\Models\SiteDetail;
use App\Models\AccountSetting;
use App\Models\Role;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\Counter;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Models\Translation;
use Livewire\WithPagination;

class Dashboard extends Component
{
     use WithPagination;

    #[Title('Dashboard')]


    public $completedAppointments;
    public $pendingAppointments;
    public $rescheduledAppointments;
    public $cancelledAppointments;
    public ?string $fromSelectedDate = null;
    public ?string $toSelectedDate = null;
    public $teamId;
    public $location;
    public $filter;
    public $appointmentFilter;
    public $bookingsystem;
    public array $dataPoints = [];

    /**Summary */
    public ?string $summaryfromSelectedDate = null;
    public ?string $summarytoSelectedDate = null;
    public $totalVisits, $servedVisits, $noShow, $cancelled, $waiting;
    public $hourlyChartData = [];
    public $monthlyChartData = [];
    public $avgServedTime;   // in seconds
    public $averageRating = 0;  // Average rating for the location
    public $maxServedTime;   // in seconds
    public $avgWaitingTime;  // in seconds
    public $maxWaitingTime;
    public $translations;
    public $language;
    public $activeUsersList = true;
    public $enablePriority = false;
    public string $search = '';
    public $childUsers = [];
    public $subChildUsers = [];
    public $user;


    public function mount()
    {

        $this->teamId = tenant('id');

        $this->location = Session::get('selectedLocation');

        $user = Auth::user();

        if (!$user->hasPermissionTo('Dashboard')) {
            if ($user->hasPermissionTo('Call Screen Read')) {
                return redirect()->to('calls');
            }
            abort(403);
        }

        $this->fromSelectedDate = $this->fromSelectedDate ?? date('Y-m-d');
        $this->toSelectedDate = $this->toSelectedDate ?? date('Y-m-d', strtotime('+1 day'));

        $this->summaryfromSelectedDate = $this->summaryfromSelectedDate ?? date('Y-m-d');
        $this->summarytoSelectedDate = $this->summarytoSelectedDate ?? date('Y-m-d', strtotime('+1 day'));

        $this->filter = 'today';
        $this->appointmentFilter = 'today';

        $this->rescheduledAppointments = Booking::where('is_rescheduled', 1)->where('team_id', $this->teamId)->where('location_id', $this->location)->count() ?? 0;
        $this->completedAppointments = Booking::where('status', 'Completed')->where('team_id', $this->teamId)->where('location_id', $this->location)->count() ?? 0;
        $this->cancelledAppointments = Booking::where('status', 'Cancelled')->where('team_id', $this->teamId)->where('location_id', $this->location)->count() ?? 0;
        $this->pendingAppointments = Booking::where('status', 'Pending')->where('team_id', $this->teamId)->where('location_id', $this->location)->count() ?? 0;

        $bookingsystemAccount = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)->value('booking_system');

        if ($bookingsystemAccount == 1) {
            $this->bookingsystem = true;
        }
        $siteDatail = SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->location)->select('select_timezone','enable_active_users_list','use_staff_priority')->first();
      
        $this->enablePriority = $siteDatail->use_staff_priority ?? false;
        $timezone = $siteDatail->select_timezone ?? 'UTC';
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        $this->language = session('app_locale');
        $this->activeUsersList =$siteDatail?->enable_active_users_list == 1 ? true : false;

        $this->translations = Translation::where('team_id', $this->teamId)
            ->get()
            ->groupBy('name') // Group by category name
            ->map(function ($items) {
                return $items->pluck('value', 'language'); // ['es' => 'Categoría 1']
            })
            ->toArray();


              if ($this->enablePriority && auth()->check()) {
            $user = auth()->user();

            $this->childUsers = collect();
            $this->subChildUsers = collect();

            if ($user->level_id == 1) {
                // Level 1: Get Managers
                if($this->user && ! $this->user->hasRole('Admin')){
                $this->childUsers = User::withTrashed()
                ->where('level_id', 2)
                    ->where('parent_id', $user->id)
                    ->whereJsonContains('locations', (string) $this->location)
                    ->get(['id', 'name']);

                // Get Agents under those Managers
                $managerIds = $this->childUsers->pluck('id');

                $this->subChildUsers = User::withTrashed()
                ->where('level_id', 3)
                    ->whereIn('parent_id', $managerIds)
                    ->whereJsonContains('locations', (string) $this->location)
                    ->get(['id', 'name']);
                }else{ // Level 1: Has role Admin show all agents
                    $this->subChildUsers = User::withTrashed()
                    ->where('level_id', 3)
                    ->whereJsonContains('locations', (string) $this->location)
                    ->get(['id', 'name']);
                }


            } elseif ($user->level_id == 2) {
                // Level 2: Get Agents
                $this->subChildUsers = User::withTrashed()
                ->where('level_id', 3)
                    ->where('parent_id', $user->id)
                    ->whereJsonContains('locations', (string) $this->location)
                    ->get(['id', 'name']);

                // No sub-child users at level 3
                // $this->subChildUsers = collect();
            }elseif ($user->level_id == 3) {
                // Agent or lowest level: Only show self
            $this->subChildUsers = User::withTrashed()
            ->where('id', $user->id)
            ->whereJsonContains('locations', (string) $this->location)
            ->get(['id', 'name']);

            }
        }

        $this->loadSummary();


    }

    public function updatedFromSelectedDate()
    {
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsSummaryChart::class);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsCallHistoryChart::class);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate,nablePriority: $this->enablePriority, subChildUsers: $this->subChildUsers)->to(\App\Livewire\Widgets\OverviewChart::class);
    }

    public function updatedToSelectedDate()
    {
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsSummaryChart::class);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsCallHistoryChart::class);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate, enablePriority: $this->enablePriority, subChildUsers: $this->subChildUsers)->to(\App\Livewire\Widgets\OverviewChart::class);
    }

    public function updatedFilter()
    {
        $this->dispatch('walkInFilter', filter: $this->filter, enablePriority: $this->enablePriority, subChildUsers: $this->subChildUsers)->to(\App\Livewire\Widgets\CallHandlingOverviewChart::class);
        $this->dispatch('walkInFilter', filter: $this->filter)->to(\App\Livewire\Widgets\WalkinQueueVisitsChart::class);
        $this->dispatch('walkInFilter', filter: $this->filter)->to(\App\Livewire\Widgets\WalkinByServiceChart::class);
    }

    public function updatedAppointmentFilter()
    {
        $this->dispatch('appointFilter', filter: $this->appointmentFilter)->to(\App\Livewire\Widgets\AppointmentsChart::class);
        $this->dispatch('appointFilter', filter: $this->appointmentFilter)->to(\App\Livewire\Widgets\AppointmentsByServicesChart::class);
        $this->dispatch('appointFilter', filter: $this->appointmentFilter)->to(\App\Livewire\Widgets\AppointmentsByTimeChart::class);
    }


    public function updated($field)
    {
        if (in_array($field, ['summaryfromSelectedDate', 'summarytoSelectedDate'])) {
            $this->loadSummary();
        }
    }

    public function loadSummary()
    {
        $from = Carbon::parse($this->summaryfromSelectedDate)->startOfDay();
        $to = Carbon::parse($this->summarytoSelectedDate)->endOfDay();

        // ✅ Fetch all records in one query
      $queues = QueueStorage::with([
			'counter:id,name',
			'category:id,name',
			'assignedStaff:id,name'
		])
		->where('team_id', $this->teamId)
		->where('locations_id', $this->location)
		->whereBetween('arrives_time', [$from . ' 00:00:00', $to . ' 23:59:59'])
		->get();

        // ✅ Filtered queues (excluding Cancelled)
        // $filtered = $queues->where('status', '!=', 'Cancelled');

        if ($this->enablePriority) {

           $queues = $queues->whereIn('assign_staff_id', $this->subChildUsers->pluck('id')->toArray());
        }
        $filtered = $queues;

        // ✅ Summary stats
        $this->totalVisits    = $filtered->count();
        $this->servedVisits   = $filtered->where('status', 'Close')->count();
        $this->noShow         = $filtered->where('is_missed', 1)->count();
        $this->cancelled      = $queues->where('status', 'Cancelled')->whereNotNull('cancelled_datetime')->count();
        $progressCount   = $filtered->where('status', 'Progress')->count();
        $PendingCount   = $filtered->where('status', 'Pending')->count();
        $this->waiting = $progressCount + $PendingCount;

        // Calculate average rating for the location
        $this->averageRating = \App\Models\Rating::where('location_id', $this->location)
            ->when($this->summaryfromSelectedDate, function($query) use ($from) {
                return $query->where('created_at', '>=', $from);
            })
            ->when($this->summarytoSelectedDate, function($query) use ($to) {
                return $query->where('created_at', '<=', $to);
            })
            ->avg('rating') ?? 0;

        // ✅ Hourly Visits
        $this->hourlyChartData = collect(range(0, 23))->map(function ($hour) use ($filtered) {
            return $filtered->filter(fn($q) => optional($q->datetime)->format('H') == str_pad($hour, 2, '0', STR_PAD_LEFT))->count();
        })->toArray();

        // ✅ Monthly Visits
        $this->monthlyChartData = collect(range(1, 12))->map(function ($month) use ($filtered) {
            return $filtered->filter(fn($q) => optional($q->datetime)->format('n') == $month)->count();
        })->toArray();

        // ✅ Serve & Wait Time
        $servedSecs = $filtered
            ->filter(fn($q) => $q->status === 'Close' && $q->called_datetime && $q->closed_datetime)
            ->map(fn($q) => $q->called_datetime->diffInSeconds($q->closed_datetime));

        $waitingSecs = $filtered
            ->filter(fn($q) => $q->arrives_time && $q->called_datetime)
            ->map(fn($q) => $q->arrives_time->diffInSeconds($q->called_datetime));

        $this->avgServedTime  = $servedSecs->count() ? max(0, round($servedSecs->avg())) : 0;
        $this->maxServedTime  = $servedSecs->count() ? max(0, $servedSecs->max()) : 0;
        $this->avgWaitingTime = $waitingSecs->count() ? max(0, round($waitingSecs->avg())) : 0;
        $this->maxWaitingTime = $waitingSecs->count() ? max(0, $waitingSecs->max()) : 0;

        // ✅ Bottom Graphs (can reuse full $queues or $filtered as needed)
        $this->dispatch('counter-updated', data: $this->calculatebottomGraph($queues)->toArray());
        $this->dispatch('category-updated', data: $this->calculateCategoryGraph($queues)->toArray());
        $this->dispatch('agent-updated', data: $this->calculateAgentGraph($queues)->toArray());

        // ✅ Dispatch Chart Data
        $this->dispatch('hourly-visits-updated', data: $this->hourlyChartData);
        $this->dispatch('monthly-visits-updated', data: $this->monthlyChartData);
    }

    protected function calculateServeAndWaitTimes($from, $to)
    {
        // only closed queues
        $served = DB::table('queues_storage')
            ->where('team_id', $this->teamId)->where('locations_id', $this->location)
            ->where('status', 'Close')
            ->whereNotNull('closed_datetime')
            ->whereBetween('datetime', [$from, $to])
            ->selectRaw('TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime) as secs')
            ->pluck('secs')
            ->toArray();

        // dd($served);
        // all queued entries for waiting
        $waiting = DB::table('queues_storage')
            ->where('team_id', $this->teamId)->where('locations_id', $this->location)
            ->whereNotNull('start_datetime')
            ->whereBetween('datetime', [$from, $to])
            ->selectRaw('TIMESTAMPDIFF(SECOND, datetime, called_datetime) as secs')
            ->pluck('secs')
            ->toArray();
        if (count($served)) {
            $this->avgServedTime = round(array_sum($served) / count($served));
            $this->maxServedTime = max($served);
        } else {
            $this->avgServedTime = $this->maxServedTime = 0;
        }

        if (count($waiting)) {
            $this->avgWaitingTime = round(array_sum($waiting) / count($waiting));
            $this->maxWaitingTime = max($waiting);
        } else {
            $this->avgWaitingTime = $this->maxWaitingTime = 0;
        }
    }

    public function calculatebottomGraph(Collection $queues)
    {
        return $queues
            ->filter(fn($q) => $q->counter_id !== null && optional($q->counter)->name !== null)
            ->groupBy('counter_id')
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'counter_id' => $first->counter_id,
                    'counter_name' => optional($first->counter)->name ?? 'Unknown',
                    'count' => $group->count(),
                ];
            })
            ->values();
    }


    protected function calculateCategoryGraph(Collection $queues)
    {
        $categoryCounts = $queues
            ->where('status', 'Close')
            ->groupBy('category_id')
            ->map(fn($items) => $items->count());

        $categories = Category::where('level_id', 1)
            ->where('team_id', $this->teamId)
            ->whereJsonContains('category_locations', "$this->location")
            ->orderBy('name')
            ->get(['id', 'name']);

        return $categories->map(function ($category) use ($categoryCounts) {
            if (isset($this->translations[$category->name][$this->language])) {
                if (!empty($this->translations[$category->name][$this->language])) {
                    $categoryName = $this->translations[$category->name][$this->language];
                } else {
                    $categoryName = $category->name;
                }
            } else {
                $categoryName = $category->name;
            }

            return [
                'id' => $category->id,
                'name' => $categoryName,
                'total' => $categoryCounts[$category->id] ?? 0,
            ];
        });
    }

    protected function calculateAgentGraph(Collection $queues)
    {
        $agentCounts = $queues
            ->where('status', 'Close')
            ->whereNotNull('closed_by')
            ->groupBy('closed_by')
            ->map(fn($items) => $items->count());

        $users = User::where('team_id', $this->teamId)
            ->whereNotNull('locations') // Ensure it's not null
            ->whereRaw('JSON_VALID(locations)') // Ensure it's valid JSON
            ->whereJsonContains('locations', (string) $this->location) // Always cast to string
            ->get(['id', 'name']);

        return $users->map(function ($user) use ($agentCounts) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'total_served' => $agentCounts[$user->id] ?? 0,
            ];
        })->sortByDesc('total_served')->values();
    }


    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getActiveUsersProperty()
    {
        return User::query()
            ->where('team_id', $this->teamId)
            ->where('is_login', 1)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->select('id', 'name', 'username', 'email')
            ->orderBy('name')
            ->paginate(10);
    }

    public function render()
    {
          return view('livewire.dashboard', [
            'activeUsers' => $this->activeUsers,
        ]);
    }
}
