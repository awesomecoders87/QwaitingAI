<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use App\Events\{QueueProgress, QueuePending, QueueCreated, BreakEvent, QueueDisplay, QueueVirtual,QueueSuspension,QueueDepartment,QueueTransfer,DisplayAudio,QueueNotification};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Services\TwilioVideoService;
use DB;
use App\Models\{
    Queue,
    Counter,
    Category,
    Level,
    SiteDetail,
    StaffBreak,
    User,
    ActivityLog,
    FeedbackSetting,
    Location,
    FormField,
    ReverbDetail,
    QueueStorage,
    SmtpDetails,
    BreakReason,
    AccountSetting,
    SmsAPI,
    SmsReport,
    ErrorLog,
    Domain,
    SuspensionLog,
    Booking,
    MessageDetail,
    ColorSetting,
    SalesforceSetting,
    SalesforceConnection,
};
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuspensionNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\Translation;
use App\Services\SalesforceService;



#[Layout('components.layouts.app-layout')]
class QueueCalls extends Component
{
    use WithPagination;
    #[Title('Calls')]
    // protected static ?string $navigationLabel = 'Call';
    public $queues;
    public $queueStorage;
    public $currentNextStorageID;
    public $liveData;
    public $counters;
    public $selectedCounter;
    public $term;
    public $currentVisitorId;
    public $currentVisitorRecord;
    public $nextId;
    public $nextStorageId;
    public $tokenServed = [];
    public $conditionTeam = [];
    public $isServingTime = false;
    public $isStartBtn = true;
    public $isCloseBtn = false;
    public $categories;
    public $siteDetail;
    public $missedCalls = [];
    public $holdCalls = [];
    public $booking_setting = SiteDetail::STATUS_YES;
    public $team_id;
    public $queuesCount = 0;

    public $showStartBtn = 'SHOW_START_CLOSE';
    public $break_comment = '';
    public $break_reason = '';
    public $break_id = '';
    public $sms = '';
    public $randomQueueID;
    public $randomQueueStorageID;
    public $randomCurrentQueue;
    public $holdCurrentQueue;
    public $userDetails = [];
    public $userAuth;
    public $getData;

    public $isRandomTransfer = false;
    public $notice_sms;
    public $activityLogs = [];
    public $feedbackSetting;
    public $fieldCatName;
    public $location;
    public $dynamicProperties = [];
    public $dynamicForm = [];
    public $allCategories = [];
    public $staticVisitorDetails = [];
    public $name, $email, $phone;
    public $selectedCategoryId, $secondChildId, $thirdChildId;
    public $firstCategories = [];
    public $secondCategories = [];
    public $thirdCategories = [];
    public $page = Queue::INITIAL_VISITOR_SHOW_COUNT;
    public $queueType = Queue::DEFAULT_QUEUE;
    public $countQueueStorage = 0;
    public $mainQueue;
    public $logo;
    public $registerqueue;
    public $datetimeFormat = 'Y-m-d';
    public $currentStorageID;
    public $isCallUnHold = 0;
    public $selectedCountryCode;
    public $modelslideCurrentVisitor = false;
    public $modelsActivityLog = false;
    public $modelCallHistory = false;
    public $modelEstimateNotes = false;
    public $modelHistoryQueue = false;
    public $modelUserDetails = false;
    public $modelhistoryTakeCall = false;
    public $modelSendsms = false;
    public $modelholdsms = false;
    public $modelSuspension = false;
    public $modelmyModalTransfer = false;
    public $modelmenuOverlayRandom = false;
    public $modelupdateClient = false;
    public $categoriesShow = true;
    public $isfixedQueueSize = true;
    public $isCheckSameCounter = true;
    public $reverbDetails;
    public $reverbKey, $reverbHost, $reverbPort, $reverbScheme;
    public $timezone;
    public $currentUrl;
    public $holdsms;
    public $isEnableholdsms = false;
    public $hold_queue_feature = false;

    public $isEnableholsuspension = false;
    public $suspensionReason;
    public $actionType;
    public $notificationType;
    public $level1, $level2, $level3;
    public $translations;
    public $language;
    public $enableStaffPriority = false;

    //Virtual variables
    public $showVirtualMeeting = false;
    public $room = 'default';
    public $virtualQueueId = null;
    public $showModal = false;
    public $getroom = 'default';
    public $getqueueId = null;
    public $identity;
    public string $token;
    public $enableVirtual = false;

    public $display_name = false;
    public $actionStatus = "Cancelled";

    public $enable_callDepartment = false;

    public $transfer_counters = [];
    public $show_transfer_counters = false;
    public $show_transfer_category = false;
    public $show_login_counters = false;
    public $transferCalls = [];
    public $colorSettings;
    public $waiting_minutes;
    public $waiting_innerQueue_token;
    public $enable_doc_file_field = false;
    public $doc_file_label;	


        public function currentTeamId()
    {
        $this->team_id = tenant('id');
        $this->conditionTeam =  ['team_id' =>  $this->team_id];
    }
    #[Computed]

    public function userCategories()
    {
        return $this->userAuth->categories->pluck('id')?->toArray();
    }

    

    public function mount()
    {

        $this->currentUrl = url()->current();

        $this->location = Session::get('selectedLocation');
        $this->userAuth = Auth::user();

        $this->currentTeamId();
        $this->datetimeFormat = AccountSetting::showDateTimeFormat($this->team_id, $this->location);
        $this->registerqueue = AccountSetting::where('team_id', $this->team_id)
        ->where('location_id', $this->location)
        ->where('slot_type', AccountSetting::BOOKING_SLOT)
        ->select('booking_system', 'id')
        ->first();


        $this->siteDetail = SiteDetail::where($this->conditionTeam)->where('location_id', $this->location)->first();
        $this->logo = !empty($this->siteDetail->business_logo) ? 'storage/' . $this->siteDetail->business_logo : 'images/logo-transparent.png';

        Queue::timezoneSet();

        $this->timezone = Session::get('timezone_set') ?? 'UTC';

        $domain = Domain::where('team_id', $this->team_id)->select('id','hold_queue_feature')->first();
        $this->hold_queue_feature = $domain['hold_queue_feature'] == 1 ? true : false;
        if ($this->hold_queue_feature == 1) {

            if (Auth::user()->hasRole('Admin') && Auth::user()->is_admin == 1) {
                $this->isEnableholdsms = true;
            } else {
                $this->isEnableholdsms = Auth::user()->enable_hold_queue == 1 ? true : false;
            }
        } else {
            $this->isEnableholdsms = false;
        }

        $this->holdsms = $this->siteDetail?->hold_message ?? '';
        $this->queueType = $this->siteDetail?->queue_priority ?? Queue::DEFAULT_QUEUE;
        $this->counters =  Counter::getCounter($this->team_id, $this->siteDetail?->counter_option, $this->userAuth, $this->location);

        $this->booking_setting =  $this->siteDetail?->booking_system ?? SiteDetail::STATUS_YES;
        $this->showStartBtn = $this->siteDetail?->hide_button;

        $this->categoriesShow = $this->siteDetail?->show_visitor_cat == 1;
        $this->isfixedQueueSize = $this->siteDetail?->fixed_visitor_list_queue == 1;
        $this->page = $this->siteDetail->fixed_queue_size ?? 10;
        $this->isCheckSameCounter = $this->siteDetail?->counter_assigned_queue == 1;
        $this->enableVirtual = $this->siteDetail->ticket_mode ?? SiteDetail::STATUS_NO;
        $this->display_name = $this->siteDetail->display_name ?? SiteDetail::STATUS_NO;

        $counterId = Auth::user()->counter_id ?? null;

        if(!empty($counterId) && !Session::has('selected_counter')){
            $this->selectedCounter =$counterId;
        }else{
           $this->selectedCounter  = Session::get('selected_counter') ?? null;
        }

         if (!empty($this->selectedCounter) && !Session::has('selected_counter')) {
            $this->updatedSelectedCounter($this->selectedCounter);
        }

        $this->enableStaffPriority = $this->siteDetail->use_staff_priority ?? false;
        $this->enable_callDepartment = $this->siteDetail->enable_callDepartment ?? false;
        $this->show_login_counters = $this->siteDetail->login_counters_only ?? false;
        $this->show_transfer_counters = $this->siteDetail->counter_transfer ?? false;
        $this->show_transfer_category = $this->siteDetail->category_transfer ?? false;
        if($this->show_transfer_counters){

        $this->transfer_counters =  Counter::getAssignedCounter($this->team_id, $this->siteDetail?->counter_option, $this->userAuth, $this->location,$this->show_login_counters);
        }

        $this->refreshQueues();

        if ($this->showStartBtn == 'HIDE_START_CLOSE'){

            $this->isCloseBtn = true;
             $this->isStartBtn = false;
        }

        if ($this->showStartBtn == 'SHOW_CLOSE')
            $this->isStartBtn = false;


         if ($this->showStartBtn == 'SHOW_START_CLOSE'){
          $this->isStartBtn = true;
         }

        $break =  StaffBreak::viewEmptyTimeEnd($this->userAuth->id);
        if (!empty($break)) {
            $breakData = BreakReason::find($break->breakreason_id);
            $this->break_id = $break->id;
            $this->dispatch('event-continue-break', ['breakTime' => $breakData->break_time]);
        }

        $this->initialiAfterQueue();

        if (Session::has('selected_counter')) {
            $this->selectedCounter = Session::get('selected_counter');
        }

        $this->feedbackSetting = FeedbackSetting::where(['team_id'=>$this->team_id, 'location_id'=>$this->location])->select('id','enable_post_interaction')->first();
        $this->firstCategories = Category::getFirstCategoryN($this->team_id, $this->location);
        $this->updateCategories('secondChildId', $this->selectedCategoryId);
        $this->updateCategories('thirdChildId', $this->secondChildId);
        $this->reverbDetails = ReverbDetail::viewReverbDetails($this->team_id, $this->location);

        $this->reverbKey = $this->reverbDetails->key ?? env('REVERB_APP_KEY');
        $this->reverbHost = $this->reverbDetails->host ?? env('REVERB_HOST', '127.0.0.1');
        $this->reverbPort = $this->reverbDetails->port ?? env('REVERB_PORT', 8080);
        $this->reverbScheme = $this->reverbDetails->scheme ?? env('REVERB_SCHEME', 'http');

        if (!Session::has('refresh-page')) {
            Session::put('refresh-page', true);
            $this->dispatch('refresh-page');
        }


        $this->isEnableholsuspension = $this->siteDetail?->is_suspension_button == 1 ? true : false;
        $this->enable_doc_file_field = $this->siteDetail->enable_doc_file ?? false;
        $this->doc_file_label = $this->siteDetail->doc_file_label ?? 'View File';

        $levels = Level::where('team_id', $this->team_id)
            ->where('location_id', $this->location)
            ->whereIn('level', [1, 2, 3])
            ->select('level', 'name')
            ->get()
            ->keyBy('level');

        $this->level1 = $levels[1]->name ?? 'Level 1';
        $this->level2 = $levels[2]->name ?? 'Level 2';
        $this->level3 = $levels[3]->name ?? 'Level 3';


        $this->language = session('app_locale');

        $this->translations = Translation::where('team_id', $this->team_id)
            ->get()
            ->groupBy('name') // Group by category name
            ->map(function ($items) {
                return $items->pluck('value', 'language'); // ['es' => 'Categoría 1']
            })
            ->toArray();

        $this->colorSettings = ColorSetting::where('team_id', $this->team_id)->where('location_id', $this->location)->first();


    }

     public function refreshQueues(): void
    {
        \Log::info('🔄 refreshQueues() called');

        if($this->enable_callDepartment){
            $newQueues = Queue::getPendingQueuesDepartment($this->conditionTeam, ($this->siteDetail?->fixed_visitor_list_queue == SiteDetail::STATUS_YES ? true : false), $this->location, $this->page, $this->term, $this->team_id, $this->queueType,(int)$this->selectedCounter,Auth::id(),true);
        }else{
            $newQueues = Queue::getPendingQueues($this->conditionTeam, ($this->siteDetail?->fixed_visitor_list_queue == SiteDetail::STATUS_YES ? true : false), $this->location, $this->page, $this->term, $this->team_id, $this->queueType,(int)$this->selectedCounter);
        }
        
        // Create new collection to ensure Livewire detects change
        $this->queues = collect($newQueues)->values();
        
        \Log::info('📊 Queues refreshed', ['count' => $this->queues->count()]);
        
          if(!empty($this->queues)){
        $this->transferCalls = $this->queues
            ->filter(function ($queue) {
                return !is_null($queue->transfer_id) || !is_null($queue->forward_counter_id);
            })
            ->map(function ($queue) {
                return [
                    'id' => $queue->id,
                    'queue_id' => $queue->queue_id,
                    'name' => $queue->name,
                    'token' => $queue->token,
                    'start_acronym' => $queue->start_acronym,
                ];
            })
            ->values() // reindex keys from 0
            ->toArray();
        }

        $this->queuesCount = $this->queues->count();
        $this->tokenServed = Queue::totalTokenServed($this->conditionTeam, $this->userAuth->id, $this->location);
    }

