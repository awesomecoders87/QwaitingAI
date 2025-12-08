<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\{
    Category,
    Counter,
    Queue,
    SiteDetail,
    GenerateQrCode,
    QueueStorage,
    Location,
    ColorSetting,
    User,
    Country,
    AccountSetting,
    CustomSlot,
    PaymentSetting,
    StripeResponse,
    Customer,
    CustomerActivityLog,
    LanguageSetting,
    MessageDetail,
    TicketPrint,
    Translation,
    MetaAdsAndCampaignsLink,
    AutomationSetting,
    ActivityLog,
    Tenant,
    SalesforceSetting,
    SalesforceConnection,
    DynamicReport as DynamicReportModel,
    Level,
};
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DynamicReportsExport;
use Maatwebsite\Excel\Facades\Excel;


class DynamicReport extends Component
{
     use WithPagination;

    public $teamId;
    public $locationId;
    public $reportId;
    public $reportName;
    public $filteredColumns = [];
    public $created_from;
    public $created_until;

    public function mount($id)
    {
        $this->teamId = tenant('id');
        $this->locationId = session('selectedLocation');
        $this->created_from = now()->format('Y-m-d');
        $this->created_until = now()->format('Y-m-d');

        $this->reportId = base64_decode($id);
        $report = DynamicReportModel::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('id', $this->reportId)
            ->where('status', 1)
            ->firstOrFail();

        $this->reportName = $report->report_name;
        $reportFields = (array) $report->report_fields;
        $availableFields = DynamicReportModel::availableFields($this->teamId, $this->locationId);

        $this->filteredColumns = array_intersect_key($availableFields, array_flip($reportFields));
    }



    public function updating($field)
    {
        $this->resetPage();
    }

    public function getReportsQuery()
    {
        $query = QueueStorage::query()->where('locations_id', $this->locationId);

        if ($this->created_from) {
            $query->whereDate('arrives_time', '>=', $this->created_from);
        }
        if ($this->created_until) {
            $query->whereDate('arrives_time', '<=', $this->created_until);
        }

        return $query->orderBy('arrives_time', 'desc');
    }


        public function exportCSV()
        {
            $reports = $this->getReportsQuery()->get();
            $dateformat = Auth::user()->date_format ?? 'd M Y';

            $columns = array_values($this->filteredColumns);
            $keys    = array_keys($this->filteredColumns);

            $rows = [];
            foreach ($reports as $index => $queue) {
                $row = [];
                foreach ($keys as $key) {
                    $row[$key] = $this->transformReportRow($queue, $index, $dateformat)[$key] ?? '';
                }
                $rows[] = $row;
            }

            $dateRange = "Date Range: " . $this->created_from . " to " . $this->created_until;

            return Excel::download(new DynamicReportsExport($rows, $columns, $this->reportName, $dateRange), 'report.xlsx');
        }

    public function exportToPDF()
    {
        $reports = $this->getReportsQuery()->get(); // all rows
        $dateformat = Auth::user()->date_format ?? 'd M Y';
        $columns = $this->filteredColumns;
        $keys    = array_keys($columns);
       $dateRange = "Date Range: " . $this->created_from . " to " . $this->created_until;
         $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->locationId);
        $rows = [];
        foreach ($reports as $index => $queue) {
            $rows[] = $this->transformReportRow($queue, $index, $dateformat);
        }

        $pdf = Pdf::loadView('pdf.report-pdf', [
            'from' => $this->created_from,
            'to' => $this->created_until,
             'logo_src' => $logo,
            'reportname' =>$this->reportName,
            'columns' => $columns,
            'reports' => $rows,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'report.pdf');
    }

