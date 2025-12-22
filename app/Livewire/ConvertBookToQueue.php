<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Booking,
    Queue,
    SiteDetail,
    Category,
    QueueStorage,
    AccountSetting,
    FormField,
    Location,
    SmsAPI,
    SmtpDetails,
    Customer,
    CustomerActivityLog,
    MessageDetail,
    User,
    CustomSlot,
    TenantLimit,
    Counter,
    Level
};
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use App\Events\QueueCreated;
use App\Events\QueueNotification;
use App\Jobs\SendQueueNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Services\SalesforceService;
use App\Models\SalesforceSetting;
use App\Models\SalesforceConnection;

#[Layout('components.layouts.custom-display-layout')]
class ConvertBookToQueue extends Component
{

    #[Title('Convert to Queue')]

    public $booking_refID;
    public $booking_placeholder = "RefId / Email";
    public $booking;
    public $step = 1;
    public $teamId;
    public $location;
    public $token_start;
    public $siteDetails;
    public $acronym;
    public $showTicketText;
    public $showTicketText_2;
    public $queue_form = false;
    public $dynamicForm = [];
    public $dynamicProperties = [];
    public $allCategories = [];

    public $categoryName = '';
    public $secondCategoryName = '';
    public $thirdCategoryName = '';
    public $selectedCategoryId = '';
    public $locationName = '';
    public $secondChildId = '';
    public $thirdChildId = '';
    public $countCatID = 0;
    public $fieldCatName = '';
    public $counterID = 0;
    public $accountSetting;
    public $fontSize = 'text-3xl';
    public $fontFamily = 'font-sans';
    public $borderWidth = 'border-4';
    public $enablePriority = false;
    public $unavailableMessage;
    public $acronym_level;

    public function render()
    {
        return view('livewire.convert-book-to-queue');
    }

    #[Computed]
    public function siteDetails()
    {
        $cacheKey = 'site_details' . $this->teamId . '_' . $this->location;
        return cache()->remember(
            $cacheKey,
            now()->addMinutes(10),
            function () {
                return SiteDetail::getMyDetails($this->teamId,$this->location);
            }
        );
    }

    // #[Computed]
    // public function domainSlug()
    // {
    //     return Team::getSlug();
    // }

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        if (empty($this->teamId) || empty($this->location)) {
            abort(403);
        }
         $this->accountSetting = AccountSetting::where('team_id', $this->teamId)
         ->where('location_id', $this->location)
         ->where('slot_type', AccountSetting::BOOKING_SLOT)
         ->first();

        if (isset( $this->accountSetting) && !empty( $this->accountSetting->con_app_input_placeholder)) {
            $this->booking_placeholder =  $this->accountSetting->con_app_input_placeholder;
        }
        if (isset( $this->accountSetting) && !empty( $this->accountSetting->show_con_app_form)) {
            $this->queue_form =  $this->accountSetting->show_con_app_form == 1 ? true : false;
            $this->step = 1;
        }
        $this->siteDetails = SiteDetail::getMyDetails($this->teamId,$this->location);

         if (!empty($this->siteDetails)) {
            $this->fontSize = $this->siteDetails->category_text_font_size ?? $this->fontSize;
            $this->borderWidth = $this->siteDetails->category_border_size ?? $this->borderWidth;
            $this->fontFamily = $this->siteDetails->ticket_font_family ?? $this->fontFamily;
             $this->enablePriority = $this->siteDetails->use_staff_priority ?? false;
         }

           $levels = Level::where('team_id',$this->teamId)
            ->where('location_id',$this->location)
            ->whereIn('level', [1, 2, 3])
            ->get()
            ->keyBy('level');

