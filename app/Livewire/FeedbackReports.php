<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Rating;
use App\Models\Location;
use App\Models\SiteDetail;
use App\Models\User;
use App\Models\FeedbackQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\QueueStorage;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MainBranchExport;
use Barryvdh\DomPDF\Facade\Pdf;

class FeedbackReports extends Component
{
    use WithPagination;

    #[Title('Feedback Report')]

    public $createdFrom;
    public $createdUntil;
    public $selectedLocation;
    public $teamId;
    public $domain;
    public $users;
    public $staff;

    public function mount()
    {
        $this->selectedLocation = Session::get('selectedLocation');
        $this->createdFrom = now()->startOfMonth()->toDateString();
        $this->createdUntil = now()->toDateString();
        $this->teamId = tenant('id');
        $this->domain = tenant('name');
      
        $this->users = User::withTrashed()
        ->where(function ($query) {
                    $query->where('team_id', $this->teamId)
                        ->orWhere('id', Auth::id());
                })
                ->whereNotNull('locations')
                ->whereJsonContains('locations', "$this->selectedLocation")
                ->pluck('name', 'id');
        

    }

    public function updating($field)
    {
        $this->resetPage();
    }

    public function getStats()
    {
        $query = QueueStorage::where('team_id', $this->teamId)->where('locations_id', $this->selectedLocation);
        $ratingQuery = Rating::where('team_id', $this->teamId)->where('location_id', $this->selectedLocation);

        if ($this->createdFrom) {
            $query->whereDate('arrives_time', '>=', $this->createdFrom);
            $ratingQuery->whereDate('created_at', '>=', $this->createdFrom);
        }

        if ($this->createdUntil) {
            $query->whereDate('arrives_time', '<=', $this->createdUntil);
            $ratingQuery->whereDate('created_at', '<=', $this->createdUntil);
        }

        return [
            'totalQueue' => $query->count(),
            'closedQueue' => $query->where('status', 'Close')->count(),
            'averageRating' => number_format($ratingQuery->average('rating'), 2),
        ];
    }

    private function getExportRows()
{
    $feedbackDetail = FeedbackQuestion::where('team_id', $this->teamId)
        ->where('location_id', $this->selectedLocation)
        ->first();
    $questions = $feedbackDetail ? json_decode($feedbackDetail->questions, true) : [];

    $ratingRows = Rating::with(['queuestorage.closedBy']) // eager load queue and staff
        ->where('team_id', $this->teamId)
        ->where('location_id', $this->selectedLocation)
        ->when($this->createdFrom, fn($q) => $q->whereDate('created_at', '>=', $this->createdFrom))
        ->when($this->createdUntil, fn($q) => $q->whereDate('created_at', '<=', $this->createdUntil))
        ->when($this->staff, fn($q) => $q->where('user_id', $this->staff))
        ->orderBy('queue_storage_id')
        ->get()
        ->groupBy('queue_storage_id');

    $rows = $ratingRows->map(function ($items, $queueStorageId) use ($questions) {

        $first = $items->first();
        $queue = $first->queuestorage;

        $row = new \stdClass();
        $row->queue_storage_id = $queueStorageId;
        $row->name = $queue->name ?? 'N/A';
        $row->token = $queue ? $queue->start_acronym . $queue->token : 'N/A';
        $row->contact = $queue->full_phone_number ?? 'N/A';
        $row->comment = $first->comment ?? 'N/A';
        $row->datetime = \Carbon\Carbon::parse($first->created_at)->format('d-m-Y h:i:s A');
        $row->staff = optional($queue)->closedBy->name ?? 'N/A';
        $row->average_rating = $items->avg('rating');

        // Emoji rating
        $emojiData = collect(\App\Models\Queue::getEmojiText())
            ->first(fn($item) => $row->average_rating >= $item['range'][0] && $row->average_rating <= $item['range'][1]);

        $row->emoji = $emojiData['emoji'] ?? 'N/A';

        // Question ratings
        foreach ($questions as $q) {
            $qRating = $items->firstWhere('question', $q['question']);
            $row->{$q['question']} = $qRating->rating ?? 'N/A';
        }

        return $row;

    })->values();

    return [$rows, $questions];
}

  public function exportcsv()
{
    [$rows, $questions] = $this->getExportRows();

    $filters = [
        'Branch Name' => Location::locationName($this->selectedLocation),
        'Created From' => $this->createdFrom,
        'Created Until' => $this->createdUntil,
        'Staff' => !empty($this->staff) ? User::find($this->staff)->name : '',
    ];

    return Excel::download(new MainBranchExport($rows, $filters, $this->domain, $questions), 'branch-report.xlsx');
}

