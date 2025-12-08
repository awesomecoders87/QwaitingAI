<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
    Booking, SiteDetail, FormField, AccountSetting,Location,Category,SmtpDetails,SmsAPI,QueueStorage,Queue,Level,MessageDetail,User,CustomSlot,Customer,TenantLimit,CustomerActivityLog,Counter};
   use Carbon\Carbon;
   use Livewire\WithPagination;
   use Illuminate\Support\Facades\Session;
   use Livewire\Attributes\Layout;
   use Livewire\Attributes\Title;
   use Livewire\Attributes\On;
   use SimpleSoftwareIO\QrCode\Facades\QrCode;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Config;
   use Illuminate\Support\Facades\DB;
   use Illuminate\Support\Facades\Log;
   use App\Events\QueueCreated;
   use Auth;
   use Symfony\Component\HttpFoundation\StreamedResponse;
    use Illuminate\Support\Facades\Response;
    use Barryvdh\DomPDF\Facade\Pdf;
   use Illuminate\Support\Facades\Storage;
   use Livewire\WithFileUploads;


class BookingList extends Component
{
    use WithPagination, WithFileUploads;

    #[Title('Booking List')]

    public $teamId;
    public $location;
    public $siteDetails;
    public $accountdetail;
    public $search;
    public $fromDate;
    public $toDate;
    public $status;
    public $interviewMode;
    public $categoryId;
    public $subCategoryId;
    public $childCategoryId;
    public $showupdatestatus = false;
    public $categories = [];
    public $subCategories = [];
    public $childCategories = [];
    public $bookingStatus = [];
    public $changeStatus;
    public $selectBookingId;
    public $selectedCategoryId;
    public $secondChildId;
    public $thirdChildId;
    public $token_start;
    public $acronym;
    public $countCatID = 0;
    public $fieldCatName = '';
    public $counterID = 0;
    public $showTicketText;
    public $showTicketText_2;
    public $categoryName = '';
    public $secondCategoryName = '';
    public $thirdCategoryName = '';
    public $queue_form = false;
    public $dynamicForm = [];
    public $dynamicProperties = [];
    public $allCategories = [];
    public $bookingDetails = [];
    public $level1, $level2, $level3,$acronym_level;
    public $enablePriority = false;
    public $unavailableMessage;

    // CSV upload
    public $csvFile;
    public $showUploadModal = false;
    public $isUploading = false;

    public function mount()
    {

        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');

        $this->siteDetails = SiteDetail::getMyDetails($this->teamId, $this->location);

        $this->accountdetail = AccountSetting::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->where('slot_type', AccountSetting::BOOKING_SLOT)->first();

        if(empty($this->siteDetails) || empty($this->accountdetail)){
            abort(403);
        }

        $this->enablePriority = $this->siteDetails->use_staff_priority ?? false;
        if($this->accountdetail->booking_system == 0){
            abort(403);
        }
        $this->categories = Category::withTrashed()
        ->where('team_id', $this->teamId)
        ->where(function ($query) {
            $query->whereNull('parent_id')
                  ->orWhere('parent_id', '');
        })
        ->whereJsonContains('category_locations', (string)$this->location)
        ->orderBy('id')
        ->get();

         $today = Carbon::now()->toDateString(); // e.g., '2025-07-17'
        $this->fromDate = $today;
        $this->toDate = $today;

          $levels = Level::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');
 $this->acronym_level = $levels[1]->acronyms_show_level ?? 1;
        $this->level1 = $levels[1]->name ?? 'Level 1';
       $this->level2 = $levels[2]->name ?? 'Level 2';
       $this->level3 = $levels[3]->name ?? 'Level 3';

        $this->bookingStatus = Booking::getBookingStatus();
    }


    
    public function openUploadModal(){ 
        $this->showUploadModal = true; 
        $this->isUploading = false;
    }
    
    public function closeUploadModal(){ 
        // Don't reset isUploading here to prevent flickering during upload
        $this->showUploadModal = false;
        $this->isUploading = false;
    }

