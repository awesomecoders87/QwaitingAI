<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\SmsReport;
use App\Models\MessageDetail;
use App\Models\Customer;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Location;


class Analytics extends Component
{
    public $teamId;
    public $today;
    public $yesterday;

    public string $activeTab = 'Overview';
    public $cards = [];
    public $overview_traffic_timePeriod = 'today';
    public $trafficChartData = [];
    public $customerPathData = [];
    public $topVisitedLocations = [];

    //Visit
    public string $visitTrafficRange = 'date';
    public bool $showVisitFilter = false;
    public string $visitTypeFilter = 'all-visits';
    public array $visitDistributionMatrix = [];
    public array $serviceLegend = [];
    public string $serviceVisitTypeFilter = 'all-visits';
    public bool $showServiceFilter = false;

    //operation
    public $locationVisitFilter = 'all';
    public $userVisitFilter = 'all';

    //Messages
    public array $messageMetrics = [];
    public array $messageChannelChartData  = [];

    //Guest experience
   public $guestExperienceChartData = [];
   public $lineGraphFilter = "hour_of_day";
   public $registrationCreatorFilter = 'creator';
    public $registrationVisitFilter = 'all';
    public $modeFilter = 'hour_of_day';

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->today = Carbon::today();
        $this->yesterday = Carbon::yesterday();

        $this->prepareDashboardCards();
       $this->loadTopVisitedLocations();



    }


    public function setVisitTypeFilter($filter)
   {
    $this->visitTypeFilter = $filter;
    $this->showVisitFilter = false;
      $this->getVisitDistributionMatrix();
    }
    public function render()
    {

         if($this->activeTab == 'Overview'){
             //overview
       $this->trafficChartData = $this->getTrafficChartData();
       $this->customerPathData = $this->getCustomerPathData();

       $this->dispatch('trafficChartUpdated', $this->trafficChartData);
       $this->dispatch('customerPathChartUpdated', $this->customerPathData);
       }

       if($this->activeTab == 'Visit'){
        $this->loadVisitTabData();
        $this->getVisitDistributionMatrix();
       }

       if ($this->activeTab === 'Services') {
            $this->getServiceCombinationData();
        }

        if ($this->activeTab === 'Operations') {
            $this->dispatch('opertaionVisitsByLocationChartUpdated', $this->getVisitsByLocationData());
            $this->dispatch('userVisitsChartUpdated', $this->getUserVisitsData());
        }

        if ($this->activeTab === 'Messages') {
            $this->loadMessageMetrics();
            $this->getMessageChartData();
        }

        if ($this->activeTab === 'Guest Experience') {
            $this->getdropoffWaitlistData();
            $this->getDropoffDonutData();
            $this->getLineLengthChartData();
            $this->dispatchRegistrationChartData();
            $this->getWaitServeDurationChartData();
             $this->getAverageDurationsChartData();
        }


        return view('livewire.analytics');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;


    }

  public function updatedOverviewTrafficTimePeriod()
{
    $this->trafficChartData = $this->getTrafficChartData();
    $this->dispatch('trafficChartUpdated', $this->trafficChartData);
}

   private function prepareDashboardCards()
{
    $today = Carbon::today();
    $yesterday = Carbon::yesterday();

    // === Bookings ===
    $todayBookings = Booking::select('status')
        ->where('team_id', $this->teamId)
        ->whereDate('created_at', $today)
        ->get();

    $totalBooking = $todayBookings->count();
    $cancelledToday = $todayBookings->where('status', 'cancelled')->count();

    $yesterdayBookings = Booking::select('status')
        ->where('team_id', $this->teamId)
        ->whereDate('created_at', $yesterday)
        ->get();

    $yesterdayBookingCount = $yesterdayBookings->count();
    $bookingChange = $this->calculateChange($totalBooking, $yesterdayBookingCount);

    // === QueueStorage ===
    $todayQueueStorage = QueueStorage::where('team_id', $this->teamId)
        ->whereDate('arrives_time', $today)
        ->get(['status', 'is_missed', 'called_datetime', 'closed_datetime', 'arrives_time']);

    $yesterdayQueueStorage = QueueStorage::where('team_id', $this->teamId)
        ->whereDate('arrives_time', $yesterday)
        ->get(['status', 'is_missed', 'called_datetime', 'closed_datetime', 'arrives_time']);

    $totalQueueStorage = $todayQueueStorage->count();
    $yesterdayQueueCount = $yesterdayQueueStorage->count();
    $waitlistChange = $this->calculateChange($totalQueueStorage, $yesterdayQueueCount);

    // === Served ===
    $servedToday = $todayQueueStorage->where('status', 'Close');
    $servedCount = $servedToday->count();
    $servedPercent = $totalQueueStorage > 0 ? round(($servedCount / $totalQueueStorage) * 100, 2) : 0;

    $yesterdayServedCount = $yesterdayQueueStorage->where('status', 'Close')->count();
    $servedChange = $this->calculateChange($servedCount, $yesterdayServedCount);

    // === Avg Wait Time & Longest Wait ===
    $waitTimes = $servedToday->map(function ($item) {
        if ($item->arrives_time && $item->called_datetime) {
            // return Carbon::parse($item->arrives_time)->diffInMinutes($item->called_datetime);
              return Carbon::parse($item->arrives_time)->diffInSeconds(Carbon::parse($item->called_datetime));
        }
        return null;
    })->filter();

    // $avgWaitMinutes = $waitTimes->count() > 0 ? round($waitTimes->avg()) : 0;
    // $longestWait = $waitTimes->count() > 0 ? round($waitTimes->max(), 3) : 0;

    $avgWaitSeconds = $waitTimes->count() > 0 ? round($waitTimes->avg()) : 0;
$longestWaitSeconds = $waitTimes->count() > 0 ? $waitTimes->max() : 0;

// Convert to HH:MM:SS
 $avgWaitgetMinutes = gmdate('H:i:s', $avgWaitSeconds);
$avgWaitMinutes = $waitTimes->count() > 0 ? round($waitTimes->avg()) : 0;
$longestWait = gmdate('H:i:s', $longestWaitSeconds);



    $yesterdayWaits = $yesterdayQueueStorage->where('status', 'Close')->map(function ($item) {
        if ($item->called_datetime && $item->closed_datetime) {
            // return Carbon::parse($item->called_datetime)->diffInMinutes($item->closed_datetime);
              return Carbon::parse($item->arrives_time)->diffInSeconds(Carbon::parse($item->called_datetime));
        }
        return null;
    })->filter();

    // $yesterdayAvgWait = $yesterdayWaits->count() > 0 ? round($yesterdayWaits->avg()) : 0;


    $yesterdayAvgWait = $yesterdayWaits->count() > 0 ? round($yesterdayWaits->avg()) : 0;
    $waitChange = $this->calculateChange($avgWaitMinutes, $yesterdayAvgWait);

    // === Dropoffs ===
    $dropoffs = $todayQueueStorage->filter(function ($item) {
        return $item->is_missed == 1 || $item->status === Queue::STATUS_CANCELLED;
    })->count();

    $dropoffRate = $totalQueueStorage > 0 ? round(($dropoffs / $totalQueueStorage) * 100, 2) : 0;

    $yesterdayDropoffs = $yesterdayQueueStorage->filter(function ($item) {
        return $item->is_missed == 1 || $item->status === Queue::STATUS_CANCELLED;
    })->count();

    $dropoffChange = $this->calculateChange($dropoffRate, $yesterdayDropoffs);

    // === Messages ===
    $messagesToday = MessageDetail::whereDate('created_at', $today)
        ->where('team_id', $this->teamId)
        ->count();

    $messagesYesterday = MessageDetail::whereDate('created_at', $yesterday)
        ->where('team_id', $this->teamId)
        ->count();

    $messageChange = $this->calculateChange($messagesToday, $messagesYesterday);

    // === Final Dashboard Cards ===
    $this->cards = [
        ['title' => 'Bookings', 'value' => $totalBooking, 'change' => $bookingChange, 'sub' => "$cancelledToday cancelled"],
        ['title' => 'Waitlist', 'value' => $totalQueueStorage, 'change' => $waitlistChange, 'sub' => ''],
        ['title' => 'Served', 'value' => $servedCount, 'change' => $servedChange, 'sub' => "$servedPercent% served"],
        ['title' => 'Avg Wait Times', 'value' => "$avgWaitgetMinutes", 'change' => $waitChange, 'sub' => "$longestWait longest wait"],
        ['title' => 'Dropoff Rate', 'value' => "$dropoffRate%", 'change' => $dropoffChange, 'sub' => "$dropoffs dropped off"],
        ['title' => 'Messages', 'value' => $messagesToday, 'change' => $messageChange, 'sub' => ''],
    ];
}

