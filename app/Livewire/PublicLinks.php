<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{
    GenerateQrCode,
    AccountSetting,
    Domain,
    ScreenTemplate
};


class PublicLinks extends Component
{

    public $showModal = false;
    public $currentUser;
    public $queueUrl;
    public $queueGenerateUrl;
    public $mobileQueueUrl;
    public $displayUrl;
    public $bookingUrl;
    public $teamId;
    public $domainSlug;
    public $selectedLocation;
    public $isBookingEnabled;
    public $screencount;


    public function mount()
    {   


        $this->showModal = false;
        
        $this->teamId = tenant('id');
        $this->selectedLocation = session('selectedLocation');
         $this->loaddata();
       
    }

    #[On('openPublicLinks')]
    public function openModal()
    {
        $this->teamId = auth()->user()->team_id ?? tenant('id');
        $this->selectedLocation = session('selectedLocation');
        $this->loaddata();
        $this->showModal = true;
    }

    public function loaddata(){
        $this->domainSlug = Domain::where('team_id',$this->teamId)->first();
        $location = '/'. base64_encode($this->selectedLocation);
       $this->screencount = ScreenTemplate::where(['team_id' => $this->teamId])->where('location_id',$this->selectedLocation)->count();
        $this->queueGenerateUrl = "https://".$this->domainSlug['domain']. "/main".$location;
        $this->mobileQueueUrl = GenerateQrCode::where('team_id', $this->teamId)->where('location_id', $this->selectedLocation)->value('url');
       if($this->screencount > 0){
           $this->displayUrl = "https://".$this->domainSlug['domain'] . "/screens";
       }else{
           $this->displayUrl = "https://".$this->domainSlug['domain'] . "/display";

       }
       
        $this->queueUrl = "https://".$this->domainSlug['domain'] . "/queue".$location;
        $this->bookingUrl = "https://".$this->domainSlug['domain'] . "/book-appointment".$location;
        $this->isBookingEnabled = AccountSetting::where('team_id', $this->teamId)->where('location_id', $this->selectedLocation)->where('slot_type', 'booking')->value('booking_system');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.public-links');
    }
}