    public function downloadSampleBookingCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample-booking.csv"',
        ];

        $callback = function() {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Unique ID','Date','Timeslot','Category','Sub Category','Name','Contact','Phone Code']);
            fputcsv($out, ['ABC-001','10/30/2025','09:00AM - 10:00AM','First Level Category Name','Second Level Category Name','Dummy Name','878545555','91']);
            fclose($out);
        };

        return new StreamedResponse($callback, 200, $headers);
    }


     public function getAgentRotationOrder()
{
    $allAgents = User::where('level_id', 3)
        ->where('team_id', $this->teamId)
        ->whereNotNull('locations')
        ->where('locations', '!=', '')
        ->whereRaw("JSON_VALID(locations)")
        ->whereJsonContains('locations', (string) $this->location)
        ->orderBy('priority')
        ->select('id', 'priority')
        ->get();


  // Filter agents by availability
    $availableAgents = $allAgents->filter(function ($agent) {
        return $this->checkStaffAvailability($agent->id);
    })->pluck('id')->values(); // Reset index


    return $availableAgents;

}

    public function getNextAgent()
    {
        $userTimezone = $this->siteDetails->select_timezone ?? 'UTC';
        $today = Carbon::today($userTimezone);

        // Get ordered agent IDs
        $agents = $this->getAgentRotationOrder();

        // Return first agent if no one has been assigned today yet
        $lastTicket = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereNotNull('locations_id')
            ->whereNull('cancelled_datetime')
            ->where('status','!=','Cancelled')
            ->whereDate('arrives_time', $today->format('Y-m-d'))
            ->orderBy('id','desc')
            ->first();

        if (!$lastTicket || !$lastTicket->assign_staff_id) {
            return $agents->first(); // Return first agent in the rotation
        }

        // Get index of last assigned agent
        $lastIndex = $agents->search($lastTicket->assign_staff_id);

        // If not found, return first
        if ($lastIndex === false) {
            return $agents->first();
        }

        // Get next index with rotation
        $nextIndex = ($lastIndex + 1) % $agents->count();

        return $agents[$nextIndex]; // Return the next agent's user_id
    }

    public function checkStaffAvailability($staffId)
    {
        $userTimezone = $this->siteDetails->select_timezone ?? 'UTC'; // Ideally fetch from DB or user settings
        // $userTimezone = 'UTC';
        $currentDate = Carbon::now($userTimezone)->format('Y-m-d');
        $currentDay = Carbon::now($userTimezone)->format('l');
        $currentTime = Carbon::now($userTimezone)->format('h:i A');

        return $this->isWithinTimeSlot(null, AccountSetting::STAFF_SLOT, $currentDate, $currentDay, $currentTime,$staffId);

    }

     private function isWithinTimeSlot($categoryId=null, $slotType, $currentDate, $currentDay, $currentTime,$userId=null)
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
            ->where('is_hold', QueueDB::STATUS_NO)
            ->where('temp_hold', QueueDB::STATUS_NO)
            ->where('is_missed', QueueDB::STATUS_NO)
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



    public function updatedCategoryId()
    {
        $this->subCategoryId = null;
        $this->childCategoryId = null;
        $this->subCategories = Category::withTrashed()
        ->where('parent_id', $this->categoryId)
            ->whereJsonContains('category_locations', (string)$this->location)
            ->get();
        $this->childCategories = [];
    }

    public function openStatusModal($value){
        $this->selectBookingId = $value;
        $this->showupdatestatus = true;
    }

    public function updatestatus()
    {
        $this->validate([
            'changeStatus' => 'required|string',
        ], [
            'changeStatus.required' => 'Please select a status.',
        ]);

         // Find and update booking
        $booking = Booking::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->where('is_convert', Booking::STATUS_NO)
        ->where('id', $this->selectBookingId)
        ->first();

        if ($booking) {
            $booking->status = $this->changeStatus;
            $booking->save();
            Log::info('Booking status updated:'.$booking->id .'and new status is '.$this->changeStatus);
               // Reset values
            $this->selectBookingId = '';
            $this->changeStatus = '';
            $this->showupdatestatus = false;

            $url = route('booking-confirmed',['id' => base64_encode($booking->id)]);
            $cleanedUrl = $url;

            $data = [
                'booking_id' => $booking->id,
                'name' => $booking->name,
                'phone' => $booking->phone,
                'phone_code' => $booking?->phone_code ?? '91',
                'booking_date' => $booking->booking_date,
                'booking_time' => $booking->booking_time,
                'booked_by' => $booking->createdBy?->name,
                'category_name' => $booking->categories?->name,
                'secondC_name' => $booking->sub_category?->name,
                'thirdC_name' => $booking->child_category?->name,
                'location' => $booking->location?->location_name,
                'locations_id' => $booking->location_id,
                'team_id' => $booking->team_id,
                'status' => $booking->status,
                'json' => $booking->json,
                'view_booking'=>$cleanedUrl,
                'refID'=>$booking->refID,
            ];

            try {
                if ($booking->status == Booking::STATUS_CONFIRMED) {
                    $data = array_merge( $data, ['to_mail' => $booking->email]);

                    if(!empty($data['to_mail']))
                    SmtpDetails::sendMail( $data, 'booking confirmed', 'Booking Confirmation', $booking->team_id);

                    if(!empty($data['phone']))
                    SmsAPI::sendSms($booking->team_id, $data,'booking confirmed','booking confirmed');

                    Log::info('Confiremd Email and sms sending');
                }
                else
                {
                    $data = array_merge( $data, ['to_mail' => $booking->email]);

                    if(!empty($data['to_mail']))
                    SmtpDetails::sendMail( $data, 'booking cancelled', 'Booking Canceled', $booking->team_id);

                    if(!empty($data['phone']))
                    SmsAPI::sendSms($booking->team_id, $data,'booking cancelled','booking cancelled');

                    Log::info('Cancelled Email and sms sending');
                }
              } catch (\Throwable $e) {
                Log::error('Email sending failed: ' . $e->getMessage());
            }
            $this->dispatch('updated');

        } else {
            $this->dispatch('error',[
                'message'=>'Booking not found or does not belong to your team/location.'
            ]);

        }


    }

    public function confimationforcheckin($value){

        $this->selectBookingId = $value;
        $this->dispatch('confirm-check-in');

    }

    public function deleteBooking($value){

        $this->selectBookingId = $value;
        $this->dispatch('confirm-delete');

    }

    #[On('confirmed-delete')]
    public function confirmedDelete()
    {
        // Retrieve booking details from the database
        $this->bookingDetails = Booking::find($this->selectBookingId)->delete();
        $this->selectBookingId = '';
        $this->dispatch('deleted');
    }

    #[On('confirmed-check-in')]
    public function confimedforcheckin(){

        $booking = Booking::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->where('is_convert', Booking::STATUS_NO)
        ->where('status', Booking::STATUS_CONFIRMED)
        ->where('id', $this->selectBookingId)
        ->first();

        if ($booking) {

        $queueCreated = $this->convertToQueue($booking);

        if($queueCreated['status'] == "success"){

            // $this->dispatch('updated');
            $this->dispatch('swal:saved-queue',$queueCreated['ticket']);

        }else{

            $this->dispatch('error', message: $queueCreated['message']);

        }
        } else {
            $this->dispatch('error', message: 'Booking not found or does not belong to your team/location.');

        }
    }

    public function resetFilters()
    {
        $this->fromDate = null;
        $this->toDate = null;
        $this->status = null;
        $this->interviewMode = null;

        $this->categoryId = null;
        $this->subCategoryId = null;
        $this->childCategoryId = null;

        $this->subCategories = [];
        $this->childCategories = [];
        $this->showupdatestatus = false;
    }


    public function updatedSubCategoryId()
    {
        $this->childCategoryId = null;
        $this->childCategories = Category::withTrashed()
        ->where('parent_id', $this->subCategoryId)
            ->whereJsonContains('category_locations', (string)$this->location)
            ->get();
    }

    public function convertToQueue($booking)
    {

        try {

            $this->location = $booking->location_id;

            $bookingDate = Carbon::parse($booking->booking_date)->startOfDay();
            $currentDate = Carbon::now()->startOfDay();
            $readableDate = $bookingDate->format('F j, Y'); // Example: July 22, 2024

            if ($bookingDate->lt($currentDate)) {

                Log::error('Error storing queue data: Booking date is in the past on ' . $readableDate);
                return (['status' => 'error','message'=>'Booking date is in the past on ' . $readableDate]);
            } elseif ($bookingDate->gt($currentDate)) {
                // return $this->dispatch('swal:exist-booking', [
                //     'title' => 'Booking is for a future date on ' . $readableDate,
                //     'icon' => 'info',
                // ]);
            }

            $isAsQueue = QueueStorage::isBookExist($booking->id);

            if ($isAsQueue) {
                // return $this->dispatch('swal:exist-booking', [
                //     'title' => 'Not found! Already converted',
                //     'icon' => 'error',
                // ]);

                Log::error('Error storing queue data: Not found! Already converted');
                return (['status' => 'error','message'=>'Not found! Already converted']);
            }

             $checkTicketLimit = SiteDetail::checkTicketLimit($this->teamId, $this->location, $this->siteDetails);

            if($checkTicketLimit)
            {
                return (['status' => 'error', 'message' => 'Unable to generate ticket. The ticket creation limit exceeded the daily limit that is ' . $this->siteDetails->ticket_limit]);
            }


            DB::beginTransaction();
            // $this->acronym = SiteDetail::DEFAULT_APPOINTMENT_A;
            $this->selectedCategoryId = $booking->category_id;
            $this->secondChildId = $booking->sub_category_id ?? '';
            $this->thirdChildId = $booking->child_category_id ?? '';

if((int)$this->acronym_level == 1 && !empty($this->selectedCategoryId)){
                $this->acronym = Category::viewAcronym($this->selectedCategoryId);
            }elseif((int)$this->acronym_level == 2 && !empty($this->secondChildId)){
                $this->acronym = Category::viewAcronym($this->secondChildId);
            }elseif((int)$this->acronym_level == 3 && !empty($this->thirdChildId)){
                $this->acronym = Category::viewAcronym($this->thirdChildId);
            }else {
                $this->acronym = SiteDetail::DEFAULT_APPOINTMENT_A;
            }
            $lastToken = QueueStorage::getLastToken($this->teamId, $this->acronym, $this->location);
            $token_digit = $this->siteDetails?->token_digit ?? 4;
            $isExistToken = true;

            while ($isExistToken) {
                $newToken = QueueStorage::newGeneratedToken($lastToken, $this->siteDetails?->token_start, $token_digit);
                if (strlen($newToken) > $token_digit) {
                    // $this->dispatch('swal:ticket-generate', [
                    //     'title' => 'Oops...',
                    //     'text' => 'Unable to create more tickets',
                    //     'icon' => 'error'
                    // ]);
                    // return;

                    Log::error('Error storing queue data: Unable to create more tickets');
                    return (['status' => 'error','message'=>'Unable to create more tickets']);
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
            $nextPrioritySort = $this->getNextPrioritySort($booking->category_id);

            $assigned_staff_id = is_numeric($booking->staff_id) ? (int)$booking->staff_id : null;

            if($this->enablePriority){
                  $assigned_staff_id = User::getNextAgent($booking->team_id,$booking->location_id);
                if(empty($assigned_staff_id)){
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


             $storeData = [
                'name' => $booking->name,
                'phone' => $booking->phone,
                'phone_code' => $booking->phone_code,
                'category_id' => $booking->category_id ?? null,
                'sub_category_id' => $booking->sub_category_id ?? null,
                'child_category_id' => $booking->child_category_id ?? null,
                'team_id' => $this->teamId,
                'token' => $this->token_start,
                'token_with_acronym' => Queue::LABEL_NO,
                'json' => $booking->json,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $this->acronym,
                'locations_id' => $booking->location_id,
                'booking_id' => $booking->id,
                'priority_sort' => $nextPrioritySort,
                'served_by' =>  $assigned_staff_id,
                'assign_staff_id' =>  $assigned_staff_id,
                'campaign_id' => is_numeric($booking->campaign_id) ? (int)$booking->campaign_id : null,
                'full_phone_number' => (!empty($booking->phone_code) && !empty($booking->phone))
                                        ? $booking->phone_code . $booking->phone
                                        : null,
            ];
            Log::emergency('update convert Booking id' . $booking->id . ' - convert date: ' . $todayDateTime);

            $queueCreated = Queue::storeQueue([
                'team_id' => $this->teamId,
                'token' => $this->token_start,
                'token_with_acronym' => Queue::LABEL_NO,
                'locations_id' => $booking->location_id,
                'arrives_time' => $todayDateTime,
                'last_category' => $lastcategory,
            ]);



            $queueStorage =  QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));

            $booking->is_convert = Booking::STATUS_YES;
            $booking->status = Booking::STATUS_COMPLETED;
            $booking->convert_datetime = $todayDateTime;
            $booking->save();

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


                if($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES ){

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

                    //   $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                $pendingCountget = (int)QueueStorage::countPending($this->teamId, $queueStorage->id, '', '', '', $this->location);
                $counterCount = Counter::where('team_id',$this->teamId)->whereJsonContains('counter_locations', "$this->location")->where('show_checkbox',1)->count();
              if((int)$pendingCountget > 0 && (int)$counterCount > 0){
                     $pendingCount = floor((int)$pendingCountget / (int)$counterCount);

                }
                }


  $locationName = Location::where('id', $booking->location_id)->value('location_name') ?? 'N/A';
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
                'to_mail' => $booking->email ?? '',
                'locations_id' => $booking->location_id,
              'location_name' => $locationName,
                'priority_sort' => $nextPrioritySort,
                'team_id' => $this->teamId
            ];

            $jsonDynamicData = $booking->json;
            $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId,$booking->location_id);
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

            if (empty($booking->created_by)) {
                 if (!empty($queueStorage->phone)) {
                $existingCustomer = Customer::where('phone', $queueStorage->phone)
                    ->where('team_id', $this->teamId)
                    ->where('location_id', $booking->location_id)
                    ->first();

                // Create customer if not exists
                if (!$existingCustomer) {
                    $existingCustomer = Customer::create([
                        'team_id' => $this->teamId,
                        'location_id' => $booking->location_id,
                        'name' => $this->name ?? null,
                        'phone' => $queueStorage->phone,
                        'json_data' => $jsonDynamicData, // casted automatically to JSON
                    ]);
                }

                // Log customer activity with type 'queue'
                CustomerActivityLog::create([
                    'team_id' => $this->teamId,
                    'location_id' => $booking->location_id,
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
                $queueStorage->created_by =$booking->created_by;
                 $queueStorage->save();
                $data['customer_id'] = $booking->created_by;
            }

            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->served_by = $assigned_staff_id;
            $queueStorage->assign_staff_id = $assigned_staff_id;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();

            // $this->sendNotification( $data,'ticket created' );

            $datanew = [
                'to_mail' => $data['to_mail'],
                'message' => "queue created and token number is " . $data['token']
            ];

            $type = 'ticket created';
            $teamId = $this->teamId; // Replace with actual team ID


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

            $logData = [
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => $queueStorage->served_by,
                'customer_id' => $queueStorage->created_by,
                'queue_id' => $queueStorage->queue_id,
                'queue_storage_id' => $queueStorage->id,
                'email' => $booking->email ?? '',
                'contact' => $queueStorage->phone,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Ticket Generate',
            ];

            $this->sendNotification($data, 'ticket created', $logData);
$ticket = [
    'timer' => 8000,
    'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
        ($showlogo ? '<img src="' . asset($logo) . '"  style="margin:auto;max-width:160px"/>' : '') .
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

];


            return (['status' => 'success','ticket'=>$ticket]);

        } catch (\Throwable $ex) {
            DB::rollBack();
            // $this->dispatch('swal:ticket-generate', [
            //     'title' => 'Oops...',
            //     'text' => 'Unable to generate ticket. Please contact to the admin',
            //     'icon' => 'error'
            // ]);
            Log::error('Error storing queue data: ' . $ex->getMessage());
            return (['status' => 'error','message'=>$ex->getMessage()]);
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
        }
    }


public function exportBookings()
{
    $query = Booking::query()
        ->where('team_id', $this->teamId)
        ->where('location_id', $this->location);

    $user = Auth::user();
    if (!$user->is_admin && !$user->hasPermissionTo('Show All Booking')) {
        $query->where('staff_id', $user->id);
    }

    if ($this->fromDate && $this->toDate) {
        $query->whereBetween('booking_date', [$this->fromDate, $this->toDate]);
    }

    if ($this->status) {
        $query->where('status', $this->status);
    }

    if ($this->interviewMode) {
        $query->where('interview_mode', $this->interviewMode);
    }

    if ($this->categoryId) {
        $query->where('category_id', $this->categoryId);
    }

    if ($this->subCategoryId) {
        $query->where('sub_category_id', $this->subCategoryId);
    }

    if ($this->childCategoryId) {
        $query->where('child_category_id', $this->childCategoryId);
    }

    if ($this->search) {
        $query->where(function ($q) {
            $q->where('refID', 'like', '%' . $this->search . '%')
              ->orWhere('email', 'like', '%' . $this->search . '%')
              ->orWhere('phone', 'like', '%' . $this->search . '%')
              ->orWhere('name', 'like', '%' . $this->search . '%');
        });
    }

    // 🏎️ Fetch up to 10,000 records for performance
    $bookings = $query->with(['categories', 'sub_category', 'child_category', 'createdBy'])->orderBy('booking_date', 'desc')
    ->orderByRaw("STR_TO_DATE(SUBSTRING_INDEX(booking_time, '-', 1), '%h:%i %p') ASC")->limit(10000)->get();

    $csvData = [];
   $csvData[] = [
    $this->level1,
    $this->level2,
    $this->level3,
    __('report.Ref ID'),
    __('report.status'), // or $accountdetail->booking_convert_label . ' Status'
    __('report.Date Time'), // or $accountdetail->booking_convert_label . ' DateTime'
    __('report.Email'),
    __('report.name'),
    __('report.contact'),
    __('report.Booking Date'),
    __('report.Booking Time'),
    __('report.Booking Status'),
    __('report.Booking Type'),
    __('report.Booked By'),
    __('report.Cancel Reason'),
    __('report.Cancel Remark'),
    __('report.created at'),

];

    foreach ($bookings as $booking) {
        $csvData[] = [
            $booking->categories->name ?? '',
            $booking->sub_category->name ?? '',
            $booking->child_category->name ?? '',
            $booking->refID ?? '',
            $booking->is_convert ?? '',
            optional($booking->convert_datetime)->format('Y-m-d H:i:s') ?? '',
            $booking->email ?? '',
            $booking->name ?? '',
            $booking->phone ?? '',
            $booking->booking_date ?? '',
            $booking->booking_time ?? '',
            $booking->status ?? '',
            $booking->booking_type ?? '',
            $booking->createdBy->name ?? '',
            $booking->cancel_reason ?? '',
            $booking->cancel_remark ?? '',
            optional($booking->created_at)->format('Y-m-d H:i:s') ?? '',
        ];
    }

    $filename = 'bookings_export_' . now()->format('Ymd_His') . '.csv';
    $handle = fopen('php://temp', 'r+');
    foreach ($csvData as $line) {
        fputcsv($handle, $line);
    }

    rewind($handle);
    $csvContent = stream_get_contents($handle);
    fclose($handle);

    return Response::streamDownload(function () use ($csvContent) {
        echo $csvContent;
    }, $filename);
}

public function exportPdf()
{
    $query = Booking::query()
        ->where('team_id', $this->teamId)
        ->where('location_id', $this->location);

    // Apply filters just like in render()
    if (!Auth::user()->is_admin && !Auth::user()->hasPermissionTo('Show All Booking')) {
        $query->where('staff_id', Auth::id());
    }

    if ($this->fromDate && $this->toDate) {
        $query->whereBetween('booking_date', [$this->fromDate, $this->toDate]);
    }

    if ($this->status) {
        $query->where('status', $this->status);
    }

    // ... include other filters similarly
 $locationName = Location::where('id', $this->location)->value('location_name') ?? 'N/A';
    $bookings = $query->with(['categories', 'sub_category', 'child_category', 'createdBy'])
      ->orderBy('booking_date', 'desc')
    ->orderByRaw("STR_TO_DATE(SUBSTRING_INDEX(booking_time, '-', 1), '%h:%i %p') ASC")
    ->get();
 $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->location);
    $pdf = Pdf::loadView('pdf.booking-pdf', [
        'bookings' => $bookings,
        'level1' => $this->level1,
        'level2' => $this->level2,
        'level3' => $this->level3,
         'logo_src' => $logo,
          'fromDate' => $this->fromDate,
        'toDate' => $this->toDate,
        'locationName' => $locationName,
    ])->setPaper('a4', 'landscape');;

    return response()->streamDownload(function () use ($pdf) {
        echo $pdf->stream();
    }, 'bookings.pdf');
}

    public function uploadCsvBookings()
    {
        $this->isUploading = true;
        $this->dispatch('csv-upload-started');
        
        try {
            $this->validate([
                'csvFile' => 'required|file|mimes:csv,txt|max:20480',
            ]);

            $path = $this->csvFile->getRealPath();
            if (!$path || !file_exists($path)) {
                throw new \Exception('File not accessible');
            }

            $fh = fopen($path, 'r');
            if (!$fh) { 
                throw new \Exception('Cannot open file');
            }

            $firstLine = '';
            while (($line = fgets($fh)) !== false) {
                if (trim($line) !== '') { 
                    $firstLine = $line; 
                    break; 
                }
            }
            if ($firstLine === '') { 
                fclose($fh);
                throw new \Exception('Empty CSV file');
            }

            // Guess delimiter
            $candidates = [",", ";", "\t"]; 
            $best = ","; 
            $max = -1;
            foreach ($candidates as $d) { 
                $c = substr_count($firstLine, $d); 
                if ($c > $max) { 
                    $max = $c; 
                    $best = $d; 
                } 
            }
            $delimiter = $best; 
            rewind($fh);

            $header = fgetcsv($fh, 0, $delimiter);
            if ($header === false) { 
                fclose($fh);
                throw new \Exception('No header row found in CSV');
            }
        $map = [];
        foreach ($header as $i => $h) {
            $key = strtolower(trim((string)$h));
            $map[$key] = $i;
        }

        $get = function(array $row, array $keys) use ($map) {
            foreach ($keys as $k) {
                $lk = strtolower($k);
                if (isset($map[$lk])) { return trim((string)($row[$map[$lk]] ?? '')); }
            }
            return '';
        };

        $created = 0; $failed = 0; $errors = [];
        $toInsert = [];
        $now = Carbon::now();
        $catCache = [];
        $subCache = [];

        while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
            try {
                $uniqueId  = $get($row, ['Unique ID','Unique','RefID','refid','reference number','unique ref id number']);
                $dateStr   = $get($row, ['Date','date']);
                $slotStr   = $get($row, ['Timeslot','TimeSlot','time slot','timeslot']);
                $catName   = $get($row, ['Category','category']);
                $subName   = $get($row, ['Sub Category','Sub-Category','sub category','subcategory']);
                $name      = $get($row, ['Name','name']);
                $contact   = $get($row, ['Contact','Phone','Phone Number','contact','phone']);
                $phoneCode = $get($row, ['Phone Code','phone code']);

                if ($dateStr === '' || $slotStr === '' || $catName === '') {
                    throw new \Exception('Missing required Date/Timeslot/Category');
                }

                // Parse date: expected format like 10/30/2025
                try { $date = Carbon::parse($dateStr); } catch (\Throwable $e) { $date = Carbon::createFromFormat('m/d/Y', $dateStr); }
                if (!$date) { throw new \Exception('Invalid date: '.$dateStr); }

                // Parse timeslot: e.g. 09:00AM - 10:00AM
                $parts = preg_split('/\s*-\s*/', $slotStr);
                if (count($parts) !== 2) { throw new \Exception('Invalid timeslot: '.$slotStr); }
                $start = Carbon::parse($parts[0]);
                $end   = Carbon::parse($parts[1]);

                // Categories by name (level 1/2) for this team/location (cache to reduce queries)
                $catKey = strtolower($catName);
                $categoryId = $catCache[$catKey] ?? null;
                if (!$categoryId) {
                    $category = Category::firstOrCreate([
                        'team_id' => $this->teamId,
                        'name' => $catName,
                        'level_id' => Level::getFirstRecord()?->id,
                    ], [ 'category_locations' => [(string)$this->location] ]);
                    $categoryId = $category?->id;
                    $catCache[$catKey] = $categoryId;
                }

                $subCategoryId = null;
                if ($subName !== '') {
                    $subKey = $catKey.'||'.strtolower($subName);
                    $subCategoryId = $subCache[$subKey] ?? null;
                    if (!$subCategoryId) {
                        $subCategory = Category::firstOrCreate([
                            'team_id' => $this->teamId,
                            'name' => $subName,
                            'level_id' => Level::getSecondRecord()?->id,
                            'parent_id' => $categoryId,
                        ], [ 'category_locations' => [(string)$this->location] ]);
                        $subCategoryId = $subCategory?->id;
                        $subCache[$subKey] = $subCategoryId;
                    }
                }

                $jsonDynamicData = [
                    'name' => ($name !== '' ? $name : null),
                    'phone' => ($contact !== '' && $phoneCode !== '' ? $phoneCode : '91').($contact !== '' ? $contact : null),
                ];

                $toInsert[] = [
                    'team_id' => $this->teamId,
                    'location_id' => $this->location,
                    'refID' => ($uniqueId !== '' ? $uniqueId : null),
                    'name' => ($name !== '' ? $name : null),
                    'phone' => ($contact !== '' ? $contact : null),
                    'phone_code' => ($phoneCode !== '' ? $phoneCode : '91'),
                    'category_id' => $categoryId,
                    'sub_category_id' => $subCategoryId,
                    'booking_date' => $date->format('Y-m-d'),
                    'booking_time' => trim($slotStr),
                    'start_time' => $start->format('h:i A'),
                    'end_time' => $end->format('h:i A'),
                    'json' => json_encode($jsonDynamicData),
                    'status' => Booking::STATUS_CONFIRMED,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            } catch (\Throwable $e) {
                $failed++;
                if (count($errors) < 10) { $errors[] = $e->getMessage(); }
            }
        }

        // Bulk insert in chunks to avoid huge single query
        $inserted = 0;
        $insertedRefIds = [];
        
        if (!empty($toInsert)) {
            foreach (array_chunk($toInsert, 500) as $chunk) {
                try {
                    // First insert the bookings
                    DB::beginTransaction();
                    
                    // Insert and get the inserted refIDs
                    $insertedCount = DB::table((new Booking())->getTable())->insertOrIgnore($chunk);
                    $inserted += $insertedCount;
                    
                    // Get the refIDs of the inserted bookings
                    $currentChunkRefs = array_column($chunk, 'refID');
                    $insertedRefIds = array_merge($insertedRefIds, $currentChunkRefs);
                    
                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    // Fallback to plain insert if driver doesn't support insertOrIgnore
                    try { 
                        DB::beginTransaction();
                        DB::table((new Booking())->getTable())->insert($chunk); 
                        $inserted += count($chunk);
                        $currentChunkRefs = array_column($chunk, 'refID');
                        $insertedRefIds = array_merge($insertedRefIds, $currentChunkRefs);
                        DB::commit();
                    } catch (\Throwable $ex) { 
                        DB::rollBack();
                        $failed += count($chunk); 
                        if (count($errors) < 10) { 
                            $errors[] = $ex->getMessage(); 
                        } 
                    }
                }
            }
            
            // Send SMS notifications for all successfully inserted bookings in a separate transaction
            if (!empty($insertedRefIds)) {
                try {
                    $insertedBookings = Booking::whereIn('refID', $insertedRefIds)->get();
                    $this->sendBookingSmsNotifications($insertedBookings->toArray());
                } catch (\Exception $e) {
                    Log::error("Error sending booking notifications: " . $e->getMessage());
                    $errors[] = "Bookings were created but there was an error sending notifications: " . $e->getMessage();
                }
            }
        }

            $created = $inserted;
            fclose($fh);
            $msg = "Imported bookings: $created created, $failed failed";
            if (!empty($errors)) { 
                $msg .= ' | Errors: '.implode('; ', $errors); 
            }
            
            // Reset file input
            $this->csvFile = null;
            
            // Show success message
            $this->dispatch('csv-uploaded', ['message' => $msg]);
            
            // Reset loading state and close modal
            $this->isUploading = false;
            $this->closeUploadModal();
            
            // Refresh the bookings list
            $this->resetPage();
        } catch (\Exception $e) {
            // Ensure file is closed in case of error
            if (isset($fh) && is_resource($fh)) {
                fclose($fh);
            }
            
            // Set error message and reset loading state
            $this->isUploading = false;
            $this->closeUploadModal();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
}

       protected function sendBookingSmsNotifications(array $bookingsData)
    {
        try {
            // Convert array to collection if it's not already
            $bookings = collect($bookingsData);
            
            // If the first item is a booking model, we already have the data
            $isModel = $bookings->first() instanceof \Illuminate\Database\Eloquent\Model;
            
            if (!$isModel) {
                // Get refIDs from the chunk
                $refIds = $bookings->pluck('refID')->filter()->toArray();
                
                if (empty($refIds)) {
                    Log::warning('No valid refIDs found for sending SMS notifications');
                    return;
                }
                
                // Get the bookings with relationships
                $bookings = Booking::whereIn('refID', $refIds)
                    ->with(['location', 'categories'])
                    ->get();
            }

            foreach ($bookings as $booking) {
                if (empty($booking->phone)) {
                    continue;
                }

                try {
                    // Prepare booking data
                    $categoryName = $booking->categories->name ?? 'your booking';
                    $url = url('booking-confirmed', ['id' => base64_encode($booking->id)]);
                    
                   
                    // Validate phone number before sending SMS
                    $phoneNumber = ltrim($booking->phone, '+');
                    if (empty($phoneNumber) || !is_numeric($phoneNumber)) {
                        Log::warning("Invalid phone number for booking #{$booking->refID}: {$booking->phone}");
                        continue;
                    }

                     

            $url = url('booking-confirmed', ['id' => base64_encode($booking->id)]);
            // $cleanedUrl = str_replace('/', '', $url);
            $cleanedUrl =  $url;

            //store customer data and activity log
            if (!empty($this->phone)) {
                $existingCustomer = Customer::where('phone', $this->phone)
                    ->where('team_id', $this->teamId)
                    ->where('location_id', $this->locationId)
                    ->first();

                // Create customer if not exists
                if (!$existingCustomer) {
                    $existingCustomer = Customer::create([
                        'team_id' => $this->teamId,
                        'location_id' => $this->locationId,
                        'name' => $this->name ?? null,
                        'phone' => $this->phone,
                        'json_data' => $jsonDynamicData, // casted automatically to JSON
                    ]);
                }

                // Log customer activity with type 'queue'
                CustomerActivityLog::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->locationId,
                    'queue_id' => null,
                    'booking_id' => $booking->id ?? null,
                    'type' => 'booking',
                    'customer_id' => $existingCustomer->id,
                    'note' => 'Customer joined the booking.',
                ]);
                $booking->update([
                    'created_by' => $existingCustomer->id,
                ]);
            }

              $userAuth = '';
            if (Auth::check()) {
                $userAuth = Auth::id();
            }

            $data = [
                'booking_id' => $booking->id,
                'name' => $booking->name ?? '',
                'phone' => $booking->phone ?? '',
                'phone_code' => $booking->phone_code ?? '91',
                'booking_date' => \Carbon\Carbon::parse($booking->booking_date)->format('d-m-Y'),
                'booking_time' => $booking?->booking_time ?? $booking->start_time,
                'booked_by' => $userAuth,
                'category_name' => $categoryName,
                'thirdC_name' => '',
                'secondC_name' => $booking->sub_category?->name,
                'location' => $booking->location?->location_name,
                'status' => $booking->status,
                'json' => $booking->json,
                'refID' => $booking->refID,
                'view_booking' => $cleanedUrl,
                'locations_id' => $booking->location_id,
                'team_id' => $booking->team_id,
            ];

    

            $message = 'Appointment request has been successfully sent.But Email is not sent';
            // Send email
         

                $logData = [
                    'team_id' => $booking->team_id,
                    'location_id' => $booking->location_id,
                    'customer_id' => $booking->created_by,
                    'booking_id' => $booking->id,
                    'email' => $booking->email,
                    'contact' => $booking->phone,
                    'type' => MessageDetail::TRIGGERED_TYPE,
                    'event_name' => 'Booking Confirmed',
                ];
                  \Log::info('step 2');

                $status = $booking->status;
                if ($status == Booking::STATUS_CONFIRMED) {
                    $message = 'Appointment Booked Successfully';
                      \Log::info('step 4');
                    // $this->sendEmail( $data, 'Appointment Booked Successfully', 'booking-confirmation', $this->teamId );
                    $this->senduploadNotification($data, 'booking confirmed', $message, $logData);
                } else {
                    // $this->sendEmail( $data, 'Appointment Request', 'admin_booking_approval', $this->teamId );
                    $message = 'Appointment request has been successfully sent. Please wait for confirmation';
                    $this->senduploadNotification($data, 'booking confirmed', $message, $logData);
                }
            


                } catch (\Exception $e) {
                    Log::error("Error processing booking #{$booking->refID}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("Error in sendBookingSmsNotifications: " . $e->getMessage());
        }
    }

     public function senduploadNotification($data, $title, $template, $logData = null)
    {
        $data['team_id'] = $this->teamId;
       
        \Log::info('sms first', ['message' => 'booking first sms send']);
        $data['location'] = Location::find($this->location)->value('location_name');
        if (!empty($data['phone'])) {
              \Log::info('step 6 sms');
            \Log::info('sms send', ['message' => 'booking sms send']);
            $logData['channel'] = 'sms';
            $logData['status'] = MessageDetail::SENT_STATUS;
            SmsAPI::sendSms($this->teamId, $data, $title, $title, $logData);
   \Log::info('sms end', ['message' => 'booking end sms send']);
            // SmsAPI::sendSmsWhatsApp( $this->teamId, $data );
        } else {
            \Log::error('sms no send', ['message' => 'no booking sms send']);
        }
    }

    public function render()
	{
		// Pre-fetch datetime format once
		$datetimeFormat = AccountSetting::showDateTimeFormat();

		$query = Booking::with(['categories', 'sub_category', 'child_category', 'createdBy'])
			->where('team_id', $this->teamId)
			->where('location_id', $this->location);

		$user = Auth::user();

		// Role & permission checks
		if (!$user->is_admin && !$user->hasPermissionTo('Show All Booking')) {
			$query->where('staff_id', $user->id);
		}

		// Filters
		if ($this->fromDate && $this->toDate) {
			$query->whereBetween('booking_date', [$this->fromDate, $this->toDate]);
		}

		if ($this->status) {
			$query->where('status', $this->status);
		}

		if ($this->interviewMode) {
			$query->where('interview_mode', $this->interviewMode);
		}

		if ($this->categoryId) {
			$query->where('category_id', $this->categoryId);
		}

		if ($this->subCategoryId) {
			$query->where('sub_category_id', $this->subCategoryId);
		}

		if ($this->childCategoryId) {
			$query->where('child_category_id', $this->childCategoryId);
		}

		// Search
		$query->when($this->search, function ($q) {
			$q->where(function ($q2) {
				$q2->where('refID', 'like', '%' . $this->search . '%')
				   ->orWhere('email', 'like', '%' . $this->search . '%')
				   ->orWhere('phone', 'like', '%' . $this->search . '%')
				   ->orWhere('name', 'like', '%' . $this->search . '%');
			});
		});

		$bookings = $query->orderBy('booking_date', 'desc')
			->orderByRaw("STR_TO_DATE(SUBSTRING_INDEX(booking_time, '-', 1), '%h:%i %p') ASC")
			->paginate(10);

		// Booking stats (optimized)
		$totalBookings = $query->count();
		$checkinCount = (clone $query)->where('is_convert', Booking::STATUS_YES)->count();
		$pendingCount = (clone $query)->where('status', Booking::STATUS_PENDING)->count();
		$cancelledCount = (clone $query)->where('status', Booking::STATUS_CANCELLED)->count();

		return view('livewire.booking-list', compact(
			'bookings',
			'totalBookings',
			'checkinCount',
			'pendingCount',
			'cancelledCount',
			'datetimeFormat' // Pass format to view
		));
	}

}
