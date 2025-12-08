<?php

namespace App\Livewire;
use App\Models\BreakReason;
use App\Models\StaffBreak;
use App\Events\BreakEvent;
use App\Models\Queue;
use Carbon\Carbon;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Auth;


use Livewire\Component;

class BreakRequest extends Component
{
    use WithPagination;

    #[Title('Break Request')]

    public $teamId;
    public $location;
    public $search;
    public $showStatusModal = false;
    public $selectedStatus;
    public $selectedBreakId;
    public $timezone;

    public function mount(){
        $this->teamId = tenant('id');
        $this->location = Session::get( 'selectedLocation' );
          Queue::timezoneSet();

        $this->timezone = Session::get('timezone_set') ?? 'UTC';
    }

   
public function openStatusModal($breakId)
{
    $this->selectedBreakId = $breakId;
    $this->selectedStatus = null;
    $this->showStatusModal = true;
}

public function updateStatus()
{

    $this->validate([
        'selectedStatus' => 'required|in:0,1,2',
    ]);

    
        Queue::timezoneSet();

        $this->timezone = Session::get('timezone_set') ?? 'UTC';
    $startTime = null;
  if($this->selectedStatus == 1){
     $startTime = Carbon::now($this->timezone);
  }
    $break = StaffBreak::find($this->selectedBreakId);
    if ($break) {

        $break->status = $this->selectedStatus;
         if($this->selectedStatus == 1){
             $break->time_start =  $startTime;
             $break->time_end =  null;
         }

        $break->approved_by = auth()->id();
        $break->approved_at = now();
        $break->save();
    }

    BreakEvent::dispatch($break);

    $this->reset(['selectedStatus', 'selectedBreakId', 'showStatusModal']);
    $this->dispatch('toast', title: 'Status updated successfully', type: 'success');
}

    public function render()
    {
            $staffBreaks = StaffBreak::with('staff') // eager load staff
        ->where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->when($this->search, function ($q) {
            $q->where(function ($query) {
                $query->where('reason', 'like', '%' . $this->search . '%')
                      ->orWhereHas('staff', function ($q2) {
                          $q2->where('name', 'like', '%' . $this->search . '%');
                      });
            });
        })
        ->orderBy('id', 'desc')
        ->paginate(10);

        return view('livewire.break-request',[
            'staffBreaks' => $staffBreaks
        ]);
    }
}