        $this->acronym_level = $levels[1]->acronyms_show_level ?? 1;
    }
    public function checkStaffAvailability($staffId)
    {
        $userTimezone = $this->siteDetails->select_timezone ?? 'UTC';
        // $userTimezone = 'UTC';
        $currentDate = Carbon::now($userTimezone)->format('Y-m-d');
        $currentDay = Carbon::now($userTimezone)->format('l');
        $currentTime = Carbon::now($userTimezone)->format('h:i A');

        return $this->isWithinTimeSlot(AccountSetting::STAFF_SLOT, $currentDate, $currentDay, $currentTime, null, $staffId);

    }

     private function isWithinTimeSlot($slotType, $currentDate, $currentDay, $currentTime, $categoryId=null, $userId=null)
    {
        // Check if the waitlist limit allows further processing
        if (!$this->checkLimit($currentDate, $currentDay, $currentTime)) {
            return false;
        }

        // Query for custom slots
        $query = CustomSlot::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('slots_type', $slotType)
            ->where('selected_date', $currentDate);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $slotData = $query->select('business_hours')->first();

        // If no custom slots found, fallback to AccountSetting
        if (!$slotData) {
            if($slotType !=AccountSetting::TICKET_SLOT){
            $query = AccountSetting::where('team_id', $this->teamId)
                ->where('location_id', $this->location)
                ->where('slot_type', $slotType);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            if ($userId) {
                $query->where('user_id', $userId);
            }

            $slotData = $query->select('business_hours')->first();
        }else{
             $slotData = $this->accountSetting;
        }
        }

        // If still no slot data, return false
        if (!$slotData) {

            return false;
        }

        // Check if current time is within business hours
        return $this->checkBusinessHours(json_decode($slotData->business_hours), $currentDay, $currentTime);
    }

    private function checkBusinessHours($businessHours, $currentDay, $currentTime)
    {
        foreach ($businessHours as $day) {
            if ($day->day === $currentDay) {
                if ($day->is_closed === 'closed') {
                    $this->unavailableMessage = "The service is closed on {$currentDay}.";
                    return false;
                }
                $availableSlots = [];
                if ($this->isTimeInRange($currentTime, $day->start_time, $day->end_time)) return true;

                $availableSlots[] = "{$day->start_time} to {$day->end_time}";

                foreach ($day->day_interval ?? [] as $interval) {
                    if ($this->isTimeInRange($currentTime, $interval->start_time, $interval->end_time)) return true;

                    $availableSlots[] = "{$interval->start_time} to {$interval->end_time}";
                }

                 // If current time doesn't match any slot, prepare message
            $slotsFormatted = implode(', ', $availableSlots);
            $this->unavailableMessage = "Queueing is only available on {$currentDay} between: {$slotsFormatted}. Please try again during these hours.";

                return false;
            }
        }
        $this->unavailableMessage = "No business hours found for {$currentDay}.";
        return false;
    }

    private function isTimeInRange($currentTime, $startTime, $endTime)
    {
        return strtotime($currentTime) >= strtotime($startTime) && strtotime($currentTime) <= strtotime($endTime);
    }

    private function checkLimit($currentDate, $currentDay, $currentTime)
    {
        $checkRecord = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('slot_type', AccountSetting::TICKET_SLOT)
            ->select('is_waitlist_limit', 'waitlist_limit')
            ->first();



        // If waitlist limit is not enabled, allow the operation
        if (!$checkRecord || $checkRecord->is_waitlist_limit == 0) {
            return true;
        }

        // Query to count queued customers
        $countQueue = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->where('is_hold', Queue::STATUS_NO)
            ->where('temp_hold', Queue::STATUS_NO)
            ->where('is_missed', Queue::STATUS_NO)
            ->whereNull([
                'start_datetime',
                'called_datetime',
                'cancelled_datetime',
                'closed_datetime',
            ])
            ->whereDate('arrives_time', Carbon::today());

        // Apply category filters if set
        if ($this->selectedCategoryId) {
            $countQueue->where('category_id', $this->selectedCategoryId);
        }
        if ($this->secondChildId) {
            $countQueue->where('sub_category_id', $this->secondChildId);
        }
        if ($this->thirdChildId) {
            $countQueue->where('child_category_id', $this->thirdChildId);
        }

        // Get total count
        $queueCount = $countQueue->count();

        // If the count reaches or exceeds the waitlist limit, return false
        return (int)$queueCount < (int)$checkRecord->waitlist_limit;
    }



    public function resetDynamic()
    {
        $this->dynamicForm = FormField::getFields($this->teamId, true,$this->location);
        $this->allCategories = [
            'thirdChildId' => $this->thirdChildId,
            'secondChildId' => $this->secondChildId,
            'selectedCategoryId' => $this->selectedCategoryId,
        ];
        foreach ($this->dynamicForm as $field) {
            $propertyName = $field['title'] . '_' . $field['id'];
            $this->dynamicProperties[$propertyName] = '';
        }
    }

    public function convertToQueueForm()
    {
        if (empty($this->booking_refID)) {
            return $this->dispatch('swal:exist-booking', [
                'title' => 'Id is required',
                'icon' => 'error',
            ]);
        }

        $this->booking = Booking::where('refID', $this->booking_refID)
            ->whereDate('booking_date', date('Y-m-d'))
            ->where('is_convert', Booking::STATUS_NO)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->first();

        if (empty($this->booking)) {
            return $this->dispatch('swal:exist-booking', [
                'title' => 'Booking ID is not found',
                'icon' => 'error',
            ]);
        }

        $this->selectedCategoryId = $this->booking->category_id ?? null;
        $this->secondChildId = $this->booking->sub_category_id ?? null;
        $this->thirdChildId = $this->booking->child_category_id ?? null;

        $this->resetDynamic();
        $this->step = 2;
    }

    public function convertToQueue()
    {

        try {
            if (empty($this->booking)) {
                if (empty($this->booking_refID)) {
                    return $this->dispatch('swal:exist-booking', [
                        'title' => 'Id is required',
                        'icon' => 'error',
                    ]);
                }

                $assigned_staff_id =null;

                $this->booking = Booking::where('refID', $this->booking_refID)
                    ->whereDate('booking_date', date('Y-m-d'))
                    ->where('is_convert', Booking::STATUS_NO)
                    ->where('status', '!=', Booking::STATUS_CANCELLED)
                    ->first();

    // $limitCheck = TenantLimit::checkTicketLimit($this->booking->team_id);

    //             if($limitCheck)
    //         {
    //             Log::error('Unable to generate ticket. The ticket creation limit exceeded the daily limit');


    //             $this->dispatch('swal:limit-exceed', [
    //           'title' => 'Oops...',
    //           'text' => 'You have reached your daily ticket limit',
    //           'icon' => 'error'
    //         ]);
    //         return $limitCheck;

    //         }
                if (empty($this->booking)) {
                    return $this->dispatch('swal:exist-booking', [
                        'title' => 'Booking ID is not found',
                        'icon' => 'error',
                    ]);
                }
                 else{
                     $this->selectedCategoryId = $this->booking->category_id ?? null;
                     $this->secondChildId = $this->booking->sub_category_id ?? null;
                     $this->thirdChildId = $this->booking->child_category_id ?? null;
                }
            }
            $this->location = $this->booking->location_id;

            if (!empty($this->location)) {
                $this->locationName = Location::locationName($this->location);
            }
            $bookingDate = Carbon::parse($this->booking->booking_date)->startOfDay();
            $currentDate = Carbon::now()->startOfDay();
            $readableDate = $bookingDate->format('F j, Y'); // Example: July 22, 2024

            if ($bookingDate->lt($currentDate)) {
                return $this->dispatch('swal:exist-booking', [
                    'title' => 'Booking date is in the past on ' . $readableDate,
                    'icon' => 'warning',
                ]);
            } elseif ($bookingDate->gt($currentDate)) {
                // return $this->dispatch('swal:exist-booking', [
                //     'title' => 'Booking is for a future date on ' . $readableDate,
                //     'icon' => 'info',
                // ]);
            }

            $isAsQueue = QueueStorage::isBookExist($this->booking->id);

            if ($isAsQueue) {
                return $this->dispatch('swal:exist-booking', [
                    'title' => 'Not found! Already converted',
                    'icon' => 'error',
                ]);
            }

             $checkTicketLimit = SiteDetail::checkTicketLimit($this->booking->team_id, $this->booking->location_id, $this->siteDetails);
            if($checkTicketLimit)
            {
                return (['status' => 'error', 'message' => 'Unable to generate ticket. The ticket creation limit exceeded the daily limit that is ' . $this->siteDetails->ticket_limit]);
            }

            DB::beginTransaction();
            // $this->acronym = SiteDetail::DEFAULT_APPOINTMENT_A;

            // if (!empty($this->selectedCategoryId)) {
            //     $this->acronym = Category::viewAcronym($this->selectedCategoryId);
            // }else{
            //     $this->acronym = SiteDetail::DEFAULT_APPOINTMENT_A;
            // }

             if((int)$this->acronym_level == 1 && !empty($this->selectedCategoryId)){
                $this->acronym = Category::viewAcronym($this->selectedCategoryId);
            }elseif((int)$this->acronym_level == 2 && !empty($this->secondChildId)){
                $this->acronym = Category::viewAcronym($this->secondChildId);
            }elseif((int)$this->acronym_level == 3 && !empty($this->thirdChildId)){
                $this->acronym = Category::viewAcronym($this->thirdChildId);
            }else {
                $this->acronym = SiteDetail::DEFAULT_APPOINTMENT_A;
            }


             $lastcategory = $this->selectedCategoryId;
              if(!empty($this->thirdChildId)){
                    $lastcategory = $this->thirdChildId;
                }elseif(!empty($this->secondChildId)){
                  $lastcategory = $this->secondChildId;
                }
            // $lastToken = QueueStorage::getLastToken($this->teamId, $this->acronym, $this->location);

             if($this->siteDetails?->count_by_service){
                    
                    $lastToken = Queue::getLastToken($this->teamId, null, $this->location);
                }else{

                    $lastToken = Queue::getLastToken($this->teamId, $this->acronym, $this->location,$lastcategory);
                }
            
            $token_digit = $this->siteDetails?->token_digit ?? 4;
            $isExistToken = true;

            while ($isExistToken) {
                $newToken = QueueStorage::newGeneratedToken($lastToken, $this->siteDetails?->token_start, $token_digit);
                if (strlen($newToken) > $token_digit) {
                    $this->dispatch('swal:ticket-generate', [
                        'title' => 'Oops...',
                        'text' => 'Unable to create more tickets',
                        'icon' => 'error'
                    ]);
                    return;
                }

                $isExistToken = Queue::checkToken($this->teamId, $this->acronym, $newToken, $this->location);

                Log::emergency('Checking if token exists: ' . $newToken . ' - Exists: ' . ($isExistToken ? 'Yes' : 'No'));

                if ($isExistToken) {
                    $lastToken = $newToken;
                    Log::emergency('Token already exists, generating a new token based on last token: ' . $lastToken);
                } else {
                    $this->token_start = $newToken;
                    $isExistToken = false;
                }
            }
            $todayDateTime = Carbon::now();

            // Determine assigned staff based on setting
            $assigned_staff_id = null;
            if (($this->siteDetails->assigned_staff_id ?? 0) == 1 && is_numeric($this->booking->staff_id)) {
                // Use staff from booking when the setting is enabled
                $assigned_staff_id = (int)$this->booking->staff_id;
            } else {
                $assigned_staff_id = is_numeric($this->booking->staff_id) ? (int)$this->booking->staff_id : null;
            }

            $nextPrioritySort = $this->getNextPrioritySort($this->booking->category_id);
            if ($this->enablePriority && empty($assigned_staff_id)) {
                $assigned_staff_id = User::getNextAgent($this->booking->team_id, $this->booking->location_id);
                if (empty($assigned_staff_id)) {
                    $this->dispatch('swal:ticket-generate', [
                        'title' => 'Oops...',
                        'text' => 'Staff is not Available',
                        'icon' => 'error'
                    ]);
                    return;
                }
            }

              $lastcategory = $this->selectedCategoryId;

                if(!empty($this->thirdChildId)){
                    $lastcategory = $this->thirdChildId;
                }elseif(!empty($this->secondChildId)){
                  $lastcategory = $this->secondChildId;
                }

                $is_virtual_meeting =0;
                if($this->booking->json){
                 $decodedJson = json_decode($this->booking->json, true);
                // if($this->enableVirtual){
                if (isset($decodedJson['type']) && $decodedJson['type'] === 'Virtual') {
                $is_virtual_meeting =1;
                }
                }
            $storeData = [
                'name' => $this->booking->name,
                'phone' => $this->booking->phone ?? '',
                'phone_code' => $this->booking->phone_code ?? '91',
                'category_id' => $this->booking->category_id ?? null,
                'sub_category_id' => $this->booking->sub_category_id ?? null,
                'child_category_id' => $this->booking->child_category_id ?? null,
                'team_id' => $this->teamId,
                'token' => $this->token_start,
                'token_with_acronym' => Queue::LABEL_NO,
                'json' => $this->booking->json,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $this->acronym,
                'locations_id' => $this->booking->location_id,
                'booking_id' => $this->booking->id,
                'priority_sort' => $nextPrioritySort ?? 0,
                // 'created_by' =>  $is_numeric($this->booking->created_by) ? (int)$this->booking->created_by : null,
                'served_by' =>  $assigned_staff_id,
                'assign_staff_id' =>  $assigned_staff_id,
                'campaign_id' => is_numeric($this->booking->campaign_id) ? (int)$this->booking->campaign_id : null,
                 'full_phone_number' => (!empty($this->booking->phone_code) && !empty($this->booking->phone))
                                        ? $this->booking->phone_code . $this->booking->phone
                                        : null,
                'is_virtual_meeting' =>$is_virtual_meeting,
            ];

          

            Log::emergency('update convert Booking id' . $this->booking->id . ' - convert date: ' . $todayDateTime);

            $queueCreated = Queue::storeQueue([
                'team_id' => $this->teamId,
                'token' => $this->token_start,
                'token_with_acronym' => Queue::LABEL_NO,
                'locations_id' => $this->booking->location_id,
                'arrives_time' => $todayDateTime,
                'last_category' => $lastcategory,
            ]);


               if (isset($decodedJson['type']) && $decodedJson['type'] === 'Virtual')
            {
                $room = 'room_' . base64_encode($queueCreated->id);
                $queueId = base64_encode($queueCreated->id);

                $storeData['meeting_link'] = url("meeting/{$room}/{$queueId}");
            }
            else
            {
                $storeData['meeting_link'] = null;
            }




            $queueStorage =  QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));

            $this->booking->is_convert = Booking::STATUS_YES;
            $this->booking->status = Booking::STATUS_COMPLETED;
            $this->booking->convert_datetime = $todayDateTime;
            $this->booking->save();

            // Create Salesforce lead when connection/settings are present
            try {
                $salesforcessettings = SalesforceSetting::where('team_id',  $this->teamId)
                    ->where('location_id', $this->booking->location_id)
                    ->first();

                $clientId = $salesforcessettings->client_id ?? null;
                $clientSecret = $salesforcessettings->client_secret ?? null;
                $tokenUrl = 'https://login.salesforce.com/services/oauth2/token';

                $refreshToken = SalesforceConnection::where('team_id', $this->teamId)
                    ->where('location_id', $this->booking->location_id)
                    ->where('status', 1)
                    ->value('salesforce_refresh_token');

                if (!empty($clientId) && !empty($clientSecret) && !empty($refreshToken)) {
                    $datetimeUtc = new \DateTime($queueStorage->arrives_time);
                    $datetimeUtc->setTimezone(new \DateTimeZone('UTC'));
                    $Qwaiting_Sync_Date__c = $datetimeUtc->format('Y-m-d\TH:i:s\Z');

                    // Determine Salesforce OwnerId (AssignId)
                    $assignUserSfId = null;
                    if (($this->siteDetails->assigned_staff_id ?? 0) == 1 && is_numeric($this->booking->staff_id)) {
                        $assignUserSfId = User::where('id', (int)$this->booking->staff_id)->value('saleforce_user_id');
                    } elseif (!empty($assigned_staff_id)) {
                        $assignUserSfId = User::where('id', $assigned_staff_id)->value('saleforce_user_id');
                    }

                    // Map custom form fields from booking->json (associative by field name)
                    $customFields = json_decode($this->booking->json, true) ?: [];
                    $customFields = array_change_key_case($customFields, CASE_LOWER);

                    $sfAge        = $customFields['age'] ?? '';
                    $sfOccupation = $customFields['occupation'] ?? '';
                    $sfAddress    = $customFields['residential address'] ?? '';
                    $sfMarital    = $customFields['marital status'] ?? '';
                    $sfPrevious   = $customFields['previous contact'] ?? '';
                    $sfPurpose    = $customFields['purpose of visit'] ?? '';
                    $sfUnit       = $customFields['unit'] ?? '';
                    $sfNotes      = $customFields['notes'] ?? '';
                    $sfMobile2    = $customFields['number'] ?? '';

                    $salesForceData = [
                        'refresh_token' => $refreshToken,
                        'FirstName' => $queueStorage->name ?? 'Guest',
                        'Phone' => $queueStorage->phone ?? '',
                        'Email' => $this->booking->email ?? '',
                        'Qwaiting_Sync_Date__c' => $Qwaiting_Sync_Date__c,
                        'Service_Name__c' => $this->categoryName ?? '',
                        'Token' => $queueStorage->token ?? '',
                        'Page' => 'BookQueue',
                        'Created' => $queueStorage->created_at ?? now(),
                        'queue_storage_id' => $queueStorage->id,
                        'Company' => '',
                        'Mobile' => $sfMobile2,
                        'Age' => $sfAge,
                        'Occupation' => $sfOccupation,
                        'Address' => $sfAddress ?: ($this->locationName ?? ''),
                        'Marital' => $sfMarital,
                        'Previous' => $sfPrevious,
                        'Purpose' => $sfPurpose,
                        'Unit' => $sfUnit,
                        'Note' => $sfNotes,
                        'AssignId' => $assignUserSfId ?: '005Hu00000SBZ8bIAH',
                    ];

                    $sfService = new SalesforceService($clientId, $clientSecret, $tokenUrl);
                    $leadResponse = $sfService->createLead($salesForceData);
                    $queueStorage->salesforce_lead = json_encode($leadResponse);
                    $queueStorage->save();
                }
            } catch (\Throwable $e) {
                Log::error('ConvertBookToQueue: Salesforce lead creation failed - ' . $e->getMessage());
            }

              QueueNotification::dispatch($queueStorage);
            QueueCreated::dispatch($queueStorage);

            // QueueStorage::assignCounterToQueue( $queueStorage->id );

            if (!empty($queueStorage->child_category_id))
                $this->thirdCategoryName = Category::viewCategoryName($queueStorage->child_category_id);
            if (!empty($queueStorage->sub_category_id))
                $this->secondCategoryName = Category::viewCategoryName($queueStorage->sub_category_id);
            if (!empty($queueStorage->category_id))
                $this->categoryName =  Category::viewCategoryName($queueStorage->category_id);

            if ($this->siteDetails?->category_estimated_time == SiteDetail::STATUS_YES)
                $this->determineCategoryColumn();

            if ($this->siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO)
                $this->counterID  = 0;

             $pendingwaiting=$pendingCount=0;


                if($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES &&  $this->siteDetails?->count_by_service == 0 ){
                  

                      $estimatedetail = QueueStorage::countPendingByCategory($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                      if($estimatedetail == false){
                        $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                      }else{
                      $pendingCount =$estimatedetail['customers_before_me'] ?? 0;
                      $pendingwaiting =$estimatedetail['estimated_wait_time'] ?? 0;
                     if($this->enablePriority == false){
                       if(!empty($estimatedetail['assigned_staff_id'])){
                            $assigned_staff_id = $estimatedetail['assigned_staff_id'];
                        }
                    }
                      }

                    }else{


                        // $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);

                $pendingCountget = (int)QueueStorage::countPending($this->teamId, $queueStorage->id, '', '', '', $this->location);
                $counterCount = Counter::where('team_id',$this->teamId)->whereJsonContains('counter_locations', "$this->location")->where('show_checkbox',1)->count();
              if((int)$pendingCountget > 0 && (int)$counterCount > 0){
                     $pendingCount = floor((int)$pendingCountget / (int)$counterCount);

                }

                    }

             
            // $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
            $dateformat = AccountSetting::showDateTimeFormat();
            $data = [
                'name' => $queueStorage->name ?? '',
                'phone' => $queueStorage->phone ?? '',
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueCreated->id,
                'arrives_time' => Carbon::parse($queueCreated->created_at)->format($dateformat),
                'category_name' => $this->categoryName,
                'thirdC_name' => $this->thirdCategoryName,
                'secondC_name' => $this->secondCategoryName,
                'pending_count' => $pendingCount,
                'token' => $queueCreated->token,
                'token_with_acronym' => $queueCreated->start_acronym,
                'to_mail' => $this->booking->email ?? '',
                'locations_id' => $this->booking->location_id,
                'location_name' => $this->locationName,
                'priority_sort' => $nextPrioritySort,
                'team_id' => $this->teamId
            ];

            $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId,$this->booking->location);
           $waitingTime = 0;
            if (!empty($this->siteDetails)) {
                $estimate_time = $this->siteDetails->estimate_time ?? 0;

                    if($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES){ // get esitmate time of category wise
                        $waitingTime =  $pendingwaiting ?? $estimate_time * $data['pending_count'];
                    }else{  // get esitmate time of globally set
                        $waitingTime =  $estimate_time * $data['pending_count'];
                    }

                if ($this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {

                    if (!empty($this->siteDetails->ticket_text_2))
                        $this->showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $this->siteDetails->ticket_text_2);

                    if (!empty($this->siteDetails->ticket_text)) {
                        $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $this->siteDetails->ticket_text);
                        $this->showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                    }
                }
            }
