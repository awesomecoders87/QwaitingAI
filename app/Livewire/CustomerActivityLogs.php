<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Customer;
use App\Models\Level;
use App\Models\QueueStorage;
use App\Models\Counter;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SmsAPI;
use App\Models\SmsReport;
use App\Models\Booking;
use App\Models\AccountSetting;

class CustomerActivityLogs extends Component
{
    use WithPagination;
    
    public $teamId;
    public $locationId;
    public $customer;
    public $queueIds = [];
    public $bookingIds = [];
    public $counter_id = [];
    public $status = [];
    public $activityLogs = [];
    public $level1,$level2,$level3;
    public $created_from;
    public $created_until;
    public $showModal = false;
    public $selectedReport;
    public $notice_sms;
    public $sms;
     public $accountdetail;
     public $bookingstatus;
     public $search;


    public function mount($customerId)
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->created_from = now()->format('Y-m-d');
        $this->created_until = now()->format('Y-m-d');
        $this->customer = Customer::where('team_id',$this->teamId)
        ->where('id',$customerId)
        ->where('location_id',$this->locationId)
        ->first();


        if(empty($this->customer)){
            abort(404);
        }

          $this->accountdetail = AccountSetting::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->where('slot_type', AccountSetting::BOOKING_SLOT)->first();

        if(empty($this->accountdetail)){
            abort(403);
        }

        $this->queueIds = $this->customer->activityLogs()->latest()->pluck('queue_id')->toArray();
        $this->bookingIds = $this->customer->activityLogs()->latest()->pluck('booking_id')->toArray();
      
        $this->selectedReport = '';

        $levels = Level::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');

       $this->level1 = $levels[1]->name ?? 'Level 1';
       $this->level2 = $levels[2]->name ?? 'Level 2';
       $this->level3 = $levels[3]->name ?? 'Level 3';
        $this->showModal = true;
    }

    public function viewReport($id)
    {
        $this->selectedReport = QueueStorage::where('id', $id)->first();
         $this->activityLogs = ActivityLog::viewLogs($this->teamId, $this->selectedReport->queue_id,$id,$this->locationId);
         $this->notice_sms = $this->selectedReport->esitmate_note ?? '';
        $this->showModal = true;
    }

    public function submitEstimateNote()
    {
        QueueStorage::where('id',$this->selectedReport->id)->update(['esitmate_note' => $this->notice_sms]);
        
        $this->dispatch('event-success-call', ['message' => 'Estimate note updated successfully!']);
    }

    public function sendSMS()
    {
       $contact = QueueStorage::where('id',$this->selectedReport->id)->select('phone','phone_code')->first();
if(!empty( $contact['phone'])){
 $phone_code = isset($contact['phone_code']) ? ltrim($contact['phone_code'], '+') : '91';
    $phone = $contact['phone'];
    $contactWithCode= $phone_code.$phone;
       $status = SmsAPI::currentQueueSms($contactWithCode,$this->sms);
         ActivityLog::storeLog($this->teamId, auth()->user()->id, $this->selectedReport->queue_id, $this->selectedReport->id, ActivityLog::SEND_SMS, $this->locationId);
        SmsReport::create([
            'team_id'     => $this->teamId,
            'location_id' => $this->locationId,
            'message'     => $this->sms,
            'contact'     =>  $contactWithCode ?? '',
            'status'      => $status == true ? 'sent' : 'failed', // e.g., 'sent', 'failed', etc.
            'channel'     => 'sms',
            'type'        => 'queue',
            'queue_storage_id'=> $this->selectedReport->id,
        ]);
       $this->sms= '';
        $this->dispatch('event-success-call', ['message' => 'SMS sent successfully!']);
    }

    }


    public function render()
    {
       
          $this->locationId = Session::get('selectedLocation');

        $query = QueueStorage::query()
        ->whereIn('id',$this->queueIds)
        ->where('locations_id', $this->locationId);

        if ($this->created_from) {
            $query->whereDate('created_at', '>=', $this->created_from);
        }

        if ($this->created_until) {
            $query->whereDate('created_at', '<=', $this->created_until);
        }


        if (!empty($this->counter_id)) {
            $query->whereIn('counter_id', $this->counter_id);
        }

        if (!empty($this->status)) {
            $query->whereIn('status', $this->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate('5');


        $queryBooking = Booking::query()
        ->whereIn('id',$this->bookingIds)
        ->where('location_id', $this->locationId);

        if ($this->created_from) {
            $queryBooking->whereDate('created_at', '>=', $this->created_from);
        }

        if ($this->created_until) {
            $queryBooking->whereDate('created_at', '<=', $this->created_until);
        }

        // if (!empty($this->status)) {
        //     $queryBooking->whereIn('status', $this->status);
        // }
    

        if ($this->bookingstatus) {
            $queryBooking->where('status', $this->bookingstatus);
        }

          $queryBooking->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('refID', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%');
                });
            });

        $bookingreports = $queryBooking->orderBy('created_at', 'desc')->paginate('5');

        
    
        $this->counters =Counter::where('team_id', $this->teamId)->whereJsonContains('counter_locations',"$this->locationId")->pluck('name', 'id');
        return view('livewire.customer-activity-logs',[
            'reports' => $reports,
            'bookings' => $bookingreports,
            'counters' => $this->counters,
           
        ]);
    }
}
