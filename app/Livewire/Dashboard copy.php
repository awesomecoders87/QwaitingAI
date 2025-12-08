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

class Dashboard extends Component
{
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
    public $bookingsystem = false;
    public array $dataPoints = [];

    /**Summary */
    public ?string $summaryfromSelectedDate = null;
    public ?string $summarytoSelectedDate = null;
     public $totalVisits, $servedVisits, $noShow, $cancelled, $waiting;
    public $hourlyChartData = [];
    public $monthlyChartData = [];
    public $avgServedTime;   // in seconds
    public $maxServedTime;   // in seconds
    public $avgWaitingTime;  // in seconds
    public $maxWaitingTime; 

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
        $this->toSelectedDate = $this->toSelectedDate ?? date('Y-m-d');

        $this->summaryfromSelectedDate = $this->summaryfromSelectedDate ?? date('Y-m-d');
        $this->summarytoSelectedDate = $this->summarytoSelectedDate ?? date('Y-m-d');

        $this->filter = 'today';
        $this->appointmentFilter = 'today';

        $this->rescheduledAppointments = Booking::where('is_rescheduled', 1)->where('team_id',$this->teamId)->where('location_id',$this->location)->count() ?? 0;
        $this->completedAppointments = Booking::where('status', 'Completed')->where('team_id',$this->teamId)->where('location_id',$this->location)->count() ?? 0;
        $this->cancelledAppointments = Booking::where('status', 'Cancelled')->where('team_id',$this->teamId)->where('location_id',$this->location)->count() ?? 0;
        $this->pendingAppointments = Booking::where('status', 'Pending')->where('team_id',$this->teamId)->where('location_id',$this->location)->count() ?? 0;

        $bookingsystemAccount = AccountSetting::where('team_id',$this->teamId)
        ->where('location_id',$this->location)
        ->where('slot_type',AccountSetting::BOOKING_SLOT)->value('booking_system');

        if($bookingsystemAccount == 1){
            $this->bookingsystem = true;
        }
        $siteDatail = SiteDetail::where('team_id', $this->teamId)->where('location_id',$this->location)->select('select_timezone')->first();
        if ($siteDatail && $siteDatail->select_timezone) {
            Config::set('app.timezone', $siteDatail->select_timezone);
            date_default_timezone_set($siteDatail->select_timezone);
        }
   $this->loadSummary();

    }

    public function updatedFromSelectedDate()
    {
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsSummaryChart::class);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsCallHistoryChart::class);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\OverviewChart::class);
     
    }

    public function updatedToSelectedDate()
    {
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsSummaryChart::class);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsCallHistoryChart::class);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\OverviewChart::class);
       
    }

    public function updatedFilter()
    {
        $this->dispatch('walkInFilter', filter: $this->filter)->to(\App\Livewire\Widgets\CallHandlingOverviewChart::class);
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

        // Total Visits = queue_storage + booking
        $this->totalVisits = DB::table('queues_storage')->where('team_id',$this->teamId)->where('locations_id',$this->location)->whereBetween('created_at', [$from, $to])->count()
                            + DB::table('bookings')->where('team_id',$this->teamId)->where('location_id',$this->location)->whereBetween('created_at', [$from, $to])->count();

        // Served Visits = close call in queue_storage
        $this->servedVisits = DB::table('queues_storage')->where('team_id',$this->teamId)->where('locations_id',$this->location)
            ->where('status', 'Close')
            ->whereBetween('arrives_time', [$from, $to])
            ->count();

        // No Show = missing status in queue_storage
        $this->noShow = DB::table('queues_storage')->where('team_id',$this->teamId)->where('locations_id',$this->location)
            ->where('is_missed', 1)
            ->whereBetween('arrives_time', [$from, $to])
            ->count();

        // Cancelled = queue_storage + booking cancel status
        $this->cancelled = DB::table('queues_storage')->where('team_id',$this->teamId)->where('locations_id',$this->location)
                                ->where('status', 'Cancelled')
                                ->whereNotNull('cancelled_datetime')
                                ->whereBetween('arrives_time', [$from, $to])
                                ->count()
                            + DB::table('bookings')->where('team_id',$this->teamId)->where('location_id',$this->location)
                                ->where('status', 'Cancelled')
                                ->whereBetween('created_at', [$from, $to])
                                ->count();

        // Waiting = pending in queue_storage + confirm in booking with is_convert = 1
        $this->waiting = DB::table('queues_storage')->where('team_id',$this->teamId)->where('locations_id',$this->location)
                            ->where('status', 'Pending')
                            ->whereBetween('arrives_time', [$from, $to])
                            ->count()
                        + DB::table('bookings')->where('team_id',$this->teamId)->where('location_id',$this->location)
                            ->whereIn('status', ['Pending', 'Confirmed'])
                            ->where('is_convert', "No")
                            ->whereBetween('created_at', [$from, $to])
                            ->count();

        // Hourly Visits Chart (queue_storage served only)
        $this->hourlyChartData = $this->getHourlyVisits($from, $to);
        $this->monthlyChartData = $this->MonthlyVisits($from, $to);
        $this->calculateServeAndWaitTimes($from, $to);
        $counterdata = $this->calculatebottomGraph($from, $to);
        $categorydata = $this->calculateCategoryGraph($from, $to);
        $agentdata = $this->calculateAgentGraph($from, $to);

        $this->dispatch('hourly-visits-updated', data: $this->hourlyChartData);
        $this->dispatch('monthly-visits-updated', data: $this->monthlyChartData);
        $this->dispatch('counter-updated', data: $counterdata->toArray());
        $this->dispatch('category-updated', data: $categorydata->toArray());
        $this->dispatch('agent-updated', data: $agentdata->toArray());
    }

  /**
 * Returns an array of 24 integers: for each hour 0–23,
 * the sum of queue_storage “served” (closed) and booking entries.
 */
