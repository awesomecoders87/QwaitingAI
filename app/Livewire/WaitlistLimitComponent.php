<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AccountSetting;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class WaitlistLimitComponent extends Component
{
    #[Title('Waitlist Limit')]

    public $teamId;
    public $locationId;
    public $type;
    public $iswaitlistlimit = false;
    public $waitlistlimit;

    public function mount($teamId,$locationId,$slotType){
        $this->teamId = $teamId;
        $this->locationId = $locationId;
        $this->type = $slotType;
     
        $accountdetail = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', $this->type)
        ->select('id','is_waitlist_limit','waitlist_limit')
        ->first();

        $this->iswaitlistlimit = $accountdetail->is_waitlist_limit ?? '';
        $this->waitlistlimit = $accountdetail->waitlist_limit ?? '';
    

    }

    public function saveSetting(){
      
        $accountdetail = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', $this->type)
        ->update([
            'is_waitlist_limit'=>$this->iswaitlistlimit,
            'waitlist_limit'=>$this->waitlistlimit,
         
        ]);
        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    
    }

  
    public function render()
    {
        return view('livewire.waitlist-limit-component');
    }
}
