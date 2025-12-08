<?php

namespace App\Livewire\Branches;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use App\Models\QueueStorage;
use App\Models\Counter;
use App\Models\User;
use App\Models\Level;
use App\Models\Location;
use App\Models\AccountSetting;
use App\Models\SiteDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MainMonthlyReport;
use App\Jobs\ExportReportCSV;
use Livewire\Attributes\On; 
use Livewire\Attributes\Title;
use League\Csv\Writer;
use SplTempFileObject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class MonthlyReport extends Component
{
    use WithPagination;
    
    #[Title('Monthly Report')]

    public $created_from;
    public $created_until;
    public $closed_by = [];
    public $counter_id = [];
    public $status = [];
    public $ticket_mode = [];
    public $users = [];
    public $counters = [];
    public $allLocation = [];

    public $teamId;
    public $locationId;
    public $selectedlocation;
    public $search = '';
    public $level1,$level2,$level3;

    public function mount()
    {
   
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->created_from = now()->format('Y-m-d');
        $this->created_until = now()->format('Y-m-d');

        $this->allLocation = Location::where('team_id', $this->teamId)
        ->where('status', 1)
        ->select('id','location_name')
        ->get();

        $levels = Level::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');

       $this->level1 = $levels[1]->name ?? 'Level 1';
       $this->level2 = $levels[2]->name ?? 'Level 2';
       $this->level3 = $levels[3]->name ?? 'Level 3';
    }

    public function updating($field)
    {
        $this->resetPage();
    }
    public function exportCSV(){

        $this->locationId = Session::get('selectedLocation');

        $query = QueueStorage::query();
        // ->where('locations_id', $this->locationId);
    
        if ($this->created_from) {
            $query->whereDate('created_at', '>=', $this->created_from);
        }
    
        if ($this->created_until) {
            $query->whereDate('created_at', '<=', $this->created_until);
        }
    
        if (!empty($this->closed_by)) {
            $query->whereIn('closed_by', $this->closed_by);
        }
    
        if (!empty($this->counter_id)) {
            $query->whereIn('counter_id', $this->counter_id);
        }
    
        if (!empty($this->status)) {
            $query->whereIn('status', $this->status);
        }
        if (!empty($this->selectedlocation)) {
            $query->whereIn('locations_id', $this->selectedlocation);
        }
    
        if (!empty($this->ticket_mode)) {
            $query->whereIn('ticket_mode', $this->ticket_mode);
        }
        if(!empty($this->search)) {
        
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('token', 'like', '%' . $this->search . '%')
                  ->orWhere('start_acronym', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhereJsonContains('json->email', $this->search);
          
        }
    
        $reports = $query->orderBy('created_at', 'desc')->get();
        // $reports = $this->reports; // use the same filtered reports
       
    
        // $data = [
        //     'reports' => $reports,
        //     'users' => $this->users,
        //     'counters' => $this->counters,
        //     'dateformat' => auth()->user()->date_format ?? 'd M Y',
        // ];
        $filters = [
            'created_from' => $this->created_from,
            'created_until' => $this->created_until,
            'closed_by' => $this->closed_by,
            'counter_id' => $this->counter_id,
            'status' => $this->status,
            'search' => $this->search,
            'ticket_mode' => $this->ticket_mode,
        ];

        $levels = [
            'level1' =>$this->level1,
            'level2' =>$this->level2,
            'level3' =>$this->level3,
        ];
    
        return Excel::download(new MainMonthlyReport($reports, $filters,$levels), 'monthly-report.xlsx');
    }
  
   

    public function exportToPDF()
{
    $this->locationId = Session::get('selectedLocation');

    $query = QueueStorage::query();
    // ->where('locations_id', $this->locationId);

    if ($this->created_from) {
        $query->whereDate('created_at', '>=', $this->created_from);
    }

    if ($this->created_until) {
        $query->whereDate('created_at', '<=', $this->created_until);
    }

    if (!empty($this->closed_by)) {
        $query->whereIn('closed_by', $this->closed_by);
    }

    if (!empty($this->counter_id)) {
        $query->whereIn('counter_id', $this->counter_id);
    }

    if (!empty($this->selectedlocation)) {
        $query->whereIn('locations_id', $this->selectedlocation);
    }

    if (!empty($this->status)) {
        $query->whereIn('status', $this->status);
    }

    if (!empty($this->ticket_mode)) {
        $query->whereIn('ticket_mode', $this->ticket_mode);
    }
    if(!empty($this->search)) {
        
        $query->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('token', 'like', '%' . $this->search . '%')
              ->orWhere('start_acronym', 'like', '%' . $this->search . '%')
              ->orWhere('phone', 'like', '%' . $this->search . '%')
              ->orWhereJsonContains('json->email', $this->search);
      
    }

    $reports = $query->orderBy('created_at', 'desc')->get();
    // $reports = $this->reports; // use the same filtered reports
   
    $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->locationId);
    $data = [
        'reports' => $reports,
        'users' => $this->users,
        'counters' => $this->counters,
        'created_from' => $this->created_from,
        'created_until' => $this->created_until,
        'closed_by' => $this->closed_by,
        'counter_id' => $this->counter_id,
        'status' => $this->status,
        'search' => $this->search,
        'ticket_mode' => $this->ticket_mode,
        'dateformat' => auth()->user()->date_format ?? 'd M Y',
        'logo_src' => $logo,  
        'level1' =>$this->level1,
        'level2' =>$this->level2,
        'level3' =>$this->level3,
   
    ];

    $pdf = Pdf::loadView('pdf.monthly-report', $data)->setPaper('a4', 'landscape');
    return response()->streamDownload(
        fn () => print($pdf->stream()),
        "Monthly-Report.pdf"
    );
}

  

    public function render()
    {
        // $this->locationId = Session::get('selectedLocation');

        $query = QueueStorage::query()->where('team_id',$this->teamId);
        // ->where('locations_id', $this->locationId);

        if ($this->created_from) {
            $query->whereDate('created_at', '>=', $this->created_from);
        }

        if ($this->created_until) {
            $query->whereDate('created_at', '<=', $this->created_until);
        }

        if (!empty($this->closed_by)) {
            $query->whereIn('closed_by', $this->closed_by);
        }

        if (!empty($this->counter_id)) {
            $query->whereIn('counter_id', $this->counter_id);
        }

        if (!empty($this->selectedlocation)) {
            $query->whereIn('locations_id', $this->selectedlocation);
        }

        if (!empty($this->status)) {
            $query->whereIn('status', $this->status);
        }

        if (!empty($this->ticket_mode)) {
            $query->whereIn('ticket_mode', $this->ticket_mode);
        }
     
        // if(!empty($this->search)) {
        
        //     $query->where('name', 'like', '%' . $this->search . '%')
        //           ->orWhere('token', 'like', '%' . $this->search . '%')
        //           ->orWhere('phone', 'like', '%' . $this->search . '%')
        //           ->orWhereJsonContains('json->email', $this->search);
          
        // }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('token', 'like', '%' . $this->search . '%')
                  ->orWhere('start_acronym', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhereJsonContains('json->email', $this->search);
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate('10');

        $this->users = User::where(function ($query) {
            $query->where('team_id', $this->teamId)
                  ->orWhere('id', Auth::id());
        })

        // ->whereNotNull('locations')
        // ->whereJsonContains('locations', "$this->locationId")
        ->pluck('name', 'id');
        $this->counters =Counter::where('team_id', $this->teamId)
        // ->whereJsonContains('counter_locations',"$this->locationId")
        ->pluck('name', 'id');

        return view('livewire.branches.monthly-report', [
            'reports' => $reports,
            'users' =>$this->users,
            'counters' => $this->counters,
        ]);
    }
}
