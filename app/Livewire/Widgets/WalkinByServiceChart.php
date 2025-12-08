<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use App\Models\Category;
use Carbon\Carbon;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class WalkinByServiceChart extends Component
{
    public $filter;
    public $location;   
    public $teamId;
    public $chartWalkinQueueService = [];
   
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
    public function updateFilter($filter)
    { 
        $this->filter = $filter;
        $this->refreshData();
    }

    public function refreshData()
    {

        $this->getData();
        $this->dispatch('updateWalkinQueueService', $this->chartWalkinQueueService); // Ensure this method fetches data based on the updated dates
        
    }


    // protected function getData(): array
    // {

    //     $filter = $this->filter;

    //     if($filter == 'weekly')
    //     {
    //         $startOfWeek = Carbon::now()->startOfWeek(); // Monday
    //         $endOfWeek = Carbon::now()->endOfWeek();     // Sunday

    //         $getVisitors = Category::withCount(['queues' => function ($query) use ($startOfWeek, $endOfWeek) {
    //             $query->where('team_id', Auth::user()->team_user_id);
    //             $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
    //         }])->whereHas('queues')->get()->toArray();
    //     }
    //     else
    //     {
    //         $startOfMonth = Carbon::now()->startOfMonth();
    //         $endOfMonth = Carbon::now()->endOfMonth();

    //         $getVisitors = Category::withCount(['queues' => function ($query) use ($startOfMonth, $endOfMonth) {
    //             $query->where('team_id', Auth::user()->team_user_id)
    //                 ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
    //         }])->whereHas('queues')->get();
    //     }

    //     $categoryNames = [];
    //     $visitorCount = [];

    //     // Vibrant color palette
    //     $backgroundColors = [
    //         '#FF6384', '#36A2EB', '#FFCE56', '#17A589', '#4BC0C0', '#9966FF', '#FF9F40',
    //         '#C9CBCF', '#5A5A5A', '#D4A017', 
    //     ];

    //     foreach($getVisitors as $visitor)
    //     {
    //         $categoryNames[] = $visitor['name'];
    //         $visitorCount[] = $visitor['queues_count'];
    //     }

    //     return [
    //     'datasets' => [
    //         [
    //             'data' => $visitorCount,
    //             'backgroundColor' => $backgroundColors,
    //             'hoverOffset' => 10, // Creates a pop-out effect on hover
    //             'borderColor' => '#ffffff', // White border for separation
    //             'borderWidth' => 2,
    //         ],
    //     ],
    //     'labels' => $categoryNames,
    // ];
    // }

   protected function getData(): array
	{
		$filter = $this->filter;
		$startDate = null;
		$endDate = null;

		if ($filter == 'today') {
			$startDate = Carbon::now()->startOfDay();
			$endDate = Carbon::now()->endOfDay();
		} elseif ($filter == 'this_week') {
			$startDate = Carbon::now()->startOfWeek();
			$endDate = Carbon::now()->endOfWeek();
		} elseif ($filter == 'this_month') {
			$startDate = Carbon::now()->startOfMonth();
			$endDate = Carbon::now()->endOfMonth();
		}

		// Eager load queues and count them
		$getVisitors = Category::with([
				'queues' => function ($query) use ($startDate, $endDate) {
					$query->where('team_id', $this->teamId)
						  ->where('locations_id', $this->location)
						  ->whereBetween('created_at', [$startDate, $endDate]);
				}
			])
			->withCount(['queues' => function ($query) use ($startDate, $endDate) {
				$query->where('team_id', $this->teamId)
					  ->where('locations_id', $this->location)
					  ->whereBetween('created_at', [$startDate, $endDate]);
			}])
			->whereHas('queues')
			->whereJsonContains('category_locations', "$this->location")
			->get();

		$categoryNames = [];
		$visitorCount = [];

		$backgroundColors = [
			'#FF6384', '#36A2EB', '#FFCE56', '#17A589', '#4BC0C0', '#9966FF', '#FF9F40',
			'#C9CBCF', '#5A5A5A', '#D4A017',
		];

		foreach ($getVisitors as $visitor) {
			$categoryNames[] = $visitor->name;
			$visitorCount[] = $visitor->queues_count; // Uses eager loaded count
		}

		return $this->chartWalkinQueueService = [
			'data' => $visitorCount,
			'backgroundColor' => $backgroundColors,
			'hoverOffset' => 10,
			'borderColor' => '#ffffff',
			'labels' => $categoryNames,
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
                'position' => 'right', // Moves legend to the right for better space usage
                'labels' => [
                    'font' => [
                        'size' => 14, // Bigger font for better readability
                    ],
                    'color' => '#333', // Dark color for contrast
                ],
            ],
        ],
        'layout' => [
            'padding' => 20, 
        ],
        'scales' => [
            'x' => [
                'display' => false, // Hides X-axis
            ],
            'y' => [
                'display' => false, // Hides Y-axis
            ],
        ],
        'elements' => [
            'arc' => [
                'borderWidth' => 2,
                'borderColor' => '#ffffff',
            ],
        ],
        'animation' => [
            'animateScale' => true,
            'animateRotate' => true,
        ],
        
    ];
}

    protected function getType(): string
    {
        return 'pie';
    }
}
