<?php

namespace App\Livewire\Widgets;

use App\Models\QueueStorage;
use App\Models\AccountSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;

class OverviewPerDayWidget extends Component
{
    public $fromSelectedDate;
    public $toSelectedDate;
    public $teamId;
    public $locationId;
    public $chartData = [];

    public function mount(): void
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->fromSelectedDate = date('Y-m-d');
        $this->toSelectedDate = date('Y-m-d');

        $this->refreshData();
    }

    public function getData()
    {
        $this->teamId = $this->teamId ?? tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $startDate = Carbon::parse($this->fromSelectedDate)->startOfDay();
        $endDate = Carbon::parse($this->toSelectedDate)->endOfDay();

        $rawData = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->locationId)
            ->whereBetween('arrives_time', [$startDate, $endDate])
            ->selectRaw('DATE(arrives_time) as date, COUNT(*) as arrived_count')
            ->groupBy('date')
            ->get();

            // dd($arrivedData);
        $datetimeFormat = AccountSetting::showDateTimeFormat($this->teamId, $this->locationId);

        // $this->chartData = $arrivedData->map(function ($item) use ($datetimeFormat) {
        //     $servedCount = QueueStorage::where('team_id', $this->teamId)
        //         ->where('locations_id', $this->locationId)
        //         ->whereDate('called_datetime', $item->date)
        //         ->count();

        //     return [
        //         'date' => Carbon::parse($item->date)->format($datetimeFormat),
        //         'arrived' => $item->arrived_count,
        //         'served' => $servedCount,
        //         'waiting' => $item->arrived_count - $servedCount,
        //     ];
        // })->toArray();


          // Ensure $rawData is an Eloquent Collection before transforming
    if ($rawData instanceof \Illuminate\Support\Collection) {
     return   $this->chartData = $rawData->map(function ($item) {
            $servedCount = QueueStorage::where('team_id',  $this->teamId)
                ->where('locations_id', $this->locationId)
                ->whereDate('called_datetime', $item->date)
                ->count();

            $percentage = $item->arrived_count > 0 ? ($servedCount / $item->arrived_count) * 100 : 0;

            return [
                'date' => $item->date,
                'arrived_count' => $item->arrived_count,
                'served_count' => $servedCount,
                'percentage' => $percentage,
            ];
        });
    }
    

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
        $this->dispatch('updateChartData', $this->chartData); // Ensure this method fetches data based on the updated dates
    }

    public function render()
    {
        return view('livewire.widgets.overview-per-day-widget', [
            'chartData' => $this->chartData,
        ]);
    }
}
