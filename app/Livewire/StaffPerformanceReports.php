<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Category;
use App\Models\Location;
use App\Models\SiteDetail;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Config;

class StaffPerformanceReports extends Component
{
    use WithPagination;

    #[Title('Staff Performance Report')]
    public $created_from;
    public $created_until;
    public $locationId;
    public $teamId;
    public $search;
    public $allLocation = [];
    public $selectedlocation = [];

    public function mount()
    {
         $this->teamId = tenant('id');
        $timezone = Config::get('app.timezone');
        $this->locationId = Session::get('selectedLocation');
          $this->allLocation = Location::where('team_id', $this->teamId)
        ->where('status', 1)
        ->select('id','location_name')
        ->get();
        $this->created_from = now()->startOfMonth()->toDateString();
        $this->created_until = Carbon::now($timezone)->toDateString();
       

      
    }

    public function updating($field)
    {
        $this->resetPage();
    }

    protected function getFilteredUsers()
    {
        return User::withTrashed()->whereHas('queues', function ($query) {
    $query->where('status', '!=', 'Cancelled')
        ->whereBetween('datetime', [$this->created_from . ' 00:00:00', $this->created_until . ' 23:59:59']);

    if (!empty($this->selectedlocation)) {
        $query->whereIn('locations_id', $this->selectedlocation);
    }
})
->where('name', 'like', '%' . $this->search . '%')
->get();
    }

    protected function getUserStats($user, $categories)
    {
        $queuesQuery = $user->queues()
        ->where('status', '!=', 'Cancelled')
        ->whereDate('arrives_time', '>=', $this->created_from)
        ->whereDate('arrives_time', '<=', $this->created_until);

    // Conditionally apply location filter
    if (!empty($this->selectedlocation)) {
        if (is_array($this->selectedlocation)) {
            $queuesQuery->whereIn('locations_id', $this->selectedlocation);
        } 
    }

    $queues = $queuesQuery->get();

        $row = [
            $user->name,
            $queues->count(),
        ];

        foreach ($categories as $category) {
            $row[] = $queues->where('category_id', $category->id)->count();
        }

        $totalServedTime = $queues->sum(function ($q) {
            return ($q->start_datetime && $q->closed_datetime)
                ? $q->closed_datetime->diffInSeconds($q->start_datetime)
                : 0;
        });

        $avgTime = $queues->count() > 0 ? $totalServedTime / $queues->count() : 0;

        $row[] = CarbonInterval::seconds($totalServedTime)->cascade()->format('%H:%I:%S');
        $row[] = CarbonInterval::seconds($avgTime)->cascade()->format('%H:%I:%S');

        return $row;
    }

    public function exportCsv(): StreamedResponse
    {
        $fileName = 'staff_performance_report_' . now()->format('Ymd_His') . '.csv';
        $users = $this->getFilteredUsers();
        $categories = Category::getStaffReportHeader($this->teamId);

        return response()->streamDownload(function () use ($users, $categories) {
            $handle = fopen('php://output', 'w');

            // Translated headers
            fputcsv($handle, array_merge([
                __('report.Staff'),
                __('report.Visitors Served'),
            ], $categories->pluck('name')->toArray(), [
                __('report.Total Served Time'),
                __('report.Average Served Time'),
            ]));

            foreach ($users as $user) {
                fputcsv($handle, $this->getUserStats($user, $categories));
            }

            fclose($handle);
        }, $fileName);
    }

    public function exportPdf()
    {
        $users = $this->getFilteredUsers();
        $categories = Category::getStaffReportHeader($this->teamId);
        $logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId, $this->locationId);

        $pdf = Pdf::loadView('pdf.staff-performance-pdf', [
            'users' => $users,
            'categories' => $categories,
            'selectedlocation' => $this->selectedlocation,
            'from' => $this->created_from,
            'to' => $this->created_until,
            'logo_src' => $logo,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print($pdf->stream()), 'staff_performance_report_' . now()->format('Ymd_His') . '.pdf');
    }

    public function render()
    {

        $categories = Category::getStaffReportHeader($this->teamId);
    $users = User::withTrashed()->whereHas('queues', function ($query) {
            $query->where('status', '!=', 'Cancelled')
                ->whereDate('arrives_time', '>=', $this->created_from)
                ->whereDate('arrives_time', '<=', $this->created_until);

            if (!empty($this->selectedlocation)) {
                $query->whereIn('locations_id', $this->selectedlocation);
            }
        })
        ->when($this->search, function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%');
        })
        ->where('team_id', $this->teamId)
        ->paginate(10);
        //  dd($users,$this->selectedlocation) ;
        return view('livewire.staff-performance-reports', compact('users', 'categories'));
    }
}