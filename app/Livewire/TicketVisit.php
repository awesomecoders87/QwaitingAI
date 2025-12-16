<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Category,
    Queue as QueueDB,
    SiteDetail,
    FeedbackSetting,
    GenerateQrCode,
    Level,
    QueueStorage,
    AccountSetting,
    ReverbDetail,
    Counter
};
use Livewire\Attributes\On;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Events\{
    QueueCreated,
    QueueProgress
};
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Config;
use App\Models\Translation;


#[Layout('components.layouts.custom-visit-layout')]
class TicketVisit extends Component
{

    #[Title('Ticket Visit')]
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
    public $thirdCategoryName, $secondCategoryName, $categoryName;
    public $thirdChildId, $secondChildId, $selectedCategoryId;
    public $counterID = 0;
    public $booking_setting = SiteDetail::STATUS_YES;
    public $fieldCatName;
    public $countCatID = 0;
    public $pendingCount = 0;
    public $userDetails;

    public $isOpen = false;
    protected $listeners = ['openModal' => 'openModal'];
    public $lateDuration;
    public $currentYourTurn = false;
    public $enableleaveQueue = false;

    public $feedbackSetting;
    public $generatUrl;
    public $location;
    public $siteData;
    public $reverbKey, $reverbHost, $reverbPort, $reverbScheme;
    public $translations;
    public $joinCall = false;
    public $locale;
    public $reverbDetails;

    public function getListeners()
    {
        return [
            "echo:queue-pending.{$this->teamId},QueuePending" => 'pushPendingQueue',
        ];
    }

    public function mount($id)
    {

        $this->teamId = tenant('id');
        $id = base64_decode($id,true);

        if (empty($id)) {
           abort(404, 'Invalid or missing ID');
        }

        $this->queueDB = QueueDB::where(['team_id' => $this->teamId, 'id' => $id])->first();

        if (empty($this->queueDB)) {
            abort(404, 'Invalid ID');
        }
        // if ( empty( $this->queueDB ) )
        // abort( 404 );

        $this->queueStorage  = QueueStorage::with('counter')->where(['queue_id' => $id])->first();
        $this->location = $this->queueStorage->locations_id;
        $this->siteDetails = siteDetail::where('team_id', $this->teamId)->where('location_id', $this->location)->first();

        $this->booking_setting =  $this->siteDetails->booking_system ?? SiteDetail::STATUS_YES;
        $this->userDetails = json_decode($this->queueStorage->json, true);

        $this->counterID =  $this->queueStorage->counter_id;
        $this->feedbackSetting = FeedbackSetting::viewFeedbackSetting($this->teamId, $this->location);

        $getType = json_decode($this->queueStorage->json, true);

        if ($this->queueStorage->status == QueueDB::STATUS_PROGRESS || !empty($this->queueStorage->called_datetime))
        {
            $this->currentYourTurn = true;
            //  $this->joinCall = $this->siteDetails->ticket_mode ?? SiteDetail::STATUS_NO;
            if(isset($getType['type']) && $getType['type'] == 'Virtual')
            {
                $this->joinCall = true;
            }
        }
        elseif ($this->queueStorage->status == QueueDB::STATUS_PENDING)
        {
            $this->currentYourTurn = false;
            $this->generatUrl = GenerateQrCode::viewGeneratorCode($this->teamId);
        }

        $checkRecord = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('slot_type', AccountSetting::TICKET_SLOT)
            ->select('cancel_booking_cus')
            ->first();

        $this->enableleaveQueue = ($checkRecord && $checkRecord['cancel_booking_cus'] == 1) ? true : false;

        $this->reverbDetails = ReverbDetail::viewReverbDetails($this->teamId, $this->location);
        $this->reverbKey = $this->reverbDetails->key ?? env('REVERB_APP_KEY');
        $this->reverbHost = $this->reverbDetails->host ?? env('REVERB_HOST', '127.0.0.1');
        $this->reverbPort = $this->reverbDetails->port ?? env('REVERB_PORT', 8080);
        $this->reverbScheme = $this->reverbDetails->scheme ?? env('REVERB_SCHEME', 'http');
        $this->selectedCategoryId = $this->queueStorage->category_id;
        $this->secondChildId = $this->queueStorage->sub_category_id ?? '';
        $this->thirdChildId = $this->queueStorage->child_category_id ?? '';
        $this->siteData = SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->location)->first();

