<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceSetting;
use App\Models\AccountSetting;
use App\Models\CustomSlot;
use Illuminate\Support\Facades\Session;

class MaxBookingPerCustomerComponent extends Component
{

    public $teamId;
    public $locationId;
    public $calendarPaxRange;
    public $calendarPaxRangePeriod;
    public $slots =[];

    public function mount($teamId,$locationId){
        $this->teamId = $teamId;
        $this->locationId = $locationId;
        $this->slots = AccountSetting::maxBookingOptions();

        $accountdetail = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', AccountSetting::BOOKING_SLOT)
        ->select('id','calendar_pax_range','calendar_pax_range_period')
        ->first();

        $this->calendarPaxRange = $accountdetail->calendar_pax_range ?? '';
        $this->calendarPaxRangePeriod = $accountdetail->calendar_pax_range_period ?? '';


    }

    public function saveSetting(){
      
        $accountdetail = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', AccountSetting::BOOKING_SLOT)
        ->update([
            'calendar_pax_range'=>$this->calendarPaxRange,
            'calendar_pax_range_period'=>$this->calendarPaxRangePeriod, 
        ]);

        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    // $this->dispatch('notify', type: 'success', message: 'Settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.max-booking-per-customer-component');
    }
}
