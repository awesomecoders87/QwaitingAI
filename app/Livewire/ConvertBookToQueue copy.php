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
    CustomerActivityLog
};
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use App\Events\QueueCreated;
use App\Jobs\SendQueueNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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
         }
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

            DB::beginTransaction();
            // $this->acronym = SiteDetail::DEFAULT_APPOINTMENT_A;

            if (!empty($this->selectedCategoryId)) {
                $this->acronym = Category::viewAcronym($this->selectedCategoryId);
            }else{
                $this->acronym = SiteDetail::DEFAULT_APPOINTMENT_A;
            }

            $lastToken = QueueStorage::getLastToken($this->teamId, $this->acronym, $this->location);
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
            $nextPrioritySort = $this->getNextPrioritySort($this->booking->category_id);
        

            $storeData = [
                'name' => $this->booking->name,
                'phone' => $this->booking->phone,
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
                'created_by' => is_numeric($this->booking->staff_id) ? (int)$this->booking->staff_id : null,
                'served_by' => is_numeric($this->booking->staff_id) ? (int)$this->booking->staff_id : null,
                'assign_staff_id' => is_numeric($this->booking->staff_id) ? (int)$this->booking->staff_id : null,
                'campaign_id' => is_numeric($this->booking->campaign_id) ? (int)$this->booking->campaign_id : null
            ];

            Log::emergency('update convert Booking id' . $this->booking->id . ' - convert date: ' . $todayDateTime);

            $this->booking->is_convert = Booking::STATUS_YES;
            $this->booking->status = Booking::STATUS_COMPLETED;
            $this->booking->convert_datetime = $todayDateTime;
            $this->booking->save();


            $queueCreated = Queue::storeQueue([
                'team_id' => $this->teamId,
                'token' => $this->token_start,
                'token_with_acronym' => Queue::LABEL_NO,
                'locations_id' => $this->booking->location_id,
                'arrives_time' => $todayDateTime,
            ]);



            $queueStorage =  QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));

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
            $pendingCount = 0;

             $pendingwaiting=$pendingCount=0; 
             $assigned_staff_id = null; 

                if($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES){ 

                      $estimatedetail = QueueStorage::countPendingByCategory($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                      $pendingCount =$estimatedetail['customers_before_me'] ?? 0;
                      $pendingwaiting =$estimatedetail['estimated_wait_time'] ?? 0;
                      $assigned_staff_id =$estimatedetail['assigned_staff_id'] ?? null;
                    }else{

                      $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                  }

            $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
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
                if ($this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {
                    $estimate_time = $this->siteDetails->estimate_time ?? 0;

                    if($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES){ // get esitmate time of category wise
                        $waitingTime =  $pendingwaiting ?? $estimate_time * $data['pending_count'];
                    }else{  // get esitmate time of globally set
                        $waitingTime =  $estimate_time * $data['pending_count'];
                    }

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
            $queueStorage->assign_staff_id = (int)$assigned_staff_id ?? null;
            $queueStorage->save();

                      //store customer data and activity log 
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
            }

            $this->sendNotification( $data,'ticket created' );

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

    public function sendNotification($data, $type)
    {
        $data['locations_id'] = $this->location;
        if (isset($data['to_mail']) && $data['to_mail'] != '') {
            SmtpDetails::sendMail($data, $type, 'ticket-created', $this->teamId);
        }
        // $data[ 'location' ] = Location::find( $this->location )->value( 'location_name' );
        if (!empty($data['phone'])) {
            SmsAPI::sendSms($this->teamId, $data, $type,$type);

            // SmsAPI::sendSmsWhatsApp( $this->teamId, $data );
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
