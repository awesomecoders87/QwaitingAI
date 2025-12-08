<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Category;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class AppointmentsByTimeChart extends Component
{
    protected static ?string $heading = 'Appointments By Time of Day';

    protected static ?string $maxHeight = '300px';

    public $filter;
    public $location;   
    public $teamId;
    public $chartAppointmentsByTimeChart = [];

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
        $this->dispatch('updateAppointmentsByTimeChart', $this->chartAppointmentsByTimeChart); // Ensure this method fetches data based on the updated dates
        
    }
    protected function getData(): array
    {
        $filter = $this->filter ?? 'today';

        // Fetch appointments with eager loading
		$appointmentsQuery = Booking::with([
            'service:id,name',
            'category:id,name',
            'customer:id,name',
            'agent:id,name'
        ])
        ->where('team_id', $this->teamId)
        ->where('location_id', $this->location);

        if ($filter === 'today') {
            $appointmentsQuery->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'this_week') {
            $appointmentsQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'this_month') {
            $appointmentsQuery->whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year);
        }

        $appointments = $appointmentsQuery->get()
            ->groupBy(fn ($booking) => Carbon::parse($booking->created_at)->format('H')); // Group by hour (24-hour format)

        // Prepare data for chart
        $hours = range(0, 23); // 24-hour range (0 - 23)
        $labels = array_map(fn ($hour) => Carbon::createFromTime($hour)->format('g A'), $hours); // Convert to 12-hour format
        $data = array_map(fn ($hour) => isset($appointments[$hour]) ? max(0, count($appointments[$hour])) : 0, $hours);

        // dd( [
        //     'labels' => $labels,
        //     'datasets' => [
        //         [
        //             'label' => 'Total Visitors',
        //             'data' => $data,
        //             'backgroundColor' => 'rgba(75, 192, 192, 0.2)', // Semi-transparent fill
        //             'borderColor' => 'rgb(75, 192, 192)', // Solid border
        //             'pointBackgroundColor' => 'rgba(75, 192, 192, 0.2)',
        //             'pointBorderColor' => 'rgb(75, 192, 192)',
        //             'pointHoverBackgroundColor' => 'rgb(75, 192, 192)',
        //             'pointHoverBorderColor' => 'rgb(75, 192, 192)',
        //             'borderWidth' => 2,
        //             'tension' => 0.4
        //         ],
        //     ],
        // ]);

      return  $this->chartAppointmentsByTimeChart = [
                    'labels' => $labels,
                    'label' => 'Total Visitors',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)', // Semi-transparent fill
                    'borderColor' => 'rgb(75, 192, 192)', // Solid border
                    'pointBackgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'pointBorderColor' => 'rgb(75, 192, 192)',
                    'pointHoverBackgroundColor' => 'rgb(75, 192, 192)',
                    'pointHoverBorderColor' => 'rgb(75, 192, 192)',
                    'borderWidth' => 2,
                    'tension' => 0.4
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
            'plugins' => [
                'legend' => ['display' => true],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Area chart is a filled line chart
    }
}