    private function transformReportRow($queue, $index, $dateformat)
    {
        $paginated = collect([$queue]); // dummy
        $row = [];
        foreach ($this->filteredColumns as $key => $label) {
           switch ($key) {
                    case 'srno':
                        $row[$key] = $index + 1;
                        break;
                    case 'token':
                        $start_acronym = '';
                        if(!empty($queue->start_acronym)){
                            $start_acronym = $queue->start_acronym;
                        }
                        $row[$key] = $start_acronym.$queue->token;
                        break;
                    case 'level1':
                        $row[$key] = $queue->category->name ?? '';
                        break;
                    case 'level2':
                        $row[$key] = $queue->subCategory->name ?? '';
                        break;
                    case 'level3':
                        $row[$key] = $queue->childCategory->name ?? '';
                        break;
                    case 'arrives_time':
                        $row[$key] = Carbon::parse($queue->arrives_time)->format($dateformat);
                        break;
                    case 'counter':
                        $row[$key] =  $queue->Counter->name ?? '';
                        break;
                    case 'served_by':
                        $row[$key] = $queue->servedBy->name ?? '' ;
                        break;
                    case 'name':
                        $row[$key] = $queue->name ?? '' ;
                        break;
                    case 'contact':
                        $row[$key] = !empty($queue->phone) ? '`'.(($queue->phone_code ??'').$queue->phone) : '' ;
                        break;
                    case 'closed_by':
                        $row[$key] =$queue->closedBy->name ?? '' ;
                        break;
                    case 'response_time':
                            $responseTime = '';
                            if ($queue->called_datetime && $queue->arrives_time) {
                            $responseTime = $queue->called_datetime->diff($queue->arrives_time);

                            $responseTime = $responseTime->format('%H:%I:%S');
                            }
                        $row[$key] = $responseTime ?? '' ;
                        break;
                    case 'serving_time':
                            $servedTime = '';
                            if ($queue->closed_datetime && $queue->start_datetime) {
                            $servedTime = $queue->closed_datetime->diff($queue->start_datetime);
                            $servedTime = $servedTime->format('%H:%I:%S');
                            }
                        $row[$key] = $servedTime ?? '' ;
                        break;
                    case 'email':
                        $json = json_decode($queue->json, true);
                        $email = $json['Email'] ?? ($json['email'] ?? $json['email_address'] ?? null);
                        $row[$key] = $email ?? '' ;
                        break;

                    case 'mode':
                        $row[$key] = $queue->mode ?? '' ;
                        break;
                    case 'forward_counter_id':
                        $row[$key] = $queue->forwardcounter->name ?? '' ;
                        break;
                    case 'transfer_id':
                        $row[$key] = $queue->transfer->name ?? '' ;
                        break;
                    case 'is_missed':
                        $row[$key] = $queue->is_missed == 1 ? __('text.yes') :  __('text.no') ;
                        break;
                    case 'is_hold':
                        $row[$key] = $queue->is_hold == 1 ? __('text.yes') :  __('text.no') ;
                        break;
                    case 'hold_by':
                        $row[$key] = $queue?->hold_by->name ?? '';
                        break;
                    case 'called_datetime':
                        $row[$key] =!empty($queue->called_datetime) ?  Carbon::parse($queue->called_datetime)->format($dateformat): '';
                        break;
                    case 'start_datetime':
                        $row[$key] =!empty($queue->start_datetime) ?  Carbon::parse($queue->start_datetime)->format($dateformat): '';
                        break;
                    case 'closed_datetime':
                        $row[$key] =!empty($queue->closed_datetime) ?  Carbon::parse($queue->closed_datetime)->format($dateformat): '';
                        break;
                    case 'hold_start_datetime':
                        $row[$key] =!empty($queue->hold_start_datetime) ?  Carbon::parse($queue->hold_start_datetime)->format($dateformat): '';
                        break;
                    case 'hold_end_datetime':
                        $row[$key] =!empty($queue->hold_end_datetime) ?  Carbon::parse($queue->hold_end_datetime)->format($dateformat): '';
                        break;
                    case 'locations_id':
                        $row[$key] =!empty($queue->locations_id) ? $queue->location->location_name : '';
                        break;
                    case 'meeting_link':
                        $row[$key] =!empty($queue->meeting_link) ? $queue->meeting_link : '';
                        break;
                    case 'esitmate_note':
                        $row[$key] =!empty($queue->esitmate_note) ? $queue->esitmate_note : '';
                        break;
                    case 'json':
                       if (!empty($queue->json)) {
                            $json = json_decode($queue->json, true);

                            // Add each JSON key as a list string
                            $list = [];

                             foreach ($json as $jsonKey => $jsonValue) {
                                    if (is_array($jsonValue) || is_object($jsonValue)) {
                                        $jsonValue = implode(', ', (array) $jsonValue); // join array into string
                                    }
                                    $list[] = ucfirst($jsonKey) . ': ' . $jsonValue;
                                }

                            // Join into a list format
                            $row[$key] = implode(', ', $list);
                        } else {
                            $row[$key] = '';
                        }
                        break;
                    default:
                        $row[$key] = $queue->{$key} ?? '-';
                }
        }
        return $row;
    }