public function getHourlyVisits($from, $to)
{
    // 1) Queue “served” by created_at
    $queueData = DB::table('queues_storage')->where('team_id',$this->teamId)->where('locations_id',$this->location)
         ->where('status', '!=', 'Cancelled')
        ->select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('count(*) as total')
        )
        ->whereBetween('arrives_time', [$from, $to])
        ->groupBy('hour')
        ->pluck('total', 'hour')
        ->toArray();

    // 2) All bookings by created_at
    $bookingData = DB::table('bookings')->where('team_id',$this->teamId)->where('location_id',$this->location)
     ->where('status', '!=', 'Cancelled')
        ->select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('count(*) as total')
        )
        ->whereBetween('created_at', [$from, $to])
        ->groupBy('hour')
        ->pluck('total', 'hour')
        ->toArray();

    // 3) Merge into a 24-element array
    $formatted = [];
    for ($h = 0; $h < 24; $h++) {
        $formatted[] = ($queueData[$h] ?? 0) + ($bookingData[$h] ?? 0);
    }

    return $formatted;
}

/**
 * Returns an array of 12 integers: for each month 1–12,
 * the sum of queues_storage (excluding cancelled) and bookings.
 */
public function MonthlyVisits($from, $to)
{
    // 1) Queue counts by month (excluding 'Cancelled')
    $queueMonthly = DB::table('queues_storage')->where('team_id',$this->teamId)->where('locations_id',$this->location)
        ->where('status', '!=', 'Cancelled')
        ->whereBetween('arrives_time', [$from, $to])
        ->select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('count(*) as total')
        )
        ->groupBy('month')
        ->pluck('total', 'month')
        ->toArray();

    // 2) Booking counts by month
    $bookingMonthly = DB::table('bookings')->where('team_id',$this->teamId)->where('location_id',$this->location)
     ->where('status', '!=', 'Cancelled')
        ->whereBetween('created_at', [$from, $to])
        ->select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('count(*) as total')
        )
        ->groupBy('month')
        ->pluck('total', 'month')
        ->toArray();

    // 3) Merge into a 12-element array
    $formatted = [];
    for ($m = 1; $m <= 12; $m++) {
        $formatted[] = ($queueMonthly[$m] ?? 0) + ($bookingMonthly[$m] ?? 0);
    }

    return $formatted;
}

protected function calculateServeAndWaitTimes($from, $to)
{
    // only closed queues
    $served = DB::table('queues_storage')
    ->where('team_id',$this->teamId)->where('locations_id',$this->location)
        ->where('status', 'Close')
        ->whereNotNull('closed_datetime')
        ->whereBetween('arrives_time', [$from, $to])
        ->selectRaw('TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime) as secs')
        ->pluck('secs')
        ->toArray();

        // dd($served);
    // all queued entries for waiting
    $waiting = DB::table('queues_storage')
    ->where('team_id',$this->teamId)->where('locations_id',$this->location)
        ->whereNotNull('start_datetime')
        ->whereBetween('arrives_time', [$from, $to])
        ->selectRaw('TIMESTAMPDIFF(SECOND, arrives_time, called_datetime) as secs')
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
protected function calculatebottomGraph($from, $to)
{
    $data = Counter::withCount(['queues as total' => function ($query) use ($from, $to) {
        $query->where('status', 'Close')
             ->where('locations_id',$this->location)
              ->whereBetween('arrives_time', [$from, $to]);
    }])
  
     ->where('team_id',$this->teamId)
      ->whereJsonContains('counter_locations', "$this->location")
    ->orderBy('name')
    ->get(['id', 'name']); // keep it light by selecting only required columns

    return $data;
}

protected function calculateCategoryGraph($from, $to)
{
    $data = Category::withCount(['queues as total' => function ($query) use ($from, $to) {
        $query->where('status', 'Close')
             ->where('locations_id',$this->location)
              ->whereBetween('arrives_time', [$from, $to]);
    }])
      ->where('level_id',1)
     ->where('team_id',$this->teamId)
     ->whereJsonContains('category_locations', "$this->location")
    ->orderBy('name')
    ->get(['id', 'name']); // Select only needed fields

    return $data;
}

protected function calculateAgentGraph($from, $to)
{
    $data =User::where('team_id', $this->teamId)
        ->whereJsonContains('locations', "$this->location")
        // ->whereHas('queues', function ($query) use ($from, $to) {
        //     $query->where('status', 'Close')
        //           ->whereNotNull('closed_by')
        //           ->whereBetween('created_at', [$from, $to]);
        // })
        ->withCount(['queues as total_served' => function ($query) use ($from, $to) {
            $query->where('status', 'Close')
                  ->whereNotNull('closed_by')
                  ->where('locations_id',$this->location)
                  ->whereBetween('arrives_time', [$from, $to]);
        }])
        ->orderByDesc('total_served')
        ->get(['id', 'name']);

    return $data;
}



    public function render()
    {
        return view('livewire.dashboard');
    }


}
