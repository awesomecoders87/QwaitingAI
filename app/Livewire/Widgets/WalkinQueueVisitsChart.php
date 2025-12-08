<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class WalkinQueueVisitsChart extends Component
{
    public $filter;
    public $location;   
    public $teamId;
    public $chartWalkinQueue = [];

    /**
     * Ensure the widget starts with "Weekly" filter on load
     */
    public function mount(): void
    {
        
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->filter = "today"; // Default to weekly view
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
        $this->dispatch('updateWalkinQueue', $this->chartWalkinQueue); // Ensure this method fetches data based on the updated dates
        
    }

    /**
     * Get chart data based on selected filter
     */

    protected function getData(): array
   {
    $filter = $this->filter;

    if ($filter === 'today') {
        // Fetch today's visitor data
        $today = Carbon::today();

        $todayData = QueueStorage::whereDate('created_at', $today)
            ->where('team_id', $this->teamId)
            ->where('locations_id',$this->location)
            ->where('ticket_mode', 'Walk-IN')
            ->count();

       
     return   $this->chartWalkinQueue = [
            'labels' => [__('text.Today')],
            'label' => 'Total Visitors',
            'data' => [$todayData],
            'backgroundColor' => 'rgba(255, 99, 132, 0.2)', // Semi-transparent fill
            'borderColor' => 'rgb(255, 99, 132)', // Solid border
            'borderWidth' => 2
                
        ];
    } 
    elseif ($filter === 'this_week') {
        // Get start and end of the current week
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $endOfWeek = Carbon::now()->endOfWeek();     // Sunday

        // Fetch visitors grouped by weekday
        $weeklyData = QueueStorage::selectRaw('COUNT(id) as total, DAYNAME(created_at) as weekday')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('team_id', $this->teamId)
            ->where('locations_id',$this->location)
            ->where('ticket_mode', 'Walk-IN')
            ->groupBy('weekday')
            ->get();

        // Ensure correct weekday order
        $weekdays = [
                    __('text.Monday'),
                    __('text.Tuesday'),
                    __('text.Wednesday'),
                    __('text.Thursday'),
                    __('text.Friday'),
                    __('text.Saturday'),
                    __('text.Sunday'),
                ];

        $dataPoints = array_fill(0, 7, 0); // Initialize with zero counts

        foreach ($weeklyData as $data) {
            $index = array_search($data->weekday, $weekdays);
            if ($index !== false) {
                $dataPoints[$index] = $data->total;
            }
        }

        
        return   $this->chartWalkinQueue = [
            'labels' => $weekdays,
            'label' => __('text.Total Visitors'),
            'data' => $dataPoints,
            'backgroundColor' => 'rgba(75, 192, 192, 0.2)', // Semi-transparent fill
            'borderColor' => 'rgb(75, 192, 192)', // Solid border
            'borderWidth' => 2,
            'tension' => 0.4

        ];
    } 
    elseif ($filter === 'this_month') {
        // Get start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Fetch visitors grouped by day in the month
        $monthlyData = QueueStorage::selectRaw('COUNT(id) as total, DATE_FORMAT(created_at, "%Y-%m") as month')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('team_id', $this->teamId)
            ->where('locations_id',$this->location)
            ->where('ticket_mode', 'Walk-IN')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

          

        $labels = [];
        $dataPoints = [];

        foreach ($monthlyData as $data) {
                        $labels[] = Carbon::createFromFormat('Y-m', $data->month)->format('F'); // Convert "2024-02" to "February"
                        $dataPoints[] = $data->total;
                    }
        

        foreach ($monthlyData as $data) {
            $labels[] = $data->day; // Numeric day of the month (1, 2, 3, ... 31)
            $dataPoints[] = $data->total;
        }

        return   $this->chartWalkinQueue = [
            'labels' => $labels,
            'label' => __('text.Total Visitors'),
            'data' => $dataPoints,
            'backgroundColor' => 'rgba(75, 192, 192, 0.2)', // Semi-transparent fill
            'borderColor' => 'rgb(75, 192, 192)', // Solid border
            'borderWidth' => 2,
            'tension' => 0.4

        ];
    }

 
}


    /**
     * Chart display options
     */
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
        return 'line';
    }
}
