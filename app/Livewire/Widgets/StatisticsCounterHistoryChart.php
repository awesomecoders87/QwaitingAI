<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use App\Models\Counter;
use App\Models\Queue;
use App\Models\QueueStorage;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use Illuminate\Support\Number;

class StatisticsCounterHistoryChart extends Component
{

    public ?string $fromSelectedDate;
    public ?string $toSelectedDate;

    protected static ?string $heading = 'Counter History';

    public $location;
    public $teamId;
    public $chartDataCounter = [];

    // public static function canView(): bool
    // {
    //     return request()->is('dashboard') && Auth::check() && Auth::user()->isAdmin();
    // }

    public function getHeading(): ?string
    {
        return __('text.counter history');
    }


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
        $this->dispatch('updateChartDataCounter', $this->chartDataCounter); // Ensure this method fetches data based on the updated dates
    }

    public function getData(): array
	{
		// ----------------------------
		// EAGER LOAD: Counters with related QueueStorage
		// ----------------------------
		// Load counters along with their related QueueStorage in the date range to avoid N+1 queries
		$counters = Counter::withCount(['queueStorages as queues_count' => function ($query) {
				$query->where('locations_id', $this->location)
					  ->whereDate('arrives_time', '>=', date('Y-m-d', strtotime($this->fromSelectedDate)))
					  ->whereDate('arrives_time', '<=', date('Y-m-d', strtotime($this->toSelectedDate)));
			}])
			->where('team_id', $this->teamId)
			->whereJsonContains('counter_locations', $this->location)
			->get();

		$dataPoints = [];
		$counterNames = [];

		foreach ($counters as $counter) {
			$counterNames[] = $counter->name;

			// Use the eager loaded count instead of querying inside the loop
			$dataPoints[] = $counter->queues_count;
		}

		return $this->chartDataCounter = [
			'label' => __('text.Calls'),
			'data' => $dataPoints,
			'backgroundColor' => "#8daced",
			'borderColor' => "#8daced",
			'borderWidth' => 1,
			'hoverOffset' => 3,
			'labels' => $counterNames,
		];
	}



    public function render()
    {
        return view('livewire.widgets.statistics-counter-history-chart');
    }


}
