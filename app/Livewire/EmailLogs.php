<?php

namespace App\Livewire;

use App\Exports\EmailLogsExport;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use App\Models\SiteDetail;
use App\Models\AccountSetting;
use App\Models\MessageDetail;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;


class EmailLogs extends Component
{
    use WithPagination;

    #[Title('Email Logs')]

    public $teamId;
    public $locationId;
    public $fromSelectedDate;
    public $toSelectedDate;
    public $datetimeFormat;
    public $searchTerm = '';

    protected $updatesQueryString = ['fromSelectedDate', 'toSelectedDate'];

    public function mount()
    {
        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');

        $this->fromSelectedDate = $this->fromSelectedDate ?? null;
        $this->toSelectedDate = $this->toSelectedDate ?? null;
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

    public function getLogsInBatch()
    {
        $query = MessageDetail::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('channel', 'email');

        if (!empty($this->fromSelectedDate)) {
            $query->whereDate('created_at', '>=', $this->fromSelectedDate);
        }

        if (!empty($this->toSelectedDate)) {
            $query->whereDate('created_at', '<=', $this->toSelectedDate);
        }

        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('message', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('queue_id', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('booking_id', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('event_name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('status', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getAllLogs()
    {
        $query = MessageDetail::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('channel', 'email');

        if (!empty($this->fromSelectedDate)) {
            $query->whereDate('created_at', '>=', $this->fromSelectedDate);
        }

        if (!empty($this->toSelectedDate)) {
            $query->whereDate('created_at', '<=', $this->toSelectedDate);
        }

        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('message', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('queue_id', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('booking_id', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('event_name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('status', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $data;
    }

    public function exportCsv()
    {
        $fileName = 'email_logs_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        $data = $this->getAllLogs();

        return Excel::download(
            new EmailLogsExport($data, $this->fromSelectedDate, $this->toSelectedDate),
            $fileName
        );
    }



    public function exportPdf()
    {

        $data = $this->getAllLogs();

        $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId, $this->locationId);
        $pdf = Pdf::loadView('pdf.email-logs-pdf', [
            'emailReports' => $data,
            'datetimeFormat' => $this->datetimeFormat,
            'from' => $this->fromSelectedDate,
            'to' => $this->toSelectedDate,
            'logo_src' => $logo,
        ]);


        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'email_logs_' . now()->format('Y_m_d_H_i_s') . '.pdf');
    }


    public function render()
    {
        return view('livewire.email-logs', [
            'emailLogs' => $this->getLogsInBatch()
        ]);
    }
}
