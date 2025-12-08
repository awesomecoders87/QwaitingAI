<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class StatisticsSummaryTimeChart extends Component
{
    public ?string $fromSelectedDate = null;
    public ?string $toSelectedDate = null;
    public ?string $fromSelectedTime = null;
    public ?string $toSelectedTime = null;

    public $location;
    public $teamId;
    public array $chartData = [];

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');

        $this->fromSelectedDate = date('Y-m-d');
        $this->toSelectedDate = date('Y-m-d');

        $this->fromSelectedTime = "08:00";
        $this->toSelectedTime = "20:00";

        $this->refreshData();
    }

    // public function updated($property)
    // {
    //     if (in_array($property, ['fromSelectedDate', 'toSelectedDate', 'fromSelectedTime', 'toSelectedTime'])) {
    //         $this->getData();
    //     }
    // }

    #[On('fromSelectedDateChanged')]
    public function updateFromDate($fromSelectedDate)
    {
     
        $this->fromSelectedDate = $fromSelectedDate;
        $this->refreshData();
    }

    #[On('toSelectedDateChanged')]
    public function updateToDate($toSelectedDate)
    {

        $this->toSelectedDate = $toSelectedDate;
        $this->refreshData();
    }
    #[On('fromSelectedTimeChanged')]
    public function updateFromTime($fromSelectedTime)
    {
        $this->fromSelectedTime = $fromSelectedTime;
        $this->refreshData();
    }

    #[On('toSelectedTimeChanged')]
    public function updateToTime($toSelectedTime)
    {

        $this->toSelectedTime = $toSelectedTime;
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->getData();
        $this->dispatch('updateChartData', $this->chartData); // Ensure this method fetches data based on the updated dates
    }

    // protected function getData(): void
    // {
   
    //     $timeSlots = $this->generateTimeSlots($this->fromSelectedTime, $this->toSelectedTime, 60);
    //     $dataPoints = [];
    
    //     $startDate = Carbon::parse($this->fromSelectedDate)->startOfDay();
    //     $endDate = Carbon::parse($this->toSelectedDate)->endOfDay();
    
    //     $domainSlug = Team::getSlug();
    //     $teamId = Team::getTeamId($domainSlug);
    //     $waiting_count = 0;
    //     foreach ($timeSlots as $timeSlot) {
    //         $transactionTimes = [];
    //         $waitingTimes = [];
    //         $arrivedCounts = [];
    //         $calledCounts = [];
    //         $waitingCounts = [];
    //         $currentDate = $startDate->copy();
    //         $arrived_count =0;
    //         $called_count =0;
    //         while ($currentDate->lte($endDate)) {
    //             $startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $timeSlot['start']);
    //             $endTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $timeSlot['end']);
    
    //             $arrived_count += QueueStorage::where('team_id', $this->teamId)
    //                 ->where('locations_id', $this->location)
    //                 ->whereBetween('arrives_time', [$startTime, $endTime])
    //                 ->count();
    //             $called_count += QueueStorage::where('team_id', $this->teamId)
    //                 ->where('locations_id', $this->location)
    //                 ->whereBetween('called_datetime', [$startTime, $endTime])
    //                 ->count();
    //             $percentage = $arrived_count > 0 ? ($called_count / $arrived_count) * 100 : 0;
    //             $waiting_arrived_count = QueueStorage::where('team_id', $this->teamId)
    //                 ->where('locations_id', $this->location)
    //                 ->whereBetween('arrives_time', [$startTime, $endTime])
    //                 ->count();
    //             $waiting_called_count = QueueStorage::where('team_id', $this->teamId)
    //                 ->where('locations_id', $this->location)
    //                 ->whereBetween('called_datetime', [$startTime, $endTime])
    //                 ->count();
                
    //             $waiting_count += $waiting_arrived_count - $waiting_called_count;
    
    //             $currentDate->addDay();
    //         }
    //         $arrivedCounts = $arrived_count;
    //         $calledCounts = $called_count;
    //         $waitingCounts = $waiting_count;
    //         // $waitingCountsper = $percentage;
    //         $dataPoints[] = [
    //             'time_slot' => $timeSlot['start'] . '-' . $timeSlot['end'],
    //             'arrived_count' => $arrivedCounts,
    //             'called_count' => $calledCounts,
    //             'waiting_count' => $waitingCounts,
    //             'percentage' => number_format($percentage ?? 0, 2, '.', ''),
    //             'transaction_time' => [
    //                 'max' => count($transactionTimes) > 0 ? max($transactionTimes) : '00:00:00',
    //                 'average' => count($transactionTimes) > 0 ? $this->calculateAverageTime($transactionTimes) : '00:00:00',
    //             ],
    //             'waiting_time' => [
    //                 'max' => count($waitingTimes) > 0 ? max($waitingTimes) : '00:00:00',
    //                 'average' => count($waitingTimes) > 0 ? $this->calculateAverageTime($waitingTimes) : '00:00:00',
    //             ],
    //         ];
    //     }
    
    //     $this->dataPoints = $dataPoints;

    // }

    private function generateTimeSlots(string $start, string $end, int $interval): array
    {
      
        $times = [];
        $current = strtotime($start);
        $end = strtotime($end);

        while ($current < $end) {
            $next = strtotime("+$interval minutes", $current);
            $times[] = [
                'start' => date('H:i', $current),
                'end' => date('H:i', $next)
            ];
            $current = $next;
        }

        return $times;
    }

    private function calculateAverageTime(array $times): string
{
    $totalSeconds = array_sum(array_map(function ($time) {
        $timeParts = explode(':', $time);
        return $timeParts[0] * 3600 + $timeParts[1] * 60 + $timeParts[2];
    }, $times));

    $averageSeconds = $totalSeconds / count($times);
    return gmdate('H:i:s', $averageSeconds);
}


