<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\AccountSetting;
use App\Models\SiteDetail;
use App\Models\Level;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SubCategoriesExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Title;

class SubCategoriesReport extends Component
{

    #[Title('Sub-category Report')]

    public $categories;
    public $categoriesList;
    public $selectedLevel1 = '';
    public $selectedLevel2 = '';
    public $selectedLevel3 = '';
    public $startDate;
    public $endDate;
    public $queues;
    public $teamId;
    public $location;
    public $slPeriod;
    public $status = [];
    public $siteDetail;
    public $searchTerm = '';
    public $level1;
    public $level2;
    public $level3;

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->categoriesList = Category::withTrashed()
        ->where(function ($query) {
            $query->whereNull('parent_id')
                  ->orWhere('parent_id', '');
        })
        ->where('team_id', $this->teamId)
        ->whereJsonContains('category_locations', "$this->location")
        ->get();

        $this->siteDetail = SiteDetail::getMyDetails($this->teamId);
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        $this->slPeriod = isset($this->siteDetail) && !empty($this->siteDetail->estimate_time) ? $this->siteDetail->estimate_time : 15;
        // Load initial data

        $levels =  Level::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');

        $this->level1 = $levels[1]->name ?? 'Level 1';
        $this->level2 = $levels[2]->name ?? 'Level 2';
        $this->level3 = $levels[3]->name ?? 'Level 3';

