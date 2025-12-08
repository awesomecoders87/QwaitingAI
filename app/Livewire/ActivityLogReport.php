<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use App\Models\ActivityLog;
use App\Models\AccountSetting;
use App\Models\SiteDetail;
use App\Models\Location;
use Livewire\Attributes\Title;
use App\Exports\ActivityLogExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ActivityLogReport extends Component
{
    
    use WithPagination;
    
    #[Title('Activity log')]   

    public $teamId;
    public $locationId;
    public $fromSelectedDate;
    public $toSelectedDate;
    public $datetimeFormat;

    protected $updatesQueryString = ['fromSelectedDate', 'toSelectedDate'];

    public function mount()
    {
        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');
    
        $this->fromSelectedDate = $this->fromSelectedDate ?? date('Y-m-d');
        $this->toSelectedDate = $this->toSelectedDate ?? date('Y-m-d');
        $this->datetimeFormat = AccountSetting::showDateTimeFormat();
    }

    public function updatingFromSelectedDate()
    {
        $this->resetPage();
    }

    public function updatingToSelectedDate()
    {
        $this->resetPage();
    }

    public function getLogs()
	{
		return ActivityLog::with('creator') // Eager load the creator relationship
			->where('team_id', $this->teamId)
			->where('location_id', $this->locationId)
			->whereDate('created_at', '>=', $this->fromSelectedDate)
			->whereDate('created_at', '<=', $this->toSelectedDate)
			->orderBy('created_at', 'desc')
			->paginate(10);
	}

    public function exportcsv()
	{
		$records = ActivityLog::with('creator') // Eager load creator
			->where('team_id', $this->teamId)
			->where('location_id', $this->locationId)
			->whereDate('created_at', '>=', $this->fromSelectedDate)
			->whereDate('created_at', '<=', $this->toSelectedDate)
			->orderBy('created_at', 'desc')
			->get();

		$locationName = Location::where('id', $this->locationId)
			->value('location_name') ?? 'N/A';

		return Excel::download(
			new ActivityLogExport($records, $this->fromSelectedDate, $this->toSelectedDate, $locationName),
			'ActivityLogReport.csv',
			\Maatwebsite\Excel\Excel::CSV
		);
	}


    public function exportPdf()
	{
		$records = ActivityLog::with('creator') // Eager load creator
			->where('team_id', $this->teamId)
			->where('location_id', $this->locationId)
			->whereDate('created_at', '>=', $this->fromSelectedDate)
			->whereDate('created_at', '<=', $this->toSelectedDate)
			->orderBy('created_at', 'desc')
			->get();

		$locationName = Location::where('id', $this->locationId)
			->value('location_name') ?? 'N/A';

		$logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId, $this->locationId);

		$pdf = Pdf::loadView('pdf.activity-log-pdf', [
			'records' => $records,
			'fromDate' => $this->fromSelectedDate,
			'toDate' => $this->toSelectedDate,
			'locationName' => $locationName,
			'logo_src' => $logo,
			'datetimeFormat' => $this->datetimeFormat
		]);

		return response()->streamDownload(function () use ($pdf) {
			echo $pdf->stream();
		}, 'ActivityLogReport.pdf');
	}

    public function render()
    {
        return view('livewire.activity-log-report', [
            'logs' => $this->getLogs()
        ]);
    }
}
