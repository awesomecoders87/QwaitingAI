<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\SiteDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Number;

class StatisticsCallHistoryChart extends Component
{

    public ?string $fromSelectedDate;
    public ?string $toSelectedDate;

    protected static ?string $heading = 'Call History';
    public $location;
    public $teamId;
    public $chartDataHistory = [];

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->fromSelectedDate = date('Y-m-d');
        $this->toSelectedDate =date('Y-m-d');
        $this->refreshData(); // Fetch initial data
    }

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

    public function refreshData()
    {
        $this->getData();
        $this->dispatch('updateChartDataHistory', $this->chartDataHistory); // Ensure this method fetches data based on the updated dates
    }

    public function getData(): array
	{
		$dataPoints = [];
		
		// ----------------------------
		// EAGER LOAD RELATIONSHIPS
		// ----------------------------
		// Add any relationships you plan to use later, e.g., Counter, servedBy, closedBy, assignStaff
		$counts = QueueStorage::with(['Counter', 'servedBy', 'closedBy', 'assignStaff']) // <-- Eager load relationships
			->where('team_id', $this->teamId)
			->where('locations_id', $this->location)
			->selectRaw('COUNT(CASE WHEN status = "Close" THEN 1 END) as served_count')
			->selectRaw('COUNT(CASE WHEN status = "Pending" THEN 1 END) as pending_count')
			->selectRaw('COUNT(CASE WHEN is_missed = 1 THEN 1 END) as missed_count')
			->whereDate('arrives_time', '>=', date('Y-m-d', strtotime($this->fromSelectedDate)))
			->whereDate('arrives_time', '<=', date('Y-m-d', strtotime($this->toSelectedDate)))
			->first()
			->toArray();

		$dataPoints = [
			$counts['served_count'],
			$counts['pending_count'],
			$counts['missed_count'],
		];

		// Define colors for each category
		$backgroundColor = [
			'#2ecc71', // Green for Served
			'#F44336', // Red for Pending
			'#FF9800', // Orange for Missed
		];

		return $this->chartDataHistory = [
			'label' => __('text.Calls'),
			'data' => $dataPoints,
			'backgroundColor' => $backgroundColor,
			'borderColor' => $backgroundColor,
			'borderWidth' => 1,
			'hoverOffset' => 3,
			'labels' => [__('text.Served'), __('text.Pending'), __('text.Missed')],
		];
	}


  
   
    public function render()
    {
        return view('livewire.widgets.statistics-call-history-chart');
    }

    
}
