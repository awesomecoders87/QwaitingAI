<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{Category, Team, Queue as QueueDB, SiteDetail, FeedbackSetting, GenerateQrCode, Level, QueueStorage, AccountSetting};
use Livewire\Attributes\On;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Events\{QueueCreated, QueueProgress};
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.custom-layout')]
class TicketView extends Component
{
    #[Title('Ticket View')] 
    public $queueDB;
    public $queueStorage;
    public $domainSlug;
    public $teamId;
    public $showModal = false;
    public $data = [];
    public $showTicketText;
    public $showTicketText_2;
    public $siteDetails;
    public $acronym;
    public $thirdCategoryName, $secondCategoryName, $categoryName, $selectedCategoryId;
    public $counterID = 0;
    public $booking_setting = SiteDetail::STATUS_YES;
    public $fieldCatName;
    public $countCatID;
    public $pendingCount = 0;
    public $userDetails;

    public $isOpen = false;
    protected $listeners = ['openModal' => 'openModal'];
    public $lateDuration;
    public $currentYourTurn = false;

    public $feedbackSetting;
    public $generatUrl;
    public $location;
    public $accountSetting;

    //     public function getListeners()
    //     {
    //            return [
    //                "echo:queue-pending.{$this->teamId},QueuePending" => 'pushPendingQueue',            
    //            ];
    //    }

    #[On('queue-pending')]
    public function pushPendingQueue($event)
    {
        if (!empty($event['queue'])) {
            if ($event['queue']['id']  == $this->queueDB->id)
                $this->queueDB =  QueueDB::find($this->queueDB->id);
            $this->userDetails = json_decode($this->queueDB->json, true);
            $this->currentYourTurn = true;
            $this->countPendingCalls();
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function mount($id)
    {

        $this->domainSlug = Team::getSlug();

        $this->teamId =  tenant('id');
        $this->queueDB = QueueDB::with('counter')->where(['team_id' => $this->teamId, 'id' => base64_decode($id)])->first();
        if (empty($this->queueDB))
            abort(404);

        $this->queueStorage =  QueueStorage::with('counter')->where(['team_id' => $this->teamId, 'queue_id' => base64_decode($id)])->get();
        $this->location = $this->queueDB->locations_id;
        $this->siteDetails = siteDetail::getMyDetails($this->teamId);
        $this->accountSetting = AccountSetting::where('team_id', $this->teamId)
         ->where('location_id', $this->location)
         ->where('slot_type', AccountSetting::BOOKING_SLOT)
         ->first();
        $this->booking_setting =  $this->siteDetails->booking_system ?? SiteDetail::STATUS_YES;
        $this->userDetails = json_decode($this->queueStorage[0]?->json, true);
        $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId);
    }


    public function render()
    {
        $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId);
        if ($this->accountSetting->booking_system == 1) {
           $this->acronym = $this->queueStorage[0]->start_acronym;
        } else {
            $this->acronym = SiteDetail::DEFAULT_WALKIN_A;
        }

        return view('livewire.ticket-view');
    }
}