    public function render()
    {
        $paginated = $this->getReportsQuery()->paginate(10);
        $dateformat = Auth::user()->date_format ?? 'd M Y';
        // transform rows to only include filtered columns
        $reports = $paginated->through(function ($queue, $index) use ($paginated,$dateformat) {
            $row = [];
            foreach ($this->filteredColumns as $key => $label) {
                switch ($key) {
                    case 'srno':
                        $row[$key] = $paginated->firstItem() + $index;
                        break;
                    case 'token':
                        $start_acronym = '';
                        if(!empty($queue->start_acronym)){
                            $start_acronym = $queue->start_acronym;
                        }
                        $row[$key] = $start_acronym.$queue->token;
                        break;
                    case 'level1':
                        $row[$key] = $queue->category->name ?? '';
                        break;
                    case 'level2':
                        $row[$key] = $queue->subCategory->name ?? '';
                        break;
                    case 'level3':
                        $row[$key] = $queue->childCategory->name ?? '';
                        break;
                    case 'arrives_time':
                        $row[$key] = Carbon::parse($queue->arrives_time)->format($dateformat);
                        break;
                    case 'counter':
                        $row[$key] =  $queue->Counter->name ?? '';
                        break;
                    case 'served_by':
                        $row[$key] = $queue->servedBy->name ?? '' ;
                        break;
                    case 'name':
                        $row[$key] = $queue->name ?? '' ;
                        break;
                    case 'contact':
                        $row[$key] = !empty($queue->phone) ? (($queue->phone_code ??'').$queue->phone) : '' ;
                        break;
                    case 'closed_by':
                        $row[$key] =$queue->closedBy->name ?? '' ;
                        break;
                    case 'response_time':
                            $responseTime = '';
                            if ($queue->called_datetime && $queue->arrives_time) {
                            $responseTime = $queue->called_datetime->diff($queue->arrives_time);

                            $responseTime = $responseTime->format('%H:%I:%S');
                            }
                        $row[$key] = $responseTime ?? '' ;
                        break;
                    case 'serving_time':
                            $servedTime = '';
                            if ($queue->closed_datetime && $queue->start_datetime) {
                            $servedTime = $queue->closed_datetime->diff($queue->start_datetime);
                            $servedTime = $servedTime->format('%H:%I:%S');
                            }
                        $row[$key] = $servedTime ?? '' ;
                        break;
                    case 'email':
                        $json = json_decode($queue->json, true);
                        $email = $json['Email'] ?? ($json['email'] ?? $json['email_address'] ?? null);
                        $row[$key] = $email ?? '' ;
                        break;

                    case 'mode':
                        $row[$key] = $queue->mode ?? '' ;
                        break;
                    case 'forward_counter_id':
                        $row[$key] = $queue->forwardcounter->name ?? '' ;
                        break;
                    case 'transfer_id':
                        $row[$key] = $queue->transfer->name ?? '' ;
                        break;
                    case 'is_missed':
                        $row[$key] = $queue->is_missed == 1 ? __('text.yes') :  __('text.no') ;
                        break;
                    case 'is_hold':
                        $row[$key] = $queue->is_hold == 1 ? __('text.yes') :  __('text.no') ;
                        break;
                    case 'hold_by':
                        $row[$key] = $queue?->hold_by->name ?? '';
                        break;
                    case 'called_datetime':
                        $row[$key] =!empty($queue->called_datetime) ?  Carbon::parse($queue->called_datetime)->format($dateformat): '';
                        break;
                    case 'start_datetime':
                        $row[$key] =!empty($queue->start_datetime) ?  Carbon::parse($queue->start_datetime)->format($dateformat): '';
                        break;
                    case 'closed_datetime':
                        $row[$key] =!empty($queue->closed_datetime) ?  Carbon::parse($queue->closed_datetime)->format($dateformat): '';
                        break;
                    case 'hold_start_datetime':
                        $row[$key] =!empty($queue->hold_start_datetime) ?  Carbon::parse($queue->hold_start_datetime)->format($dateformat): '';
                        break;
                    case 'hold_end_datetime':
                        $row[$key] =!empty($queue->hold_end_datetime) ?  Carbon::parse($queue->hold_end_datetime)->format($dateformat): '';
                        break;
                    case 'locations_id':
                        $row[$key] =!empty($queue->locations_id) ? $queue->location->location_name : '';
                        break;
                    case 'meeting_link':
                        $row[$key] =!empty($queue->meeting_link) ? $queue->meeting_link : '';
                        break;
                    case 'esitmate_note':
                        $row[$key] =!empty($queue->esitmate_note) ? $queue->esitmate_note : '';
                        break;
                    case 'json':
                       if (!empty($queue->json)) {
                                $json = json_decode($queue->json, true);

                                // Add each JSON key as a list string
                                $list = [];
                                foreach ($json as $jsonKey => $jsonValue) {
                                    if (is_array($jsonValue) || is_object($jsonValue)) {
                                        $jsonValue = implode(', ', (array) $jsonValue); // join array into string
                                    }
                                    $list[] = ucfirst($jsonKey) . ': ' . $jsonValue;
                                }

                                // Join into a list format
                                $row[$key] = implode(', ', $list);
                            }
                        break;
                    default:
                        $row[$key] = $queue->{$key} ?? '-';
                }
            }
            return $row;
        });

        return view('livewire.dynamic-report', [
            'reports' => $reports,
        ]);
    }
}