    public function resetDynamic()
    {
        // $this->dynamicForm = FormField::getFields($this->team_id);
        $this->allCategories = [
            'thirdChildId' => $this->thirdChildId,
            'secondChildId' => $this->secondChildId,
            'selectedCategoryId' => $this->selectedCategoryId,
        ];
        $this->dynamicForm = FormField::getFields($this->team_id, false, $this->location, null, $this->allCategories);
        $usedFields = [];

        $userDetails = json_decode($this->currentVisitorRecord?->json, true);
        $this->staticVisitorDetails = [];

        foreach ($this->dynamicForm as $field) {
            $titleKey = strtolower($field['title']);
            $propertyName = $titleKey . '_' . $field['id'];

            if (!empty($userDetails) && array_key_exists($titleKey, array_change_key_case($userDetails, CASE_LOWER))) {
                $userDetailsLower = array_change_key_case($userDetails, CASE_LOWER);
                $this->dynamicProperties[$propertyName] = $userDetailsLower[$titleKey];
                $usedFields[] = $titleKey;
            } else {
                $this->dynamicProperties[$propertyName] = '';
            }
        }
        if (!empty($userDetails)) {
            $userDetailsLower = array_change_key_case($userDetails, CASE_LOWER);

            foreach ($userDetailsLower as $key => $value) {
                if (!in_array($key, $usedFields)) {
                    $this->staticVisitorDetails[$key] = $value;
                }
            }
        }
    }
    public function rules()
    {

        $rules = [];
        foreach ($this->dynamicProperties as $fieldName => $value) {
            $fieldId = explode('_', $fieldName)[1];

            $field = FormField::findDynamicFormField($this->dynamicForm, $fieldId);

            if ($field) {
                FormField::addDynamicFieldRules($rules, $fieldName, $field, $this->allCategories);
            }
        }
        return $rules;
    }

    public function messages()
    {
        $messages = [];

        foreach ($this->dynamicProperties as $fieldName => $value) {
            $fieldId = explode('_', $fieldName)[1];

            $field = FormField::findDynamicFormField($this->dynamicForm, $fieldId);
            if ($field) {
                $fieldTitle = $field['title'];
                $messages["dynamicProperties.$fieldName.required"] = "The {$fieldTitle} field is required.";
                if (str_contains(strtolower($fieldTitle), 'email')) {
                    $messages["dynamicProperties.$fieldName.email"] = "Invalid email address for {$fieldTitle}.";
                }
                $messages["dynamicProperties.$fieldName.regex"] = "The {$fieldTitle} field is invalid.";
                $messages["dynamicProperties.$fieldName.min"] = "The {$fieldTitle} field must be at least :min characters.";
                $messages["dynamicProperties.$fieldName.max"] = "The {$fieldTitle} field must be at most :max characters.";
            }
        }
        return $messages;
    }

    public function initialiAfterQueue()
    {
        $this->nextId  = $this->queues?->first()?->queue_id ?? null;
        $this->nextStorageId = $this->queues?->value('id') ?? null;

        $this->categories =  Category::where(['level_id' => Level::getFirstRecord()->id, 'team_id' => $this->team_id])->whereJsonContains('category_locations', "$this->location")->pluck('name', 'id')->toArray();
        if($this->enable_callDepartment){

            $this->missedCalls = Queue::getMissedCallId($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location,true);
        }else{
            $this->missedCalls = Queue::getMissedCallId($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location,false);

        }
        
        $this->holdCalls = QueueStorage::getHoldCall($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location);

        // $this->cVRecordFn();

        if (empty($this->selectedCounter)) {
            if ($this->userAuth->hasRole(User::ROLE_ADMIN)) {

                $this->selectedCounter = $this->currentVisitorRecord?->counter_id ??  Counter::where('team_id', $this->team_id)
                    ->whereJsonContains('counter_locations', (string)$this->location)
                    ->first()?->id;;
            } else {

                $authCounterId = $this->userAuth->counter_id;

                $validCounter = Counter::where('id', $authCounterId)->whereJsonContains('counter_locations', "$this->location")->first()?->id;

                $this->selectedCounter =  $this->currentVisitorRecord?->counter_id ??  ($validCounter?->id ?? null);
            }
        } else {
            $condition = array_merge($this->conditionTeam, ['counter_id' => $this->selectedCounter]);

            // $this->currentVisitorRecord =  Queue::currentVisitorRecord($this->conditionTeam, null, $this->currentVisitorId, (int)$this->location, null);
            $this->currentVisitorRecord =  Queue::currentVisitorRecord($condition, null, null, (int)$this->location, null);
        }

          if ($this->currentVisitorRecord) {
            $this->notice_sms = $this->currentVisitorRecord->esitmate_note;
            if (empty($this->currentVisitorRecord->start_datetime)) {
                $this->isStartBtn = true;
                $this->isCloseBtn = false;
                $this->isServingTime = false;
            } else if (!empty($this->currentVisitorRecord->start_datetime)) {
                $this->isStartBtn = false;
                $this->isServingTime = true;
                if ($this->showStartBtn == 'HIDE_START_CLOSE')
                    $this->isCloseBtn = false;
                else
                    $this->isCloseBtn = true;
                $this->dispatch('event-serving-time');
            }
            $this->thirdChildId   = $this->currentVisitorRecord->child_category_id;
            $this->secondChildId = $this->currentVisitorRecord->sub_category_id;
            $this->selectedCategoryId = $this->currentVisitorRecord->category_id;
            $this->currentStorageID  = $this->currentVisitorRecord->id;
            $this->queueStorage  =$this->currentVisitorRecord;
            $this->resetDynamic();
        }

        if (empty($this->counters)) {
            $this->selectedCounter = '';
        }

    }


   public function updatedSelectedCounter($value)
    {

        $this->selectedCounter = $value;
        Session::put('selected_counter', $value);
        $condition = array_merge($this->conditionTeam, ['counter_id' => $this->selectedCounter]);

        // $this->currentVisitorRecord =  Queue::currentVisitorRecord($this->conditionTeam, null, $this->currentVisitorId, (int)$this->location, null);
        $this->dispatch('refreshComponent');
        $this->currentVisitorRecord =  Queue::currentVisitorRecord($condition, null, null, (int)$this->location, null);
        $this->refreshQueues();
        $this->initialiAfterQueue();

    }

    public function updatedTerm()
    {
        $this->refreshQueues();
         $this->queues =  Queue::getPendingQueues($this->conditionTeam, ($this->siteDetail?->fixed_visitor_list_queue == SiteDetail::STATUS_YES ? true : false), $this->location, $this->page, $this->term, $this->team_id, $this->queueType);
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }

    public function holdSendSMS()
    {
        $queuesdata =  Queue::getPendingQueuesNumber($this->conditionTeam, $this->location, $this->term, $this->team_id, $this->queueType);

        if (!empty($queuesdata) && !empty($this->holdsms)) {
            foreach ($queuesdata as $contactWithCode) {
                $status = SmsAPI::currentQueueSms($contactWithCode, $this->holdsms, $this->team_id, 'hold queue');
            }
        }

        $this->modelholdsms = false;

        $this->dispatch('event-success-call', ['message' => __('message.SUCCESS0011.message')]);

        $this->dispatch('close-modal', id: 'holdsms');
    }


    public function updatedActionType($value){

        if($value == "appointment"){
                $this->actionStatus = "Cancelled";
            }
    }


   public function suspensionSendData()
    {
        // Remove the dd() debug statement
        // Validate the input

        $this->validate([
            'suspensionReason' => 'required|string',
            'actionType' => 'required',
            'notificationType' => 'required',
            'team_id' => 'required',
            'location' => 'required',
            'location' => 'required',
        ]);

        // Create suspension log
        $suspensionLogId = SuspensionLog::create([
            'team_id' => $this->team_id,
            'location_id' => $this->location,
            'action_type' => $this->actionType,
            'notification_type' => $this->notificationType,
            'reason' => $this->suspensionReason ?? '',
        ]);

        $queuestorages = collect();
        $bookings = collect();
        $message = $this->suspensionReason ?? 'Suspended all Appointments';
           if($this->actionStatus == 'Cancelled'){
              $subject = "Call Cancellation";
            }elseif($this->actionStatus == 'Skip'){
                 $subject = "Call Skipped";
            }else{
                $subject = "Call Closed";
            }

        // Get relevant records based on action type
        if ($this->actionType == 'queue' || $this->actionType == 'appointment_and_queue') {
            $queuestorages = QueueStorage::where('team_id', $this->team_id)
                ->where('locations_id', $this->location)
                ->whereDate('arrives_time', Carbon::today())
                ->whereNull('called_datetime')
                ->where('status', "!=", "Cancelled")
                ->get();
        }

        if ($this->actionType == 'appointment' || $this->actionType == 'appointment_and_queue') {
            $bookings = Booking::where('team_id', $this->team_id)
                ->where('location_id', $this->location)
                ->when(auth()->user()->is_admin != 1, function ($query) {
                    $query->where('staff_id', auth()->id());
                })
                ->whereDate('booking_date', Carbon::today())
                ->where('status', "!=", "Cancelled")
                ->get();
        }
        // Process Email notifications
        if ($this->notificationType == 'email' || $this->notificationType == 'sms_and_email') {
            $this->processEmailNotifications($queuestorages, $bookings, $message, $suspensionLogId,$subject);
        }

        // Process SMS notifications
        if ($this->notificationType == 'sms' || $this->notificationType == 'sms_and_email') {
            $this->processSmsNotifications($queuestorages, $bookings, $message, $suspensionLogId);
        }



        // Update records status
        $this->updateRecordsStatus($queuestorages, $bookings, $message, $suspensionLogId);
        $this->reset('actionType', 'notificationType', 'suspensionReason');

        if(!empty($this->team_id) && !empty($this->location)){

            QueueSuspension::dispatch($this->team_id,$this->location);
        }

        $this->dispatch('event-success-suspended', ['message' => __('message.SUCCESS002.message')]);
        $this->dispatch('close-modal', id: 'holdSuspension');
    }



    protected function processSmsNotifications($queuestorages, $bookings, $message, $suspensionLogId)
    {
        // Process queue storage SMS
        foreach ($queuestorages as $queuestorage) {
            if (!empty($queuestorage->phone)) {
                $phone_code = isset($queuestorage->phone_code) ? ltrim($queuestorage->phone_code, '+') : '91';
                $contactWithCode = $phone_code . $queuestorage->phone;
                SmsAPI::currentQueueSms($contactWithCode, $message, $this->team_id, 'suspensions queue');
            }
        }

        // Process booking SMS
        foreach ($bookings as $booking) {
            if (!empty($booking->phone)) {
                $phone_code = '91';
                $contactWithCode = $phone_code . $booking->phone;
                SmsAPI::currentQueueSms($contactWithCode, $message, $this->team_id, 'suspensions appointment');
            }
        }
    }

