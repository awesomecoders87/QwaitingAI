<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Rating;
use App\Models\Queue;
use App\Models\SiteDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StaffRatingsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class StaffRatingSummary extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $staff = '';
    public $teamId;
    public $locationId;

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->startDate = now()->toDateString();
        $this->endDate   = now()->toDateString();
    }

    public function updated($field)
    {
        $this->resetPage();
    }


    protected function getQuery()
    {
        $start = $this->startDate . " 00:00:00";
        $end   = $this->endDate . " 23:59:59";

        return User::where('team_id', $this->teamId)
            ->whereJsonContains('locations', (String)$this->locationId)
            ->select(
                'users.id',
                'users.name',
                DB::raw("(SELECT COUNT(*) FROM queues_storage 
                    WHERE queues_storage.closed_by = users.id 
                    AND queues_storage.locations_id = '$this->locationId'
                    AND queues_storage.arrives_time BETWEEN '$start' AND '$end') AS guest_served"),
                DB::raw("(SELECT COUNT(*) FROM ratings 
                    WHERE ratings.user_id = users.id 
                    AND ratings.location_id = '$this->locationId'
                    AND ratings.created_at BETWEEN '$start' AND '$end') AS total_feedback"),
                DB::raw("(SELECT COUNT(*) FROM ratings 
                    WHERE ratings.user_id = users.id 
                    AND ratings.rating = 4
                    AND ratings.location_id = '$this->locationId'
                    AND ratings.created_at BETWEEN '$start' AND '$end') AS star4"),
                DB::raw("(SELECT COUNT(*) FROM ratings 
                    WHERE ratings.user_id = users.id 
                    AND ratings.rating = 3
                    AND ratings.location_id = '$this->locationId'
                    AND ratings.created_at BETWEEN '$start' AND '$end') AS star3"),
                DB::raw("(SELECT COUNT(*) FROM ratings 
                    WHERE ratings.user_id = users.id 
                    AND ratings.rating = 2
                    AND ratings.location_id = '$this->locationId'
                    AND ratings.created_at BETWEEN '$start' AND '$end') AS star2"),
                DB::raw("(SELECT COUNT(*) FROM ratings 
                    WHERE ratings.user_id = users.id 
                    AND ratings.rating = 1
                    AND ratings.location_id = '$this->locationId'
                    AND ratings.created_at BETWEEN '$start' AND '$end') AS star1"),
                DB::raw("(SELECT COALESCE(AVG(ratings.rating),0) FROM ratings 
                    WHERE ratings.user_id = users.id 
                    AND ratings.location_id = '$this->locationId'
                    AND ratings.created_at BETWEEN '$start' AND '$end') AS avg_rating")
            )
            ->when($this->staff, fn($q) => $q->where('users.id', $this->staff))
            ->orderByDesc('total_feedback');
    }

    public function getStaffRatingsProperty()
    {
        return $this->getQuery()->paginate(10);
    }

  public function exportCsv()
{
    $records = $this->getQuery()->get();
    $export = new StaffRatingsExport(
        $records,
        'Staff Ratings',
        $this->startDate,
        $this->endDate
    );
    
    return Excel::download($export, 'staff-ratings-' . now()->format('Y-m-d') . '.xlsx');
}

  public function exportPdf()
{
     $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->locationId);
    $data = [
        'records' => $this->getQuery()->get(),
        'startDate' => $this->startDate,
        'endDate' => $this->endDate,
        'title' => 'Staff Ratings Report',
        'logo' => $logo,
    ];

     $pdf = Pdf::loadView('pdf.staff-ratings-pdf', $data)->setPaper('a4', 'landscape');
    return response()->streamDownload(
        fn () => print($pdf->stream()),
        "staff-ratings.pdf"
    );

  
}

    public function render()
    {
        return view('livewire.staff-rating-summary', [
            'records' => $this->staffRatings,
            'staffList' => User::where('team_id', $this->teamId)
            ->whereJsonContains('locations', (String)$this->locationId)
            ->pluck('name', 'id')->toArray(),
        ]);
    }
}
