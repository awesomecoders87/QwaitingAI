<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Booking,
    SiteDetail,
    AccountSetting,
    Location,
    Category,
    QueueStorage,
    Queue,
    SmtpDetails,
    SmsAPI,
    TenantLimit,
    User,
    SalesforceSetting,
    SalesforceConnection
};
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Events\QueueCreated;
use App\Events\QueueDisplay;
use App\Services\SalesforceService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Config;



#[Layout('components.layouts.custom-layout')]
class CheckinComponent extends Component
{

    #[Title('Appointment Checkin')]
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
    public $queueId;
    public $decodedId;
    public $decodedLocation;
    public $locationName;
    public $enablePriority = false;

    // Salesforce properties
    public $client_id;
    public $client_secret;
    public $redirect_uri;
    public $auth_url;
    public $token_url;
    public $access_token;
    public $instance_url;

    public function mount($id, $location)
    {
        $this->teamId = tenant('id');
        $this->location = $location;
        $this->selectBookingId = base64_decode($id);
        $this->siteDetails = SiteDetail::getMyDetails($this->teamId, base64_decode($this->location));
        $this->enablePriority = $this->siteDetails->use_staff_priority ?? false;

        // Initialize Salesforce settings
        $this->initializeSalesforceSettings();

        $this->checkin();
    }

    private function initializeSalesforceSettings()
    {
        $salesforcessettings = SalesforceSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->first();

        if ($salesforcessettings) {
            $this->client_id = $salesforcessettings->client_id ?? '';
            $this->client_secret = $salesforcessettings->client_secret ?? '';
            $this->redirect_uri = !empty($salesforcessettings->redirect_uri) ? $salesforcessettings->redirect_uri : '';
        }

        $this->auth_url = 'https://login.salesforce.com/services/oauth2/authorize';
        $this->token_url = 'https://login.salesforce.com/services/oauth2/token';

        $connectionData = SalesforceConnection::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('status', 1)
            ->first();

        if (!empty($connectionData) && !empty($connectionData->salesforce_refresh_token) && !empty($connectionData->salesforce_instance_url)) {
            $this->access_token = $connectionData->salesforce_access_token ?? '';
            $this->instance_url = $connectionData->salesforce_instance_url ?? '';
        }
    }

    public function checkin()
    {

        $booking = Booking::where('team_id', $this->teamId)
            ->where('location_id', base64_decode($this->location))
            ->where('is_convert', Booking::STATUS_NO)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->where('id', base64_decode($this->selectBookingId))
            ->first();

        if ($booking) {

            $queueCreated = $this->convertToQueue($booking);

            if ($queueCreated['status'] == "success") {

                // $this->dispatch('updated');
                $this->dispatch('swal:saved-queue', $queueCreated['ticket']);
 // return redirect('visits/' . base64_encode($this->queueId));
                return redirect('ticket-view/' . base64_encode($this->queueId));
            } else {

                $this->dispatch('error', message: $queueCreated['message']);
            }
        } else {

            $this->dispatch('error', message: 'Booking not found or does not belong to your team/location.');
        }
    }

