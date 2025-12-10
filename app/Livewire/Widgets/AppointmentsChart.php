<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Booking;
use Auth;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;

class AppointmentsChart extends Component
{
    public $filter;
    public $location;   
    public $teamId;
    public $appointmentchart = [];

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

    #[On('appointFilter')]
    public function updateFilter($filter)
    { 
        $this->filter = $filter;
        $this->refreshData();
    }

    public function refreshData()
    {

        $this->getData();
        $this->dispatch('updateAppChat', $this->appointmentchart); // Ensure this method fetches data based on the updated dates
    }

    protected function getData(): array
    {

        $filter = $this->filter;

        // ✅ Add eager loading here
			$query = Booking::with([
					'categories:id,name',
					'sub_category:id,name',
					'child_category:id,name',
					'staff:id,name',
					'createdBy:id,name'
				])
				->where('team_id', $this->teamId)
				->where('location_id', $this->location);
        if ($filter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'this_week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        $appointments = $query->get()->toArray();

        $appointmentType = [
            __('text.Confirmed') => 0,
            __('text.Cancelled') => 0,
            __('text.Completed') => 0
        ];

        foreach($appointments as $appointment)
        {
            if(isset($appointmentType[$appointment['status']]))
            {
                $appointmentType[$appointment['status']]++;
            }
        }

        $backgroundColors = [
            '#FF6384', // Soft Red
            '#36A2EB', // Blue
            '#FFCE56', // Yellow
        ];

        $hoverBackgroundColors = [
            '#E55373', // Darker Red on Hover
            '#2F92D1', // Darker Blue on Hover
            '#E5B950', // Darker Yellow on Hover
        ];

        // return [
        //     'labels' => array_keys($appointmentType),
        //     'datasets' => [
        //         [
        //             'label' => 'Appointments',
        //             'data' => array_values($appointmentType),
        //             'backgroundColor' => array_slice($backgroundColors, 0, count($appointmentType)),
        //             'hoverBackgroundColor' => array_slice($hoverBackgroundColors, 0, count($appointmentType)),
        //             'borderColor' => array_slice($backgroundColors, 0, count($appointmentType)),
        //             'borderWidth' => 1,
        //             'barThickness' => 40,
        //         ],
        //     ],
        // ];

      return  $this->appointmentchart = [
            'labels' => array_keys($appointmentType),
            'label' => __('text.Appointments'),
            'data' => array_values($appointmentType),
            'backgroundColor' => array_slice($backgroundColors, 0, count($appointmentType)),
            'hoverBackgroundColor' => array_slice($hoverBackgroundColors, 0, count($appointmentType)),
            'borderColor' => array_slice($backgroundColors, 0, count($appointmentType)),
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
