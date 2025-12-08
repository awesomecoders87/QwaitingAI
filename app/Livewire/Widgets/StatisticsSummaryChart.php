<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use App\Models\Queue;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;


class StatisticsSummaryChart extends Component
{

    public ?string $fromSelectedDate;
    public ?string $toSelectedDate;

    public $teamId;
    public $location;
    public $chartData = [];

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->fromSelectedDate = date('Y-m-d');
        $this->toSelectedDate = date('Y-m-d');
        $this->refreshData(); // Fetch initial data
    }

    // public function fromSelectedDateChanged($newDate)
    // {
    //     $this->fromSelectedDate = $newDate;
    //     $this->getData(); // Fetch updated data
    // }

    // public function toSelectedDateChanged($newDate)
    // {
    //     $this->toSelectedDate = $newDate;
    //     $this->getData(); // Fetch updated data
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

    public function refreshData()
    {

        $this->getData();
        $this->dispatch('updateChartData', $this->chartData); // Ensure this method fetches data based on the updated dates
    }

  public function getData(): array
	{
		$startDate = Carbon::parse($this->fromSelectedDate)->startOfDay();
		$endDate = Carbon::parse($this->toSelectedDate)->endOfDay();

		$timeSlots = $this->generateTimeSlots('8:00 AM', '8:00 PM', 60);
		$dataPoints = [];

		// ----------------------------
		// EAGER LOAD QUEUESTORAGE RELATIONS
		// ----------------------------
		$records = QueueStorage::with(['Counter', 'servedBy', 'closedBy', 'assignStaff']) // <-- Eager load relationships
			->where('team_id', $this->teamId)
			->where('locations_id', $this->location)
			->whereBetween('arrives_time', [$startDate, $endDate])
			->get();

		foreach ($timeSlots as $timeSlot) {
			$countsPerSlot = 0;

			// Iterate through each day in the date range
			$currentDate = $startDate->copy();
			while ($currentDate->lte($endDate)) {
				$startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $timeSlot);
				$endTime = $startTime->copy()->addHour();

				// Filter records for the current time slot
				$count = $records->where('arrives_time', '>=', $startTime)
								 ->where('arrives_time', '<=', $endTime)
								 ->count();

				$countsPerSlot += $count;

				$currentDate->addDay();
			}

			$dataPoints[] = $countsPerSlot;
		}

		$this->chartData = [
			'label' => __('text.Calls'),
			'data' => $dataPoints,
			'labels' => $timeSlots,
		];

		return $this->chartData;
	}

 

    private function generateTimeSlots(string $start, string $end, int $interval): array
    {
        $times = [];
        $current = strtotime($start);
        $end = strtotime($end);
    
        while ($current <= $end) {
            $times[] = date('H:i', $current); // Use 24-hour format for accurate parsing
            $current = strtotime("+$interval minutes", $current);
        }
    
        return $times;
    }

    public function render()
    {
        return view('livewire.widgets.statistics-summary-chart');
    }
}