private function getTrafficChartData()
{
    $labels = [];
    $waitlist = [];
    $booking = [];
    $serving = [];
    $dropoff = [];

    $teamId = $this->teamId;

    if ($this->overview_traffic_timePeriod === 'today') {
        $today = Carbon::today();
        $labels[] = $today->format('d M');

        // Load all required data once
        $queueToday = QueueStorage::whereDate('arrives_time', $today)
            ->where('team_id', $teamId)
            ->get();

        $bookingToday = Booking::whereDate('created_at', $today)
            ->where('team_id', $teamId)
            ->count();

        $booking[] = $bookingToday;
        $waitlist[] = $queueToday->count();
        $serving[] = $queueToday->where('status', 'Close')->count();
        $dropoff[] = $queueToday->filter(fn($q) => $q->is_missed == 1 || $q->status == Queue::STATUS_CANCELLED)->count();
    }

    elseif ($this->overview_traffic_timePeriod === 'hour') {
        $today = Carbon::today();
        $queueToday = QueueStorage::whereDate('arrives_time', $today)
            ->where('team_id', $teamId)
            ->get();

        $bookingToday = Booking::whereDate('created_at', $today)
            ->where('team_id', $teamId)
            ->get();

        for ($i = 0; $i < 24; $i++) {
            $start = $today->copy()->addHours($i);
            $end = $start->copy()->addHour();
            $labels[] = $start->format('H:00');

            $booking[] = $bookingToday->filter(fn($b) => $b->created_at >= $start && $b->created_at < $end)->count();
            $waitlist[] = $queueToday->filter(fn($q) => $q->arrives_time >= $start && $q->arrives_time < $end)->count();
            $serving[] = $queueToday->filter(fn($q) => $q->arrives_time >= $start && $q->arrives_time < $end && $q->status === 'Close')->count();
            $dropoff[] = $queueToday->filter(fn($q) => $q->arrives_time >= $start && $q->arrives_time < $end && ($q->is_missed == 1 || $q->status == Queue::STATUS_CANCELLED))->count();
        }
    }

    elseif ($this->overview_traffic_timePeriod === 'weekly') {
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today();

        $allBookings = Booking::whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->where('team_id', $teamId)
            ->get();

        $allQueues = QueueStorage::whereBetween('arrives_time', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->where('team_id', $teamId)
            ->get();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');

            $booking[] = $allBookings->where('created_at->format("Y-m-d")', $date->format('Y-m-d'))->count();
            $queuesForDay = $allQueues->filter(fn($q) => $q->arrives_time->format('Y-m-d') === $date->format('Y-m-d'));

            $waitlist[] = $queuesForDay->count();
            $serving[] = $queuesForDay->where('status', 'Close')->count();
            $dropoff[] = $queuesForDay->filter(fn($q) => $q->is_missed == 1 || $q->status == Queue::STATUS_CANCELLED)->count();
        }
    }

    return [
        'labels' => $labels,
        'waitlist' => $waitlist,
        'booking' => $booking,
        'serving' => $serving,
        'dropoff' => $dropoff,
    ];
}

   private function getCustomerPathData()
{
    $today = Carbon::today();

    // Fetch bookings for today
    $bookings = Booking::whereDate('created_at', $today)->where('team_id', $this->teamId)->get();
    $bookingCount = $bookings->count();
    $convertedBookings = $bookings->where('is_convert', 'Yes')->count();

    // Fetch queue data for today
    $queue = QueueStorage::whereDate('arrives_time', $today)->where('team_id', $this->teamId)->get();
    $queueCount = $queue->count();
    $servedCount = $queue->where('status', 'Close')->count();

    return [
        'labels' => ['Bookings', 'Converted bookings', 'Waitlist', 'Direct to served'],
        'datasets' => [
            [
                // 'label' => ['Bookings', 'Converted bookings', 'Waitlist', 'Direct to served'],
                'data' => [$bookingCount, $convertedBookings, $queueCount, $servedCount],
                'backgroundColor' => [
                    'rgba(147,197,253,0.6)',  // pastel blue
                    'rgba(167,243,208,0.6)',  // pastel green
                    'rgba(253,186,116,0.6)',  // pastel orange
                    'rgba(254,240,138,0.6)'   // pastel yellow
                ],
                'borderRadius' => 4
            ]
        ],
        'summary' => [
            'registration' => $bookingCount + $queueCount,
            'served' => $servedCount,
            'completed' => $servedCount
        ]
    ];
}



 public function loadTopVisitedLocations()
{
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();

    $locations = Location::where('team_id', $this->teamId)->where('status', 1)->get();

    // Preload today's and yesterday's data
    $bookingsToday = Booking::where('team_id', $this->teamId)
        ->whereDate('created_at', $today)
        ->get();

    $bookingsYesterday = Booking::where('team_id', $this->teamId)
        ->whereDate('created_at', $yesterday)
        ->get();

    $queueToday = QueueStorage::where('team_id', $this->teamId)
        ->whereDate('arrives_time', $today)
        ->get();

    $queueYesterday = QueueStorage::where('team_id', $this->teamId)
        ->whereDate('arrives_time', $yesterday)
        ->get();

    // Group by location
    $groupedBookingsToday = $bookingsToday->groupBy('location_id');
    $groupedBookingsYesterday = $bookingsYesterday->groupBy('location_id');
    $groupedQueueToday = $queueToday->groupBy('locations_id');
    $groupedQueueYesterday = $queueYesterday->groupBy('locations_id');

    $this->topVisitedLocations = $locations->map(function ($location) use (
        $groupedBookingsToday, $groupedBookingsYesterday,
        $groupedQueueToday, $groupedQueueYesterday
    ) {
        $locationId = $location->id;

        // === Today Data ===
        $bookingsToday = $groupedBookingsToday->get($locationId, collect());
        $queueToday = $groupedQueueToday->get($locationId, collect());

        $servedToday = $queueToday->where('status', 'Close');
        $dropoffsToday = $queueToday->filter(fn($q) => $q->is_missed == 1 || $q->status == Queue::STATUS_CANCELLED);
        $holdToday = $queueToday->where('is_hold', 1);

        $todayData = [
            'visits' => $bookingsToday->count() + $queueToday->count(),
            'waitlisted' => $queueToday->count(),
            'bookings' => $bookingsToday->count(),
            'served' => $servedToday->count(),
            'completed' => $servedToday->count(),
            'dropoff' => $dropoffsToday->count(),
            'hold' => $holdToday->count(),
            'avg_wait' => $this->calculateAverageMinutes($servedToday, 'arrives_time', 'called_datetime'),
            'avg_serve' => $this->calculateAverageMinutes($servedToday, 'called_datetime', 'closed_datetime'),
        ];

        // === Yesterday Data ===
        $bookingsYesterday = $groupedBookingsYesterday->get($locationId, collect());
        $queueYesterday = $groupedQueueYesterday->get($locationId, collect());

        $servedYesterday = $queueYesterday->where('status', 'Close');
        $dropoffsYesterday = $queueYesterday->filter(fn($q) => $q->is_missed == 1 || $q->status == Queue::STATUS_CANCELLED);
        $holdYesterday = $queueYesterday->where('is_hold', 1);

        $yesterdayData = [
            'visits' => $bookingsYesterday->count() + $queueYesterday->count(),
            'waitlisted' => $queueYesterday->count(),
            'bookings' => $bookingsYesterday->count(),
            'served' => $servedYesterday->count(),
            'completed' => $servedYesterday->count(),
            'dropoff' => $dropoffsYesterday->count(),
            'hold' => $holdYesterday->count(),
            'avg_wait' => $this->calculateAverageMinutes($servedYesterday, 'arrives_time', 'called_datetime'),
            'avg_serve' => $this->calculateAverageMinutes($servedYesterday, 'called_datetime', 'closed_datetime'),
        ];

        // === Merge + Compare
        return [
            'location' => $location->location_name,
            'visits' => $this->compare($todayData['visits'], $yesterdayData['visits']),
            'waitlisted' => $this->compare($todayData['waitlisted'], $yesterdayData['waitlisted']),
            'bookings' => $this->compare($todayData['bookings'], $yesterdayData['bookings']),
            'served' => $this->compare($todayData['served'], $yesterdayData['served']),
            'completed' => $this->compare($todayData['completed'], $yesterdayData['completed']),
            'dropoff' => $this->compare($todayData['dropoff'], $yesterdayData['dropoff']),
            'hold' => $this->compare($todayData['hold'], $yesterdayData['hold']),
            'avg_wait' => $this->compare($todayData['avg_wait'], $yesterdayData['avg_wait'], ' mins'),
            'avg_serve' => $this->compare($todayData['avg_serve'], $yesterdayData['avg_serve'], ' mins'),
        ];
    })->toArray();
}