    protected function processEmailNotifications($queuestorages, $bookings, $message, $suspensionLogId,$subject)
    {

        $details = SmtpDetails::where('team_id', $this->team_id)->where('location_id', $this->location)->first();
        if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', trim($details->hostname));
            Config::set('mail.mailers.smtp.port', trim($details->port));
            Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
            Config::set('mail.mailers.smtp.username', trim($details->username));
            Config::set('mail.mailers.smtp.password', trim($details->password));

            Config::set('mail.from.address', trim($details->from_email));
            Config::set('mail.from.name', trim($details->from_name));
        }
        // Process queue storage emails
        foreach ($queuestorages as $queuestorage) {
            $email = $this->extractEmailFromQueueStorage($queuestorage);

            if (!empty($email)) {
                try {
                    $recipientName = $this->extractNameFromQueueStorage($queuestorage);
                    $queueData = [
                        'arrives_time' => $queuestorage->arrives_time,
                        'token' => $queuestorage->token ?? null,
                        'team_id' => $queuestorage->team_id ?? null,
                        'location_id' => $queuestorage->locations_id ?? null,
                        // Add other minimal required fields
                    ];

                    if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
                        // dd( $email,$recipientName,$queueData);
                        Mail::to($email)->send(new SuspensionNotification(
                            $message,
                            $subject,
                            $queueData
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send email to queue storage (ID: {$queuestorage->id}): " . $e->getMessage());
                }
            }
        }


        // Process booking emails
        foreach ($bookings as $booking) {
            if (!empty($booking->email)) {
                try {
                    $bookingData = [
                        'booking_date' => $booking->booking_date,
                        'booking_time' => $booking->booking_time,
                        'team_id' => $booking->team_id,
                        'location_id' => $booking->location_id,
                    ];
                    if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
                        Mail::to($booking->email)->send(new SuspensionNotification(
                            $message,
                            'Appointment Cancellation',
                            $bookingData,
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send email to booking: " . $e->getMessage());
                }
            }
        }
    }

    protected function extractNameFromQueueStorage($queuestorage)
    {
        if ($queuestorage->json) {
            $jsonData = is_string($queuestorage->json) ? json_decode($queuestorage->json, true) : $queuestorage->json;

            return $jsonData['name'] ??
                $jsonData['Name'] ??
                $jsonData['Full Name'] ??
                $jsonData['full_name'] ?? null;
        }

        return null;
    }

    protected function extractEmailFromQueueStorage($queuestorage)
    {
        if ($queuestorage->json) {
            $jsonData = is_string($queuestorage->json) ? json_decode($queuestorage->json, true) : $queuestorage->json;

            return $jsonData['Email'] ??
                $jsonData['email'] ??
                $jsonData['Email Address'] ??
                $jsonData['email_address'] ??
                $queuestorage->email ?? null;
        }

        return $queuestorage->email ?? null;
    }

     protected function updateRecordsStatus($queuestorages, $bookings, $message, $suspensionLogId)
    {
        // Update queue storage records
        foreach ($queuestorages as $queuestorage) {
            if($this->actionStatus == 'Cancelled'){

                $queuestorage->update([
                    'suspension_logs_id' => $suspensionLogId->id,
                    'status' =>Queue::STATUS_CANCELLED,
                    'cancelled_datetime' => Carbon::now($this->timezone),
                ]);
            }elseif($this->actionStatus == 'Skip'){
                  $queuestorage->update([
                    'suspension_logs_id' => $suspensionLogId->id,
                    'status' => Queue::STATUS_PENDING,
                    'start_datetime' => null,
                    'called_datetime' => null,
                    'cancelled_datetime' => null,
                    'is_missed' => Queue::STATUS_YES,
                    'is_arrived' => Queue::STATUS_NO,
                    'temp_hold' => Queue::STATUS_NO,
                    'late_duration' => null,
                    // 'datetime' => Carbon::now($this->timezone),
                    'served_by' => null,
                    'counter_id' => null,
                ]);


            }else{


                 $queuestorage->update([
                    'suspension_logs_id' => $suspensionLogId->id,
                   'status' => Queue::STATUS_CLOSE,
                   'called_datetime' => Carbon::now($this->timezone),
                   'start_datetime' => Carbon::now($this->timezone),
                   'closed_datetime' => Carbon::now($this->timezone),
                   'closed_by' => $this->userAuth->id,
                   'served_by' => $this->userAuth->id,
                   'counter_id' => $this->selectedCounter ?? auth::user()->counter_id,
                   'cancelled_datetime' => null,
                    'is_missed' => Queue::STATUS_NO,
                    'is_arrived' => Queue::STATUS_NO,
                    'temp_hold' => Queue::STATUS_NO,
                ]);
            }
        }

        // Update booking records
        foreach ($bookings as $booking) {
            $booking->update([
                'suspension_logs_id' => $suspensionLogId->id,
                'status' => "Cancelled",
                'cancel_remark' => $message,
                'cancel_reason' => $message,
            ]);
        }
    }



 public function nextCall($vistorId  = null, $nextStorageId = null)
    {
        try {
            $this->dispatch('close-modal', id: 'menuOverlayRandom');
            $this->dispatch('close-modal', id: 'historyTakeCall');
            $this->dispatch('close-modal', id: 'unholdCall');

            $holdCall = false;
            $progressCall = false;

            if (empty($this->selectedCounter)) {
                $this->throwError('ERR001');
                return;
            }


            $progressCall = Queue::getProgressRecordExist($this->conditionTeam, $this->userAuth->id, $this->location, $this->selectedCounter);


            if (isset($vistorId, $nextStorageId)) {
                $holdCall = Queue::getHoldRecordExist($this->conditionTeam, $vistorId, $nextStorageId, $this->location);
            } else {
                // no call in visitor list then close current call
                if (empty($vistorId) &&  !$progressCall) {  // when no call remaining
                    $this->throwError('ERR002');
                    return;
                }
                $this->closeCall();
                $progressCall = false;
                return;
            }

            if ($this->showStartBtn == 'HIDE_START_CLOSE') {
                $this->isCloseBtn =false;
                $this->closeCall();

                $progressCall = false;
            }

            if (empty($vistorId) &&  !$progressCall) {  // when no call remaining
                $this->throwError('ERR002');
                return;
            }



            if ($progressCall) {
                $this->throwError('ERR003');
                return;
            }

            if ($holdCall) {
                $this->throwError('ERR004');
                return;
            }

            if(empty($nextStorageId)){
                  $this->throwError('ERR002');
                return;
            }
             $this->queueStorage  = QueueStorage::viewQueue($nextStorageId);

              if (empty($this->queueStorage)){
                $this->throwError('ERR010');
                    return;
               }



          // ✅ Load queue storage once (with relations if you want)

                if ($this->queueStorage->is_missed == Queue::STATUS_YES
                    && !is_null($this->queueStorage->closed_by)
                    && !is_null($this->queueStorage->closed_datetime))
                {
                    // ✅ Directly update model (no `only()`)
                    $this->queueStorage->is_missed = Queue::STATUS_NO;
                    $this->queueStorage->save();

                    // Refresh tokens after updating missed call
                    $this->tokenMissedRefresh();

                    return $this->dispatch('event-success-call', [
                        'message' => __('message.SUCCESS001.message')
                    ]);
                }

                $this->currentVisitorId = $vistorId;
               $this->currentStorageID = $this->queueStorage?->id;

               //when queue call on different call screens.check queue is called or not
                if (!empty($this->queueStorage->called_datetime)){
                // $this->throwError('ERR011');
                $this->emptyCurrentVisitor();
                $this->refreshQueues();

                $this->nextId  = $this->queues?->first()?->queue_id ?? null;
                $this->nextStorageId = Queue::nextStorage($this->queues?->first(), $this->userAuth, $this->userCategories)?->id ?? null;
                $this->nextCall($this->nextId, $this->nextStorageId);
                return;
               }

               $nextcalldata = [
                'queueStorage' => $this->queueStorage,
                'selectedCounter' => $this->selectedCounter,
                'userAuth' => $this->userAuth,
                'isCheckSameCounter' => $this->isCheckSameCounter,
               ];

            if ($this->showStartBtn  == 'SHOW_START_CLOSE') {
                $startCallRes = Queue::nextCalled($nextcalldata);

                if ($startCallRes == 'hold on') {
                    $this->throwError('ERR004');
                    return;
                }
                if(!empty($startCallRes)){

                   $this->currentVisitorId = $startCallRes->id;
                   $this->currentVisitorRecord =  $startCallRes;

                    $this->resetDynamic();
               }else{
                   $this->cVRecordFn();
               }

                if(!empty($this->queueStorage)){

                    QueueCreated::dispatch($this->queueStorage);
                    QueueProgress::dispatch($this->queueStorage);

                }

                // $this->dispatch('event-success-call', ['message' => __('message.SUCCESS001.message')]);
            } else if ($this->showStartBtn  == 'SHOW_CLOSE') {
                $nextcalldata['isStartBtn'] = false;
                $startCallRes = Queue::startCalled($nextcalldata);

                if ($startCallRes == 'hold on') {
                    $this->throwError('ERR004');
                    return;
                }
               if(!empty($startCallRes)){

                    $this->currentVisitorId = $startCallRes->id;
                    $this->currentVisitorRecord =  $startCallRes;
                    $this->notice_sms = $this->currentVisitorRecord->esitmate_note;

                    $this->resetDynamic();
               }else{
                   $this->cVRecordFn();
               }

                // $this->cVRecordFn();
                $this->compusloryStartNextCall();

                if(!empty($this->queueStorage)){
                 QueueCreated::dispatch($this->queueStorage);
                QueueProgress::dispatch($this->queueStorage);

                }
            } else {

                $nextcalldata['isStartBtn'] = false;
                $startCallRes = Queue::startCalled($nextcalldata);

                if ($startCallRes == 'hold on') {
                    $this->throwError('ERR004');
                    return;
                }

                if(!empty($startCallRes)){

                   $this->currentVisitorId = $startCallRes->id;
                   $this->currentVisitorRecord =  $startCallRes;
                     $this->notice_sms = $this->currentVisitorRecord->esitmate_note;

                        $this->resetDynamic();
               }else{
                   $this->cVRecordFn();
               }

                $this->compusloryStartNextCall();

                if(!empty($this->queueStorage)){
                QueueCreated::dispatch($this->queueStorage);
                QueueProgress::dispatch($this->queueStorage);

            }
            }


            if ($this->isCallUnHold == 1) {
                // $this->unholdCall($nextStorageId);
                $this->unholdCall($this->queueStorage->id);
            }
            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS001.message')]);
            $getQueue = $this->queueStorage;

            if (!empty($getQueue)) {

               $counter = Counter::where('id', $this->selectedCounter)->value('name');
               $this->queueStorage['counter_name'] = $counter ?? '';

                QueueDisplay::dispatch($this->queueStorage);
                // QueueNotification::dispatch($this->queueStorage);
               $this->dispatch('audio-sound');

                $currentQueue = json_decode($getQueue->json, true);
                $normalizedJsonArray = array_change_key_case($currentQueue, CASE_LOWER);

                $this->selectedCategoryId = $getQueue->category_id;
                $this->secondChildId = $getQueue->sub_category_id ?? '';
                $this->thirdChildId = $getQueue->child_category_id ?? '';

                $this->firstCategories = Category::getFirstCategoryN($this->team_id, $this->location);
                $this->updateCategories('secondChildId', $this->selectedCategoryId);
                $this->updateCategories('thirdChildId', $this->secondChildId);
                $email = isset($normalizedJsonArray['email']) ? $normalizedJsonArray['email'] : '';
                $name = isset($normalizedJsonArray['name']) ? $normalizedJsonArray['name'] : '';
               

                $this->getData = [
                    'to_mail' => $email,
                    'name' => $name,
                    'phone' => $getQueue->phone ?? '',
                    'phone_code' => $getQueue->phone_code ?? '91',
                    'queue_no' => $getQueue->queue_id,
                    'token' => $getQueue->start_acronym . '' . $getQueue->token,
                    'token_with_acronym' => $getQueue->start_acronym,
                    'counter_name' => $counter,
                    'pending_count' => $getQueue->queue_count,
                    'waiting_time' => $getQueue->waiting_time,
                     'category_name' => $getQueue->category?->name,
                    'secondC_name' => $getQueue->subCategory?->name,
                    'thirdC_name' => $getQueue->childCategory?->name,
                    'locations_id' => $this->location,
                    'meeting_link' => $getQueue->meeting_link
                ];



                $logData = [
                    'team_id' => $this->team_id,
                    'location_id' => $this->location,
                    'user_id' => $this->queueStorage->served_by,
                    'customer_id' => $this->queueStorage->created_by,
                    'queue_id' => $this->queueStorage->queue_id,
                    'queue_storage_id' => $this->queueStorage->id,
                    'email' => $email,
                    'contact' => $this->queueStorage->phone,
                    'type' => MessageDetail::TRIGGERED_TYPE,
                    'event_name' => 'Call Screen - Next Call',
                ];
                DisplayAudio::dispatch($this->queueStorage);
                $this->sendNotification($this->getData, 'call', $logData);
            }

            //SMS Reminder
             if(!empty($this->currentVisitorId) && !empty($nextStorageId)){

                Queue::smsReminderNumber($this->currentVisitorId,$nextStorageId, $this->team_id);
            }

            $this->callPendingEvent($this->currentStorageID);
            ActivityLog::storeLog($this->team_id, $this->userAuth->id, $this->currentVisitorId, $this->currentStorageID, ActivityLog::QUEUE_CALLED, $this->location);
            
            // Immediately refresh queues to remove the called queue from waiting list
            $this->refreshQueues();
        }
        catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

    public function showPopup($url)
    {
        $this->showModal = true;
    }

     public function startCall()
    {
        try {

              $nextcalldata = [
                'queueStorage' => $this->queueStorage,
                'selectedCounter' => $this->selectedCounter,
                'userAuth' => $this->userAuth,
                'isCheckSameCounter' => $this->isCheckSameCounter,
                'isStartBtn' => false,
               ];

                $startCallRes = Queue::startCalled($nextcalldata);

            if ($startCallRes == 'hold on') {
                $this->dispatch('event-error-call', ['message' => __('message.ERR005.message')]);
                return;
            }

            $this->compusloryStartNextCall();
            $this->isCloseBtn = true;
            QueueCreated::dispatch($this->currentVisitorRecord);
            QueueProgress::dispatch($this->currentVisitorRecord);
            QueueDisplay::dispatch($this->currentVisitorRecord);

        } catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

   private function updateSalesforceLeadForQueue($queue, $transferId, bool $isCategoryTransfer)
{
    try {
        if (empty($queue) || empty($queue->salesforce_lead)) {
            return;
        }

        $leadPayload = json_decode($queue->salesforce_lead, true);
        if (!is_array($leadPayload)) {
            return;
        }

        // Make sure this is the Salesforce Lead ID
        $leadId = $leadPayload['id'] ?? ($leadPayload['lead']['id'] ?? null);
        if (empty($leadId)) {
            Log::warning("Salesforce lead ID missing for queue {$queue->id}");
            return;
        }

        // Get Salesforce credentials
        $sfSettings = SalesforceSetting::where('team_id', $this->team_id)
            ->where('location_id', $this->location)
            ->first();

        $connection = SalesforceConnection::where('team_id', $this->team_id)
            ->where('location_id', $this->location)
            ->where('status', 1)
            ->first();

        if (empty($sfSettings) || empty($connection?->salesforce_refresh_token)) {
            Log::warning("Salesforce settings or connection missing for team {$this->team_id}");
            return;
        }

        $clientId = $sfSettings->client_id;
        $clientSecret = $sfSettings->client_secret;
        $tokenUrl = 'https://login.salesforce.com/services/oauth2/token';

        // Base fields to update
        $fields = [
            'QwaitingSyncDate__c' => now()->toIso8601String(),
        ];

        // Assign OwnerId based on transfer type
        $staff = null;

        if ($isCategoryTransfer) {
            // Update ServiceName__c
            $fields['ServiceName__c'] = Category::viewCategoryName($transferId) ?? null;

            // Get first Level 3 user for this category (level_id = 3)
            $staff = Category::find($transferId)
                ->users()
                ->where('level_id', 3)
                 ->select('id','saleforce_user_id')
                ->first();

        } else {
            // Counter transfer
            $staff = User::where('team_id', $this->team_id)
                ->where('level_id', 3)
                ->where('counter_id', $transferId)
                ->select('id','saleforce_user_id')
                ->first();
        }

        if (!empty($staff?->saleforce_user_id)) {
            $fields['Ownerid'] = $staff->saleforce_user_id;

            // Update queue's assign_staff_id
            $queue->assign_staff_id = $staff->id;
            $queue->save();
        }

        // Filter out null fields
        $fields = array_filter($fields, fn($v) => !is_null($v));

        if (empty($fields)) {
            Log::info("No fields to update for Salesforce Lead {$leadId}");
            return;
        }

        // Logging before sending
        Log::info('Updating Salesforce Lead', [
            'queue_id' => $queue->id,
            'leadId' => $leadId,
            'fields' => $fields,
        ]);

        // Update lead via Salesforce service
        $service = new SalesforceService($clientId, $clientSecret, $tokenUrl);
        $result = $service->updateLead($connection->salesforce_refresh_token, $leadId, $fields);

        Log::info('Salesforce update result', $result);

    } catch (\Throwable $e) {
        Log::warning('Salesforce lead update failed: '.$e->getMessage(), [
            'queue_id' => $queue->id,
        ]);
    }
}

    public  function compusloryStartNextCall()
    {
        $this->isServingTime = true;
        $this->isStartBtn = false;

        $this->dispatch('event-serving-time');

        $this->dispatch('event-success-call', ['message' => __('message.SUCCESS003.message')]);
    }

  public function closeCall()
    {

        try {
            $this->currentVisitorId = $this->currentVisitorRecord->id;
            $this->currentVisitorRecord?->update(['status' => Queue::STATUS_CLOSE, 'closed_datetime' => Carbon::now($this->timezone), 'closed_by' => $this->userAuth->id]);

            ActivityLog::storeLog($this->team_id, $this->userAuth->id, $this->currentVisitorId, $this->currentStorageID, ActivityLog::QUEUE_CLOSED, $this->location);

            if (!empty($this->currentVisitorId)) {

   if($this->enable_callDepartment){

                    $nextdepartmentcall = QueueStorage::where('queue_id',$this->currentVisitorRecord->queue_id)->where('called','no')->whereNull('called_datetime')->first();
                    $remaindepartmentcall = QueueStorage::where('queue_id',$this->currentVisitorRecord->queue_id)->update(['temp_hold'=>1]);


                    if(!empty($nextdepartmentcall)){
                      $nextdepartmentcall->update(['called' =>'yes','temp_hold'=>0]);

                      QueueDepartment::dispatch($nextdepartmentcall);
                    }
                }

                    if(!empty($this->currentVisitorRecord)){
                        QueueCreated::dispatch($this->currentVisitorRecord);
                 QueueProgress::dispatch($this->currentVisitorRecord);
                QueueDisplay::dispatch($this->currentVisitorRecord);
                $getType = json_decode($this->currentVisitorRecord->json, true);
                if (isset($getType['type']) && $getType['type'] == 'Virtual') {
                // if ($this->enableVirtual) {

                    QueueVirtual::dispatch($this->currentVisitorRecord);
                    $this->dispatch('close-virtual-call');
                }
                }


                $data = [];

                $userDetails = json_decode($this->currentVisitorRecord?->json, true);

                $data['to_mail'] = isset($userDetails['email']) ? $userDetails['email'] : (isset($userDetails['Email']) ? $userDetails['Email'] : null);
                $data['name'] = $this->currentVisitorRecord?->name;
                $data['phone'] = $this->currentVisitorRecord?->phone;
                $data['phone_code'] = $this->currentVisitorRecord->phone_code ?? '91';
                $data['locations_id'] = $this->currentVisitorRecord->locations_id;

                $logData = [
                    'team_id' => $this->team_id,
                    'location_id' => $this->location,
                    'user_id' => $this->currentVisitorRecord->served_by,
                    'customer_id' => $this->currentVisitorRecord->created_by,
                    'queue_id' => $this->currentVisitorRecord->queue_id,
                    'queue_storage_id' => $this->currentVisitorRecord->id,
                    'email' => $this->currentVisitorRecord->email ?? '',
                    'contact' => $this->currentVisitorRecord->phone ?? '',
                    'type' => MessageDetail::TRIGGERED_TYPE,
                    'event_name' => 'Call Screen - Close Call',
                ];


                // if ($this->feedbackSetting?->enable_post_interaction == FeedbackSetting::STATUS_INACTIVE  && !empty($this->currentVisitorRecord?->closed_datetime) && $this->currentVisitorRecord?->ticket_mode  == Queue::TICKET_MODE_Walk_IN) {
                if ($this->feedbackSetting?->enable_post_interaction == FeedbackSetting::STATUS_INACTIVE  && !empty($this->currentVisitorRecord?->closed_datetime)) {
                    $data = [];
                    $userDetails = json_decode($this->currentVisitorRecord?->json, true);

                    $data['to_mail'] = isset($userDetails['email']) ? $userDetails['email'] : (isset($userDetails['Email']) ? $userDetails['Email'] : null);
                    $data['name'] = $this->currentVisitorRecord?->name;
                    $data['phone'] = $this->currentVisitorRecord?->phone;
                    $data['phone_code'] = $this->currentVisitorRecord->phone_code ?? '91';
                    $data['locations_id'] = $this->currentVisitorRecord->locations_id;

                    $logData = [
                        'team_id' => $this->team_id,
                        'location_id' => $this->location,
                        'user_id' => $this->currentVisitorRecord->served_by,
                        'customer_id' => $this->currentVisitorRecord->created_by,
                        'queue_id' => $this->currentVisitorRecord->queue_id,
                        'queue_storage_id' => $this->currentVisitorRecord->id,
                        'email' => $this->currentVisitorRecord->email,
                        'contact' => $this->currentVisitorRecord->phone,
                        'type' => MessageDetail::TRIGGERED_TYPE,
                        'event_name' => 'Call Screen - Close Call',
                    ];
                    // $this->sendNotification($data, 'rating survey', $logData);

                    if (!empty($data['to_mail']) || !empty($data['phone'])) {
                        // $data['feedback_link'] = request()->getHost() . '/rating/survey?code=' . base64_encode($this->currentVisitorRecord?->id) . '&loc=' . base64_encode($this->location);

                        $data['feedback_link'] = request()->getHost() . '/rating/survey?code=' . base64_encode($this->currentVisitorRecord?->id);

                        $this->sendNotification($data, 'rating survey', $logData);
                    }
                } else {

                    $this->callPendingEvent($this->currentStorageID);
                     if (!empty($data['phone'])) {
                    // $this->sendNotification($data, 'rating survey', $logData);
                }

                }

                //unhold the remaining calls of same queue id when the current call closed
                QueueStorage::where('queue_id', $this->currentVisitorId)
                    ->where('id', '!=', $this->currentStorageID)
                    ->update(['temp_hold' => Queue::STATUS_NO]);

                $this->emptyCurrentVisitor();
                $this->refreshQueues();

                if (empty($this->currentVisitorRecord)) {
                    $this->nextId  = $this->queues?->first()?->queue_id ?? null;
                    $this->nextStorageId = Queue::nextStorage($this->queues?->first(), $this->userAuth, $this->userCategories)?->id ?? null;
                }

                $this->dispatch('event-success-call', ['message' => __('message.SUCCESS004.message')]);



            }
        } catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }
    
    public function removQueue()
    {
        $this->queues = $this->queues->reject(
            function ($queue) {
                return $queue->id === $this->currentVisitorId;
            }
        );
        $this->nextId = $this->queues->first()?->queue_id;
        $this->nextStorageId = Queue::nextStorage($this->queues?->first(), $this->userAuth, $this->userCategories)?->id ?? null;
    }

    public function cVRecordFn()
    {

        if (empty($this->currentVisitorId)) {
            $condition = array_merge($this->conditionTeam, ['counter_id' => $this->selectedCounter]);
            $this->currentVisitorRecord = Queue::currentVisitorRecord($condition, $this->userAuth->id, null, (int)$this->location, $this->currentStorageID);
            if (!empty($this->currentVisitorRecord))
                $this->currentVisitorId = $this->currentVisitorRecord->queue_id;
        } else {
            $condition = array_merge($this->conditionTeam, ['counter_id' => $this->selectedCounter]);
            $this->currentVisitorRecord =  Queue::currentVisitorRecord($condition, $this->userAuth->id, $this->currentVisitorId, (int)$this->location, $this->currentStorageID);
        }


        if ($this->currentVisitorRecord) {
            $this->notice_sms = $this->currentVisitorRecord->esitmate_note;
            if (empty($this->currentVisitorRecord->start_datetime)) {
                $this->isStartBtn = true;
                $this->isCloseBtn = false;
                $this->isServingTime = false;
            } else if (!empty($this->currentVisitorRecord->start_datetime)) {
                $this->isStartBtn = false;
                $this->isServingTime = true;
                if ($this->showStartBtn == 'HIDE_START_CLOSE'){

                    $this->isCloseBtn = false;
                }
                else{

                    $this->isCloseBtn = true;
                $this->dispatch('event-serving-time');
                }
            }
            $this->thirdChildId   = $this->currentVisitorRecord->child_category_id;
            $this->secondChildId = $this->currentVisitorRecord->sub_category_id;
            $this->selectedCategoryId = $this->currentVisitorRecord->category_id;
            $this->currentStorageID  = $this->currentVisitorRecord->id;
            $this->queueStorage  = $this->currentVisitorRecord;
            $this->resetDynamic();
            // $this->mainQueue = Queue::find($this->currentVisitorId);
        }
        // if ( !empty( $this->queueStorage ) ) {
        //     $this->nextId  = $this->queues?->first()?->id ?? null;
        // }
    }

    public function skipCall()
    {

        try {

            if (!empty($this->currentVisitorRecord)) {

                $this->moveBackMQVL();
                $getQueue = $this->currentVisitorRecord;
                 $getType = json_decode($this->currentVisitorRecord->json, true);

              if (isset($getType['type']) && $getType['type'] == 'Virtual') {
                    QueueVirtual::dispatch($this->currentVisitorRecord);
                    $this->dispatch('close-virtual-call');
                }
            }

             if(!empty($this->currentVisitorRecord)){
            QueueCreated::dispatch($this->currentVisitorRecord);
            QueueProgress::dispatch($this->currentVisitorRecord);
            QueueDisplay::dispatch($this->currentVisitorRecord);
             }
            $this->callPendingEvent($this->currentStorageID);
            ActivityLog::storeLog($this->team_id, $this->userAuth->id, $this->currentVisitorId, $this->currentStorageID, ActivityLog::CALL_SKIPPED, $this->location);

            $this->emptyCurrentVisitor();
            $this->removQueue();

            $this->refreshQueues();


            // $this->missedCalls = Queue::getMissedCallId($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location);

            if($this->enable_callDepartment){

            $this->missedCalls = Queue::getMissedCallId($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location,true);
        }else{
            $this->missedCalls = Queue::getMissedCallId($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location,false);

        }
            if (!empty($getQueue)) {

                $currentQueue = json_decode($getQueue->json, true);
                $normalizedJsonArray = array_change_key_case($currentQueue, CASE_LOWER);

                // $getCategory = Category::where('id', $getQueue->category_id)->value('name');
                // $getSubCategory = Category::where('id', $getQueue->sub_category_id)->value('name');
                // $getChildCategory = Category::where('id', $getQueue->child_category_id)->value('name');





                $email = isset($normalizedJsonArray['email']) ? $normalizedJsonArray['email'] : '';
                $name = isset($normalizedJsonArray['name']) ? $normalizedJsonArray['name'] : '';

                $this->getData = [
                    'to_mail' => $email,
                    'name' => $name,
                    'phone' => $getQueue->phone ?? '',
                    'phone_code' => $getQueue->phone_code ?? '',
                    'queue_no' => $getQueue->queue_id,
                    'token' => $getQueue->start_acronym . '' . $getQueue->token,
                    'token_with_acronym' => $getQueue->start_acronym,
                    'pending_count' => $getQueue->queue_count,
                    'waiting_time' => $getQueue->waiting_time,
                     'category_name' => $getQueue->category?->name,
                    'secondC_name' => $getQueue->subCategory?->name,
                    'thirdC_name' => $getQueue->childCategory?->name,
                ];

                $logData = [
                    'team_id' => $this->team_id,
                    'location_id' => $this->location,
                    'user_id' => $getQueue->served_by,
                    'customer_id' => $getQueue->created_by,
                    'queue_id' => $getQueue->queue_id,
                    'queue_storage_id' => $getQueue->id,
                    'email' => $getQueue->email,
                    'contact' => $getQueue->phone,
                    'type' => MessageDetail::TRIGGERED_TYPE,
                    'event_name' => 'Call Screen - Skip Call',
                ];

                $this->sendNotification($this->getData, 'call skip', $logData);
            }

            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS0016.message')]);
        } catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

     public function transferCall($transferId)
    {

        try {

            if ($this->isRandomTransfer == true) {
                $this->isRandomTransfer = false;
                $this->randomCurrentQueue->transfer_id = $transferId;
                $this->randomCurrentQueue->transfer_by = Auth::id();
                $this->randomCurrentQueue->forward_counter_id = null;
                $this->randomCurrentQueue->datetime = Carbon::now($this->timezone);
                $this->randomCurrentQueue->save();
                // Update Salesforce lead for this queue
                $this->updateSalesforceLeadForQueue($this->randomCurrentQueue, $transferId, true);
                $queueID = $this->randomCurrentQueue?->queue_id;
                $currentStorageID = $this->randomCurrentQueue->id;
                if(!empty($this->randomCurrentQueue)){

                   QueueCreated::dispatch($this->randomCurrentQueue);
                    QueueProgress::dispatch($this->randomCurrentQueue);
                    QueueTransfer::dispatch($this->randomCurrentQueue);
                    QueueDisplay::dispatch($this->randomCurrentQueue);

                }

                $transferCategoryname  = Category::viewCategoryName($this->randomCurrentQueue?->transfer_id) ?? '';
            $transfer_remark = "and transfer to ".$transferCategoryname;

            } else {
                if (!empty($this->currentVisitorRecord)) {
                    $this->isRandomTransfer = false;
                    $this->currentVisitorRecord->update([
                        'start_datetime' => null,
                        'called_datetime' => null,
                        'status' => Queue::STATUS_PENDING,
                        'transfer_id' => $transferId,
                         'transfer_by' => Auth::id(),
                        'forward_counter_id' => null,
                        'datetime' => Carbon::now($this->timezone)

                    ]);

                    // Update Salesforce lead for this queue
                    $this->updateSalesforceLeadForQueue($this->currentVisitorRecord, $transferId, true);
                }

                if (!empty($this->currentVisitorRecord)) {
                    QueueCreated::dispatch($this->currentVisitorRecord);
                    QueueProgress::dispatch($this->currentVisitorRecord);
                    QueueTransfer::dispatch($this->currentVisitorRecord);
                     QueueDisplay::dispatch($this->currentVisitorRecord);

                    $getType = json_decode($this->currentVisitorRecord->json, true);

                   if (isset($getType['type']) && $getType['type'] == 'Virtual') {
                       QueueVirtual::dispatch($this->currentVisitorRecord);
                    $this->dispatch('close-virtual-call');
                    }

                }
                $this->nextId = $this->queues->first()?->queue_id;
                $this->nextStorageId = Queue::nextStorage($this->queues?->first(), $this->userAuth, $this->userCategories)?->id ?? null;

                $queueID = $this->currentVisitorRecord?->queue_id;
                $currentStorageID = $this->currentVisitorRecord?->id;
                $this->emptyCurrentVisitor();
                $transferCategoryname  = Category::viewCategoryName($this->currentVisitorRecord?->transfer_id) ?? '';
                $transfer_remark = "and transfer to ".$transferCategoryname;
            }

            $this->callPendingEvent($currentStorageID);

            ActivityLog::storeLog($this->team_id, $this->userAuth->id, $queueID, $currentStorageID, ActivityLog::SERVICE_TRANSFER, $this->location,null,$transfer_remark);

            $this->modelclose();
            $this->modelmyModalTransfer = false;
            // $this->dispatch('close-modal', id: 'myModalTransfer');
            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS005.message')]);

        } catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }
    public function transferCounterCall($transferId)
    {

        try {

            if ($this->isRandomTransfer == true) {
                $this->isRandomTransfer = false;
                $this->randomCurrentQueue->forward_counter_id = $transferId;
                $this->randomCurrentQueue->transfer_id = null;
                $this->randomCurrentQueue->counter_id = $transferId;
                 $this->randomCurrentQueue->transfer_by = Auth::id();
                $this->randomCurrentQueue->datetime = Carbon::now($this->timezone);
                $this->randomCurrentQueue->save();
                // Update Salesforce lead for this queue (owner change)
                $this->updateSalesforceLeadForQueue($this->randomCurrentQueue, $transferId, false);
                $queueID = $this->randomCurrentQueue?->queue_id;
                $currentStorageID = $this->randomCurrentQueue->id;
                if(!empty($this->randomCurrentQueue)){

                    QueueCreated::dispatch($this->randomCurrentQueue);
                    QueueProgress::dispatch($this->randomCurrentQueue);
                    QueueTransfer::dispatch($this->randomCurrentQueue);
                     QueueDisplay::dispatch($this->randomCurrentQueue);

                }

                  $transferCountername  = $this->randomCurrentQueue?->forwardcounter->name ?? '';
                  $transfer_remark = "and transfer to ".$transferCountername;
            } else {
                if (!empty($this->currentVisitorRecord)) {
                    $this->isRandomTransfer = false;
                    $this->currentVisitorRecord->update([
                        'start_datetime' => null,
                        'called_datetime' => null,
                        'status' => Queue::STATUS_PENDING,
                        'forward_counter_id' => $transferId,
                        'counter_id' => $transferId,
                        'transfer_id' => null,
                         'transfer_by' => Auth::id(),
                        'datetime' => Carbon::now($this->timezone)

                    ]);
                    // Update Salesforce lead for this queue (owner change)
                    $this->updateSalesforceLeadForQueue($this->currentVisitorRecord, $transferId, false);
                    QueueCreated::dispatch($this->currentVisitorRecord);
                    QueueProgress::dispatch($this->currentVisitorRecord);
                    QueueTransfer::dispatch($this->currentVisitorRecord);
                     QueueDisplay::dispatch($this->currentVisitorRecord);

                    $getType = json_decode($this->currentVisitorRecord->json, true);

                   if (isset($getType['type']) && $getType['type'] == 'Virtual') {
                       QueueVirtual::dispatch($this->currentVisitorRecord);
                    $this->dispatch('close-virtual-call');
                    }


                }


                $this->nextId = $this->queues->first()?->queue_id;
                $this->nextStorageId = Queue::nextStorage($this->queues?->first(), $this->userAuth, $this->userCategories)?->id ?? null;

                $queueID = $this->currentVisitorRecord?->queue_id;
                $currentStorageID = $this->currentVisitorRecord?->id;
                $this->emptyCurrentVisitor();

                  $transferCountername  = $this->currentVisitorRecord?->forwardcounter->name ?? '';
                   $transfer_remark = "and transfer to ".$transferCountername;
            }

            $this->callPendingEvent($currentStorageID);


            ActivityLog::storeLog($this->team_id, $this->userAuth->id, $queueID, $currentStorageID, ActivityLog::COUNTER_TRANSFER, $this->location,null,$transfer_remark);

            $this->modelclose();
            $this->modelmyModalTransfer = false;
            // $this->dispatch('close-modal', id: 'myModalTransfer');
            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS005.message')]);

        } catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

    public function openmyModalTransfer()
    {
        $this->modelclose();
        $this->modelmyModalTransfer = true;
        $this->dispatch('open-modal', id: 'myModalTransfer');
    }

    public function reCall()
    {
        try {

            ActivityLog::storeLog($this->team_id, $this->userAuth->id, $this->currentVisitorId,  $this->currentStorageID,  ActivityLog::QUEUE_RECALLED, $this->location);

            $getQueue = $this->currentVisitorRecord;

            $getQueue->update([
                'datetime' => date('Y-m-d H:i:s')
            ]);

             if(!empty($getQueue)){
                QueueCreated::dispatch($getQueue);
                QueueProgress::dispatch($getQueue);
                QueueDisplay::dispatch($getQueue);
                DisplayAudio::dispatch($getQueue);
             }

            if (empty($this->getData)) {
                $currentQueue = json_decode($getQueue->json, true);
                $normalizedJsonArray = array_change_key_case($currentQueue, CASE_LOWER);

                // $getCategory = Category::where('id', $getQueue->category_id)->value('name');
                // $getSubCategory = Category::where('id', $getQueue->sub_category_id)->value('name');
                // $getChildCategory = Category::where('id', $getQueue->child_category_id)->value('name');

                $email = isset($normalizedJsonArray['email']) ? $normalizedJsonArray['email'] : '';
                $name = isset($normalizedJsonArray['name']) ? $normalizedJsonArray['name'] : '';
                $counter = Counter::where('id', $this->selectedCounter)->value('name');

                $this->getData = [
                    'to_mail' => $email,
                    'name' => $name,
                    'phone' => $getQueue->phone ?? '',
                    'phone_code' => $getQueue->phone_code ?? '',
                    'queue_no' => $getQueue->queue_id,
                    'counter_name' => $counter,
                    'token' => $getQueue->start_acronym . '' . $getQueue->token,
                    'token_with_acronym' => $getQueue->start_acronym,
                    'pending_count' => $getQueue->queue_count,
                    'waiting_time' => $getQueue->waiting_time,
                    'category_name' => $getQueue->category?->name,
                    'secondC_name' => $getQueue->subCategory?->name,
                    'thirdC_name' => $getQueue->childCategory?->name,
                ];
            }

            $logData = [
                'team_id' => $this->team_id,
                'location_id' => $this->location,
                'user_id' => $getQueue->served_by,
                'customer_id' => $getQueue->created_by,
                'queue_id' => $getQueue->queue_id,
                'queue_storage_id' => $getQueue->id,
                'email' => $getQueue->email,
                'contact' => $getQueue->phone,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Call Screen',
            ];

            $this->sendNotification($this->getData, 'recall', $logData);
            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS006.message')]);
        } catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

  public function moveBackMQVL()
{
    // Common update fields
    $data = [
        'status'               => Queue::STATUS_PENDING,
        'called_datetime'      => null,
        'cancelled_datetime'   => null,
        'start_datetime'       => null,
        'closed_datetime'      => null,
        'datetime'             => Carbon::now($this->timezone),
        'is_missed'            => Queue::STATUS_YES,
        'is_hold'              => Queue::STATUS_NO,
        'hold_by'              => null,
        'hold_start_date'      => null,
        'hold_end_date'        => null,
        'is_arrived'           => Queue::STATUS_NO,
        'temp_hold'            => Queue::STATUS_NO,
        'late_duration'        => null,
        'served_by'            => null,
        'closed_by'            => null,
        'alert_waiting_show'   => 0,
    ];


    // if (!$this->enable_callDepartment) {
    //     $data['counter_id'] = null;
    // }

    $this->currentVisitorRecord->update($data);
}


    public function moveBack()
    {

        try {

            if (!empty($this->currentVisitorRecord)) {


                if ($this->siteDetail?->served_queue_move == Queue::MOVE_BACK_TO_MQ) {
                    $this->moveBackMQVL();
                } else {
                    $this->currentVisitorRecord->update([
                        'status' => Queue::STATUS_PENDING,
                        'start_datetime' => null,
                        'called_datetime' => null,
                        'closed_datetime' => null,
                        'datetime' => Carbon::now($this->timezone),
                        'is_arrived' => Queue::STATUS_NO,
                        'late_duration' => null,
                        'is_arrived' => Queue::STATUS_NO,
                        'temp_hold' => Queue::STATUS_NO,
                        'is_missed' => Queue::STATUS_NO,
                        'is_hold' => Queue::STATUS_NO,
                        'hold_by' => null,
                        'hold_start_date' => null,
                        'hold_end_date' => null,
                        'served_by' => null,
                        'closed_by' => null,
                         'alert_waiting_show' =>0
                    ]);

                    if (!$this->enable_callDepartment) {
                        $this->currentVisitorRecord->update([
                            'counter_id' => null,
                        ]);
                    }
                }

            $getType = json_decode($this->currentVisitorRecord->json, true);

              if (isset($getType['type']) && $getType['type'] == 'Virtual') {
                    QueueVirtual::dispatch($this->currentVisitorRecord);
                    $this->dispatch('close-virtual-call');
            }

            QueueDisplay::dispatch($this->currentVisitorRecord);
            ActivityLog::storeLog($this->team_id, $this->userAuth->id,  $this->currentVisitorRecord?->queue_id, $this->currentVisitorRecord?->id, ActivityLog::VISITOR_MOVE_BACK, $this->location);
            QueueProgress::dispatch($this->currentVisitorRecord);
            $this->callPendingEvent($this->currentVisitorRecord?->id);

            QueueCreated::dispatch($this->currentVisitorRecord);
            }



            $this->nextId = $this->queues->first()?->queue_id;
            $this->nextStorageId = Queue::nextStorage($this->queues?->first(), $this->userAuth, $this->userCategories)?->id ?? null;

            $this->emptyCurrentVisitor();

            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS007.message')]);
        } catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

    public function getListeners()
    {
        return [
            // "echo-private:queue-call.{$this->team_id},QueueCreated" => 'pushLiveQueue',
            "echo-private:break-reason.{$this->userAuth->id},BreakEvent" => 'pushLiveBreak',

        ];
    }

    #[On('break-request')]
    public function pushLiveBreak($event)
    {
        $eventData =  $event['breakReason'];

        if (!empty($eventData)) {
            $breakData = BreakReason::find($eventData['breakreason_id']);
            $this->break_id = $eventData['id'];
            if ($eventData['status'] == StaffBreak::STATUS_APPROVED) {
                $this->dispatch('event-continue-break', ['breakTime' => $breakData?->break_time]);
            } elseif ($eventData['status'] == StaffBreak::STATUS_REJECTED) {
                $this->dispatch('event-continue-break-close');
            }
        }
    }

    #[On('next-queue')]
     public function pushNextcall($event)
    {

        $this->currentVisitorId = $event['queue']['queue_id'];
        $this->currentStorageID  = $event['queue']['id'];
        // $this->cVRecordFn();
     if($this->enable_callDepartment){

            $this->dispatch('refreshComponent');
        }
        $condition = array_merge($this->conditionTeam, ['counter_id' => $this->selectedCounter]);
        if (empty($this->currentVisitorId)) {
            $this->currentVisitorRecord = Queue::currentVisitorRecord($condition, $this->userAuth->id, null, (int)$this->location, $this->currentStorageID);
            if (!empty($this->currentVisitorRecord)) {

                $this->currentVisitorId = $this->currentVisitorRecord->queue_id;
            }
        } else {
            // dd($this->conditionTeam, $this->userAuth->id, $this->currentVisitorId, (int)$this->location, $this->currentStorageID);
            if (empty($this->currentVisitorRecord)) {
                $served_by =  $event['queue']['served_by'] ?? $this->userAuth->id;

                $this->currentVisitorRecord =  Queue::currentVisitorRecord($condition, $served_by, $this->currentVisitorId, (int)$this->location, $this->currentStorageID);
            }
        }


        if ($this->currentVisitorRecord && $this->currentVisitorRecord->closed_datetime == null ) {
            $this->notice_sms = $this->currentVisitorRecord->esitmate_note;
            if (empty($this->currentVisitorRecord->start_datetime)) {
                $this->isStartBtn = true;
                $this->isCloseBtn = false;
                $this->isServingTime = false;
            } else if (!empty($this->currentVisitorRecord->start_datetime)) {
                $this->isStartBtn = false;
                $this->isServingTime = true;
                if ($this->showStartBtn == 'HIDE_START_CLOSE')
                    $this->isCloseBtn = false;
                else
                    $this->isCloseBtn = true;
            }
            $this->thirdChildId   = $this->currentVisitorRecord->child_category_id;
            $this->secondChildId = $this->currentVisitorRecord->sub_category_id;
            $this->selectedCategoryId = $this->currentVisitorRecord->category_id;
            $this->currentStorageID  = $this->currentVisitorRecord->id;
            $this->queueStorage  = $this->currentVisitorRecord;
        }else{

            $this->emptyCurrentVisitor();
        }
        
        // Refresh queues to remove the called queue from waiting list
        $this->refreshQueues();

        $this->nextId  = $this->queues?->first()?->queue_id ?? null;
        $this->nextStorageId = Queue::nextStorage($this->queues?->first(), $this->userAuth, $this->userCategories)?->id ?? null;
         $this->dispatch('reset-serving-time');
         $this->dispatch('event-serving-time');
        }

      #[On('transfer-queue')]
 public function pushtransfercall($event)
{
    $userAuth = Auth::user();

    $assignedCategories = $userAuth->categories->pluck('id')->toArray();
    $assignedCounter    = $userAuth->counter_id;
    $assignedCounters   = $userAuth['assign_counters'] ?? [];
    $allCounters = array_values(array_unique(array_merge(["$assignedCounter"], $assignedCounters)));

    $queueData = $event['queue'] ?? null;

    if ($queueData) {
        $queue = QueueStorage::with(['transfer', 'forwardcounter', 'transferBy'])
            ->find($queueData['id']);

        if (!empty($queueData['transfer_id']) && in_array((int) $queueData['transfer_id'], $assignedCategories)) {
            $this->dispatch('queue-alert', [
                'type'   => 'category',
                'teamId'   => $queue->team_id,
                'name'   => $queue->transfer?->name ?? 'Unknown',
                'token'  => $queue?->start_acronym.$queue->token,
                'by'     => $queue->transferBy?->name ?? 'System',
            ]);
              $this->dispatch('audio-sound');
        }

        if (!empty($queueData['forward_counter_id']) && in_array($queueData['forward_counter_id'], $allCounters)) {
            $this->dispatch('queue-alert', [
                'type'   => 'counter',
                 'teamId'   => $queue->team_id,
                'name'   => $queue->forwardcounter?->name ?? 'Unknown',
                'token'  =>  $queue?->start_acronym.$queue->token,
                'by'     => $queue->transferBy?->name ?? 'System',
            ]);
              $this->dispatch('audio-sound');
        }
    }
}

    #[On('create-queue')]
    public function pushLiveQueue($event)
    {
        \Log::info('📢 create-queue event received', ['event' => $event]);
        
        // Extract queue data from event structure
        // Event comes as: { event: { queue: {...} } }
        $queueData = $event['queue'] ?? $event['event']['queue'] ?? ($event['event'] ?? null);
        
        if ($queueData) {
            $queueId = $queueData['id'] ?? null;
            $queueLocation = $queueData['locations_id'] ?? $queueData['location_id'] ?? null;
            
            \Log::info('📢 Queue data extracted', [
                'queue_id' => $queueId,
                'queue_location' => $queueLocation,
                'current_location' => $this->location
            ]);
            
            // Only process if queue is for current location (or no location filter)
            if ($queueLocation && $queueLocation != $this->location) {
                // Queue is for different location, skip
                \Log::info('⏭️ Skipping queue - different location');
                return;
            }
            
            if ($queueId) {
                $queueStorage = QueueStorage::find($queueId);
                // Only add to list if it hasn't been called yet and matches location
                if ($queueStorage && is_null($queueStorage->called_datetime) && $queueStorage->locations_id == $this->location) {
                    // Immediately refresh queues to show new queue
                    \Log::info('✅ Refreshing queues - new queue added');
                    $this->refreshQueues();
                    $this->initialiAfterQueue();
                    // Force Livewire to re-render
                    $this->dispatch('$refresh');
                } else {
                    // If queue was called, just refresh to remove it from waiting list
                    \Log::info('🔄 Refreshing queues - queue was called');
                    $this->refreshQueues();
                    $this->dispatch('$refresh');
                }
            } else {
                // Fallback: refresh anyway
                \Log::info('🔄 Refreshing queues - fallback');
                $this->refreshQueues();
                $this->initialiAfterQueue();
                $this->dispatch('$refresh');
            }
        } else {
            // Fallback: refresh anyway (event received but data structure unexpected)
            \Log::warning('⚠️ Event received but queue data not found', ['event' => $event]);
            $this->refreshQueues();
            $this->initialiAfterQueue();
            $this->dispatch('$refresh');
        }
        
        if(!empty($this->currentVisitorRecord) && is_null($this->currentVisitorRecord->called_datetime)){
           $this->dispatch('reset-serving-time');
           $this->dispatch('event-serving-time');
       }

    }
    #[On('break-created')]
    public function handleBreakSubmitted($breakType, $breakComment)
    {
        //breaktype here is break reason  id;
        $breakData = BreakReason::find($breakType);
        if ($breakData->is_approved == BreakReason::IS_APPROVED_YES) {
            // $breakData = BreakReason::find($breakType);
            $break =  StaffBreak::storeStaffBreak([
                'team_id' => $this->team_id,
                'location_id' => $this->location,
                'user_id' => $this->userAuth->id,
                'reason' => $breakData->reason,
                'comment' => $breakComment,
                'time_start' => date('Y-m-d H:i:s'),
                'status' => StaffBreak::STATUS_AUTO_APPROVAL,
                'breakreason_id' => $breakData->id,

            ]);
            $this->break_id = $break->id;
            $this->dispatch('event-continue-break', ['breakTime' => $breakData->break_time]);
        } else {
            $break =  StaffBreak::storeStaffBreak([
                'team_id' => $this->team_id,
                'location_id' => $this->location,
                'user_id' => $this->userAuth->id,
                'reason' => $breakData->reason,
                'comment' => $breakComment,
                'status' => StaffBreak::STATUS_PENDING,
                'breakreason_id' => $breakData->id,
            ]);
            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS008.message')]);
            // if ($break->status == StaffBreak::STATUS_APPROVED) {
            //     $this->dispatch('event-continue-break', ['breakTime' => $breakData->break_time]);
            // } else {

            // }
        }
    }
    #[On('break-end')]
    public function handleBreakEnd()
    {
        $break = StaffBreak::findOrFail($this->break_id);

        $break->update(['time_end' => Carbon::now($this->timezone)]);
        $breakStartTime = Carbon::parse($break->time_start);
        $breakEndTime = Carbon::parse($break->time_end);
        $differenceInMinutes = $breakStartTime->diffInMinutes($breakEndTime);
        $breakData = BreakReason::find($break->breakreason_id);
        $breaktime = $breakData->break_time;
        if ($differenceInMinutes <= $breaktime) {
            $break->update(['break_status' => 'On Time']);
        } else {
            $break->update(['break_status' => 'Late Arrival']);
        }
    }

    public function callPendingEvent($currentStorageID)
    {
        if (!empty($currentStorageID))
            QueuePending::dispatch(QueueStorage::viewQueue($currentStorageID));
    }


      public function emptyCurrentVisitor()
    {
        $this->currentVisitorRecord = null;
        $this->currentVisitorId = null;
        $this->queueStorage = null;
        $this->nextStorageId = null;

         $this->dispatch('reset-serving-time');
    }

    public function menuOverlay($queueID, $nextStorageId  = null)
    {
        $this->randomQueueID = $queueID;
        $this->dispatch('open-modal', id: 'menuOverlayRandom');
        $this->randomQueueStorageID = $nextStorageId;
        $this->modelclose();
        $this->modelmenuOverlayRandom = true;
    }

  public function holdCall($queueID)
{
    // $record = QueueStorage::findOrFail($queueID);

     $record = QueueStorage::select('id', 'queue_id', 'team_id', 'locations_id','is_hold','hold_start_datetime', 'hold_by','status','called_datetime','start_datetime','closed_datetime','counter_id','start_acronym','token','arrives_time')
        ->findOrFail($queueID);


    $record->update([
        'is_hold'             => Queue::STATUS_YES,
        'hold_start_datetime' => Carbon::now($this->timezone),
        'hold_by'             => Auth::id(),
    ]);

    // Store activity log
    ActivityLog::storeLog(
        $this->team_id,
        $this->userAuth->id,
        (int) $record->queue_id,
        (int) $record->id,
        ActivityLog::HOLD_QUEUE,
        $this->location
    );

    // Handle Virtual type
    if (!empty($record->type) && $record->type === 'Virtual') {
        QueueVirtual::dispatch($record);
        $this->dispatch('close-virtual-call');
    }

    $this->isRandomTransfer = true;
    $this->modelclose();
    $this->tokenHoldRefresh();
    $this->viewQueue();
    $this->loadMoreVisitor();

    QueueDisplay::dispatch($record);

    $this->dispatch('close-modal', id: 'menuOverlayRandom');
    $this->dispatch('event-success-call', [
        'message' => __('message.SUCCESS009.message')
    ]);
}
   public function cancelCall($queueID)
    {
        // $record = QueueStorage::findOrFail($queueID);
         $record = QueueStorage::select('id', 'queue_id', 'team_id', 'locations_id','is_hold','hold_start_datetime', 'hold_by','status','called_datetime','start_datetime','closed_datetime','counter_id','start_acronym','token','arrives_time','cancelled_datetime','dropoff_position')
        ->findOrFail($queueID);

        $record->update([
            'status'  => Queue::STATUS_CANCELLED,
            'cancelled_datetime' => Carbon::now($this->timezone),
        ]);

       
        
        if($this->enable_callDepartment){

                    $nextdepartmentcall = QueueStorage::where('queue_id',$record->queue_id)->where('called','no')->whereNull('called_datetime')->first();
                    $remaindepartmentcall = QueueStorage::where('queue_id',$record->queue_id)->update(['temp_hold'=>1]);


                    if(!empty($nextdepartmentcall)){
                      $nextdepartmentcall->update(['called' =>'yes','temp_hold'=>0]);

                      QueueDepartment::dispatch($nextdepartmentcall);
                    }
        }

        // Calculate drop-off position
        $dropOffPosition = QueueStorage::where('team_id', $this->team_id)
            ->where('locations_id', $this->location)
            ->where('id', '<', $queueID)
            ->where('is_hold', Queue::STATUS_NO)
            ->where('temp_hold', Queue::STATUS_NO)
            ->where('is_missed', Queue::STATUS_NO)
            ->whereNull([
                'start_datetime',
                'called_datetime',
                'cancelled_datetime',
                'closed_datetime'
            ])
            ->whereDate('arrives_time', Carbon::now($this->timezone)->toDateString())
            ->count();

        $record->dropoff_position = $dropOffPosition + 1;
        $record->save();

        // Log activity
        ActivityLog::storeLog(
            $this->team_id,
            $this->userAuth->id,
            (int) $record->queue_id,
            (int) $record->id,
            ActivityLog::CANCEL_CALL,
            $this->location
        );

        // State refreshes
        $this->isRandomTransfer = true;
        $this->modelclose();
        $this->dispatch('close-modal', id: 'menuOverlayRandom');
        $this->tokenHoldRefresh();
        $this->dispatch('event-success-call', ['message' => __('message.SUCCESS0010.message')]);
        $this->viewQueue();
        $this->loadMoreVisitor();

        // Notify displays
        QueueDisplay::dispatch($record);

        $this->dispatch('refresh-page');
    }

    public function tokenHoldRefresh()
    {
        $this->holdCalls = QueueStorage::getHoldCall($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location);
    }

    public function openTransferModal($queueID)
    {

        $this->modelclose();
        $this->isRandomTransfer = true;
        $this->dispatch('close-modal', id: 'menuOverlayRandom');
        $this->viewQueue();
        $this->dispatch('open-modal', id: 'myModalTransfer');
        $this->modelmyModalTransfer = true;
    }

    public function sendSMSModal($queuestoargeID, $queueId = null)
    {
        $this->randomQueueID = '';
        $this->randomQueueID = $queuestoargeID;
        $this->modelclose();
        $this->modelSendsms = true;
        $this->dispatch('close-modal', id: 'menuOverlayRandom');
        $this->dispatch('open-modal', id: 'sendSMSModal');
    }

    public function sendUpdateClient($queuestoargeID)
    {
        $this->randomQueueID = '';
        $this->randomQueueID = $queuestoargeID;

        $this->modelclose();
        $this->modelupdateClient = true;
        // $this->dispatch('close-modal', id: 'menuOverlayRandom');
        $this->dispatch('open-modal', id: 'modelupdateClient');
    }

    public function unholdCallModal($queueID)
    {
        $this->isCallUnHold = 1;
        $this->holdCurrentQueue = QueueStorage::with([
				'category', 
				'subCategory', 
				'childCategory',
			])->where([
				'team_id' => $this->team_id, 
				'id' => $queueID, 
				'locations_id' => $this->location
			])->first();
       
	    $this->activityLogs = ActivityLog::viewLogs($this->team_id, $this->holdCurrentQueue->queue_id, $queueID, $this->location);
        $this->dispatch('close-modal', id: 'menuOverlayRandom');
        $this->modelclose();
        $this->modelCallHistory = true;
        $this->dispatch('open-modal', id: 'unholdCall');
    }

    public function unholdCall($queueID)
    {

        // $this->holdCurrentQueue = QueueStorage::where( [ 'team_id' =>$this->team_id, 'id'=> $queueID, 'locations_id'=>$this->location ] )->first();
    // Retrieve the queue record once
    $record = QueueStorage::where([
        'team_id' => $this->team_id,
        'id' => $queueID,
        'locations_id' => $this->location,
    ])->first();

    // If no record found, exit gracefully
    if (!$record) {
        logger()->warning("Unhold failed: QueueStorage not found (ID: {$queueID})");
        return;
    }

    // Update the hold status
    $record->update([
        'is_hold' => Queue::STATUS_NO,
        'hold_end_datetime' => Carbon::now($this->timezone),
        'hold_by' => null,
    ]);

    // Log activity
    ActivityLog::storeLog(
        $this->team_id,
        $this->userAuth->id,
        (int) $record->queue_id,
        (int) $record->id,
        ActivityLog::UNHOLD_QUEUE,
        $this->location
    );
    }

    public function sendSMS()
    {
        $contact = QueueStorage::where(['id' => $this->randomQueueID])->select('id', 'queue_id', 'phone', 'phone_code')->first();
        if (!empty($contact['phone']) && !empty($this->sms)) {
            ActivityLog::storeLog($this->team_id, $this->userAuth->id,  $contact['queue_id'], $contact['id'], ActivityLog::SEND_SMS, $this->location);
            $phone_code = isset($contact['phone_code']) ? ltrim($contact['phone_code'], '+') : '91';
            $phone = $contact['phone'];
            $contactWithCode = $phone_code . $phone;
            $status = SmsAPI::currentQueueSms($contactWithCode, $this->sms, $this->team_id, 'queue call');

            // SmsReport::create([
            //     'team_id'     => $this->team_id,
            //     'location_id' => $this->location,
            //     'user_id'    =>Auth::id() ?? '',
            //      'message'     => $this->sms,
            //     'contact'     => $contactWithCode ?? '',
            //     'status'      => $status == true ? 'sent' : 'failed', // e.g., 'sent', 'failed', etc.
            //     'channel'     => 'sms',
            //     'type' =>'queue',

            // ]);
            $this->sms = '';
            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS0011.message')]);
        }
        $this->dispatch('close-modal', id: 'sendSMSModal');
    }

    public function historyQueue($storageID, $queueID)
    {
        $this->randomQueueID = $queueID;
        $this->randomQueueStorageID = $storageID;
        $this->viewQueue();
        $this->dispatch('close-modal', id: 'menuOverlayRandom');
        if ($this->siteDetail?->activity_log == SiteDetail::STATUS_YES)
            $this->activityLogs = ActivityLog::viewLogs($this->team_id, $queueID, $storageID, $this->location);
        $this->modelclose();
        $this->modelHistoryQueue = true;
        $this->dispatch('open-modal', id: 'historyQueue');
    }

    public function userdetails($storageID, $queueID)
    {
        $this->randomQueueID = $queueID;
        $this->randomQueueStorageID = $storageID;
        $this->viewQueue();
        $this->dispatch('close-modal', id: 'menuOverlayRandom');
        if ($this->siteDetail?->activity_log == SiteDetail::STATUS_YES)
            $this->activityLogs = ActivityLog::viewLogs($this->team_id, $queueID, $storageID, $this->location);
        $this->modelclose();
        $this->modelUserDetails = true;
        $this->dispatch('open-modal', id: 'userDetail');
    }

    public function viewQueue()
    {
        $this->randomCurrentQueue = QueueStorage::where(['team_id' => $this->team_id, 'id' => $this->randomQueueStorageID, 'queue_id' => $this->randomQueueID, 'locations_id' => $this->location])->first();

        if (!empty($this->randomCurrentQueue->json))
            $this->userDetails = json_decode($this->randomCurrentQueue?->json, true);
    }

    #[On('generate-queue-created')]
    public function generateQueue($queueNumber, $parentSelect)
    {
        try {

            if (Queue::where(['token' => $queueNumber, 'team_id' => $this->team_id, 'locations_id' => $this->location])->whereDate('arrives_time', Carbon::today())->exists()) {
                $this->throwError('ERR008');
                return;
            }

            $queueCreated = Queue::storeQueue([
                'team_id' => $this->team_id,
                'locations_id' => $this->location,
                'token' => $queueNumber,
                'token_with_acronym' => $this->booking_setting == Queue::STATUS_NO ? Queue::LABEL_YES : Queue::LABEL_NO,
                'arrives_time' => Carbon::now($this->timezone),
                'created_by' => $this->userAuth->id,
            ]);

            $queueStorage =  QueueStorage::storeQueue([
                'category_id' => $parentSelect ?? null,
                'team_id' => $this->team_id,
                'locations_id' => $this->location,
                'token' => $queueNumber,
                'token_with_acronym' => $this->booking_setting == Queue::STATUS_NO ? Queue::LABEL_YES : Queue::LABEL_NO,
                'arrives_time' => Carbon::now($this->timezone),
                'datetime' => Carbon::now($this->timezone),
                'created_by' => $this->userAuth->id,
                'queue_id' => $queueCreated->id
            ]);
            $counterID =  Queue::assignCounterToQueue($queueStorage->id, $this->location);

            $queueStorage->counter_id = $counterID;
            $queueStorage->save();
            ActivityLog::storeLog($this->team_id, $this->userAuth->id, $queueCreated->id, $queueStorage->id, ActivityLog::QUEUE_REGISTERED, $this->location);
              if(!empty($queueStorage)){
            QueueCreated::dispatch($queueStorage);
            QueueDisplay::dispatch($queueStorage);
              }

            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS0012.message')]);
        } catch (\Throwable $ex) {
            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

    #[On('esitmate-note-created')]
    public function submitEstimateNote()
    {
        $this->currentVisitorRecord?->update(['esitmate_note' => $this->notice_sms]);
        $this->dispatch('close-modal', id: 'estimateNote');
        $this->dispatch('event-success-call', ['message' =>  __('message.SUCCESS0013.message')]);
    }

    public function sendNote()
    {
        $this->notice_sms = $this->currentVisitorRecord?->esitmate_note;
        $this->modelclose();
        $this->modelEstimateNotes = true;
        $this->dispatch('open-modal', id: 'estimateNote');
    }
    public function modelEditCurrentVisitor()
    {
        $this->modelclose();
        $getQueue = $this->currentVisitorRecord;
        $this->selectedCategoryId = $getQueue->category_id;
        $this->secondChildId = $getQueue->sub_category_id ?? '';
        $this->thirdChildId = $getQueue->child_category_id ?? '';

        $this->firstCategories = Category::getFirstCategoryN($this->team_id, $this->location);
        $this->updateCategories('secondChildId', $this->selectedCategoryId);
        $this->updateCategories('thirdChildId', $this->secondChildId);
           $this->resetDynamic();

        $this->modelslideCurrentVisitor = true;


        $this->dispatch('open-modal', id: 'slideCurrentVisitor');
    }
    public function modelHoldQueue()
    {
        $this->modelclose();
        $this->modelholdsms = true;

        $this->dispatch('open-modal', id: 'holdsms');
    }
    public function modelOpenSuspension()
    {
        $this->modelclose();
        $this->modelSuspension = true;

        $this->dispatch('open-modal', id: 'holdSuspension');
    }

    #[On('reset-current-queue')]
    public function resetCurrentQueue()
    {
        try {

            if (!empty($this->currentVisitorRecord)) {

                $this->currentVisitorRecord?->update(['status' => Queue::STATUS_RESET, 'reset_call' => Carbon::now($this->timezone), 'reset_call_by' => $this->userAuth->id, 'datetime' => Carbon::now($this->timezone)]);

                if (!empty($this->currentVisitorId))
                    QueueProgress::dispatch($this->currentVisitorRecord);
                $this->callPendingEvent($this->currentVisitorRecord->id);

                $this->emptyCurrentVisitor();
                

                $this->refreshQueues();
            } else {
                $this->throwError('ERR007');
                return;
            }

            $this->dispatch('event-success-call', ['message' => __('message.ERR009.message')]);
        } catch (\Throwable $ex) {

            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

    public function historyTakeCall($queueID, $nextStorageId = null)
    {
        $this->randomQueueID = $queueID;
        $this->randomQueueStorageID = $nextStorageId;
        $this->viewQueue();
        if ($this->siteDetail?->activity_log == SiteDetail::STATUS_YES)
            $this->activityLogs = ActivityLog::viewLogs($this->team_id, $queueID, $nextStorageId, $this->location);
        $this->modelclose();
        $this->modelhistoryTakeCall = true;
        $this->dispatch('open-modal', id: 'historyTakeCall');
    }

    #[On('revert-served-queue')]
    public function revertServedCall($queueID, $storageID)
    {
        try {
             if($this->enable_callDepartment){
                if(QueueStorage::where(['queue_id' => $queueID,'temp_hold' =>0])->exists()){
                   return $this->dispatch('event-error-call', ['message' => __('message.ERR004.message')]);
                }
            }
            $queue = Queue::findOrFail($queueID);
            $queue->status = Queue::STATUS_PENDING;
            $queue->save();

            $queueStorage =  QueueStorage::where(['queue_id' => $queueID, 'id' => $storageID])->first();
            $queueStorage->is_missed = Queue::STATUS_NO;
            $queueStorage->status = Queue::STATUS_PENDING;
            $queueStorage->closed_by =  null;
            $queueStorage->called_datetime =  null;
            $queueStorage->start_datetime =  null;
            $queueStorage->closed_datetime =  null;
            $queueStorage->served_by =  null;
             if($this->enable_callDepartment){
                  $queueStorage->temp_hold = 0;
            }
           
            $queueStorage->save();

            $this->tokenMissedRefresh();
            if(!empty($queueStorage)){
            QueueCreated::dispatch($queueStorage);
            QueueProgress::dispatch($queueStorage);
            QueueDisplay::dispatch($queueStorage);
            }
            $this->dispatch('event-success-call', ['message' => __('message.SUCCESS0014.message')]);
        } catch (\Throwable $ex) {
            $this->dispatch('event-error-call', ['message' => $ex->getMessage()]);
        }
    }

    public function tokenMissedRefresh()
    {
        $this->tokenServed = Queue::totalTokenServed($this->conditionTeam, $this->userAuth->id, $this->location);
        // $this->missedCalls = Queue::getMissedCallId($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location);

        if($this->enable_callDepartment){

            $this->missedCalls = Queue::getMissedCallId($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location,true);
        }else{
            $this->missedCalls = Queue::getMissedCallId($this->conditionTeam, $this->siteDetail?->show_department_missed_queue, $this->location,false);

        }
    }

    public function showActivityLog()
    {
        $this->activityLogs = ActivityLog::viewLogs($this->team_id, $this->currentVisitorId, $this->currentStorageID, $this->location);
        $this->modelclose();
        $this->modelsActivityLog = true;
        $this->dispatch('open-modal', id: 'showActivityLog');
    }

    #[On('rating-service')]
    public function ratingService($rating)
    {
        $this->currentVisitorRecord?->update(['rating' => Queue::getRatingEmoji($rating)]);
        $this->closeCall();
    }

    public function currentVisitorEdit()
    {
        $formattedFields = [];
        if ($this->siteDetail?->queue_form_display == SiteDetail::STATUS_YES){

            // $this->validate();
        }


        foreach ($this->dynamicProperties as $key => $value) {
            $trimmedKey = trim($key);
            $fieldName = preg_replace('/_\d+/', '', $trimmedKey);
            $fieldName = strtolower($fieldName); // normalize to lowercase
            $formattedFields[$fieldName] = $value;
        }

        $this->name = $formattedFields['name'] ?? null;
        $this->phone = $formattedFields['phone'] ?? null;
        $this->email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['Email']) ? $formattedFields['Email'] : null);

        // if (!empty($this->staticVisitorDetails))
        // $formattedFields = array_merge($formattedFields, $this->staticVisitorDetails);

        $jsonDynamicData = json_encode($formattedFields);

        $storeData = [
            'name' => $this->name,
            'phone' => $this->phone,
            'json' => $jsonDynamicData,
            'category_id' => $this->selectedCategoryId ?: null,
            'sub_category_id' => $this->secondChildId ?: null,
            'child_category_id' => $this->thirdChildId ?: null,
            'locations_id' => $this->location,

        ];
        $this->currentVisitorRecord?->update($storeData);
        $this->callPendingEvent($this->currentVisitorRecord?->id);

        $this->dispatch('close-modal', id: 'slideCurrentVisitor');

        return $this->dispatch('event-success-call', ['message' => __('message.SUCCESS0015.message')]);
    }

    public function updatedSelectedCategoryId($value)
    {

        $this->reset(['secondChildId', 'thirdChildId']);
        $this->secondCategories = [];
        $this->thirdCategories = [''];

        $this->updateCategories('secondChildId', $value);
    }

    public function updatedSecondChildId($value)
    {
        $this->reset(['thirdChildId']);
        $this->thirdCategories = [''];

        $this->updateCategories('thirdChildId', $value);
    }

    private function updateCategories($childProperty, $parentId)
    {
        $categories = [];

        if ($parentId !== null) {
            $categories = Category::getPluckNames($parentId, $this->location)?->toArray();
        }

        $propertyMap = [
            'secondChildId' => 'secondCategories',
            'thirdChildId' => 'thirdCategories'
        ];

        $this->{$propertyMap[$childProperty]}
            = $categories;

        if (count($categories) === 1) {
            $this->$childProperty = array_key_first($categories);

            // Determine the next level property
            $nextChildPropertyMap = [
                'secondChildId' => 'thirdChildId'
            ];

            // Check if there is a next level property
            if (isset($nextChildPropertyMap[$childProperty])) {
                $nextChildProperty = $nextChildPropertyMap[$childProperty];
                $this->updateCategories($nextChildProperty, $this->$childProperty);
            }
        } else {
            // Reset the next level category if there are no options or multiple options
            $resetChildPropertyMap = [
                'secondChildId' => 'thirdChildId'
            ];

            if (isset($resetChildPropertyMap[$childProperty])) {
                $nextChildProperty = $resetChildPropertyMap[$childProperty];
                $this->$nextChildProperty = null;
                $this->{$propertyMap[$nextChildProperty]}
                    = [];
            }
        }
    }

    public function loadMoreVisitor()
    {
        // $this->page += Queue::INITIAL_VISITOR_SHOW_COUNT;
        $this->refreshQueues();
        // $this->queues =  Queue::getPendingQueues($this->conditionTeam, ($this->siteDetail?->fixed_visitor_list_queue == SiteDetail::STATUS_YES ? true : false), $this->location, $this->page, null, $this->team_id, $this->queueType);
    }

    public function modelclose()
    {
        $this->modelslideCurrentVisitor = false;
        $this->modelsActivityLog = false;
        $this->modelCallHistory = false;
        $this->modelEstimateNotes = false;
        $this->modelHistoryQueue = false;
        $this->modelUserDetails = false;
        $this->modelhistoryTakeCall = false;
        $this->modelSendsms = false;
        $this->modelholdsms = false;
        $this->modelmyModalTransfer = false;
        $this->modelmenuOverlayRandom = false;
        $this->modelSuspension = false;
    }

    public function sendNotification($data, $type, $logData = null)
    {
        $data['locations_id'] = $this->location;

        if (!empty($logData)) {
            $logData['channel'] = 'email';
            // $logData['status'] = MessageDetail::SENT_STATUS;
            // MessageDetail::storeLog($logData);
        }
        if ($type == 'virtual meeting') {
            if (isset($data['to_mail']) && $data['to_mail'] != '') {
                SmtpDetails::sendMail($data, $type, '',  $this->team_id,$logData);
            }
        } else {
            if (isset($data['to_mail']) && $data['to_mail'] != '') {
                SmtpDetails::sendMail($data, $type, '',  $this->team_id,$logData);
            }
            if (!empty($data['phone']) && $data['phone_code']) {
                $logData['channel'] = 'sms';
                $logData['status'] = MessageDetail::SENT_STATUS;
                SmsAPI::sendSms($this->team_id, $data, $type, $type, $logData);
            }
        }
    }


    /**
     * Throw error, save to DB and dispatch event.
     */
    public function throwError(string $errorCode)
    {

        // Fetch localized messages from lang files
        $message     = __("message.{$errorCode}.message");
        $description = __("message.{$errorCode}.description");
        $resolution  = __("message.{$errorCode}.resolution");

        // Save error log to DB
        $errorLog = ErrorLog::create([
            'team_id'     => $this->team_id ?? tenant('id'),
            'location_id' => Session::get('selectedLocation'),
            'code'        => $errorCode,
            'message'     => $message,
            'description' => $description,
            'resolution'  => $resolution,
            'url'         =>  $this->currentUrl ?? url()->current(),  // Current full URL
        ]);

        // Dispatch Livewire event (adjust according to your component context)
        return  $this->dispatch('event-error', [
            'code'        => $errorLog->code,
            'message'     => $errorLog->message,
            'description' => $errorLog->description,
            'resolution'  => $errorLog->resolution,
            'label_error' => __('message.error'),
            'label_description' => __('message.description'),
            'label_resolution' => __('message.resolution'),
        ]);
    }

    #[On('leave-meeting')]
    public function showVideoCall()
    {
        $this->showVirtualMeeting = false;
    }

    public function joinCall()
    {
        $this->showVirtualMeeting = true;
        $this->getroom = 'room_' . base64_encode($this->currentVisitorRecord->queue_id);
        $this->getqueueId = base64_encode($this->currentVisitorRecord->queue_id);

          if ($this->showStartBtn  == 'SHOW_START_CLOSE') {
            $this->startCall();
          }
    }

    public function videoToken(TwilioVideoService $twilio)
    {
        $this->identity = auth()->check()
            ? 'staff_' . uniqid()
            : 'guest_' . uniqid();

        $this->token = $twilio->generateToken($this->identity, $this->getroom);

        $this->dispatch('join-call', token: $this->token, room: $this->getroom);
    }

    public function checkWaitingTime()
    {
        if (!$this->siteDetail->enable_waiting_popup) return;
$minutes='';
        // Fetch all active visitors (replace with your DB query)
        $activeVisitors = $this->queues ?? [];
$queue_check =true;
        if(!empty( $activeVisitors)){
        foreach ($activeVisitors as $visitor) {
                    // $minutes = now()->diffInMinutes($visitor->datetime);

                    $seconds = abs(
                                    now($this->timezone)->diffInSeconds(
                                    Carbon::parse($visitor->datetime, $this->timezone)
                                    )
                                    );
                                    $minutes = floor($seconds / 60);

                    // Trigger alert only if waiting >= popup threshold

                    if ((int)$minutes >= (int)$this->siteDetail->popup_waiting_time && $queue_check && $visitor->alert_waiting_show == 0) {
                        $tokenNumber = ($visitor->start_acronym ?? '') . $visitor->token;
                        // dd((int)$minutes,(int)$this->siteDetail->popup_waiting_time);
                        // Dispatch browser event for alert
                        // $this->dispatch('waiting-alert', [
                        //     'message' => "Visitor has been waiting {$minutes} minutes!",
                        // ]);
$visitor->update([
'alert_waiting_show' =>1
]);
    $this->dispatch('waiting-notification', [
'token_notify' => "Visitor has been waiting {$minutes} minutes! and token is {$tokenNumber}"
                        ]);
                        $queue_check =false;

                    }
                }
            }
    }


    #[On('show-desktop-notification')]
    public function showdesktopnotification($event)
    {
        $queue = $event['queue'] ?? null;

        if (!$queue) {
            return;
        }

        $userId = Auth::id();
        $teamId = $queue['team_id'] ?? null;
        $location = $queue['locations_id'] ?? null;

        // Extract values
    $assignStaffId      = $queue['assign_staff_id'] ?? null;
    $transferId         = $queue['transfer_id'] ?? null;
    $categoryId         = $queue['category_id'] ?? null;
    $subCategoryId      = $queue['sub_category_id'] ?? null;
    $childCategoryId    = $queue['child_category_id'] ?? null;
    $forwardCounterId   = $queue['forward_counter_id'] ?? null;

        // Check 1: Assigned staff
        if (!empty($assignStaffId) && $assignStaffId == $userId) {
            $this->notifyUser($queue);
            return;
        }

          // 1️⃣ Check transfer_id first
    if (!empty($transferId) && $transferId == $userId) {
       $typeValue = $transferId;
        if (!empty($typeValue)) {
            $isAssigned = Queue::checkUserAssigned($teamId, $location, 'category', $typeValue);

            if ($isAssigned) {
                $this->notifyUser($queue);
            }
        }
        return;
    }


    // 3️⃣ Check forward_counter_id
    if (!empty($forwardCounterId)) {
        $isAssigned = Queue::checkUserAssigned($teamId, $location, 'counter', $forwardCounterId);
        if ($isAssigned) {
            $this->notifyUser($queue);
            return;
        }
    }

        // Check 2: Category assignment (fallback chain)
        $typeValue = $childCategoryId ?: ($subCategoryId ?: $categoryId);
        if (!empty($typeValue) && $teamId != 22) {
            $isAssigned = Queue::checkUserAssigned($teamId, $location, 'category', $typeValue);

            if ($isAssigned) {
                $this->notifyUser($queue);
            }
        }
    }

    protected function notifyUser($queue)
    {
        $acy = $queue['start_acronym'] ?? '';
        $token = $queue['token'] ?? '';

        $message = "The Generated Token Number is {$acy}{$token}";

        // Dispatch to frontend for desktop notification
        $this->dispatch('desktop-notify', token_notify: $message);
        $this->dispatch('audio-sound');
    }

    public function hidelistpopup()
    {
        $this->modelclose();
    }

    public function render()
    {
        return view('livewire.queue-calls');
    }
}
