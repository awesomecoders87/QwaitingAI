<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Livewire\Widgets\FeedbackStatisticsChart;
use App\Models\ {
    SiteDetail};
    use Illuminate\Support\Facades\Session;
    use Barryvdh\DomPDF\Facade\Pdf;
    use Illuminate\Support\Facades\Http;

class FeedbackStaticsReport extends Component
{
    #[Title('Feeback Statics report')]
    public ?string $fromSelectedDate = null;
    public ?string $toSelectedDate = null;
    public $teamId;
    public $location;

    public function mount()
    {
        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->location = Session::get('selectedLocation');
    
        $this->fromSelectedDate = $this->fromSelectedDate ?? date('Y-m-d');
        $this->toSelectedDate = $this->toSelectedDate ?? date('Y-m-d');
    }

    public function exportPdf()
    {

        $widget = new FeedbackStatisticsChart();
        $widget->fromSelectedDate = $this->fromSelectedDate;
        $widget->toSelectedDate = $this->toSelectedDate;
        $widget->teamId = $this->teamId;
    
        // Get the dataset from the widget
        $dataFromWidget = $widget->getData();

        $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->location);
       
        $labels = $dataFromWidget['labels'];
        $arrivedCounts = $dataFromWidget['data'];
        $backgroundColor = $dataFromWidget['backgroundColor'];
        $borderColor = $dataFromWidget['borderColor'];
     
    // // Format chart data for QuickChart
    // $labels = array_map(fn($row) => $row['time_slot'], $this->dataPoints);
    // $arrived = array_map(fn($row) => $row['arrived_count'], $this->dataPoints);
    // $called = array_map(fn($row) => $row['called_count'], $this->dataPoints);
    // $waiting = array_map(fn($row) => $row['waiting_count'], $this->dataPoints);
   
    $chartConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Calls',
                    'data' => $arrivedCounts,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'borderWidth' => 1,
                    'hoverOffset' => 3,
                    
                ],
               
            ],
        ],
        'options' => [
            'title' => ['display' => true, 'text' => 'Summary by Time Slot'],
            'legend' => ['position' => 'bottom'],
        ],
    ];

    // // Generate QuickChart URL
    $encoded = urlencode(json_encode($chartConfig));
    $chartUrl = "https://quickchart.io/chart?c={$encoded}";
    $data = [
        'from' => $this->fromSelectedDate,
        'to' => $this->toSelectedDate,
        'logo_src' => $logo,
        'chart_url' => $chartUrl,
    ];


        $pdf = Pdf::loadView('pdf.feedback-static-report-pdf', $data);

        return response()->streamDownload(fn() => print($pdf->stream()), "report.pdf");
    }


    public function updatedFromSelectedDate()
    {
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\FeedbackStatisticsChart::class);
       
    }

    public function updatedToSelectedDate()
    {
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\FeedbackStatisticsChart::class);
       
    }



    public function render()
    {
        return view('livewire.feedback-statics-report');
    }
}