    public function convertToQueue($booking)
    {

        try {

            $this->location = $booking->location_id;

            if (!empty($this->location)) {
                $this->locationName = Location::locationName($this->location);
            }
            $bookingDate = Carbon::parse($booking->booking_date)->startOfDay();
            $currentDate = Carbon::now()->startOfDay();
            $readableDate = $bookingDate->format('F j, Y'); // Example: July 22, 2024

            if ($bookingDate->lt($currentDate)) {

                Log::error('Error storing queue data: Booking date is in the past on ' . $readableDate);
                return (['status' => 'error', 'message' => 'Booking date is in the past on ' . $readableDate]);
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
                return (['status' => 'error', 'message' => 'Not found! Already converted']);
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

            if (!empty($this->selectedCategoryId)) {
                $this->acronym = Category::viewAcronym($this->selectedCategoryId);
            } else {
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
                    return (['status' => 'error', 'message' => 'Unable to create more tickets']);
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
            $timezone = $this->siteDetails->select_timezone ?? 'UTC';
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);

            $todayDateTime = Carbon::now($timezone);

            $nextPrioritySort = $this->getNextPrioritySort($booking->category_id);

             $assigned_staff_id = is_numeric($booking->staff_id) ? (int)$booking->staff_id : null;

             if ($this->enablePriority || empty( $assigned_staff_id) || $this->siteDetails->assigned_staff_id == 1) {
                $assigned_staff_id = User::getNextAgent($booking->team_id, $booking->location_id);
                if (empty($assigned_staff_id)) {
                    $this->dispatch('swal:ticket-generate', [
                        'title' => 'Oops...',
                        'text' => 'Staff is not Available',
                        'icon' => 'error'
                    ]);
                    return;
                }
            }
            $storeData = [
                'name' => $booking->name,
                'phone' => $booking->phone,
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
                'campaign_id' => $booking->campaign_id
            ];

            Log::emergency('update convert Booking id' . $booking->id . ' - convert date: ' . $todayDateTime);

            $booking->is_convert = Booking::STATUS_YES;
            $booking->status = Booking::STATUS_COMPLETED;
            $booking->convert_datetime = $todayDateTime;
            $booking->save();


            $queueCreated = Queue::storeQueue([
                'team_id' => $this->teamId,
                'token' => $this->token_start,
                'token_with_acronym' => Queue::LABEL_NO,
                'locations_id' => $booking->location_id,
                'arrives_time' => $todayDateTime,
            ]);

            $this->queueId = $queueCreated->id;

            $queueStorage =  QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));

            QueueCreated::dispatch($queueStorage);
            QueueDisplay::dispatch($queueStorage);

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
            $pendingCount = 0;
             $pendingwaiting = $pendingCount = 0;


            if ($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES) {

                $estimatedetail = QueueStorage::countPendingByCategory($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                if ($estimatedetail == false) {
                    $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                } else {
                    $pendingCount = $estimatedetail['customers_before_me'] ?? 0;
                    $pendingwaiting = $estimatedetail['estimated_wait_time'] ?? 0;
                    if ($this->enablePriority == false || $this->siteDetails->assigned_staff_id != 1) {
                        if (!empty($estimatedetail['assigned_staff_id'])) {
                            $assigned_staff_id = $estimatedetail['assigned_staff_id'];
                        }
                    }
                }
            } else {

                $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
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
                'to_mail' => $booking->email ?? '',
                'locations_id' => $booking->location_id,
                'location_name' => $this->locationName,
                'priority_sort' => $nextPrioritySort,
                'team_id' => $this->teamId
            ];

            $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId, $booking->location_id);
            $waitingTime = 0;
            if (!empty($this->siteDetails)) {
                if ($this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {
                    $estimate_time = $this->siteDetails->estimate_time ?? 0;

                    $waitingTime =  $estimate_time * $data['pending_count'];

                    if (!empty($this->siteDetails->ticket_text_2))
                        $this->showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $this->siteDetails->ticket_text_2);

                    if (!empty($this->siteDetails->ticket_text)) {
                        $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $this->siteDetails->ticket_text);
                        $this->showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                    }
                }
            }

            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();

            // Create Salesforce lead if settings are configured
            $this->createSalesforceLead($queueStorage, $booking, $assigned_staff_id);

            $this->sendNotification($data, 'ticket created');

            $datanew = [
                'to_mail' => $data['to_mail'],
                'message' => "queue created and token number is " . $data['token']
            ];

            $type = 'ticket created';
            $teamId = $this->teamId; // Replace with actual team ID


            DB::commit();

            $showQrcode = $this->siteDetails->is_qrcode_ticket == 1 ? true : false;
            $showlogo = $this->siteDetails->is_logo_on_print == 1 ? true : false;
            $showusername = $this->siteDetails->is_name_on_print == 1 ? true : false;
            $showarrived = $this->siteDetails->is_arrived_on_print == 1 ? true : false;
            $showlocation = $this->siteDetails->is_location_on_print == 1 ? true : false;
            $showcategory = $this->siteDetails->is_category_on_print == 1 ? true : false;
            $showTextmessage = $this->siteDetails->ticket_text_enable == 1 ? true : false;
            $showToken = $this->siteDetails->is_token_on_print == 1 ? true : false;

            $nameLabel = $this->siteDetails->print_name_label ?? 'Name';
            $tokenLabel = $this->siteDetails->print_token_label ?? 'Token';
            $arrivedLabel = $this->siteDetails->arrived_time_label ?? 'Arrived';

