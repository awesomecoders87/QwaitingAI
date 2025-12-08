<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use App\Models\QueueStorage;
use App\Models\Counter;
use App\Models\User;
use App\Models\Level;
use App\Models\FormField;
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

    public $teamId;
    public $locationId;
    public $user;
    public $search = '';
    public $level1,$level2,$level3;
    public $enablePriority = false;
    public $childUsers = [];
    public $subChildUsers = [];
    public $formfields = [];
    public $enable_doc_file_field = false;
    public $doc_file_label;
    public $enable_export_buttons = false;

    public function mount()
    {

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->created_from = now()->format('Y-m-d');
        $this->created_until = now()->format('Y-m-d');
        
		$siteDetail= Sitedetail::where(['team_id'=>$this->teamId,'location_id'=>$this->locationId])->select('use_staff_priority','enable_doc_file','doc_file_label')->first();
		
        $this->enablePriority = $siteDetail->use_staff_priority ?? false;
        $this->enable_doc_file_field = $siteDetail->enable_doc_file ?? false;
        $this->doc_file_label = $siteDetail->doc_file_label ?? 'Document Link';
        // Toggle this as needed; using doc file setting as default gate if separate setting not present
        $this->enable_export_buttons = (bool)($this->enable_doc_file_field);
        $this->user = Auth::user();

        $levels = Level::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');

        $this->level1 = $levels[1]->name ?? 'Level 1';
        $this->level2 = $levels[2]->name ?? 'Level 2';
        $this->level3 = $levels[3]->name ?? 'Level 3';

		if ($this->enablePriority && auth()->check()) {
		$user = auth()->user();

		$this->childUsers = collect();
		$this->subChildUsers = collect();

		if ($user->level_id == 1) {
			// Level 1: Get Managers
			if($this->user && ! $this->user->hasRole('Admin')){
			  $this->childUsers = User::withTrashed()
			  ->where('level_id', 2)
				->where('parent_id', $user->id)
				->whereJsonContains('locations', "$this->locationId")
				->get(['id', 'name']);

			// Get Agents under those Managers
			$managerIds = $this->childUsers->pluck('id');

			$this->subChildUsers = User::withTrashed()
			->where('level_id', 3)
				->whereIn('parent_id', $managerIds)
				->whereJsonContains('locations', "$this->locationId")
				->get(['id', 'name']);
			}else{ // Level 1: Has role Admin show all agents
				 $this->subChildUsers = User::withTrashed()
				->where('level_id', 3)
				->whereJsonContains('locations', "$this->locationId")
				->get(['id', 'name']);
			}


		} elseif ($user->level_id == 2) {
			// Level 2: Get Agents
			$this->subChildUsers = User::withTrashed()
			->where('level_id', 3)
				->where('parent_id', $user->id)
				->whereJsonContains('locations', "$this->locationId")
				->get(['id', 'name']);

			// No sub-child users at level 3
			// $this->subChildUsers = collect();
		}elseif ($user->level_id == 3) {
			// Agent or lowest level: Only show self
		   $this->subChildUsers = User::withTrashed()
		->where('id', $user->id)
		->whereJsonContains('locations', "$this->locationId")
		->get(['id', 'name']);

		}
	}

        $this->formfields = FormField::where('team_id', $this->teamId)->where('location_id',$this->locationId)->get();

    }

    public function updating($field)
    {
        $this->resetPage();
    }
   public function exportCSV()
{
    $this->locationId = Session::get('selectedLocation');

    $query = QueueStorage::query()
        ->where('locations_id', $this->locationId)
        // Eager load relations to prevent N+1 queries
        ->with(['assignStaff:id,name', 'closedBy:id,name', 'Counter:id,name']);

    // Use date range instead of whereDate for better index usage
    if ($this->created_from) {
        $query->where('arrives_time', '>=', $this->created_from . ' 00:00:00');
    }

    if ($this->created_until) {
        $query->where('arrives_time', '<=', $this->created_until . ' 23:59:59');
    }

    // Filter by closed_by or assign_staff_id
    if (!empty($this->closed_by)) {
        $column = $this->enablePriority ? 'assign_staff_id' : 'closed_by';
        $query->whereIn($column, $this->closed_by);
    } elseif ($this->enablePriority) {
        $query->whereIn('assign_staff_id', $this->subChildUsers->pluck('id')->toArray());
    }

    // Filter by counter
    if (!empty($this->counter_id)) {
        $query->whereIn('counter_id', $this->counter_id);
    }

    // Filter by status
    if (!empty($this->status)) {
        $query->where(function ($q) {
            $q->whereIn('status', $this->status);
            if (in_array('Skip', $this->status)) {
                $q->orWhere('is_missed', 1);
            }
        });
    }

    // Filter by ticket mode
    if (!empty($this->ticket_mode)) {
        $query->whereIn('ticket_mode', $this->ticket_mode);
    }

    // Search filter
    if (!empty($this->search)) {
        $numericPart = preg_replace('/^\D+/', '', $this->search);
        $search = $this->search;

        $query->where(function ($q) use ($search, $numericPart) {
            $q->where('name', 'like', "%$search%")
              ->orWhere('token', 'like', "%$search%")
              ->orWhere('token', 'like', "%$numericPart%")
              ->orWhere('phone', 'like', "%$search%")
              ->orWhereJsonContains('json->email', $search);
        });
    }

    // Get filtered reports
    $reports = $query->orderBy('arrives_time', 'desc')->get();

    // Prepare filters and levels for export
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
        'level1' => $this->level1,
        'level2' => $this->level2,
        'level3' => $this->level3,
    ];

    // Export Excel
    return Excel::download(
        new MainMonthlyReport(
            $reports,
            $filters,
            $levels,
            $this->formfields,
            [
                'enable_export_buttons' => $this->enable_export_buttons,
                'doc_file_label' => $this->doc_file_label
            ]
        ),
        'monthly-report.xlsx'
    );
}




   public function exportToPDF()
	{
		$this->locationId = Session::get('selectedLocation');

		$query = QueueStorage::query()
			->where('locations_id', $this->locationId)
			// Eager load related models to prevent N+1 queries
			->with(['assignStaff:id,name', 'closedBy:id,name', 'Counter:id,name']);

		// Use date filtering for better index usage
		if ($this->created_from) {
			$query->where('arrives_time', '>=', $this->created_from . ' 00:00:00');
		}

		if ($this->created_until) {
			$query->where('arrives_time', '<=', $this->created_until . ' 23:59:59');
		}

		// Filter by closed_by or assign_staff_id
		if (!empty($this->closed_by)) {
			$column = $this->enablePriority ? 'assign_staff_id' : 'closed_by';
			$query->whereIn($column, $this->closed_by);
		} elseif ($this->enablePriority) {
			$query->whereIn('assign_staff_id', $this->subChildUsers->pluck('id')->toArray());
		}

		// Filter by counter
		if (!empty($this->counter_id)) {
			$query->whereIn('counter_id', $this->counter_id);
		}

		// Filter by status
		if (!empty($this->status)) {
			$query->where(function ($q) {
				$q->whereIn('status', $this->status);
				if (in_array('Skip', $this->status)) {
					$q->orWhere('is_missed', 1);
				}
			});
		}

		// Filter by ticket mode
		if (!empty($this->ticket_mode)) {
			$query->whereIn('ticket_mode', $this->ticket_mode);
		}

		// Search filter
		if (!empty($this->search)) {
			$numericPart = preg_replace('/^\D+/', '', $this->search);
			$search = $this->search;

			$query->where(function ($q) use ($search, $numericPart) {
				$q->where('name', 'like', "%$search%")
				  ->orWhere('token', 'like', "%$search%")
				  ->orWhere('token', 'like', "%$numericPart%")
				  ->orWhere('phone', 'like', "%$search%")
				  ->orWhereJsonContains('json->email', $search);
			});
		}

		// Get filtered reports
		$reports = $query->orderBy('arrives_time', 'desc')->get();

		// Prepare PDF data
		$logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId, $this->locationId);

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
			'level1' => $this->level1,
			'level2' => $this->level2,
			'level3' => $this->level3,
			'formfields' => $this->formfields,
			'enable_export_buttons' => $this->enable_export_buttons,
			'doc_file_label' => $this->doc_file_label,
		];

		// Generate PDF
		$pdf = Pdf::loadView('pdf.monthly-report', $data)
				  ->setPaper('a4', 'landscape');

		return response()->streamDownload(
			fn () => print($pdf->stream()),
			"Monthly-Report.pdf"
		);
	}


    public function render()
    {
        $this->locationId = Session::get('selectedLocation');
        $user = Auth::user();
        $query = QueueStorage::query()
				->where('locations_id', $this->locationId)
				->with([
					'category:id,name',
					'subCategory:id,name',
					'childCategory:id,name',
					'Counter:id,name',
					'servedBy:id,name',
					'closedBy:id,name',
					'assignStaff:id,name'
				]);

         if ($this->created_from) {
			$query->where('arrives_time', '>=', $this->created_from . ' 00:00:00');
		}

		if ($this->created_until) {
			$query->where('arrives_time', '<=', $this->created_until . ' 23:59:59');
		}

          if (!empty($this->closed_by)) {

            $column = $this->enablePriority ? 'assign_staff_id' : 'closed_by';
            $query->whereIn($column, $this->closed_by);
        } elseif ($this->enablePriority) {

            $query->whereIn('assign_staff_id', $this->subChildUsers->pluck('id')->toArray());
        }

        if (!empty($this->counter_id)) {
            $query->whereIn('counter_id', $this->counter_id);
        }

        if (!empty($this->status)) {
            $query->where(function ($q) {
                $q->whereIn('status', $this->status);

              if (in_array('Skip', $this->status)) {
                $q->orWhere('is_missed', 1);
            }
            });
        }

        if (!empty($this->ticket_mode)) {
            $query->whereIn('ticket_mode', $this->ticket_mode);
        }
        if(!empty($this->search)) {
            $numericPart = preg_replace('/^\D+/', '', $this->search);

            $query->where(function($q) use ($numericPart) {
                $search = $this->search;

                $q->where('name', 'like', "%$search%")
                ->orWhere('token', 'like', "%$search%")
                ->orWhere('token', 'like', "%$numericPart%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhereJsonContains('json->email', $search);
            });
        }

            $reports = $query->orderBy('arrives_time', 'desc')->paginate('10');
        if ($this->enablePriority) {
            $this->users = $this->subChildUsers->pluck('name', 'id')->toArray();
        }else{
        $this->users = User::withTrashed()
        ->where(function ($query) {
                    $query->where('team_id', $this->teamId)
                        ->orWhere('id', Auth::id());
                })
                ->whereNotNull('locations')
                ->whereJsonContains('locations', "$this->locationId")
                ->pluck('name', 'id');
        }



        $this->counters =Counter::withTrashed()
        ->where('team_id', $this->teamId)->whereJsonContains('counter_locations',"$this->locationId")->pluck('name', 'id');

        return view('livewire.monthly-report', [
            'reports' => $reports,
            'users' =>$this->users,
            'counters' => $this->counters,
        ]);
    }
}
