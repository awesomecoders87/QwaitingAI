<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\QueueStorage;
use App\Models\SiteDetail;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use App\Livewire\Widgets\StatisticsSummaryTimeChart;

class OverviewPerTimePeriodReports extends Component
{
    use WithPagination;
    #[Title('Overview Per Time Period')]
    
    public ?string $fromSelectedDate = null;
    public ?string $toSelectedDate = null;
    public ?string $fromSelectedTime = null;
    public ?string $toSelectedTime = null;
    public $location;
    public array $dataPoints = [];
    public $teamId;

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->fromSelectedDate = now()->toDateString();
        $this->toSelectedDate = now()->toDateString();
        $this->fromSelectedTime = "08:00";
        $this->toSelectedTime = "20:00";
        $this->getData();
    }

    // public function updated($property)
    // {
    //     if (in_array($property, ['fromSelectedDate', 'toSelectedDate', 'fromSelectedTime', 'toSelectedTime'])) {
    //         $this->getData();
    //     }
    // }

    public function updatedFromSelectedDate()
    {
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsSummaryTimeChart::class);
        $this->getData();
    }

    public function updatedToSelectedDate()
    {
      
        // $this->dispatch('toSelectedDateChanged', $this->toSelectedDate);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsSummaryTimeChart::class);
        $this->getData();
    }

    public function updatedFromSelectedTime()
    {
        $this->dispatch('fromSelectedTimeChanged', fromSelectedTime: $this->fromSelectedTime)->to(\App\Livewire\Widgets\StatisticsSummaryTimeChart::class);
        $this->getData();
    }

    public function updatedToSelectedTime()
    {
      
        // $this->dispatch('toSelectedTimeChanged', $this->toSelectedTime);
        $this->dispatch('toSelectedTimeChanged', toSelectedTime: $this->toSelectedTime)->to(\App\Livewire\Widgets\StatisticsSummaryTimeChart::class);
        $this->getData();
    }


    protected function getData(): void
{
    $timeSlots = $this->generateTimeSlots($this->fromSelectedTime, $this->toSelectedTime, 60);
    $dataPoint = [];

    $startDate = Carbon::parse($this->fromSelectedDate)->startOfDay();
    $endDate = Carbon::parse($this->toSelectedDate)->endOfDay();

    // Load all relevant QueueStorage records for the period in one query
    $queues = QueueStorage::where('team_id', $this->teamId)
        ->where('locations_id', $this->location)
        ->whereBetween('arrives_time', [$startDate, $endDate->endOfDay()])
        ->orWhereBetween('called_datetime', [$startDate, $endDate->endOfDay()])
        ->get()
        ->groupBy(function ($item) {
            return $item->arrives_time->format('Y-m-d'); // Group by date
        });

    foreach ($timeSlots as $timeSlot) {
        $arrived_count = 0;
        $called_count = 0;
        $waiting_count = 0;

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $timeSlot['start']);
            $endTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $timeSlot['end']);

            $dayQueues = $queues->get($currentDate->format('Y-m-d'), collect());

            // Filter queues in the current time slot
            $arrivedInSlot = $dayQueues->filter(fn($q) => $q->arrives_time >= $startTime && $q->arrives_time <= $endTime);
            $calledInSlot = $dayQueues->filter(fn($q) => $q->called_datetime && $q->called_datetime >= $startTime && $q->called_datetime <= $endTime);

            $arrived_count += $arrivedInSlot->count();
            $called_count += $calledInSlot->count();
            $waiting_count += $arrivedInSlot->count() - $calledInSlot->count();

            $currentDate->addDay();
        }

        $percentage = $arrived_count > 0 ? ($called_count / $arrived_count) * 100 : 0;

        $dataPoint[] = [
            'time_slot' => Carbon::parse($timeSlot['start'])->format('h:i A') . ' - ' . Carbon::parse($timeSlot['end'])->format('h:i A'),
            'arrived_count' => $arrived_count,
            'called_count' => $called_count,
            'waiting_count' => $waiting_count,
            'percentage' => number_format($percentage, 2, '.', ''),
            'transaction_time' => ['max' => '00:00:00', 'average' => '00:00:00'], // If you want, you can compute from queues
            'waiting_time' => ['max' => '00:00:00', 'average' => '00:00:00'],   // Same here
        ];
    }

    $this->dataPoints = $dataPoint;
    $this->dispatch('updateChartData', $this->dataPoints);
}


    private function generateTimeSlots(string $start, string $end, int $interval): array
    {
        $times = [];
        $current = strtotime($start);
        $end = strtotime($end);

        while ($current < $end) {
            $next = strtotime("+$interval minutes", $current);
            $times[] = [
                'start' => date('H:i', $current),
                'end' => date('H:i', $next)
            ];
            $current = $next;
        }

        return $times;
    }

    private function calculateAverageTime(array $times): string
{
    $totalSeconds = array_sum(array_map(function ($time) {
        $timeParts = explode(':', $time);
        return $timeParts[0] * 3600 + $timeParts[1] * 60 + $timeParts[2];
    }, $times));

    $averageSeconds = $totalSeconds / count($times);
    return gmdate('H:i:s', $averageSeconds);
}



    public function exportPdf()
    {

        $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->location);
       
    // Format chart data for QuickChart
    $labels = array_map(fn($row) => $row['time_slot'], $this->dataPoints);
    $arrived = array_map(fn($row) => $row['arrived_count'], $this->dataPoints);
    $called = array_map(fn($row) => $row['called_count'], $this->dataPoints);
    $waiting = array_map(fn($row) => $row['waiting_count'], $this->dataPoints);

    $chartConfig = [
        'type' => 'line',
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Arrived',
                    'data' => $arrived,
                    'borderColor' => 'green',
                    'fill' => false,
                ],
                [
                    'label' => 'Called',
                    'data' => $called,
                    'borderColor' => 'blue',
                    'fill' => false,
                ],
                [
                    'label' => 'Waiting',
                    'data' => $waiting,
                    'borderColor' => 'orange',
                    'fill' => false,
                ],
            ],
        ],
        'options' => [
            'title' => ['display' => true, 'text' => 'Summary by Time Slot'],
            'legend' => ['position' => 'bottom'],
        ],
    ];

    // Generate QuickChart URL
    $encoded = urlencode(json_encode($chartConfig));
    $chartUrl = "https://quickchart.io/chart?c={$encoded}";

    $data = [
        'dataPoints' => $this->dataPoints,
        'from' => $this->fromSelectedDate,
        'to' => $this->toSelectedDate,
        'logo_src' => $logo,
        'chart_url' => $chartUrl,
    ];


        $pdf = Pdf::loadView('pdf.static-report-pdf', $data);

        return response()->streamDownload(fn() => print($pdf->stream()), "report.pdf");
    }

    public function render()
    {
        return view('livewire.overview-per-time-period-reports');
    }
}
