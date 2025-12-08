<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BreakReason;
use App\Models\ActivityLog;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;


class BreakReasonForm extends Component
{
    public $team_id;
    public $locationId;
    public $reason, $break_time, $is_approved = false, $created_by;
    public $breakReasonId;
    public $allLocations = [];
    public $userAuth;

     #[Validate('required|array')]
    public $break_locations=[];

    public function mount($breakReasonId = null)
    {
        $this->team_id = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();
        $this->created_by = Auth::id();

         $this->allLocations = Location::where('team_id', $this->team_id)->select('id', 'location_name')->get();
        if(Auth::user()->is_admin == 1){
            $this->allLocations = Location::where('team_id', tenant('id'))
            ->where('status',1)
            ->select('location_name', 'id')
            ->get();
        }else{
            $this->allLocations = Location::where('team_id', tenant('id'))
            ->where('status',1)
            ->where('id', Auth::user()?->locations)
            ->select('location_name', 'id')
            ->get();
        }

        if ($breakReasonId) {
            $this->breakReasonId = $breakReasonId;
            $breakReason = BreakReason::findOrFail($breakReasonId);
            $this->reason = $breakReason->reason;
            $this->break_time = $breakReason->break_time;
            $this->is_approved = $breakReason->is_approved;
             $this->break_locations = $breakReason->break_location ?? [];

        }
    }
     protected function rules()
    {
        return [
            'reason' => 'required|string|max:255',
            'break_time' => 'required|in:10,15,30,60',
            'is_approved' => 'required|boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        BreakReason::updateOrCreate(
            ['id' => $this->breakReasonId,'team_id'=>$this->team_id],
            [
                'reason' => $this->reason,
                'break_time' => $this->break_time,
                'is_approved' => $this->is_approved,
                'break_location'=>$this->break_locations,
                'created_by' => $this->created_by,

            ]
        );

        session()->flash('success', $this->breakReasonId ? 'Break Reason updated.' : 'Break Reason created.');
         ActivityLog::storeLog($this->team_id, $this->userAuth->id, null, null, 'Break Add', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        return redirect()->to('/break-reason');
    }

    public function render()
    {
        return view('livewire.break-reason-form');
    }
}
