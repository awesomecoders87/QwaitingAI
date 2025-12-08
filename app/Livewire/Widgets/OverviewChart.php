<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use App\Models\Queue;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Number;
use Livewire\Attributes\On;

class OverviewChart extends Component
{
 
    // protected static ?string $heading = 'Overview';
    public ?string $fromSelectedDate;
    public ?string $toSelectedDate;
    protected static ?string $maxHeight = '240px';
    protected static ?string $minHeight = '240px';
    public $location;   
    public $teamId;
    public $chartDataOverview = [];
    public $enablePriority=false;
    public $subChildUsers;
 

    public function getHeading(): ?string
    {
        return __('text.overview');
    }


    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->fromSelectedDate =date('Y-m-d');
        $this->toSelectedDate = date('Y-m-d');

        $this->refreshData(); // Fetch initial data
    }

    #[On('fromSelectedDateChanged')]
    public function updateFromDate($fromSelectedDate, $enablePriority = false, $subChildUsers = [])
    { 
        $this->fromSelectedDate = $fromSelectedDate;
        $this->enablePriority = $enablePriority;
        $this->subChildUsers = collect($subChildUsers);
        $this->refreshData();
    }

    #[On('toSelectedDateChanged')]
    public function updateToDate($toSelectedDate, $enablePriority = false, $subChildUsers = [])
    {
        $this->toSelectedDate = $toSelectedDate;
        $this->enablePriority = (bool) $enablePriority;
        $this->subChildUsers = collect($subChildUsers);
        $this->refreshData();
    }

    public function refreshData()
    {

        $this->getData();
        $this->dispatch('updateChartDataOverview', $this->chartDataOverview); // Ensure this method fetches data based on the updated dates
    }

    protected function getData(): array
    {
        
    $location = Session::get('selectedLocation');

    $dataPoints = [];
    $counts = QueueStorage::where('locations_id',$location)
    ->where('team_id', $this->teamId)
    ->when($this->enablePriority && $this->subChildUsers->isNotEmpty(), function ($query) {
        return $query->whereIn('assign_staff_id', $this->subChildUsers->pluck('id')->toArray());
    })
    ->whereDate('arrives_time', '>=', date('Y-m-d',strtotime($this->fromSelectedDate)))
    ->whereDate('arrives_time', '<=', date('Y-m-d',strtotime($this->toSelectedDate)))
    ->selectRaw('COUNT(CASE WHEN status = "Cancelled" THEN 1 END) as cancelled')
    ->selectRaw('COUNT(CASE WHEN status = "Pending" THEN 1 END) as pending')
    ->selectRaw('COUNT(CASE WHEN status = "Close" THEN 1 END) as close')
    ->selectRaw('COUNT(CASE WHEN is_missed = "1" THEN 1 END) as skip')
    // ->selectRaw('COUNT(CASE WHEN queues.status = "Progress" THEN 1 END) as progress')
    // ->selectRaw('COUNT(CASE WHEN queues.status = "Skip" THEN 1 END) as skip')
    ->first()
    ->toArray();

    $dataPoints = [
    $counts['cancelled'],
    $counts['skip'],
    $counts['close'],
    $counts['pending'],
    ];
   ;
      

        $this->chartDataOverview = [
            'label' => __('text.Calls'),
            'data' =>$dataPoints,
            'backgroundColor' => [
                'rgba(75, 192, 192, 0.2)',   // Pending
                'rgba(54, 162, 235, 0.2)',   // Close
                'rgba(255, 206, 86, 0.2)',   // Progress
                'rgba(153, 102, 255, 0.2)'   // Skip
            ],
            'borderColor' => [
                'rgba(75, 192, 192, 1)',   // Pending
                'rgba(54, 162, 235, 1)',   // Close
                'rgba(255, 206, 86, 1)',   // Progress
                'rgba(153, 102, 255, 1)'   // Skip
            ],
            'borderWidth' => 1,
            'hoverOffset' => 3,
          'labels' =>  [
            __('text.Cancelled'),
            __('text.Skip'),
            __('text.Served'),
            __('text.Pending'),
          ],
       ];

return $this->chartDataOverview;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