private function calculateAverageMinutes($collection, $startField, $endField)
{
    $diffs = $collection->map(function ($item) use ($startField, $endField) {
        if ($item->$startField && $item->$endField) {
            return Carbon::parse($item->$startField)->diffInMinutes($item->$endField);
        }
        return null;
    })->filter();

    return $diffs->count() ? round($diffs->avg()) : 0;
}

private function compare($today, $yesterday, $unit = '')
{
    if ($yesterday == 0) {
        if ($today == 0) {
            return "0{$unit}"; // ➤ No data both days
        }
        if ($yesterday == 0) {
            return $today > 0 ? "{$today}{$unit} ↑ +100%" : '0%';
        }
        //  return "{$today}{$unit} ↑ New"; // ➤ Fresh activity today
    }

    $percent = round((($today - $yesterday) / $yesterday) * 100);
    $symbol = $percent >= 0 ? '↑' : '↓';

    return "{$today}{$unit} {$symbol}" . abs($percent) . '%';
}
private function calculateChange($today, $yesterday)
{
    try {
        if ($yesterday == 0) {
            return $today > 0 ? '+100%' : '0%';
        }

        // Ensure $today and $yesterday are numeric
        if (!is_numeric($today) || !is_numeric($yesterday)) {
            throw new \Exception("Non-numeric value provided. Today: $today, Yesterday: $yesterday");
        }

        $change = round((($today - $yesterday) / $yesterday) * 100);
        return ($change >= 0 ? '+' : '') . $change . '%';

    } catch (\Throwable $e) {
        // Debug output (in production you might log this instead)
        dd('Error in calculateChange(): ' . $e->getMessage());
    }
}


