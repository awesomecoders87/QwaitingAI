<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class AppointmentsByServicesChart  extends Component
{
   

    public $filter;
    public $location;   
    public $teamId;
    public $chartAppointmentsByService = [];

    public function mount(): void
    {
        
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->filter = 'today'; // Default to weekly view
        $this->refreshData(); // Fetch initial data
    }
   
    #[On('appointFilter')]
    public function updateFilter($filter)
    { 
        $this->filter = $filter;
        $this->refreshData();
    }

    public function refreshData()
    {

        $this->getData();
        $this->dispatch('updateAppointmentsByService', $this->chartAppointmentsByService); // Ensure this method fetches data based on the updated dates
        
    }

    protected function getData(): array
{
    $filter = $this->filter ?? 'today';

    // Fetch categories with related bookings based on filter
    $query = Category::with([
    'bookings' => function ($bookingQuery) use ($filter) {
        // 👉 Add eager loading for booking relationships here
				$bookingQuery->with([
					'service:id,name',
					'category:id,name',
					'customer:id,name',
					'agent:id,name'
				]);

				if ($filter == 'today') {
					$bookingQuery->whereDate('created_at', Carbon::today());
				} elseif ($filter == 'this_week') {
					$bookingQuery->whereBetween('created_at', [
						Carbon::now()->startOfWeek(),
						Carbon::now()->endOfWeek()
					]);
				} elseif ($filter == 'this_month') {
					$bookingQuery->whereMonth('created_at', Carbon::now()->month)
								 ->whereYear('created_at', Carbon::now()->year);
				}

				$bookingQuery->where('location_id', $this->location);
			}
		])
		->whereHas('bookings')
		->whereJsonContains('category_locations', "$this->location")
		->where('team_id', $this->teamId)
		->get();

    // Prepare data
    $servicesNames = $query->pluck('name')->toArray();
    $appointmentsCount = $query->map(fn($service) => $service->bookings->count())->toArray();

    // Define colors
    $backgroundColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
    $hoverColors = ['#E55374', '#2B8CD3', '#E5B945', '#3AA7A7', '#8B5EC7'];

//   dd( [
//         'labels' => $servicesNames,
//         'datasets' => [
//             [
//                 'data' => $appointmentsCount,
//                 'backgroundColor' => $backgroundColors,
//                 'hoverBackgroundColor' => $hoverColors,
//                 'hoverOffset' => 12, // Creates a pop-out effect
//                 'borderColor' => '#fff',
//                 'borderWidth' => 2,
//             ],
//         ],
//     ]);

   return  $this->chartAppointmentsByService =[
               'labels' => $servicesNames,
                'data' => $appointmentsCount,
                'backgroundColor' => $backgroundColors,
                'hoverBackgroundColor' => $hoverColors,
                'hoverOffset' => 12, // Creates a pop-out effect
                'borderColor' => '#fff',
                'borderWidth' => 2,
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
                    'position' => 'right', // Move legend to the right for better visibility
                    'labels' => [
                        'font' => [
                            'size' => 14, // Bigger font for better readability
                            'weight' => 'bold'
                        ],
                        'color' => '#444', // Darker text color
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => '#222',
                    'titleFont' => [
                        'size' => 14,
                        'weight' => 'bold',
                    ],
                    'bodyFont' => [
                        'size' => 12,
                    ],
                    'bodyColor' => '#fff',
                    'cornerRadius' => 6,
                    'padding' => 10,
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