   public function exportpdf()
{
    [$rows, $questions] = $this->getExportRows();

    $logo_src = SiteDetail::viewImage(
        SiteDetail::FIELD_BUSINESS_LOGO,
        $this->teamId,
        $this->selectedLocation
    );

    $filters = [
        'Branch Name'  => Location::locationName($this->selectedLocation),
        'Created From' => $this->createdFrom,
        'Created Until'=> $this->createdUntil,
        'Staff' => !empty($this->staff) ? User::find($this->staff)->name : '',
    ];

    // ❗ Use $rows instead of $records
    $pdf = Pdf::loadView('pdf.feedback-pdf', [
        'records' => $rows,
        'filters' => $filters,
        'domain' => $this->domain,
        'logo_src' => $logo_src,
        'questions' => $questions,
    ]);

    return response()->streamDownload(function () use ($pdf) {
        echo $pdf->stream();
    }, 'feedback-report.pdf');
}

    public function render()
{
    if (!Auth::user()->hasPermissionTo('Report Read')) {
        abort(403);
    }

    $cardsDetails = $this->getStats();

    // Get the questions config for columns
    $feedbackDetail = FeedbackQuestion::where('team_id', $this->teamId)
        ->where('location_id', $this->selectedLocation)
        ->first();
    $questions = $feedbackDetail ? json_decode($feedbackDetail->questions, true) : [];

    // ---------
    // 1) Build a query that returns distinct queue_storage_id values (filtered)
    // ---------
    $idsQuery = Rating::query()
        ->select('queue_storage_id')
        ->where('team_id', $this->teamId)
        ->where('location_id', $this->selectedLocation)
        ->when($this->createdFrom, fn($q) => $q->whereDate('created_at', '>=', $this->createdFrom))
        ->when($this->createdUntil, fn($q) => $q->whereDate('created_at', '<=', $this->createdUntil))
        ->when($this->staff, fn($q) => $q->where('user_id', $this->staff))
        ->groupBy('queue_storage_id')
        ->orderBy('queue_storage_id');

    // ---------
    // 2) Paginate the distinct queue IDs at DB level (Livewire-friendly)
    //    Use the same $perPage you want in the view
    // ---------
    $perPage = 10; // change to your desired per-page
    $paginatedIds = $idsQuery->paginate($perPage);

    // If there are no ids, keep an empty collection for rows
    if ($paginatedIds->total() === 0) {
        $rows = collect();
    } else {
        // ---------
        // 3) Load ALL rating rows for the current page queue IDs and group them
        // ---------
        $queueIds = $paginatedIds->pluck('queue_storage_id')->filter()->values()->all();

       $ratingRows = Rating::with(['queuestorage.closedBy']) // eager load queue and staff who closed
		->whereIn('queue_storage_id', $queueIds)
		->where('team_id', $this->teamId)
		->where('location_id', $this->selectedLocation)
		->when($this->createdFrom, fn($q) => $q->whereDate('created_at', '>=', $this->createdFrom))
		->when($this->createdUntil, fn($q) => $q->whereDate('created_at', '<=', $this->createdUntil))
		->when($this->staff, fn($q) => $q->where('user_id', $this->staff))
		->orderBy('queue_storage_id')
		->get()
		->groupBy('queue_storage_id');

        // ---------
        // 4) Map grouped ratings into display rows (one row per queue_storage_id)
        // ---------
        $rows = $ratingRows->map(function ($items, $queueStorageId) use ($questions) {
            $first = $items->first();
            $queue = $first->queuestorage;

            $row = new \stdClass();
            $row->queue_storage_id = $queueStorageId;
            $row->name = $queue->name ?? 'N/A';
            $row->token = $queue ? $queue->start_acronym . $queue->token : 'N/A';
            $row->contact = $queue->full_phone_number ?? 'N/A';
            $row->comment = $first->comment ?? 'N/A';
            $row->datetime = Carbon::parse($first->created_at)->format('d-m-Y h:i:s A');
            $row->staff = optional($queue)->closedBy->name ?? 'N/A';
            $row->average_rating = $items->avg('rating');

            // question ratings
            foreach ($questions as $q) {
                $qRating = $items->firstWhere('question', $q['question']);
                $row->{$q['question']} = $qRating->rating ?? 'N/A';
            }

            return $row;
        })->values();
    }

    // ---------
    // 5) Create a paginator for the mapped rows but reuse pagination meta from $paginatedIds
    //    (so links, total, currentPage, lastPage remain correct for Livewire)
    // ---------
    $reports = new \Illuminate\Pagination\LengthAwarePaginator(
        $rows,
        $paginatedIds->total(),
        $perPage,
        $paginatedIds->currentPage(),
        ['path' => request()->url(), 'query' => request()->query()]
    );

    return view('livewire.feedback-reports', [
        'reports' => $reports,
        'cardsDetails' => $cardsDetails,
        'questions' => $questions,
    ]);
}
}