// Visit code

public function loadVisitTabData()
{
    $today = now()->toDateString();

    // Get today's new customers
    $todayCustomerIds = Customer::whereDate('created_at', $today)
        ->where('team_id', $this->teamId)
        ->pluck('id');

    // Repeat customers from queue_storage (customer created before today and visited today)
    $queueRepeatIds = QueueStorage::where('team_id', $this->teamId)
        ->whereDate('arrives_time', $today)
        ->whereIn('created_by', function ($query) use ($today) {
            $query->select('id')
                ->from('customers')
                ->whereDate('created_at', '<', $today);
        })
        ->pluck('created_by');

    // Repeat customers from bookings (customer created before today and booked today)
    $bookingRepeatIds = Booking::where('team_id', $this->teamId)
        ->whereDate('created_at', $today) // the booking happened today
        ->whereIn('created_by', function ($query) use ($today) {
            $query->select('id')
                ->from('customers')
                ->whereDate('created_at', '<', $today);
        })
        ->pluck('created_by');

    // Merge and filter out today's new customers
    $repeatCustomerIds = $queueRepeatIds
        ->merge($bookingRepeatIds)
        ->unique()
        ->diff($todayCustomerIds);

    $this->dispatch('customerDistributionUpdated', [
        [$todayCustomerIds->count(), $repeatCustomerIds->count()]
    ]);
}

public function getVisitDistributionMatrix()
{
    $filter = $this->visitTypeFilter;
    $matrix = array_fill(0, 7, array_fill(0, 24, 0));

    $startOfWeek = now()->startOfWeek();
    $endOfWeek = now()->endOfWeek();

    // Fetch data once per model
    $bookings = Booking::where('team_id', $this->teamId)
        ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
        ->get();

    $queueItems = QueueStorage::where('team_id', $this->teamId)
        ->whereBetween('arrives_time', [$startOfWeek, $endOfWeek])
        ->get();

    // Process according to filter
    switch ($filter) {
        case 'bookings':
            foreach ($bookings as $booking) {
                $this->addToMatrix($matrix, $booking->booking_date);
            }
            break;

        case 'waitlisted':
            foreach ($queueItems as $q) {
                $this->addToMatrix($matrix, $q->arrives_time);
            }
            break;

        case 'dropoffs':
            foreach ($queueItems->filter(fn($q) => $q->is_missed == 1 || $q->status === Queue::STATUS_CANCELLED) as $q) {
                $this->addToMatrix($matrix, $q->arrives_time);
            }
            break;

        case 'hold':
            foreach ($queueItems->where('is_hold', 1) as $q) {
                $this->addToMatrix($matrix, $q->arrives_time);
            }
            break;

        case 'served':
            foreach ($queueItems->where('status', 'Close') as $q) {
                $this->addToMatrix($matrix, $q->arrives_time);
            }
            break;

        case 'cancelled':
            foreach ($bookings->where('status', 'Cancelled') as $booking) {
                $this->addToMatrix($matrix, $booking->booking_date);
            }
            foreach ($queueItems->where('status', Queue::STATUS_CANCELLED) as $q) {
                $this->addToMatrix($matrix, $q->arrives_time);
            }
            break;

        case 'all-visits':
        default:
            foreach ($bookings->where('is_convert', 'No') as $booking) {
                $this->addToMatrix($matrix, $booking->booking_date);
            }
            foreach ($queueItems as $q) {
                $this->addToMatrix($matrix, $q->arrives_time);
            }
            break;
    }

    $this->visitDistributionMatrix = $matrix;
}


