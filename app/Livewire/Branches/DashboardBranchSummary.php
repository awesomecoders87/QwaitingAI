<?php
// Livewire component: DashboardSummary.php

namespace App\Livewire\Branches;

use Livewire\Component;
use App\Models\Rating;
use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use App\Models\Queue;
use App\Models\QueueStorage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class DashboardBranchSummary extends Component
{
    public $teamId;
    public $locationId;
    public $years;
    public $year;
    public $months = [];
    public $staff = [];
    public $allLocation = [];
    public $selectedStaff = [];
    public $selectedLocation = [];
    public $selectedAll = true;
    public $selectedAllLocation = true;
    public $selectedallMonth = false;



    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId =  Session::get('selectedLocation');
        $this->allLocation =  Location::where('team_id',$this->teamId)->where('status',1)->select('id','location_name')->get();
        $this->year = date('Y');
        $currentMonth = date('m'); // '01' to '12'
        $this->months = [$currentMonth];
        $this->getFilteredLocation();
        $currentYear = date('Y');
        $this->years = range($currentYear-2, $currentYear);
    
       
    }

       public function getFilteredLocation()
    { 
         if (empty($this->selectedLocation) || count($this->selectedLocation) !== $this->allLocation->count()) {
        $this->selectedLocation = $this->allLocation->pluck('id')->toArray();
       
    }

      if (!empty($this->selectedLocation) || count($this->selectedLocation) == $this->allLocation->count()) {
        $this->selectedAllLocation =  true;
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

public function toggleAllLocation()
{
    if (count($this->selectedLocation) < $this->allLocation->count()) {
        $this->selectedLocation = $this->allLocation->pluck('id')->toArray();
       $this->selectedAllLocation =false;
    } else {
        $this->selectedLocation = [];
       $this->selectedAllLocation =false;
    }

     if (!empty($this->selectedLocation) || count($this->selectedLocation) == $this->allLocation->count()) {
       $this->selectedAllLocation =  true;
       
    }
}



        public function updatedselectedLocation(){
            if (count($this->selectedLocation) < $this->allLocation->count()) {
                $this->selectedAllLocation =false;
            }else{
                $this->selectedAllLocation =true;

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
        $feedbackLabels = [];
        $feedbackCounts = [];
        $avgWaitingTimes = [];
        $avgTransactionTimes = [];
        $ratingLabels = [];
        $ratingPercentages = [];

        $feedbacks = Rating::whereIn('location_id', $this->selectedLocation)
    ->whereYear('created_at', $this->year)
    ->when(!empty($this->months), function ($query) {
        $query->whereIn(\DB::raw("LPAD(MONTH(created_at), 2, '0')"), $this->months);
    })
    ->get();

         $feedbackCount = $feedbacks->count();

        $totalRating = $feedbacks->avg('rating');

        // Each rating is out of 4, so max possible = $feedbackCount * 4
        $overallavgRating = $feedbackCount > 0
            ? round(($totalRating / 5) * 100,1)
            : 0;// Assume 1-4 stars, scaled to 100%

     $feedbackByLocation = $feedbacks->groupBy('location_id');


        foreach ($feedbackByLocation as $locationId => $items) {
            $locationName = optional($items->first()->location)->location_name ?? 'Unknown';

            // Total feedback count
            $feedbackLabels[] = $locationName;
            $feedbackCounts[] = $items->count();

            // Average rating %
            $avgRating = $items->avg('rating'); // average out of 5
            $percentage = round(($avgRating / 5) * 100, 1); // convert to percent
            $ratingLabels[] = $locationName;
            $ratingPercentages[] = $percentage;
        }

        $servedQueues = QueueStorage::with('location')->whereYear('arrives_time', $this->year)
            ->when(!empty($this->months), function ($query) {
                $query->whereIn(\DB::raw("LPAD(MONTH(arrives_time), 2, '0')"), $this->months);
            })
            ->where('status', Queue::STATUS_CLOSE)
             ->whereIn('locations_id',$this->selectedLocation) // ← location filter here
            ->get();
          
            $totalServedQueues = $servedQueues->count();
  
          $totalTime = 0;
          $totalwaitingTime = 0;
          $count = 0;
       foreach ($servedQueues as $queue) {
            if ($queue->start_datetime && $queue->closed_datetime) {
                $totalTime += abs($queue->closed_datetime->diffInSeconds($queue->start_datetime));
                $count++;
            }

             if ($queue->start_datetime && $queue->closed_datetime) {
                $totalwaitingTime += abs(Carbon::parse($queue->start_datetime)->diffInSeconds($queue->arrives_time));
            }
        }

            // --- Calculate Served Time and Waiting Time ---

            $formattedServedTime = $count > 0 ? \Carbon\CarbonInterval::seconds(round($totalTime/$count))->cascade()->format('%H:%I:%S') : '00:00:00';
             $formattedWaitingTime = $count > 0
            ? \Carbon\CarbonInterval::seconds(round($totalwaitingTime/$count))->cascade()->format('%H:%I:%S')
            : '00:00:00';

     
    // Guests Served by Office - Pie Chart
    $groupedByLocation = $servedQueues->groupBy('locations_id');
    $chartLabels = [];
    $chartData = [];


    $avgWaitingTimeByOffice = [];
    $avgTransactionTimeByOffice = [];
      
    foreach ($groupedByLocation as $locationId => $queues) {
        $totalWait = 0;
        $totalTxn = 0;
        $validCount = 0;
        $waitCount = 0;
        $txCount = 0;

    $locationName = optional($queues->first()->location)->location_name ?? 'Unknown';
    $locationCount = $queues->count();
    $percentage = $totalServedQueues > 0 ? round(($locationCount / $totalServedQueues) * 100, 1) : 0;

    $chartLabels[] = "{$locationName}: {$locationCount} ({$percentage}%)";
    $chartData[] = $locationCount;

     foreach ($queues as $queue) {
            if ($queue->arrives_time && $queue->start_datetime) {
                $totalWait += abs(Carbon::parse($queue->start_datetime)->diffInSeconds($queue->arrives_time));
                $waitCount++;
            }

            if ($queue->start_datetime && $queue->closed_datetime) {
                $totalTxn += abs(Carbon::parse($queue->closed_datetime)->diffInSeconds($queue->start_datetime));
                 $txCount++;
            }

            $validCount++;
        }

        
        $avgWaitingTimeByOffice[] = [
            'location' => optional($queues->first()->location)->location_name ?? 'Unknown',
            'time' => $validCount > 0 ? Carbon::now()->startOfDay()->addSeconds(round($totalWait / $validCount))->format('H:i:s') : '00:00:00',
        ];

        $avgTransactionTimeByOffice[] = [
            'location' => optional($queues->first()->location)->location_name ?? 'Unknown',
            'time' => $validCount > 0 ? Carbon::now()->startOfDay()->addSeconds(round($totalTxn / $validCount))->format('H:i:s') : '00:00:00',
        ];

          // Waiting & Transaction Time by Office
      

    $avgWaitingTimes[$locationName] = [
        'office' => $locationName,
        'time' => $waitCount > 0 ? \Carbon\CarbonInterval::seconds(round($totalWait / $validCount))->cascade()->format('%H:%I:%S') : '00:00:00',
    ];

    $avgTransactionTimes[$locationName] = [
        'office' => $locationName,
        'time' => $txCount > 0 ? \Carbon\CarbonInterval::seconds(round($totalTxn / $validCount))->cascade()->format('%H:%I:%S') : '00:00:00',
    ];
}
  
        $this->dispatch('refreshCharts', [
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
        'feedbackLabels' => $feedbackLabels,
        'feedbackCounts' => $feedbackCounts,
        'ratingLabels' => $ratingLabels,
        'ratingPercentages' => $ratingPercentages,
    ]);



        return view('livewire.branches.dashboard-branch-summary', [
            'totalServedQueues' =>  $totalServedQueues,
            'formattedServedTime' => $formattedServedTime,
            'formattedWaitingTime' => $formattedWaitingTime,
            'feedbackCount' => $feedbackCount,
            'avgRating' =>  $overallavgRating,
            'avgWaitingTimes' => $avgWaitingTimes,
            'avgTransactionTimes' => $avgTransactionTimes,
        
        ]);
    }
}
