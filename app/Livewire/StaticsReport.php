<?php

namespace App\Livewire;
use Livewire\Component;
use App\Models\QueueStorage;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SiteDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Livewire\Widgets\StatisticsSummaryChart;
use App\Livewire\Widgets\StatisticsCallHistoryChart;
use App\Livewire\Widgets\StatisticsCounterHistoryChart;
use App\Exports\MainStaticsChartExport;

class StaticsReport extends Component
{
    #[Title('Statistics Report')]

    public ?string $fromSelectedDate = null;
    public ?string $toSelectedDate = null;
    public $teamId;
    public $location;
    public array $dataPoints = [];

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->fromSelectedDate = $this->fromSelectedDate ?? date('Y-m-d');
        $this->toSelectedDate = $this->toSelectedDate ?? date('Y-m-d');
        $this->getData();
    }

    public function updatedFromSelectedDate()
    {
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsSummaryChart::class);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsCallHistoryChart::class);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class);
        $this->getData();
    }

    public function updatedToSelectedDate()
    {
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsSummaryChart::class);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsCallHistoryChart::class);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class);
        $this->getData();
    }

    public function exportCsv() {
        $imageExcel = $this->exportdata();
        return Excel::download(new MainStaticsChartExport($imageExcel), ' statistics-report.xlsx');
    }

   public function exportdata()
{
    $imageExcel = [];

    // ------------------------------
    // 1. Summary Chart
    // ------------------------------
    $summaryW = new StatisticsSummaryChart();
    $summaryW->fromSelectedDate = $this->fromSelectedDate;
    $summaryW->toSelectedDate = $this->toSelectedDate;
    $summaryW->location = $this->location;
    $summaryW->teamId = $this->teamId;

    // **EAGER LOAD QUEUESTORAGE RELATIONS IN WIDGET**
    // If the widget queries QueueStorage, ensure it loads relationships like Counter, servedBy, closedBy, assignStaff
    $getdataFromWidget = $summaryW->getData();

    $labels = $getdataFromWidget['labels'] ?? [];
    $data = $getdataFromWidget['data'] ?? [];

    $dataFromWidget = [
        'type' => 'bar',
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('report.Calls'),
                    'data' => $data,
                    'backgroundColor' => '#2ecc71',
                    'borderColor' => '#2ecc71',
                    'borderWidth' => 1,
                    'hoverOffset' => 3
                ],
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]
    ];

    $imageExcel['summaryChart'] = SiteDetail::createImage($dataFromWidget, 'stats-summary-chart-' . $this->teamId . '.png');

    // ------------------------------
    // 2. Call History Chart
    // ------------------------------
    $callHistoryW = new StatisticsCallHistoryChart();
    $callHistoryW->fromSelectedDate = $this->fromSelectedDate;
    $callHistoryW->toSelectedDate = $this->toSelectedDate;
    $callHistoryW->location = $this->location;
    $callHistoryW->teamId = $this->teamId;

    // **EAGER LOAD RELATIONS**
    $dataCallHistory = $callHistoryW->getData(); // ensure this method eager loads QueueStorage relationships

    $labels = $dataCallHistory['labels'] ?? [];
    $data = $dataCallHistory['data'] ?? [];
    $backgroundColor = $dataCallHistory['backgroundColor'] ?? [];
    $borderColor = $dataCallHistory['borderColor'] ?? [];

    $dataSetCallHistory = [
        'type' => 'bar',
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('report.Calls History'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'borderWidth' => 1,
                    'hoverOffset' => 3
                ],
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]
    ];

    $imageExcel['callHistoryChart'] = SiteDetail::createImage($dataSetCallHistory, 'stats-callhistory-chart-' . $this->teamId . '.png');

    // ------------------------------
    // 3. Counter History Chart
    // ------------------------------
    $counterHistoryW = new StatisticsCounterHistoryChart();
    $counterHistoryW->fromSelectedDate = $this->fromSelectedDate;
    $counterHistoryW->toSelectedDate = $this->toSelectedDate;
    $counterHistoryW->location = $this->location;
    $counterHistoryW->teamId = $this->teamId;

    // **EAGER LOAD RELATIONS**
    $dataCounterHistory = $counterHistoryW->getData(); // ensure this method eager loads QueueStorage relationships

    $labels = $dataCounterHistory['labels'] ?? [];
    $data = $dataCounterHistory['data'] ?? [];
    $backgroundColor = $dataCounterHistory['backgroundColor'] ?? [];
    $borderColor = $dataCounterHistory['borderColor'] ?? [];

    $dataSetCounterHistory = [
        'type' => 'bar',
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('report.Calls Counter'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'borderWidth' => 1,
                    'hoverOffset' => 3
                ],
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]
    ];

    $imageExcel['counterHistoryChart'] = SiteDetail::createImage($dataSetCounterHistory, 'stats-counter-chart-' . $this->teamId . '.png');

    // ------------------------------
    // 4. Additional Meta Data
    // ------------------------------
    $imageExcel['start_date'] = Carbon::parse($this->fromSelectedDate)->format('d-m-Y');
    $imageExcel['end_date'] = Carbon::parse($this->toSelectedDate)->format('d-m-Y');
    $imageExcel['teamId'] = $this->teamId;

    // **EAGER LOAD LOGO IMAGE**
    $logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId, $this->location);
    $imageExcel['logo_src'] = $logo;

    return $imageExcel;
}



    protected function getData(): void
	{
		$timeSlots = $this->generateTimeSlots('8:00 AM', '8:00 PM', 60);
		$dataPoints = [];

		$startDate = Carbon::parse($this->fromSelectedDate)->startOfDay();
		$endDate = Carbon::parse($this->toSelectedDate)->endOfDay();

		// Fetch all records at once for the date range
		$queues = QueueStorage::where('team_id', $this->teamId)
					->where('locations_id', $this->location)
					->whereBetween('arrives_time', [$startDate, $endDate])
					->get(['id', 'arrives_time']);

		foreach ($timeSlots as $timeSlot) {
			$countsPerSlot = 0;

			$currentDate = $startDate->copy();
			while ($currentDate->lte($endDate)) {
				$startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $timeSlot);
				$endTime = $startTime->copy()->addHour();

				// Count using collection filtering instead of querying DB repeatedly
				$countsPerSlot += $queues->whereBetween('arrives_time', [$startTime, $endTime])->count();

				$currentDate->addDay();
			}

			$dataPoints[] = [
				'time_slot' => $timeSlot,
				'count' => $countsPerSlot,
			];
		}

		$this->dataPoints = $dataPoints;
	}

    private function generateTimeSlots(string $start, string $end, int $interval): array
    {
        $times = [];
        $current = strtotime($start);
        $end = strtotime($end);

        while ($current <= $end) {
            $times[] = date('H:i', $current);
            $current = strtotime("+$interval minutes", $current);
        }

        return $times;
    }

    
    public function exportPdf()
{
    $pdfData = $this->exportdata();
    $pdf = Pdf::loadView('pdf.statistics-report-pdf', $pdfData);
    return response()->streamDownload(fn () => print($pdf->stream()), 'statistics-report.pdf');
}

    public function render()
    {
        return view('livewire.statics-report');
    }
}