$jsonDynamicData = $this->booking->json ?? '';

            if (empty($this->booking->created_by)) {
                 if (!empty($queueStorage->phone)) {
                $existingCustomer = Customer::where('phone', $queueStorage->phone)
                    ->where('team_id', $this->teamId)
                    ->where('location_id', $this->booking->location_id)
                    ->first();

                // Create customer if not exists
                if (!$existingCustomer) {
                    $existingCustomer = Customer::create([
                        'team_id' => $this->teamId,
                        'location_id' => $this->booking->location_id,
                        'name' => $this->name ?? null,
                        'phone' => $queueStorage->phone,
                        'json_data' => $jsonDynamicData, // casted automatically to JSON
                    ]);
                }

                // Log customer activity with type 'queue'
                CustomerActivityLog::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->booking->location_id,
                    'queue_id' => $queueStorage->id,
                    'booking_id' => null,
                    'type' => 'queue',
                    'customer_id' => $existingCustomer->id,
                    'note' => 'Customer joined the queue.',
                ]);
                $queueStorage->created_by = $existingCustomer->id;
                 $queueStorage->save();
                 $data['customer_id'] = $existingCustomer->id;
            }
        }else{
                $queueStorage->created_by =$this->booking->created_by;
                 $queueStorage->save();
                $data['customer_id'] = $this->booking->created_by;
            }

            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->served_by = $assigned_staff_id;
            $queueStorage->assign_staff_id = $assigned_staff_id;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();


            $datanew = [
                'to_mail' => $data['to_mail'],
                'message' => "queue created and token number is " . $data['token']
            ];

            $type = 'ticket created';
            $teamId = $this->teamId; // Replace with actual team ID

            $this->booking_refID = null;

            DB::commit();

            $showQrcode =$this->siteDetails->is_qrcode_ticket == 1 ? true : false;
            $showlogo =$this->siteDetails->is_logo_on_print == 1 ? true : false;
            $showusername =$this->siteDetails->is_name_on_print == 1 ? true : false;
            $showarrived =$this->siteDetails->is_arrived_on_print == 1 ? true : false;
            $showlocation =$this->siteDetails->is_location_on_print == 1 ? true : false;
            $showcategory =$this->siteDetails->is_category_on_print == 1 ? true : false;
            $showTextmessage =$this->siteDetails->ticket_text_enable == 1 ? true : false;
            $showToken =$this->siteDetails->is_token_on_print == 1 ? true : false;

            $nameLabel =$this->siteDetails->print_name_label ?? 'Name';
            $tokenLabel =$this->siteDetails->print_token_label ?? 'Token';
            $arrivedLabel =$this->siteDetails->arrived_time_label ?? 'Arrived';

            $baseencodeQueueId = base64_encode($queueCreated->id);
            $customUrl = url("/visits/{$baseencodeQueueId}");
            $qrcodeSvg = QrCode::format('svg')
            ->size(150)
            ->errorCorrection('H')
            ->generate($customUrl);

            $data = array_merge($data, ['waiting_time' => $waitingTime,'ticket_link' => $customUrl]);

            $data['meeting_link'] = $storeData['meeting_link'];

            $logData = [
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => $queueStorage->served_by,
                'customer_id' => $queueStorage->created_by,
                'queue_id' => $queueStorage->queue_id,
                'queue_storage_id' => $queueStorage->id,
                'email' => $this->booking->email ?? '',
                'contact' => $queueStorage->phone,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Ticket Generate',
            ];

            $this->sendNotification($data, 'ticket created', $logData);

            $this->dispatch('swal:saved-queue', [
                'timer' => 8000,
                'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                    ($showlogo ? '<img src="' . asset($logo) . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                '</div>
                <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                    ' . (($showusername && !empty($data['name'])) ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                    ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $this->acronym . $data['token'] . '</strong></h3></div>' : '') . '
                    ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
                    ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                    ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . $data['category_name'] . '</h3><h3 style="font-size:16px;margin:0">' . $data['secondC_name'] . '</h3><h3 style="font-size:16px;">' . $data['thirdC_name'] . '</h3></div>' : '') . '
                    ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') . '
                    ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                </div>',
                'confirmButtonText' => $this->siteDetails->confirm_btn_label ?? 'Thank you',
                'token_notify' => 'The Generated Token Number is ' . $this->acronym . $data['token']

            ]);

            $this->step = 1;
        } catch (\Throwable $ex) {
            DB::rollBack();
           Log::error('Convert booking error: ' . $ex->getMessage());
            $this->dispatch('swal:ticket-generate', [
                'title' => 'Oops...',
                'text' => 'Unable to generate ticket. Please contact to the admin',
                'icon' => 'error'
            ]);
        }
    }

     public function sendNotification($data, $type,$logData=[])
        {
            $data['location_id'] = $this->location;
            if (isset($data['to_mail']) && $data['to_mail'] != '') {
                $logData['channel'] = 'email';
                $logData['status'] = MessageDetail::SENT_STATUS;
                SmtpDetails::sendMail($data, $type, 'ticket-created', $this->teamId,$logData);
            }

        if (!empty($data['phone'])) {

                SmsAPI::sendSms( $this->teamId, $data,$type,$type,$logData);

            }
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


    /***  set soring of queue code */
    protected function generateSequencePattern()
    {
        $categories = Category::where('team_id', $this->teamId)
        ->where(function ($query) {
            $query->whereNull('parent_id')
                  ->orWhere('parent_id', '');
        })
        ->whereJsonContains('category_locations', (string)$this->location)
        ->orderBy('sort')
        ->pluck('visitor_in_queue', 'id');

    return $categories;
    }

    public function getNextPrioritySort($categoryId)
    {

        $category = Category::find($categoryId);

        $nextserial = 1;
        // Generate the sequence pattern dynamically
        $sequencePattern = $this->generateSequencePattern();

        $filteredCategories = $sequencePattern->except($category->id);

        // Sum the 'visitor_in_queue' values of the remaining categories
        $sumVisitorInQueue = $filteredCategories->sum() + $sequencePattern[$category->id];
        // Fetch existing queues for the current team and location
        $queues = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->where('category_id', $category->id)
            ->whereNotNull('priority_sort')
            ->orderBy('priority_sort')
            ->whereDate('created_at', Carbon::today())
            ->pluck('priority_sort')
            ->toArray();

        if (!empty($queues)) {
            $maxValue = max($queues);
            if ($maxValue == 0) {
                $maxValue = $nextserial;
                $queues = [];
            }
        } else {
            $maxValue = $nextserial;
        }


        if ($sequencePattern[$category->id] == 1) {
            if (!empty($queues)) {
                return $nextserial = $maxValue + $sumVisitorInQueue;
            } else {
                // Convert the collection to an array
                $categoriesArray = $sequencePattern->toArray();

                // Slice the array to get values before the key 44
                $slicedArray = array_slice($categoriesArray, 0, array_search($category->id, array_keys($categoriesArray)));

                // Sum the values in the sliced array
                $sumBefore = array_sum($slicedArray);
                // dd($sumBefore);
                return $nextserial = $maxValue + $sumBefore;
            }
        } elseif ($sequencePattern[$category->id] > 1) {

            $countserial = 0;
            if (!empty($queues)) {
                for ($i = $maxValue; $i >= 1; $i--) {
                    $checkSort = QueueStorage::where('team_id', $this->teamId)
                        ->where('locations_id', $this->location)
                        ->where('category_id', $category->id)
                        ->whereNotNull('priority_sort')
                        ->whereDate('created_at', Carbon::today())
                        ->where('priority_sort', $i)
                        ->exists();
                    if ($checkSort) {
                        $countserial += 1;
                    } else {
                        break;
                    }
                }
                //   dd($countserial.'/'.$sequencePattern[$category->id].'/'.$maxValue .'/'.$sumVisitorInQueue);
                if ($countserial == $sequencePattern[$category->id]) {
                    return $nextserial = $maxValue + $sumVisitorInQueue - 1;
                } else {
                    return $nextserial = $maxValue + 1;
                }
            } else {
                $categoriesArray = $sequencePattern->toArray();

                // Slice the array to get values before the key 44
                $slicedArray = array_slice($categoriesArray, 0, array_search($category->id, array_keys($categoriesArray)));

                // Sum the values in the sliced array
                $sumBefore = array_sum($slicedArray);
                return $nextserial = $maxValue + $sumBefore;
            }
        }else{
            return $nextserial;
        }
    }
}