        $this->loadCategories();
    }

    public function updated($propertyName)
    {
        $this->loadCategories();
    }

    public function search()
    {
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $query = QueueStorage::query();

        if ($this->selectedLevel1) {
            $query->where('category_id', $this->selectedLevel1);
        } else {
            $this->selectedLevel2 = '';
            $this->selectedLevel3 = '';
        }
        if ($this->selectedLevel2) {
            $query->where('sub_category_id', $this->selectedLevel2);
        }
        if ($this->selectedLevel3) {
            $query->where('child_category_id', $this->selectedLevel3);
        }

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

         // Filter by status (if any statuses are selected)
         if (!empty($this->status)) {
            $query->whereIn('status', $this->status);
        }


        // Add ordering by the levels
        $this->categories = $query->select([
            'category_id',
            'sub_category_id',
            'child_category_id',
            DB::raw('COUNT(id) AS total_calls'),
            DB::raw('SUM(CASE WHEN closed_datetime IS NULL AND is_missed = 0 AND status ="Pending" THEN 1 ELSE 0 END) AS pending_calls'),
            DB::raw('(SUM(CASE WHEN closed_datetime IS NULL AND is_missed = 0 AND status ="Pending" THEN 1 ELSE 0 END) / COUNT(id)) * 100 AS pending_percentage'),
            DB::raw('SUM(CASE WHEN status ="Cancelled" THEN 1 ELSE 0 END) AS cancel_calls'),
            DB::raw('(SUM(CASE WHEN status ="Cancelled" THEN 1 ELSE 0 END) / COUNT(id)) * 100 AS cancel_percentage'),
            DB::raw('SUM(CASE WHEN closed_datetime IS NOT NULL AND status ="Close" THEN 1 ELSE 0 END) AS served_calls'),
            DB::raw('(SUM(CASE WHEN closed_datetime IS NOT NULL AND status ="Close" THEN 1 ELSE 0 END) / COUNT(id)) * 100 AS served_percentage'),
            DB::raw('SUM(CASE WHEN is_missed = 1 THEN 1 ELSE 0 END) AS no_show'),
            DB::raw('(SUM(CASE WHEN is_missed = 1 THEN 1 ELSE 0 END) / COUNT(id)) * 100 AS no_show_percentage'),
            DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(closed_datetime, start_datetime)))) AS total_served_time'),
            DB::raw('TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(closed_datetime, start_datetime)))),"%H:%i:%s") AS average_served_time'),
            DB::raw('SEC_TO_TIME(MAX(TIME_TO_SEC(TIMEDIFF(closed_datetime, start_datetime)))) AS max_served_time'),
            DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, arrives_time, called_datetime) < ' . $this->slPeriod . ' AND status ="Close" THEN 1 ELSE 0 END) AS total_waiting_less_15_min'),
            DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, arrives_time, called_datetime) >= ' . $this->slPeriod . ' AND status ="Close" THEN 1 ELSE 0 END) AS total_waiting_greater_15_min'),
            DB::raw('TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(called_datetime, arrives_time)))),"%H:%i:%s") AS average_wait_time'),
            DB::raw('SEC_TO_TIME(MAX(TIME_TO_SEC(TIMEDIFF(called_datetime, arrives_time)))) AS max_waiting_time'),
        ])
            ->where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->groupBy('category_id', 'sub_category_id', 'child_category_id')
            ->orderBy('category_id')
            ->orderBy('sub_category_id')
            ->orderBy('child_category_id')
            ->with(['category', 'subCategory', 'childCategory'])
            ->get();
    }

    public function downloadPdf()
    {

        $level1 = Category::where('id', $this->selectedLevel1)->value('name');
        $level2 = Category::where('id', $this->selectedLevel2)->value('name');
        $level3 = Category::where('id', $this->selectedLevel3)->value('name');

        $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->location);
       
        $this->loadCategories();

        $pdf = Pdf::loadView('pdf.sub-categories-report', [
            'categories' => $this->categories,
            'selectedLevel1' => $level1,
            'selectedLevel2' => $level2,
            'selectedLevel3' => $level3,
            'status' => implode(',',$this->status),
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'logo_src' => $logo,
            'level1' =>$this->level1,
            'level2' =>$this->level2,
            'level3' =>$this->level3,
        ])->setPaper('a4', 'landscape')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('margin-left', '10mm')
            ->setOption('margin-right', '10mm');;

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'sub-services-report.pdf');
    }


    public function downloadCsv()
    {


        $this->loadCategories();
        $level1 = Category::where('id', $this->selectedLevel1)->value('name');
        $data = [
            'categories' => $this->categories,
        ];
        $filter = [
            'selectedLevel1' => $level1,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'status' => implode(',',$this->status),
        ];
        $totals = $this->getTotals();
        
        $levels = [
            'level1' =>$this->level1,
            'level2' =>$this->level2,
            'level3' =>$this->level3,
        ];

        return Excel::download(new SubCategoriesExport($data, $filter, $totals,$levels), 'services-report.xlsx');
    }

    public function getTotals()
    {

        // Helper function to convert HH:MM:SS to seconds
        function timeToSecond($time)
        {
            // Check if $time is in HH:MM:SS format
            $timeParts = explode(':', $time);

            // Ensure we have exactly 3 parts (hours, minutes, seconds)
            if (count($timeParts) === 3) {
                list($hours, $minutes, $seconds) = $timeParts;
                return ($hours * 3600) + ($minutes * 60) + $seconds;
            } else {
                // Handle cases where time format is invalid
                return 0; // or handle as needed, e.g., throw an error or return null
            }
        }

        function secondToTime($seconds)
        {

            // Calculate hours, minutes, and seconds
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;

            // Format to HH:ii:ss
            $time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
            return $time; // Outputs: 375:18:01

        }

        $totals = [
            'total_calls' => 0,
            'pending_calls' => 0,
            'pending_percentage_total' => 0,
            'cancel_calls' => 0,
            'cancel_percentage_total' => 0,
            'served_calls' => 0,
            'served_percentage_total' => 0,
            'no_show' => 0,
            'no_show_percentage_total' => 0,
            'total_served_time' => 0,
            'average_served_time' => 0,
            'max_served_time' => 0,
            'total_waiting_less_15_min' => 0,
            'total_waiting_greater_15_min' => 0,
            'average_wait_time' => 0,
            'max_waiting_time' => 0
        ];

        foreach ($this->categories as $row) {
            $total_calls = $row['total_calls'];
            $served_calls = $row['served_calls'];
            $cancel_calls = $row['cancel_calls'];
            $pending_calls = $row['pending_calls'];
            $served_percentage = ($total_calls > 0) ? ($served_calls / $total_calls) * 100 : 0;
            $no_show_percentage = rtrim($row['no_show_percentage'], '%');

            $totals['total_calls'] += $total_calls;
            $totals['served_calls'] += $served_calls;
            $totals['cancel_calls'] += $cancel_calls;
            $totals['pending_calls'] += $pending_calls;
            $totals['served_percentage_total'] += $served_percentage;
            $totals['no_show'] += $row['no_show'];
            $totals['no_show_percentage_total'] += (float)$no_show_percentage;
            $totals['total_waiting_less_15_min'] += $row['total_waiting_less_15_min'];
            $totals['total_waiting_greater_15_min'] += $row['total_waiting_greater_15_min'];

            // Convert the served time to seconds
            $served_time_seconds = !empty($row['total_served_time']) ? timeToSecond($row['total_served_time']) : 0;
            $totals['total_served_time'] += $served_time_seconds;


            // Convert average wait time to seconds and accumulate
            if ($row['average_served_time']) {
                $parts1 = explode(':', $row['average_served_time']);
                $current_served_time_seconds = ($parts1) ? ($parts1[0] * 3600) + ($parts1[1] * 60) + $parts1[2] : 0;
                $totals['average_served_time'] += $current_served_time_seconds;
            }

            $current_max_served_time = strtotime($row['max_served_time']) - strtotime('TODAY');
            // Update the max_waiting_time only if the current row has a higher value
            if ($current_max_served_time > $totals['max_served_time']) {
                $totals['max_served_time'] = $current_max_served_time;
            }



            // Convert average wait time to seconds and accumulate
            if ($row['average_wait_time']) {
                $parts = explode(':', $row['average_wait_time']);
                $current_wait_time_seconds = ($parts) ? ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2] : 0;
                $totals['average_wait_time'] += $current_wait_time_seconds;
            }


            $current_max_waiting_time = strtotime($row['max_waiting_time']) - strtotime('TODAY');
            // Update the max_waiting_time only if the current row has a higher value
            if ($current_max_waiting_time > $totals['max_waiting_time']) {
                $totals['max_waiting_time'] = $current_max_waiting_time;
            }
        }

        return $totals;
    }

    public function render()
    {
        return view('livewire.sub-categories-report');
    }
}
