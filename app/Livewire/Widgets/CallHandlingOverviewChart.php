<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use App\Models\QueueStorage;
use Auth;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;

class CallHandlingOverviewChart extends Component
{

    public $filter;
    public $location;   
    public $teamId;
    public $chartCallHandling = [];
    public $subChildUsers = [];
    public $enablePriority=false;
   

    /**
     * Ensure the widget starts with "Weekly" filter on load
     */
    public function mount(): void
    {
        
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->filter = 'today'; // Default to weekly view
        $this->refreshData(); // Fetch initial data
    }

    #[On('walkInFilter')]
    public function updateFilter($filter, $enablePriority = false, $subChildUsers = [])
    { 
        
        $this->filter = $filter;
        $this->subChildUsers = collect($subChildUsers);
        $this->enablePriority = $enablePriority;
        $this->refreshData();
    }

    public function refreshData()
    {

        $this->getData();
        $this->dispatch('updateCallHandling', $this->chartCallHandling); // Ensure this method fetches data based on the updated dates
    }


    protected function getData(): array
    {

        $filter = $this->filter;

        $query = QueueStorage::where('team_id', $this->teamId)->where('locations_id',$this->location)->where('ticket_mode', 'Walk-IN');
      
        if ($filter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'this_week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

       
        if($this->enablePriority && !empty($this->subChildUsers)) {
             $query->whereIn('assign_staff_id', $this->subChildUsers->pluck('id')->toArray());
        }

        $walkinQueueVisits = $query->get()->toArray();

        $queueStatus = [
            __('text.Served') => 0,
            __('text.Missed') => 0,
            __('text.Cancelled') => 0,
            __('text.Pending') => 0
        ];

        foreach($walkinQueueVisits as $visit)
        {

            if($visit['status'] != 'Close')
            {
                $queueStatus[__('text.Served')] += 1; 
            }

            if($visit['status'] == 'Skip')
            {
                $queueStatus[__('text.Missed')] += 1;
            }

            if($visit['status'] == 'Cancelled')
            {
                $queueStatus[__('text.Cancelled')] += 1;
            }

            if($visit['status'] == 'Pending')
            {
                $queueStatus[__('text.Pending')] += 1;
            }

        }

        $backgroundColors = [
            '#FF6384', // Soft Red
            '#36A2EB', // Blue
            '#9966FF', // Purple
            '#FF9F40', // Orange
        ];

        $hoverBackgroundColors = [
            '#E55373', // Darker Red on Hover
            '#2F92D1', // Darker Blue on Hover
            '#8E5CE6', // Darker Purple on Hover
            '#E58935', // Darker Orange on Hover
        ];

    //    dd([
    //     'labels' => array_keys($queueStatus),
    //     'label' => 'Calls',
    //     'data' => array_values($queueStatus),
    //     'backgroundColor' => array_slice($backgroundColors, 0, count($queueStatus)),
    //     'hoverBackgroundColor' => array_slice($hoverBackgroundColors, 0, count($queueStatus)),
    //     'borderColor' => array_slice($backgroundColors, 0, count($queueStatus)),
    //     'borderWidth' => 1,
    //     'barThickness' => 40,
        
    //    ]);

       return  $this->chartCallHandling =[
        'labels' => array_keys($queueStatus),
        'label' => __('text.Calls'),
        'data' => array_values($queueStatus),
        'backgroundColor' => array_slice($backgroundColors, 0, count($queueStatus)),
        'hoverBackgroundColor' => array_slice($hoverBackgroundColors, 0, count($queueStatus)),
        'borderColor' => array_slice($backgroundColors, 0, count($queueStatus)),
        'borderWidth' => 1,
        'barThickness' => 40,
        
       ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold'
                        ],
                        'color' => '#333',
                        'boxWidth' => 20,
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => '#333',
                    'titleFont' => [
                        'size' => 14,
                        'weight' => 'bold'
                    ],
                    'bodyFont' => [
                        'size' => 12,
                    ],
                    'bodyColor' => '#fff',
                    'cornerRadius' => 6,
                    'padding' => 10,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false], // Hide grid lines
                    'ticks' => [
                        'font' => ['size' => 14, 'weight' => 'bold'],
                        'color' => '#666',
                    ],
                ],
                'y' => [
                    'grid' => ['color' => '#ddd'],
                    'ticks' => [
                        'beginAtZero' => true,
                        'font' => ['size' => 12, 'weight' => 'bold'],
                        'color' => '#666',
                    ],
                ],
            ],
            'elements' => [
                'bar' => [
                    'borderRadius' => 8, // Rounded edges
                    'borderWidth' => 1,
                    'hoverBorderWidth' => 2,
                ],
            ],
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeOutBounce',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
