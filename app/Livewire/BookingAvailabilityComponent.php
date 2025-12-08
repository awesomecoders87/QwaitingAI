<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceSetting;
use App\Models\AccountSetting;
use App\Models\SiteDetail;
use App\Models\ActivityLog;
use App\Models\CustomSlot;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class BookingAvailabilityComponent extends Component
{
    public $teamId;
    public $locationId;
    public $slotPeriod;
    public $requestPerSlot;
    public $choose_time_slot;
    public $is_customer_login;
    public $is_prefer_time_slot;
    public $assignedStaffId;
    public $slots =[];
    public $enableTimeSlots =[];
    public $userAuth;


    public function mount($teamId,$locationId){
        $this->teamId = $teamId;
        $this->locationId = $locationId;
        $this->slots = AccountSetting::periodOfSlot();
        $this->enableTimeSlots = SiteDetail::getTimeSlotEnable();
        $this->userAuth = Auth::user();

        $accountdetail = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', AccountSetting::BOOKING_SLOT)
        ->select('id','slot_period','req_per_slot')
        ->first();

        $siteDetail = SiteDetail::where('team_id', $this->teamId)->where('location_id',$this->locationId)->select('choose_time_slot','is_customer_login','is_prefer_time_slot','assigned_staff_id')->first();

        $this->slotPeriod = $accountdetail->slot_period ?? '';
        $this->requestPerSlot = $accountdetail->req_per_slot ?? '';
        $this->choose_time_slot = $siteDetail->choose_time_slot ?? 'staff';
        $this->is_customer_login = $siteDetail->is_customer_login ?? 0;
        $this->is_prefer_time_slot = $siteDetail->is_prefer_time_slot ?? 0;
        $this->assignedStaffId = $siteDetail->assigned_staff_id ?? 0;


    }

    public function saveAvailability(){
        $accountdetail = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', AccountSetting::BOOKING_SLOT)
        ->update([
            'slot_period'=>$this->slotPeriod,
            'req_per_slot'=>$this->requestPerSlot,
        ]);

        $sitedetail = SiteDetail::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->update([
            'choose_time_slot'=>$this->choose_time_slot ?? 'staff',
            'is_customer_login'=>$this->is_customer_login ?? 0,
            'is_prefer_time_slot'=>$this->is_prefer_time_slot ?? 0,
            'assigned_staff_id'=>$this->assignedStaffId ?? 0,
        ]);

        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);

        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    // $this->dispatch('notify', type: 'success', message: 'Availability settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.booking-availability-component');
    }
}