private function addToMatrix(array &$matrix, $datetime)
{
    if (!$datetime) return;

    $carbon = Carbon::parse($datetime)->setTimezone(config('app.timezone', 'Asia/Kolkata'));

    $dayOfWeek = $carbon->dayOfWeek; // Sunday = 0, Monday = 1, ..., Saturday = 6
    $hour = $carbon->hour;

    if (isset($matrix[$dayOfWeek][$hour])) {
        $matrix[$dayOfWeek][$hour]++;
    }
}


//Service tabs

public function setServiceVisitTypeFilter(string $type)
{
    $this->serviceVisitTypeFilter = $type;
    $this->showServiceFilter = false;
    $this->getServiceCombinationData(); // reload chart data
}

public function getServiceCombinationData()
{
    $today = now()->toDateString();

    // Get all top-level categories (parent_id null or empty string)
    $services = Category::where('team_id', $this->teamId)
        ->where(fn($q) => $q->whereNull('parent_id')->orWhere('parent_id', ''))
        ->pluck('name', 'id'); // [id => name]

    $this->serviceLegend = [];
    $serviceCounts = [];
    $pastelColors = $this->generatePastelColors(count($services));

    // Assign pastel colors
    foreach (array_values($services->all()) as $i => $name) {
        $this->serviceLegend[$name] = $pastelColors[$i % count($pastelColors)];
    }

    // Preload all data for today for the current team to avoid repeated queries
    $allBookings = Booking::where('team_id', $this->teamId)
        ->whereDate('created_at', $today)
        ->get();

    $allQueues = QueueStorage::where('team_id', $this->teamId)
        ->whereDate('arrives_time', $today)
        ->get();

    foreach ($services as $id => $name) {
        $bookingSet = $allBookings->where('category_id', $id);
        $queueSet = $allQueues->where('category_id', $id);

        switch ($this->serviceVisitTypeFilter) {
            case 'bookings':
                $total = $bookingSet->where('is_convert', 'No')->count();
                break;

            case 'waitlisted':
                $total = $queueSet->count();
                break;

            case 'dropoffs':
                $total = $queueSet->filter(fn($item) => $item->is_missed == 1 || $item->status === Queue::STATUS_CANCELLED)->count();
                break;

            case 'hold':
                $total = $queueSet->where('is_hold', 1)->count();
                break;

            case 'served':
                $total = $queueSet->where('status', 'close')->count();
                break;

            case 'cancelled':
                $bookingCancelled = $bookingSet->where('is_convert', 'No')->where('status', 'Cancelled')->count();
                $queueCancelled = $queueSet->where('status', 'cancelled')->count();
                $total = $bookingCancelled + $queueCancelled;
                break;

            case 'all-visits':
            default:
                $total = $bookingSet->where('is_convert', 'No')->count() + $queueSet->count();
                break;
        }

        if ($total > 0) {
            $serviceCounts[$name] = $total;
        }
    }

    // Sort by total descending
    arsort($serviceCounts);

    // Prepare chart data
    $labels = array_keys($serviceCounts);
    $data = array_values($serviceCounts);
    $colors = array_values(array_intersect_key($this->serviceLegend, array_flip($labels)));

    $this->dispatch('serviceCombinationUpdated', [
        'labels' => $labels,
        'data' => $data,
        'colors' => $colors,
    ]);
}
public function generatePastelColors($count)
{
    $colors = [];

    for ($i = 0; $i < $count; $i++) {
        // Use a consistent saturation and lightness for pastel look
        $hue = ($i * 360 / $count) % 360;         // Distribute hues evenly
        $saturation = 60 + rand(0, 10);           // 60-70% for softer tones
        $lightness = 80 + rand(0, 5);             // 80-85% for pastel brightness

        $colors[] = "hsl($hue, $saturation%, $lightness%)";
    }

    return $colors;
}
//operation graph

public function getVisitsByLocationData()
{
    $today = now()->toDateString();
    $locations = Location::where('team_id', $this->teamId)->where('status', 1)->get();
    $locationIds = $locations->pluck('id')->toArray();
    $count = count($locationIds);

    // Fetch all today's queues and bookings at once
    $todayQueues = QueueStorage::where('team_id', $this->teamId)
        ->whereIn('locations_id', $locationIds)
        ->whereDate('arrives_time', $today)
        ->get()
        ->groupBy('locations_id');

    $todayBookings = Booking::where('team_id', $this->teamId)
        ->whereIn('location_id', $locationIds)
        ->whereDate('created_at', $today)
        ->get()
        ->groupBy('location_id');

    $labels = [];
    $values = [];
    $colors = [];
    $pastelColors = $this->generatePastelColors($count);

    foreach ($locations as $index => $location) {
        $queues = $todayQueues->get($location->id, collect());
        $bookings = $todayBookings->get($location->id, collect());

        switch ($this->locationVisitFilter) {
            case 'bookings':
                $countData = $bookings->where('is_convert', 'No')->count();
                break;

            case 'waitlisted':
                $countData = $queues->count();
                break;

            case 'dropoffs':
                $countData = $queues->filter(fn($q) => $q->is_missed == 1 || $q->status == Queue::STATUS_CANCELLED)->count();
                break;

            case 'hold':
                $countData = $queues->where('is_hold', 1)->count();
                break;

            case 'served':
                $countData = $queues->where('status', 'Close')->count();
                break;

            case 'cancelled':
                $bookingCancelled = $bookings->where('status', 'Cancelled')->where('is_convert', 'No')->count();
                $queueCancelled = $queues->where('status', 'cancelled')->count();
                $countData = $bookingCancelled + $queueCancelled;
                break;

            case 'all':
            default:
                $countData = $bookings->where('is_convert', 'No')->count() + $queues->count();
                break;
        }

        if ($countData > 0) {
            $labels[] = $location->location_name;
            $values[] = $countData;
            $colors[] = $pastelColors[$index % $count];
        }
    }

    return [
        'labels' => $labels,
        'values' => $values,
        'colors' => $colors,
    ];
}



