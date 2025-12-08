<?php
// Livewire component: DashboardSummary.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Rating;
use App\Models\Customer;
use App\Models\User;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class DashboardSummary extends Component
{
    public $teamId;
    public $locationId;
    public $years;
    public $year;
    public $months = [];
    public $staff = [];
    public $selectedStaff = [];
    public $selectedAll = true;
    public $selectedallMonth = false;



    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId =  Session::get('selectedLocation');
        $this->year = date('Y');
         $currentMonth = date('m'); // '01' to '12'
        $this->months = [$currentMonth];
        $this->getFilteredUsers();
         $currentYear = date('Y');
        $this->years = range($currentYear-2, $currentYear);
    
       
    }

       public function getFilteredUsers()
    {
         $this->staff = User::whereNotNull('locations')
        ->where('locations', '!=', '')
        ->whereRaw("JSON_VALID(locations)")
        ->whereJsonContains('locations', (string) $this->locationId)
        ->orWhereJsonContains('locations', (int) $this->locationId)
        ->get();
        
        
         if (empty($this->selectedStaff) || count($this->selectedStaff) !== $this->staff->count()) {
        $this->selectedStaff = $this->staff->pluck('id')->toArray();
       
    }

      if (!empty($this->selectedStaff) || count($this->selectedStaff) == $this->staff->count()) {
        $this->selectedAll =  true;
       
    }

    }

    public function toggleAllMonths()
{
    if (count($this->months) < 12) {
        $this->months = ["01","02","03","04","05","06","07","08","09","10","11","12"];
        $this->selectedallMonth = false;
    } else {
        $this->months = [];
        $this->selectedallMonth = false;
    }

     if (count($this->months) == 12) {
         $this->selectedallMonth = true;
     }
}