            $baseencodeQueueId = base64_encode($queueCreated->id);
            $customUrl = url("/visits/{$baseencodeQueueId}");
            $qrcodeSvg = QrCode::format('svg')
                ->size(150)
                ->errorCorrection('H')
                ->generate($customUrl);

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




            // $this->dispatch('swal:saved-queue', [
            //     'timer' => 8000,
            //     'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
            //         ($showlogo ? '<img src="' . asset($logo) . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
            //     '</div>
            //     <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
            //         ' . (($showusername && !empty($data['name'])) ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
            //         ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $this->acronym . $data['token'] . '</strong></h3></div>' : '') . '
            //         ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
            //         ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
            //         ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . $data['category_name'] . '</h3><h3 style="font-size:16px;margin:0">' . $data['secondC_name'] . '</h3><h3 style="font-size:16px;">' . $data['thirdC_name'] . '</h3></div>' : '') . '
            //         ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') . '
            //         ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
            //     </div>',
            //     'confirmButtonText' => $this->siteDetails->confirm_btn_label ?? 'Thank you',
            //     'token_notify' => 'The Generated Token Number is ' . $this->acronym . $data['token']

            // ]);

            return (['status' => 'success', 'ticket' => $ticket]);
        } catch (\Throwable $ex) {
            DB::rollBack();
            // $this->dispatch('swal:ticket-generate', [
            //     'title' => 'Oops...',
            //     'text' => 'Unable to generate ticket. Please contact to the admin',
            //     'icon' => 'error'
            // ]);
            Log::error('Error storing queue data: ' . $ex->getMessage());
            return (['status' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    private function createSalesforceLead($queueStorage, $booking, $assigned_staff_id)
    {
        // Check if Salesforce is configured
        if (!empty($queueStorage) && !empty($this->client_id) && !empty($this->client_secret) && !empty($this->access_token) && !empty($this->instance_url)) {
            try {
                $datetime11 = new \DateTime($queueStorage->arrives_time);
                $datetime11->setTimezone(new \DateTimeZone('UTC'));
                $Qwaiting_Sync_Date__c = $datetime11->format('Y-m-d\TH:i:s\Z');

                $salesForceData = [
                    'refresh_token' => SalesforceConnection::where('team_id', $this->teamId)
                        ->where('location_id', $this->location)
                        ->where('status', 1)
                        ->value('salesforce_refresh_token'),
                    'FirstName' => $queueStorage->name ?? 'Guest',
                    'Phone' => $queueStorage->phone ?? '',
                    'Email' => $booking->email ?? '',
                    'Qwaiting_Sync_Date__c' => $Qwaiting_Sync_Date__c,
                    'Service_Name__c' => $this->categoryName ?? '',
                    'Token' => $queueStorage->token ?? '',
                    'Page' => 'Checkin',
                    'Created' => $queueStorage->created_at ?? now(),
                    'queue_storage_id' => $queueStorage->id,
                    'Company' => '',
                    'Mobile' => $queueStorage->phone ?? '',
                    'Age' => '',
                    'Occupation' => '',
                    'Address' => $this->locationName ?? '',
                    'Marital' => '',
                    'Previous' => '',
                    'Purpose' => '',
                    'Unit' => '',
                    'Note' => '',
                    'AssignId' => !empty($assigned_staff_id) ? User::where('id', $assigned_staff_id)->value('saleforce_user_id') : '005Hu00000SBZ8bIAH',
                ];

                // Call Salesforce Lead creation
                $salesforceService = new SalesforceService($this->client_id, $this->client_secret, $this->token_url);
                $leadResponse = $salesforceService->createLead($salesForceData);

                $queueStorage->salesforce_lead = json_encode($leadResponse);
                $queueStorage->save();

            } catch (\Exception $e) {
                Log::error('Error creating Salesforce lead: ' . $e->getMessage());
            }
        }
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

    public function sendNotification($data, $type)
    {
        $data['locations_id'] = $this->location;
        if (isset($data['to_mail']) && $data['to_mail'] != '') {
            SmtpDetails::sendMail($data, $type, 'ticket-created', $this->teamId);
        }
        // $data[ 'location' ] = Location::find( $this->location )->value( 'location_name' );
        if (!empty($data['phone'])) {
            SmsAPI::sendSms($this->teamId, $data, $type, $type);

            // SmsAPI::sendSmsWhatsApp( $this->teamId, $data );
        }
    }

    public function render()
    {
        return view('livewire.checkin-component');
    }
}
