<?php

namespace App\Livewire\Widgets;

use Livewire\Component;
use App\Models\Queue;
use App\Models\Rating;
use App\Models\FeedbackQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class FeedbackStatisticsChart extends Component
{
    protected static ?string $heading = 'Feedback Statistics Report';

    public ?string $fromSelectedDate;
    public ?string $toSelectedDate;
    public $teamId;
    public $location;
    public $chartDataFeedback = [];
  

    public function mount(): void
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->fromSelectedDate =date('Y-m-d');
        $this->toSelectedDate = date('Y-m-d');

        $this->refreshData(); // Fetch initial data
    }

    public function getData(): array
{
    // Fetch the feedback questions row
    $feedback = FeedbackQuestion::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->first();

    if (! $feedback || empty($feedback->questions)) {
        return [
            'label' => __('text.Calls'),
            'data' => [],
            'backgroundColor' => "#8daced",
            'borderColor' => "#8daced",
            'labels' => [],
        ];
    }

    // Decode questions JSON (stored as string)
    $questionArray = json_decode($feedback->questions, true); // array of objects

    // Extract only the question text
    $questions = collect($questionArray)->pluck('question')->toArray();

    $dataPoints = [];

    foreach ($questions as $question) {
        $count = Rating::join('queues', 'ratings.queue_id', '=', 'queues.id')
            ->where('ratings.team_id', $this->teamId)
            ->where('queues.locations_id', $this->location)
            ->where('ratings.question', $question)
            ->whereDate('ratings.created_at', '>=', $this->fromSelectedDate)
            ->whereDate('ratings.created_at', '<=', $this->toSelectedDate)
            ->count();

        $dataPoints[] = $count;
    }

    // Prepare chart data
    return $this->chartDataFeedback = [
        'label' => __('text.Calls'),
        'data' => $dataPoints,
        'backgroundColor' => "#8daced",
        'borderColor' => "#8daced",
        'labels' => $questions,
    ];
}

   

    // public function getData(): array
    // {

    //     $dataPoints = [];
       
    //     $counts=Rating::where('ratings.team_id', $this->teamId)
    //     ->join('queues', 'ratings.queue_id', '=', 'queues.id')
    //     ->where('queues.locations_id', $this->location)
    //     ->selectRaw('COUNT(CASE WHEN ratings.question = "Please Rate Our Service" THEN 1 END) as our_service')
    //     ->selectRaw('COUNT(CASE WHEN ratings.question = "Please Rate Our Staff" THEN 1 END) as our_staff')
    //     ->selectRaw('COUNT(CASE WHEN ratings.question = "Please Rate Overall Experience" THEN 1 END) as our_overall')
    //     ->whereDate('ratings.created_at', '>=', $this->fromSelectedDate)
    //     ->whereDate('ratings.created_at', '<=', $this->toSelectedDate)
    //     ->first()
    //     ->toArray();

    //   $dataPoints = [
    //     $counts['our_service'],
    //     $counts['our_staff'],
    //     $counts['our_overall'],
    // ];
       
    
    //   return $this->chartDataFeedback = [
    //                 'label' => __('text.Calls'),
    //                 'data' => $dataPoints,
    //                 'backgroundColor' => "#8daced",
    //                 'borderColor' => "#8daced",
    //                 'labels' => [__('text.Please Rate Our Service'),__('text.Please Rate Our Staff'),__('text.Please Rate Our Overall Experience')],
    //     ];

    // }

 

    // protected function getType(): string
    // {
    //     return 'bar';
    // }

    #[On('fromSelectedDateChanged')]
    public function updateFromDate($fromSelectedDate)
    {
        $this->fromSelectedDate = $fromSelectedDate;
        $this->refreshData();
    }

    #[On('toSelectedDateChanged')]
    public function updateToDate($toSelectedDate)
    {
        $this->toSelectedDate = $toSelectedDate;
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->getData();
        $this->dispatch('updateChartFeedback', $this->chartDataFeedback); // Ensure this method fetches data based on the updated dates
    }

    public function render()
    {
        return view('livewire.widgets.feedback-statistics-chart');
    }
}