public function getUserVisitsData()
{
    $today = now()->toDateString();

    $queue = QueueStorage::where('team_id', $this->teamId)
        ->whereDate('arrives_time', $today);

 
    $queue->where('status', 'Close');
    $userCounts = $queue->select('served_by', DB::raw('COUNT(*) as total'))
        ->groupBy('served_by')
        ->with('servedBy') // assumes `servedBy` relationship exists
        ->get()
        ->filter(fn($row) => $row->servedBy) // ensure user exists
        ->mapWithKeys(fn($row) => [$row->servedBy->name ?? 'Unknown' => $row->total]);

    $labels = $userCounts->keys()->toArray();
    $values = $userCounts->values()->toArray();
    $colors = $this->generatePastelColors(count($labels));

    return [
        'labels' => $labels,
        'values' => $values,
        'colors' => $colors,
    ];
}

public function updatedLocationVisitFilter()
{
    $this->dispatch('opertaionVisitsByLocationChartUpdated', $this->getVisitsByLocationData());
}

public function updatedUserVisitFilter()
{
    $this->dispatch('userVisitsChartUpdated', $this->getUserVisitsData());
}

//Messages
public function loadMessageMetrics()
{
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();

    // Batch query: count and sum characters grouped by date and type
    $rawStats = MessageDetail::where('team_id', $this->teamId)
        ->whereIn(DB::raw('DATE(created_at)'), [$today, $yesterday])
        ->selectRaw('
            DATE(created_at) as date,
            type,
            COUNT(*) as message_count,
            SUM(CHAR_LENGTH(message)) as total_characters
        ')
        ->groupBy('date', 'type')
        ->get();

    // Prepare counters
    $data = [
        'today' => ['automatic' => 0, 'triggered' => 0, 'custom' => 0, 'characters' => 0],
        'yesterday' => ['automatic' => 0, 'triggered' => 0, 'custom' => 0, 'characters' => 0],
    ];

    // Fill counters from query result
    foreach ($rawStats as $stat) {
        $day = $stat->date === $today ? 'today' : 'yesterday';
        $data[$day][$stat->type] = $stat->message_count;
        $data[$day]['characters'] += $stat->total_characters;
    }

    // Total messages
    $totalMessagesToday = array_sum(array_slice($data['today'], 0, 3));
    $totalMessagesYesterday = array_sum(array_slice($data['yesterday'], 0, 3));

    // Unique contacts
    $uniqueToday = MessageDetail::where('team_id', $this->teamId)
        ->whereDate('created_at', $today)
        ->distinct('contact')->count('contact');

    $uniqueYesterday = MessageDetail::where('team_id', $this->teamId)
        ->whereDate('created_at', $yesterday)
        ->distinct('contact')->count('contact');

    $messagesPerCustomer = $uniqueToday > 0 ? round($totalMessagesToday / $uniqueToday, 2) : 0;
    $messagesPerCustomerYesterday = $uniqueYesterday > 0 ? round($totalMessagesYesterday / $uniqueYesterday, 2) : 0;

    // Final metrics
    // $this->messageMetrics = [
    //     ['title' => 'Messages', 'value' => number_format($totalMessagesToday), 'change' => $this->calculateChange($totalMessagesToday, $totalMessagesYesterday)],
    //     ['title' => 'Messages per Customer', 'value' => $messagesPerCustomer, 'change' => $this->calculateChange($messagesPerCustomer, $messagesPerCustomerYesterday)],
    //     ['title' => 'Automatic SMS', 'value' => number_format($data['today']['automatic']), 'change' => $this->calculateChange($data['today']['automatic'], $data['yesterday']['automatic'])],
    //     ['title' => 'Triggered SMS', 'value' => number_format($data['today']['triggered']), 'change' => $this->calculateChange($data['today']['triggered'], $data['yesterday']['triggered'])],
    //     ['title' => 'Custom SMS', 'value' => number_format($data['today']['custom']), 'change' => $this->calculateChange($data['today']['custom'], $data['yesterday']['custom'])],
    //     ['title' => 'Message Segments', 'value' => number_format($data['today']['characters']), 'change' => $this->calculateChange($data['today']['characters'], $data['yesterday']['characters'])],
    // ];

     $this->messageMetrics = [
        ['title' => __('report.Messages'), 'value' => number_format($totalMessagesToday), 'change' => $this->calculateChange($totalMessagesToday, $totalMessagesYesterday)],
        ['title' => __('report.Messages per Customer'), 'value' => $messagesPerCustomer, 'change' => $this->calculateChange($messagesPerCustomer, $messagesPerCustomerYesterday)],
        ['title' => __('report.Automatic SMS'), 'value' => number_format($data['today']['automatic']), 'change' => $this->calculateChange($data['today']['automatic'], $data['yesterday']['automatic'])],
        ['title' => __('report.Triggered SMS'), 'value' => number_format($data['today']['triggered']), 'change' => $this->calculateChange($data['today']['triggered'], $data['yesterday']['triggered'])],
        ['title' => __('report.Custom SMS'), 'value' => number_format($data['today']['custom']), 'change' => $this->calculateChange($data['today']['custom'], $data['yesterday']['custom'])],
        ['title' => __('report.Message Segments'), 'value' => number_format($data['today']['characters']), 'change' => $this->calculateChange($data['today']['characters'], $data['yesterday']['characters'])],
    ];
}

public function getMessageChartData()
{
    $startDate = now()->subDays(6)->startOfDay(); // Last 7 days
    $endDate = now()->endOfDay();
    $today = now()->toDateString();

    $channels = ['email', 'sms', 'api_email', 'api_sms'];
    $totals = [];

    foreach ($channels as $channel) {
        $totals[] = MessageDetail::where('team_id', $this->teamId)
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->whereDate('created_at', $today)
            ->where('channel', $channel)
            ->count();
    }

    $this->messageChannelChartData = [
        'labels' => ['Email', 'SMS', 'API Email', 'API SMS'],
        'values' => $totals,
    ];

    $this->dispatch('messageChannelChartUpdated', $this->messageChannelChartData);
}

// guest
public function getdropoffWaitlistData()
{
     // Bucket definitions
    $buckets = [
        '<1' => [0, 1],
        '1' => [1, 2],
        '2' => [2, 3],
        '3' => [3, 4],
        '4' => [4, 5],
        '5' => [5, 6],
        '6' => [6, 7],
        '7' => [7, 8],
        '8-15' => [8, 16],
    ];

    $labels = array_keys($buckets);
    $dropoffCounts = [];
    $dropoffRates = [];

    $totalQueues = QueueStorage::where('team_id',$this->teamId)->where('status', 'Cancelled')->whereNotNull('dropoff_position')->count();

    // For each range, calculate dropoffs
    foreach ($buckets as $label => [$min, $max]) {
        $count = QueueStorage::where('status', 'Cancelled')
            ->whereNotNull('dropoff_position')
            ->where('team_id',$this->teamId)
            ->whereBetween('dropoff_position', [$min, $max - 0.00001])
            ->count();

        $dropoffCounts[] = $count;
        $rate = $totalQueues > 0 ? ($count / $totalQueues) * 100 : 0;
        $dropoffRates[] = round($rate, 2);
    }

    // Average dropoff position
    $averageDropoff = QueueStorage::where('status', 'Cancelled')
        ->whereNotNull('dropoff_position')
        ->where('team_id',$this->teamId)
        ->avg('dropoff_position');

    $this->dispatch('dropoffByPositionChartUpdated', [
        'labels' => $labels,
        'dropoffCounts' => $dropoffCounts,
        'dropoffRates' => $dropoffRates,
        'averageDropoff' => round($averageDropoff, 2),
    ]);
}

public function getDropoffDonutData(): array
{
        $today = now()->toDateString();
    $result = QueueStorage::select(
            DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
            DB::raw("SUM(CASE WHEN  is_missed = 1 THEN 1 ELSE 0 END) as no_show")
        )
        ->where('team_id', $this->teamId)
        ->whereDate('arrives_time', $today)
        ->first();

    $getDropoffDonutDataChat= [
        'labels' => ['Cancelled', 'No-shows'],
        'values' => [
            $result->cancelled ?? 0,
            $result->no_show ?? 0
        ]
    ];

     $this->dispatch('guestgetDropoffDonutDataChat', $getDropoffDonutDataChat);
     return  $getDropoffDonutDataChat;
}


public function getLineLengthChartData(): void
{
    $labels = [];
    $values = [];

    if ($this->lineGraphFilter === 'hour_of_day') {
        $rawData = QueueStorage::selectRaw('HOUR(arrives_time) as hour, COUNT(*) as total')
            ->where('team_id', $this->teamId)
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour')
            ->toArray();

        for ($i = 0; $i < 24; $i++) {
            $start = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $end = str_pad(($i + 1) % 24, 2, '0', STR_PAD_LEFT) . ':00';
            $labels[] = "$start - $end";
            $values[] = $rawData[$i] ?? 0;
        }
    }

    if ($this->lineGraphFilter === 'hour_of_date') {
        $start = now()->startOfMonth();
        $end = now();
        $rawData = QueueStorage::selectRaw('DAY(arrives_time) as day, COUNT(*) as total')
            ->whereBetween('arrives_time', [$start, $end])
            ->where('team_id', $this->teamId)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $labels = range(1, now()->day);
        $values = array_map(fn($day) => $rawData[$day] ?? 0, $labels);
    }

    if ($this->lineGraphFilter === 'hour_of_week') {
       $start = now()->startOfMonth();
    $end = now()->endOfMonth();

    $rawData = QueueStorage::selectRaw('DAYOFWEEK(arrives_time) as weekday, COUNT(*) as total')
        ->whereBetween('arrives_time', [$start, $end])
        ->where('team_id', $this->teamId)
        ->groupBy('weekday')
        ->orderBy('weekday')
        ->pluck('total', 'weekday')
        ->toArray();

        $labels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        for ($i = 1; $i <= 7; $i++) {
            $values[] = $rawData[$i] ?? 0;
        }
    }

    $finalData = [
        'labels' => $labels,
        'values' => $values,
    ];



    $this->dispatch('lineLengthChartDataUpdated', $finalData);
}

public function updatedRegistrationCreatorFilter()
{
    $this->dispatchRegistrationChartData();
}

public function updatedRegistrationVisitFilter()
{
    $this->dispatchRegistrationChartData();
}

public function dispatchRegistrationChartData()
{
    $data = $this->getRegistrationChartData(); // Your logic here
    //  $this->dispatch('registrationDetailsChartUpdated', data: $data);
}


public function getRegistrationChartData(): void
{
    $today = Carbon::today();

    $labels = ['Web', 'API', 'Kiosk', 'Mobile'];
    $modes = ['web', 'api', 'kiosk', 'mobile'];

    $query = QueueStorage::select(
        DB::raw('LOWER(mode) as mode'),
        DB::raw('COUNT(*) as total')
    );

    if ($this->modeFilter === 'hour_of_day') {
        $query->whereDate('arrives_time', $today);
    } elseif ($this->modeFilter === 'hour_of_date') {
        $query->whereBetween('arrives_time', [now()->startOfMonth(), now()]);
    } elseif ($this->modeFilter === 'hour_of_week') {
        $query->whereBetween('arrives_time', [now()->startOfWeek(), now()]);
    }

    $rawData = $query->groupBy(DB::raw('LOWER(mode)'))
        ->where('team_id', $this->teamId)
        ->pluck('total', 'mode')
        ->toArray();

    $values = array_map(fn($mode) => $rawData[$mode] ?? 0, $modes);

    $chartData = [
        'labels' => $labels,
        'datasets' => [
            [
                'data' => $values,
                'backgroundColor' => [
                    '#a5b4fc', // pastel indigo
                    '#bbf7d0', // pastel green
                    '#f5d0fe', // pastel pink
                    '#fde68a'  // pastel yellow
                ]
            ]
        ]
    ];

    $this->dispatch('registrationDetailsChartUpdated', $chartData);
}

public function getWaitServeDurationChartData(): void
{
    $timeRanges = [
        '<1m'      => [0, 1],
        '13m–25m'  => [13, 25],
        '25m–38m'  => [25, 38],
        '38m–50m'  => [38, 50],
        '50m–63m'  => [50, 63],
        '63m–75m'  => [63, 75],
        '75m–88m'  => [75, 88],
        '88m–3d'   => [88, 4320],
    ];

    $waitCounts = [];
    $serveCounts = [];

    foreach ($timeRanges as $label => [$min, $max]) {
        $waitCount = QueueStorage::where('status', 'Pending')
             ->where('team_id', $this->teamId)
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->whereRaw("TIMESTAMPDIFF(MINUTE, arrives_time, called_datetime) >= ?", [$min])
            ->whereRaw("TIMESTAMPDIFF(MINUTE, arrives_time, called_datetime) < ?", [$max])
            ->count();

        $serveCount = QueueStorage::where('status', 'Close')
            ->where('team_id', $this->teamId)
            ->whereNotNull('start_datetime')
            ->whereNotNull('closed_datetime')
            ->whereRaw("TIMESTAMPDIFF(MINUTE, start_datetime, closed_datetime) >= ?", [$min])
            ->whereRaw("TIMESTAMPDIFF(MINUTE, start_datetime, closed_datetime) < ?", [$max])
            ->count();

        $waitCounts[] = $waitCount;
        $serveCounts[] = $serveCount;
    }

    $chartData = [
        'labels' => array_keys($timeRanges),
        'datasets' => [
            [
                'label' => 'Waitlisted',
                'data' => $waitCounts,
                'borderColor' => '#a5b4fc', // pastel indigo
                'backgroundColor' => 'rgba(165, 180, 252, 0.3)', // translucent
                'fill' => true,
                'tension' => 0.4
            ],
            [
                'label' => 'Served',
                'data' => $serveCounts,
                'borderColor' => '#bbf7d0', // pastel green
                'backgroundColor' => 'rgba(187, 247, 208, 0.3)',
                'fill' => true,
                'tension' => 0.4
            ]
        ]
    ];

    $this->dispatch('waitServeChartUpdated', $chartData);
}

public function getAverageDurationsChartData(): void
{

    // 1. Booking Lead (Booking table)
    $bookingLead = Booking::whereNotNull('convert_datetime')
        ->whereDate('created_at',$this->today)
        ->where('team_id', $this->teamId)
        ->where('is_convert', 'Yes')
        ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, convert_datetime)'));

    // 2. Booking to Serve (QueueStorage)
    $bookingToServe = QueueStorage::whereNotNull('called_datetime')
     ->whereDate('arrives_time',$this->today)
        ->where('team_id', $this->teamId)
        ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, arrives_time, called_datetime)'));

    // 3. Serve Duration (QueueStorage)
    $serveDuration = QueueStorage::whereNotNull('called_datetime')
    ->whereDate('arrives_time',$this->today)
        ->whereNotNull('closed_datetime')
        ->where('team_id', $this->teamId)
        ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, called_datetime, closed_datetime)'));

    $labels = ['Booking lead', 'Wait', 'Serve duration'];
    $values = [
        $bookingLead ?? 0,
        $bookingToServe ?? 0,
        $serveDuration ?? 0
    ];

    $chartData = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Average Duration (minutes)',
                'data' => $values,
                'backgroundColor' => [
                    '#E9D5FF', // light purple
                    '#BBF7D0', // light green
                    '#BFDBFE'  // light blue
                ],
                'borderColor' => [
                    '#C084FC', // purple border
                    '#4ADE80', // green border
                    '#60A5FA'  // blue border
                ], // pastel purple, green, blue
                'borderRadius' => 6
            ]
        ]
    ];

    $this->dispatch('averageDurationsChartUpdated', $chartData);
}


}
