<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Livewire\Livewire;
use League\Csv\Writer;
use App\Models\QueueStorage;
use SplTempFileObject;
use Illuminate\Support\Facades\Log;

class ExportReportCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
        Log::info('ExportReportCSV job started.contruct');
      
    }

    public function handle()
    {
        try {
            Log::info('ExportReportCSV job started.');
         
             // Apply filters
        $query = QueueStorage::query();

        if (!empty($this->filters['created_from'])) {
            $query->whereDate('datetime', '>=', $this->filters['created_from']);
        }

        if (!empty($this->filters['created_until'])) {
            $query->whereDate('datetime', '<=', $this->filters['created_until']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        $reports = $query->get();
  
        // Create CSV in memory
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $header = [
            'S.No', 'Token', 'Created At', 'Level 1', 'Level 2', 'Level 3', 
            'Counter', 'Called By', 'Name', 'Contact', 'Email', 'Closed By',
            'Response Time', 'Serving Time'
        ];
        if (!empty($this->filters['enable_export_buttons'])) {
            $header[] = 'Document';
        }
        $csv->insertOne($header);

        $serialNumber = 1;

        foreach ($reports as $report) {
            $emailData = json_decode($report->json, true);
            $email = $emailData['Email'] ?? ($emailData['email'] ?? $emailData['email_address'] ?? null);

            // Calculate response time
            $responseTime = $report->called_datetime && $report->arrives_time 
                ? $report->called_datetime->diff($report->arrives_time)->format('%H:%I:%S') 
                : '';

            // Calculate serving time
            $servedTime = $report->closed_datetime && $report->start_datetime 
                ? $report->closed_datetime->diff($report->start_datetime)->format('%H:%I:%S') 
                : '';

            $row = [
                $serialNumber++,
                $report->start_acronym . $report->token,
                $report->datetime,
                $report->category->name ?? '',
                $report->subCategory->name ?? '',
                $report->childCategory->name ?? '',
                $report->Counter->name ?? '',
                $report->servedBy->name ?? '',
                $report->name ?? '',
                $report->phone ?? '',
                $email,
                $report->closedBy->name ?? '',
                $responseTime,
                $servedTime
            ];
            if (!empty($this->filters['enable_export_buttons'])) {
                $doc = !empty($report->doc_file) ? url('storage/' . $report->doc_file) : null;
                $row[] = $doc;
            }
            $csv->insertOne($row);
        }

        // Convert CSV to base64 and send it to Livewire
        $base64CSV = base64_encode($csv->toString());

        Livewire::dispatch('csvReady', $base64CSV);
        } catch (\Exception $e) {
            Log::error('Error in ExportReportCSV job: ' . $e->getMessage());
        }
       
    }
}