        $timezone = $this->siteData->select_timezone ?? 'UTC';
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        $this->translations = Translation::where('team_id', $this->teamId)
            ->get()
            ->groupBy('name') // Group by category name
            ->map(function ($items) {
                return $items->pluck('value', 'language'); // ['es' => 'Categoría 1']
            })
            ->toArray();

        $this->locale = session('app_locale');
    }

    public function waitTime($time)
    {

        $this->lateDuration = $time;
        $this->dispatch('confirm-alert');
    }


    #[On('visitor-update')]
    public function pushPendingQueue($event)
    {
        $getType = json_decode($this->queueStorage->json, true);

        if (!empty($event['queue'])) {
            if ($event['queue']['id']  == $this->queueDB->id)
                $this->queueDB =  QueueDB::find($this->queueDB->id);
            $this->queueStorage = QueueStorage::with('counter')->where(['queue_id' => $this->queueDB->id])->first();
            $this->userDetails = json_decode($this->queueStorage->json, true);
            $this->currentYourTurn = true;
            //  $this->joinCall = $this->siteDetails->ticket_mode ?? SiteDetail::STATUS_NO;
            if(isset($getType['type']) && $getType['type'] == 'Virtual')
            {
                $this->joinCall = true;
            }
            $this->countPendingCalls();
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }


    public function transferCategory($transferId)
    {
        $category = Category::viewCategory($transferId);
        switch ($category?->level_id) {
            case Level::getThirdRecord()?->id:
                $this->fieldCatName = 'child_category_id';
                break;
            case Level::getSecondRecord()?->id:
                $this->fieldCatName = 'sub_category_id';
                break;

            case Level::getFirstRecord()?->id:
                $this->fieldCatName = 'category_id';
                break;
        }
    }

    public function countPendingCalls()
    {

        $getType = json_decode($this->queueStorage->json, true);

        if ($this->siteDetails?->category_estimated_time == SiteDetail::STATUS_YES) {
            $transferID = $this->queueStorage->transfer_id;
            if (!empty($transferID)) {
                $this->transferCategory($transferID);
                $this->countCatID = $transferID;
            } else
                $this->determineCategoryColumn();
        }

        if ($this->siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO)
            $this->counterID  = 0;

        $this->pendingCount = QueueStorage::countPending($this->teamId, $this->queueStorage->id, $this->countCatID, $this->fieldCatName, $this->counterID, $this->location);

        if ($this->feedbackSetting?->form_after_closedcall == FeedbackSetting::STATUC_ACTIVE   && !empty($this->queueStorage->closed_datetime))
            $this->redirect('/rating/survey?code=' . base64_encode($this->queueStorage->id));

        if (!empty($this->queueStorage->called_datetime))
        {
            $this->currentYourTurn = true;
             if(isset($getType['type']) && $getType['type'] == 'Virtual')
            {
                $this->joinCall = true;
            }
        }
        else
        {
            $this->currentYourTurn = false;
        }
    }

    public function render()
    {
        if ($this->booking_setting == QueueDB::STATUS_NO) {
            if (!empty($this->queueStorage->sub_category_id)) {
                $this->acronym = Category::viewAcronym($this->queueStorage->sub_category_id);
            } elseif (!empty($this->queueStorage?->category_id)) {
                $this->acronym = Category::viewAcronym($this->queueStorage?->category_id);
            }
        } else {
            $this->acronym = $this->queueStorage->start_acronym ?? SiteDetail::DEFAULT_WALKIN_A;
        }

        $this->countPendingCalls();
        $this->showEstimatedTime();
        return view('livewire.ticket-visit');
    }

    public function showEstimatedTime()
    {
        $waitingTime = '';


        $pendingwaiting = $pendingCount = 0;
        $assigned_staff_id = null;
          $waitingTime = 0;
        if (!empty($this->siteDetails)) {
            $estimate_time = $this->siteDetails->estimate_time ?? 0;
            $this->determineCategoryColumn();
                if ($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES) {

                   if($this->siteDetails->count_by_service){
$pendingCountget = (int)QueueStorage::countPending($this->queueStorage->team_id, $this->queueStorage->id, '', '', '', $this->queueStorage->locations_id);
                $counterCount = Counter::where('team_id',$this->queueStorage->team_id)->whereJsonContains('counter_locations', (string)$this->queueStorage->locations_id)->where('show_checkbox',1)->count();
                 if((int)$pendingCountget > 0 && (int)$counterCount > 0){
                    $this->pendingCount = (int)(floor((int)$pendingCountget / (int)$counterCount));

                }
                   }else{
  if($this->siteDetails->estimate_time_mode == 1){
                  $estimatedetail = QueueStorage::countPendingByCategorywithstaff($this->queueStorage->team_id, $this->queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->queueStorage->locations_id);
                if ($estimatedetail == false) {

                     $this->pendingCount = QueueStorage::countPending($this->queueStorage->team_id, $this->queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->queueStorage->locations_id);
                } else {
                    $this->pendingCount = $estimatedetail['customers_before_me'] ?? 0;
                    $pendingwaiting = $estimatedetail['estimated_wait_time'] ?? 0;
                    if($this->enablePriority == false){
                        $assigned_staff_id = $estimatedetail['assigned_staff_id'] ?? null;
                    }
                }

                 $estimatedetail = QueueStorage::countPendingByCategorywithstaff($this->queueStorage->team_id, $this->queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->queueStorage->locations_id);
                    if ($estimatedetail == false) {
                        $this->pendingCount = QueueStorage::countPending($this->queueStorage->team_id, $this->queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->queueStorage->locations_id);
                    } else {
                        $this->pendingCount = $estimatedetail['customers_before_me'] ?? 0;
                        $pendingwaiting = $estimatedetail['estimated_wait_time'] ?? 0;
                        if($this->enablePriority == false){
                            $assigned_staff_id = $estimatedetail['assigned_staff_id'] ?? null;
                        }
                    }
                }elseif($this->siteDetails->estimate_time_mode == 2){
                    if($this->siteDetails->count_all_services == 2){
                            $estimatedetail = QueueStorage::countAllPendingQueues($this->queueStorage->team_id, $this->queueStorage->id, $this->countCatID,$this->queueStorage->locations_id);
                        }else{
                    $estimatedetail = QueueStorage::countPendingByCategory($this->queueStorage->team_id, $this->queueStorage->id, $this->countCatID, $this->fieldCatName,$this->queueStorage->locations_id);
                    $pendingwaiting = $estimatedetail['estimated_wait_time'] ?? 0;
                        }
                        $this->pendingCount = $estimatedetail['customers_before_me'] ?? 0;

                }else{
                    //  $serviceTime = $this->siteDetails->estimate_time ?? 0;
                    $estimatedetail = QueueStorage::countPendingByStaff($this->queueStorage->team_id, $this->queueStorage->id,$this->countCatID,$this->queueStorage->locations_id);
                      if ($estimatedetail == false) {
                          $this->pendingCount = 0;
                        } else {
                            $this->pendingCount = $estimatedetail['customers_before_me'] ?? 0;
                            $pendingwaiting = $estimatedetail['estimated_wait_time'] ?? 0;
                            if($this->enablePriority == false){
                                $assigned_staff_id = $estimatedetail['assigned_staff_id'] ?? null;
                            }
                        }
                }
                   }
              

            } else {

             $pendingCountget = (int)QueueStorage::countPending($this->queueStorage->team_id, $this->queueStorage->id, '', '', '', $this->queueStorage->locations_id);
                $counterCount = Counter::where('team_id',$this->queueStorage->team_id)->whereJsonContains('counter_locations', (string)$this->queueStorage->locations_id)->where('show_checkbox',1)->count();
                 if((int)$pendingCountget > 0 && (int)$counterCount > 0){
                    $this->pendingCount = (int)(floor((int)$pendingCountget / (int)$counterCount));

                }
            }




            if ($this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {

                // $waitingTime =   $pendingwaiting ?? $estimate_time * $this->pendingCount;

                 if ($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES) { // get esitmate time of category wise
                   if($this->siteDetails->estimate_time_mode == 2 && $this->siteDetails->count_all_services == 2){ //check pending according to service only
                    $waitingTime =  $estimate_time * $this->pendingCount;
                   }else{
                       $waitingTime =  $pendingwaiting ?? $estimate_time * $this->pendingCount;
                   }

                } else {  // get esitmate time of globally set
                    $waitingTime =  $estimate_time * $this->pendingCount;
                }

                if (!empty($this->siteDetails->ticket_text_2))
                {
                     $text = str_replace('{{QUEUE COUNT}}',$this->pendingCount, $this->siteDetails->ticket_text_2);
                   if ($this->locale !== 'en' && isset($this->translations['Ticket Message 2'][$this->locale])) {
                            $text = $this->translations['Ticket Message 2'][$this->locale];
                            $text = str_replace('{{QUEUE COUNT}}',$this->pendingCount, $text);
                        }

                         $this->showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $text);
                }

                if (!empty($this->siteDetails->ticket_text)) {
                    $text = str_replace('{{QUEUE COUNT}}', $this->pendingCount, $this->siteDetails->ticket_text);

                     if ($this->locale !== 'en' && isset($this->translations['Ticket Message 1'][$this->locale])) {
                            $text = $this->translations['Ticket Message 1'][$this->locale];
                            $text = str_replace('{{QUEUE COUNT}}', $this->pendingCount, $text);
                        }

                         $this->showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                }
            }
        }

        $this->queueStorage->waiting_time = $waitingTime;
        $this->queueStorage->queue_count = $this->pendingCount;
        $this->queueStorage->save();
    }

    public function determineCategoryColumn()
    {

        if (!empty($this->thirdChildId)) {
            if ($this->siteDetails?->category_level_est == 'automatic') {
                $this->fieldCatName = 'child_category_id';
                $this->countCatID =  $this->thirdChildId;
            } elseif ($this->siteDetails?->category_level_est == 'child') {
                $this->fieldCatName = 'sub_category_id';
                $this->countCatID =  $this->secondChildId;
            } else {
                $this->fieldCatName = 'category_id';
                $this->countCatID =  $this->selectedCategoryId;
            }
        } else if (!empty($this->secondChildId)) {

            if ($this->siteDetails?->category_level_est == 'child') {
                $this->fieldCatName = 'sub_category_id';
                $this->countCatID =  $this->secondChildId;
            } else {
                $this->fieldCatName = 'category_id';
                $this->countCatID =  $this->selectedCategoryId;
            }
        } else {
            $this->fieldCatName = 'category_id';
            $this->countCatID =  $this->selectedCategoryId;
        }
    }


    public function openModal()
    {

        $this->dispatch('leave-waitlist');
    }

    #[On('cancel-from-waitlist')]
    public function cancelFromWaitlist()
    {

        $this->queueStorage->cancelled_datetime = Carbon::now();
        $this->queueStorage->status = QueueDB::STATUS_CANCELLED;
        $this->queueStorage->save();
        QueueCreated::dispatch($this->queueStorage);
        QueueProgress::dispatch($this->queueStorage);

        $this->dispatch('event-success-call', 'The ticket cancelled successfully');
    }



    #[On('late-save-waitlist')]
    public function lateSaveWaitlist()
    {
        // Parse the existing datetime
        $datetime = Carbon::parse($this->queueStorage->datetime);

        // Add late duration in minutes
        $updatedDatetime = $datetime->addMinutes((int)$this->lateDuration);

        // Update the queue storage with new values
        $this->queueStorage->datetime = $updatedDatetime;
        $this->queueStorage->late_duration = $this->lateDuration;
        $this->queueStorage->is_arrived = QueueDB::STATUS_NO;
        $this->queueStorage->save();

        // Dispatch the event
        QueueCreated::dispatch($this->queueStorage);

        // Notify frontend
        $this->dispatch('event-success-call', 'The time for late visit updated successfully');
    }
    // public function lateSaveWaitlist() {

    //     $this->queueStorage->late_duration = $this->lateDuration;
    //     $this->queueStorage->is_arrived = QueueDB::STATUS_NO;
    //     $this->queueStorage->save();

    //     QueueCreated::dispatch($this->queueStorage);
    //     $this->dispatch( 'event-success-call', 'The time for late visit updated successfully' );
    // }

    public function isArrived()
    {

        $this->dispatch('arrived-alert');
    }

    #[On('is-arrived')]
    public function isArrvied()
    {
        $this->queueStorage->is_arrived = QueueDB::STATUS_YES;
        $this->queueStorage->save();
        QueueCreated::dispatch($this->queueStorage);

        $this->dispatch('event-success-call', 'Updated successfully');
    }
}
