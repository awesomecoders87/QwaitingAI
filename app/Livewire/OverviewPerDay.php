<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
    Category, Team, Queue, SiteDetail, SmtpDetails, SmsAPI, GenerateQrCode, CategoryFormField, Location,QueueStorage}
    ;
    use Illuminate\Validation\Rule;
    use Livewire\Rules\Numeric;
    use Carbon\Carbon;
    use Livewire\Attributes\On;
    use App\Events\QueueCreated;
    use App\Models\FormField;
    use DB;
    use Illuminate\Support\Facades\Auth;

    use Illuminate\Support\Facades\Redis;
    use Illuminate\Support\Facades\Cache;

    use Illuminate\Support\Facades\Session;
    use Filament\Facades\Filament;
    use Livewire\Attributes\Computed;
    use Livewire\WithPagination;
    use Barryvdh\DomPDF\Facade\Pdf;
    use Illuminate\Support\Facades\Http;
    use App\Livewire\Widgets\OverviewPerDayWidget;
    use Illuminate\Support\Facades\Log;
    use Livewire\Attributes\Title;

class OverviewPerDay extends Component
{
    #[Title('Overview Per Day')]

    public $teamId;
    public $locationId;
    public ?string $fromSelectedDate = null;
    public ?string $toSelectedDate = null;
    public $location;
    public $dataPoints = [];
    public $perPage = 1; // Number of items per page
    public $page = 1; // Current page

    public function mount()
    {
        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');
    
        $this->fromSelectedDate = $this->fromSelectedDate ?? date('Y-m-d');
        $this->toSelectedDate = $this->toSelectedDate ?? date('Y-m-d');

        $this->getData();
    }



    public function updatedFromSelectedDate()
    {
   
        $this->page = 1;
        // $this->dispatch('fromSelectedDateChanged', $this->fromSelectedDate);
        $this->dispatch('fromSelectedDateChanged', fromSelectedDate: $this->fromSelectedDate)->to(\App\Livewire\Widgets\OverviewPerDayWidget::class);
        $this->getData();
    }

    public function updatedToSelectedDate()
    {
        $this->page = 1;
        // $this->dispatch('toSelectedDateChanged', $this->toSelectedDate);
        $this->dispatch('toSelectedDateChanged', toSelectedDate: $this->toSelectedDate)->to(\App\Livewire\Widgets\OverviewPerDayWidget::class);
        $this->getData();
    }

    public function updatedPage()
    {
        $this->getData();
    }


public function getData()
{
    $startDate = Carbon::parse($this->fromSelectedDate)->startOfDay();
    $endDate = Carbon::parse($this->toSelectedDate)->endOfDay();

    // Get all arrived and served counts for the date range
    $queueData = QueueStorage::where('team_id', $this->teamId)
        ->where('locations_id', $this->locationId)
        ->whereBetween('arrives_time', [$startDate, $endDate])
        ->selectRaw('DATE(arrives_time) as date')
        ->selectRaw('COUNT(*) as arrived_count')
        ->selectRaw('SUM(CASE WHEN called_datetime IS NOT NULL THEN 1 ELSE 0 END) as served_count')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    // Transform for frontend
    $this->dataPoints = [
        'data' => $queueData->map(function ($item) {
            $percentage = $item->arrived_count > 0 
                ? ($item->served_count / $item->arrived_count) * 100 
                : 0;

            return [
                'date' => $item->date,
                'arrived_count' => $item->arrived_count,
                'served_count' => $item->served_count,
                'percentage' => $percentage,
            ];
        })->toArray(),
        'links' => [],
    ];
}


   
    public function exportPdf()
    {
     
        $widget = new OverviewPerDayWidget();
        $widget->fromSelectedDate = $this->fromSelectedDate;
        $widget->toSelectedDate = $this->toSelectedDate;
        $widget->teamId = $this->teamId;
    
        // Get the dataset from the widget
        $dataFromWidget = $widget->getData();
        // Format the dataset properly for QuickChart
        $labels = $dataFromWidget->pluck('date')->toArray();
        $arrivedCounts = $dataFromWidget->pluck('arrived_count')->toArray();
        $servedCounts = $dataFromWidget->pluck('served_count')->toArray();
        $waitingCounts = $dataFromWidget->map(function ($item) {
            return $item['arrived_count'] - $item['served_count'];
        })->toArray();
        
        // Format for QuickChart
        $dataset =[ // app/Livewire/OverviewPerDay.php:169
        "type" => "line",
        "data" =>  [
          "labels" => $labels,
          "datasets" => [
            0 =>  [
              "label" => "Arrived",
              "data" => $arrivedCounts,
              "borderColor" => "green",
              "fill" => false,
            ],
            1 => [
            "label" => "Served",
            "data" => $servedCounts,
            "borderColor" => "blue",
            "fill" => false,
            ],
            2 => [
              "label" => "Waiting",
              "data" =>$waitingCounts,
              "borderColor" => "orange",
              "fill" => false,
            ]
          ]
        ]
            ];
      
    // Encode the dataset as JSON and then URL-encode it
    $encodedDataset = urlencode(json_encode($dataset));
    $chartImageUrl = "https://quickchart.io/chart?c={$encodedDataset}";
  
    // Download the chart image to the server
    $chartImage = Http::get($chartImageUrl);
   
    if ($chartImage->successful()) {
        $chartImagePath = 'charts/chart-per-day-'.$this->teamId.'.png';

        // Ensure the directory exists
        if (!file_exists(public_path('charts'))) {
            mkdir(public_path('charts'), 0777, true);
        }

        // Store the image
        file_put_contents(public_path($chartImagePath), $chartImage->body());

        // Get the URL of the downloaded image
        $chartImageLocalUrl = url($chartImagePath);

        // Log the local URL for debugging
        // Log::info("Chart image saved at: {$chartImageLocalUrl}");

        // Check if the file exists
        if (file_exists(public_path($chartImagePath))) {
            Log::info("Chart image successfully saved at: {$chartImageLocalUrl}");
        } else {
            Log::error("Failed to save chart image at: {$chartImagePath}");
            return response()->json(['error' => 'Failed to save chart image'], 500);
        }
    } else {
        Log::error("Failed to download chart image: {$chartImage->body()}");
        return response()->json(['error' => 'Failed to download chart image'], 500);
    }

        $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->locationId);
        $data = [
            'dataPoints' => $this->dataPoints['data'],
            'from' => $this->fromSelectedDate,
            'to' => $this->toSelectedDate,
            'logo_src' => $logo,
            'chart_url'=>$chartImageLocalUrl,
        ];

        $pdf = Pdf::loadView('pdf.overview-per-day-report-pdf', $data);

        $fromDate = Carbon::parse($this->fromSelectedDate)->format('Ymd');
        $toDate = Carbon::parse($this->toSelectedDate)->format('Ymd');
        $fileName = "static_report_per_day_{$fromDate}_to_{$toDate}.pdf";

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $fileName);
    }

    public function render()
    {
   
        return view('livewire.overview-per-day');
    }
}