public function toggleAllStaff()
{
    if (count($this->selectedStaff) < $this->staff->count()) {
        $this->selectedStaff = $this->staff->pluck('id')->toArray();
        $this->selectedAll =false;
    } else {
        $this->selectedStaff = [];
        $this->selectedAll =false;
    }

     if (!empty($this->selectedStaff) || count($this->selectedStaff) == $this->staff->count()) {
        $this->selectedAll =  true;
       
    }
    
}

        public function updatedselectedStaff(){
            if (count($this->selectedStaff) < $this->staff->count()) {
                $this->selectedAll =false;
            }else{
                $this->selectedAll =true;

            }
          
            
        }

        public function updatedmonths(){
           
            $this->selectedallMonth = false;
            if (count($this->months) == 12) {
                $this->selectedallMonth = true;
            }

        }

    public function render()
    {
     
        $chartData = [];
        $avgTxnTimePerStaff = [];

        $feedbacks = Rating::whereYear('created_at', $this->year)
             ->whereIn('user_id', $this->selectedStaff)
            ->whereIn(\DB::raw("LPAD(MONTH(created_at), 2, '0')"), $this->months)
            ->get();

        $feedbackCount = $feedbacks->count();

        $totalRating = $feedbacks->sum('rating');

        // Each rating is out of 4, so max possible = $feedbackCount * 4
        $avgRating = $feedbackCount > 0
            ? round(($totalRating / ($feedbackCount * 4)) * 100)
            : 0;// Assume 1-4 stars, scaled to 100%

     
        $guestsServed = User::whereHas('closedBy', function ($query) {
                $query->whereYear('arrives_time', $this->year)->where('status', Queue::STATUS_CLOSE)
                    ->when(!empty($this->months), function ($query) {
                        $query->whereIn(\DB::raw("LPAD(MONTH(arrives_time), 2, '0')"), $this->months)->where('status', Queue::STATUS_CLOSE);
                    });
            })
            ->whereIn('id', $this->selectedStaff)
            ->whereNotNull('locations')
            ->where('locations', '!=', '')
            ->whereRaw("JSON_VALID(locations)")
            ->where(function ($query) {
                $query->whereJsonContains('locations', (string) $this->locationId)
                    ->orWhereJsonContains('locations', (int) $this->locationId);
            })
            ->withCount(['closedBy as queues_count' => function ($query) {
                $query->whereYear('arrives_time', $this->year)->where('status', Queue::STATUS_CLOSE)
                    ->when(!empty($this->months), function ($query) {
                        $query->whereIn(\DB::raw("LPAD(MONTH(arrives_time), 2, '0')"), $this->months);
                    });
            }])
            ->with(['closedBy' => function ($query) {
                $query->whereYear('arrives_time', $this->year)->where('status', Queue::STATUS_CLOSE)
                    ->when(!empty($this->months), function ($query) {
                        $query->whereIn(\DB::raw("LPAD(MONTH(arrives_time), 2, '0')"), $this->months);
                    });
            }])
            ->get();

      
            $totalServedQueues = $guestsServed->pluck('queues_count')->sum();

            $chartData = [
            'labels' => $guestsServed->pluck('name'),
            'data' => $guestsServed->pluck('queues_count'),
        ];

        foreach ($guestsServed as $staff) {
            $totalTime = 0;
            $count = 0;

    foreach ($staff->closedBy as $queue) {
        if ($queue->start_datetime && $queue->closed_datetime) {
            $totalTime += abs($queue->closed_datetime->diffInSeconds($queue->start_datetime));
            $count++;
        }
    }

            $avgTxnTimePerStaff[$staff->name] = $count > 0
                ? \Carbon\CarbonInterval::seconds($totalTime / $count)->cascade()->format('%H:%I:%S')
                : '00:00:00';
        }

            // --- Calculate Served Time and Waiting Time ---
            $totalServedTime = 0;
            $totalWaitingTime = 0;
            $totalQueueCount = 0;

            foreach ($guestsServed as $staff) {
                foreach ($staff->closedBy as $queue) {
                    if ($queue->start_datetime && $queue->closed_datetime) {
                        // $totalServedTime += $queue->closed_datetime->diffInSeconds($queue->start_datetime);
                        $totalServedTime += abs($queue->closed_datetime->diffInSeconds($queue->start_datetime));
                    }

                    if ($queue->arrives_time && $queue->called_datetime) {
                        // $totalWaitingTime += $queue->arrives_time->diffInSeconds($queue->called_datetime);
                        $totalWaitingTime += abs(Carbon::parse($queue->start_datetime)->diffInSeconds($queue->arrives_time));

                    }

                    $totalQueueCount++;
                }
            }

            // Final average calculations
            $avgServedTime = $totalQueueCount > 0 ? $totalServedTime / $totalQueueCount : 0;
            $avgWaitingTime = $totalQueueCount > 0 ? $totalWaitingTime / $totalQueueCount : 0;

            $formattedServedTime = \Carbon\CarbonInterval::seconds($avgServedTime)->cascade()->format('%H:%I:%S');
            $formattedWaitingTime = \Carbon\CarbonInterval::seconds($avgWaitingTime)->cascade()->format('%H:%I:%S');

            foreach ($guestsServed as $staff) {
                $queueCountsPerStaff[$staff->name] = $staff->queues->count();
            }
         
            $dailyStats = [];
            // 1. Collect guests served per day
        $queues = DB::table('queues_storage')
            ->select(DB::raw('DATE(arrives_time) as date'), DB::raw('COUNT(*) as count'))
            ->whereYear('arrives_time', $this->year)
            ->whereIn('closed_by', $this->selectedStaff)
            ->whereIn(DB::raw("LPAD(MONTH(arrives_time), 2, '0')"), $this->months)
            ->where('status', Queue::STATUS_CLOSE)
            ->groupBy('date')
            ->pluck('count', 'date'); // ['2025-06-01' => 12, '2025-06-02' => 5, ...]


        // 2. Collect feedbacks per day
        $feedbacksPerDay = $feedbacks->groupBy(function ($item) {
            return Carbon::parse($item->arrives_time)->toDateString();
        })->map->count(); // ['2025-06-01' => 4, '2025-06-02' => 2, ...]


        // 3. Merge all available dates
        $allDates = collect($queues->keys())
            ->merge($feedbacksPerDay->keys())
            ->unique()
            ->sort()
            ->values();

        // 4. Build chart array
        $dailyChartLabels = $allDates;
        $dailyGuestsServed = $allDates->map(fn($date) => $queues[$date] ?? 0);
        $dailyFeedbacks = $allDates->map(fn($date) => $feedbacksPerDay[$date] ?? 0);

       $feedbackByStaff = Rating::where('team_id', $this->teamId)
    ->where('location_id', $this->locationId)
    ->whereIn('user_id', $this->selectedStaff)
    ->whereYear('created_at', $this->year)
    ->whereIn(DB::raw("LPAD(MONTH(created_at), 2, '0')"), $this->months)
    ->select('user_id', DB::raw('COUNT(*) as count'))
    ->groupBy('user_id')
    ->pluck('count', 'user_id'); 

    $staffNames = User::whereIn('id', $feedbackByStaff->keys())
    ->pluck('name', 'id'); 

    $feedbackStaffLabels = $staffNames->values(); // ['Arman', 'John', ...]
$feedbackStaffCounts = $feedbackByStaff->mapWithKeys(fn($count, $id) => [$staffNames[$id] => $count])->values();

 $ratingBreakdown = Rating::where('team_id', $this->teamId)
    ->where('location_id', $this->locationId)
    ->whereIn('user_id', $this->selectedStaff)
    ->whereYear('created_at', $this->year)
    ->whereIn(DB::raw("LPAD(MONTH(created_at), 2, '0')"), $this->months)
    ->select('rating', DB::raw('COUNT(*) as count'))
    ->groupBy('rating')
    ->pluck('count', 'rating'); // [5 => 3, 4 => 2, ...]

$ratingCounts = collect([4, 3, 2, 1])->map(fn($star) => $ratingBreakdown[$star] ?? 0);

$this->dispatch('refreshCharts', [
    'chartLabels' => $chartData['labels'],
    'chartData' => $chartData['data'],
    'dailyLabels' => $dailyChartLabels,
    'dailyFeedbacks' => $dailyFeedbacks,
    'dailyServed' => $dailyGuestsServed,
    'feedbackStaffLabels' => $feedbackStaffLabels,
    'feedbackStaffCounts' => $feedbackStaffCounts,
    'ratingCounts' => $ratingCounts,
]);

        return view('livewire.dashboard-summary', [
            'totalServedQueues' => $totalServedQueues,
            'formattedServedTime' => $formattedServedTime,
            'formattedWaitingTime' => $formattedWaitingTime,
            'feedbackCount' => $feedbackCount,
            'avgRating' => round($avgRating),
            'staffData' => $this->staff,
            'chartData' => $chartData,
            'avgTxnTimePerStaff' => $avgTxnTimePerStaff,

            // Add these for the line chart
            'dailyLabels' => $dailyChartLabels,
            'dailyGuestsServed' => $dailyGuestsServed,
            'dailyFeedbacks' => $dailyFeedbacks,

             'feedbackStaffLabels' => $feedbackStaffLabels,
                'feedbackStaffCounts' => $feedbackStaffCounts,
                'ratingCounts' => $ratingCounts,
        ]);
    }
}