public function getData()
{
    $timeSlots = $this->generateTimeSlots($this->fromSelectedTime, $this->toSelectedTime, 60);

    $startDate = Carbon::parse($this->fromSelectedDate)->startOfDay();
    $endDate = Carbon::parse($this->toSelectedDate)->endOfDay();
    $arrivedCounts = [];
    $calledCounts = [];
    $waitingCounts = [];
    $label = [];

    $waiting =0;
    foreach ($timeSlots as $timeSlot) {
        $currentDate = $startDate->copy();
        $arrived = 0;
        $called = 0;
        while ($currentDate->lte($endDate)) {
            $startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $timeSlot['start']);
            $endTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $timeSlot['end']);

        // Fetch correct arrived count
        $arrived += QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [$startTime, $endTime])
            ->count();

        // Fetch correct called count
        $called += QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('called_datetime', [$startTime, $endTime])
            ->count();

       
        // Format the label correctly (e.g., 08:00 AM - 09:00 AM)
        
        $currentDate->addDay();
    }
    if($waiting <= $called){
        $waiting = 0;
    }
     // Calculate waiting count
     $waiting += max($arrived - $called, 0);

    $label[] = $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A');
        
        // Store the counts
        $arrivedCounts[] = $arrived;
        $calledCounts[] = $called;
        $waitingCounts[] = $waiting;
    }

    // Prepare chart data
    $this->chartData = [
        'labels' => $label,
        'datasets' => [
            [
                'label' => 'Arrived',
                'data' => $arrivedCounts,
                'borderColor' => 'rgba(46, 204, 113, 1)',
                'backgroundColor' => 'rgba(46, 204, 113, 0.2)',
                'borderWidth' => 2,
            ],
            [
                'label' => 'Called',
                'data' => $calledCounts,
                'borderColor' => 'rgba(5, 55, 195, 1)',
                'backgroundColor' => 'rgba(5, 55, 195, 0.2)',
                'borderWidth' => 2,
            ],
            [
                'label' => 'Waiting',
                'data' => $waitingCounts,
                'borderColor' => 'rgba(195, 163, 5, 1)',
                'backgroundColor' => 'rgba(195, 163, 5, 0.2)',
                'borderWidth' => 2,
            ],
        ],
    ];
// dd($this->chartData);
    // Dispatch event for chart update
    $this->dispatch('updateChartTimeData', $this->chartData);
}


    public function render()
    {
        return view('livewire.widgets.overview-per-time-period');
    }
}
