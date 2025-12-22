<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Level;
use App\Models\Location;
use App\Models\Category;
use App\Models\FormField;
use App\Models\Queue;
use App\Models\SmsAPI;
use App\Models\QueueStorage;
use App\Models\SiteDetail;
use App\Models\AccountSetting;
use App\Models\SmtpDetails;
use App\Models\ScreenTemplate;
use App\Models\DisplaySettingModel;
use App\Models\Booking;
use App\Models\ColorSetting;
use App\Models\Country;
use App\Models\GenerateQrCode;
use App\Models\CustomSlot;
use App\Models\PusherDetail;
use App\Models\Customer;
use App\Models\CustomerActivityLog;
use App\Models\ActivityLog;
use App\Models\MessageDetail;
// use App\Models\TenantLimit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendEmailJob;
use App\Mail\TenantCreated;
use Illuminate\Support\Facades\Mail;
use App\Models\Domain;
use App\Models\Role;
use App\Models\Counter;
use App\Models\NotificationTemplate;
use App\Models\MessageTemplate;
use App\Models\WhatsappTemplate;
use App\Models\LanguageSetting;
use App\Events\{QueueProgress, QueuePending, QueueCreated, BreakEvent, QueueDisplay};
use Carbon\Carbon;
use App\Helpers\AesEncryptionHelper;




class ApiController extends Controller
{


    public function login(Request $request)
    {

        try {
            $request->validate([
                'email' => 'required',
                'password' => 'required|string|min:2',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $key = Str::lower('login:' . $request->email . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 2)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'status' => 'error',
                'message' => "Too many login attempts. Please try again in {$seconds} seconds."
            ], 429);
        }

        // $user = User::with('checkrole')->where('email', $request->email)->first();


        // Check by email or username
        $user = User::with('checkrole')
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhere('username', $request->email);
            })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 20); // 60 seconds until the attempt decays
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        RateLimiter::clear($key); // Clear attempts on successful login

        if (!$user->team_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Team not assigned'
            ], 401);
        }

        $team = Tenant::with('domains')->where('id', $user->team_id)->first();
        $subdomain = optional($team->domains->first())->domain;

        if (!$subdomain) {
            return response()->json(['error' => 'Domain not found'], 404);
        }

        $storageUrl = url('/storage');
        $subdomainUrl = "https://{$subdomain}/storage/";
        $url = "https://{$subdomain}";

        $locations = Location::whereIn('id', $user->locations)
            ->pluck('location_name', 'id')
            ->toArray();

        session(['team_id' => $user->team_id]);

        $sitedetail = SiteDetail::where('team_id', $user->team_id)
            ->select('id', 'business_logo', 'mobile_logo')
            ->first();

        // $colorSettings = ColorSetting::where('team_id', $user->team_id)
        //     ->select('page_layout', 'categories_background_layout', 'text_layout', 'buttons_layout', 'theme_color', 'button_color', 'font_color', 'mobile_page_layout', 'mobile_header_background_color', 'mobile_heading_text_color', 'mobile_category_button_color', 'mobile_button_text_color', 'mobile_button_color', 'mobile_font_color')
        //     ->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Logged-IN',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'storage_url' => $subdomainUrl,
            'url' => $url,
            'user' => $user,
            'locations' => $locations,
            'site' => $sitedetail,
        ]);
    }


    /**
     * select location
     */

    public function setLocation(Request $request)
    {
        $request->validate([
            'teamId' => 'required|integer',
            'locationId' => 'required|integer|exists:locations,id'
        ]);

        $teamId = $request->teamId;
        $locationId = $request->locationId;

        $sitedetail = SiteDetail::where('team_id', $teamId)->where('location_id', $locationId)
            // ->select('id', 'country_code', 'app_heading_first', 'app_heading_second', 'logo_print_ticket', 'business_logo', 'mobile_logo', 'logo_print_ticket', 'logo_footer_ticket_screen', 'category_text_font_size', 'ticket_font_family')
            ->first();

        $colorSettings = ColorSetting::where('team_id', $teamId)->where('location_id', $locationId)
            ->select('page_layout', 'categories_background_layout', 'text_layout', 'buttons_layout', 'theme_color', 'button_color', 'font_color', 'mobile_page_layout', 'mobile_header_background_color', 'mobile_heading_text_color', 'mobile_category_button_color', 'mobile_button_text_color', 'mobile_button_color', 'mobile_font_color')
            ->first();

        $pusherDetail = PusherDetail::where('team_id', $teamId)->where('location_id', $locationId)->first();


        $accountDetails = AccountSetting::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->value('booking_system');

        // Store in session (or use cookies if preferred)
        session(['location_id' => $request->location_id]);
        if (Auth::check()) {
            $user = Auth::user();
            ActivityLog::storeLog($user->team_id, Auth::id(), null, null, ActivityLog::LOGIN,  $locationId, ActivityLog::LOGIN, "Login by API", $user);
        }

        return response()->json([
            'status' => 'success',
            'site' => $sitedetail,
            'colors' => $colorSettings,
            'pusherDetail' => $pusherDetail,
            'bookingSystem' => $accountDetails,
            'message' => 'Location selected successfully'
        ]);
    }

    /**
     * Get categories according to team and selected location
     */

    public function allCategory(Request $request)
    {

        // $request->validate([]);


        $validator = Validator::make($request->all(), [
            'teamId' => 'required',
            'locationId' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $teamId = (int) $request->teamId;
        $locationId = (int) $request->locationId;

        // Fetch the first category
        $categories = Category::getAllCategories($teamId, $locationId);
        $QrCode = GenerateQrCode::where('team_id', $teamId)->where('location_id', $locationId)->first();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No categories found for the given team and location.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'categories' => $categories,
            'qrcode' => $QrCode,
        ]);
    }


    /**
     * Get active form fields according to team id
     */
    public function getFormFields(Request $request)
    {
        try {
            // Validate early to avoid unnecessary processing
            $validator = Validator::make($request->all(), [
                'teamId' => 'required|integer',
                'locationId' => 'required|integer',
                'categoryIds' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            $teamId = $request->teamId;
            $locationId = $request->locationId;
            $categoryIds = $request->categoryIds;

            // Create a single cache key for the entire response
            // This reduces the number of cache operations
            $mainCacheKey = 'form_fields_response_' . md5(json_encode([$teamId, $locationId, $categoryIds]));

            // Try to get the complete response from cache first
            $cachedResponse = Cache::get($mainCacheKey);
            if ($cachedResponse !== null) {
                return response()->json($cachedResponse);
            }

            // If not in cache, proceed with processing
            $fieldCacheKey = 'category_field_ids_' . md5(json_encode($categoryIds));

            // Extend cache time to 10 minutes or adjust based on your data update frequency
            $fieldsId = Cache::remember($fieldCacheKey, now()->addMinute(), function () use ($categoryIds) {
                return DB::table('category_form_field')
                    ->whereIn('category_id', $categoryIds)
                    ->distinct()
                    ->pluck('form_field_id');
            });

            $response = [];
            if ($fieldsId->isEmpty()) {
                $response = [
                    'status' => 'success',
                    'dynamicForm' => [],
                    'enablefields' => 0,
                ];

                // Cache empty results too
                Cache::put($mainCacheKey, $response, now()->addMinute());
                return response()->json($response);
            }

            $dynamicForm = Cache::remember(
                'form_fields_details_' . md5(json_encode([$teamId, $locationId, $fieldsId])),
                now()->addMinute(),
                function () use ($teamId, $locationId, $fieldsId) {
                    return FormField::getFields($teamId, false, $locationId, $fieldsId->toArray());
                }
            );

            $response = [
                'status' => 'success',
                'dynamicForm' => $dynamicForm,
                'enablefields' => count($fieldsId),
            ];

            // Cache the complete response
            Cache::put($mainCacheKey, $response, now()->addMinute());
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Form Field Error: ' . $e->getMessage(), [
                'teamId' => $request->teamId ?? null,
                'locationId' => $request->locationId ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * store data in queue and queuestore table and Ticket generate
     */

    public function storeQueue(Request $request)
    {

        // return $request->all();
        try {
            $validator = Validator::make($request->all(), [
                'dynamicProperties' => 'array',
                'selectedCategoryId' => 'required|integer|exists:categories,id', // Ensure the selected category ID exists in the 'categories' table
                'location' => 'required|integer|exists:locations,id', // Ensure the location exists in the 'locations' table
                'teamId' => 'required|integer', // Ensure the team exists in the 'teams' table
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            $assigned_staff_id = null;
            $dynamicProperties = $request->input('dynamicProperties', []);
            $selectedCategoryId = (int)$request->input('selectedCategoryId');
            $secondChildId = !empty($request->input('secondChildId')) ? (int)$request->input('secondChildId') : null;
            $thirdChildId = !empty($request->input('thirdChildId')) ? (int)$request->input('thirdChildId') : null;
            $location = $request->input('location');
            $teamId = $request->input('teamId');

            // Get or cache account setting
            $accountSetting = AccountSetting::where('team_id', $teamId)
                ->where('location_id', $location)
                ->where('slot_type', AccountSetting::TICKET_SLOT)
                ->select('id', 'booking_system')
                ->first();

            // Get or cache site details
            $siteDetails = SiteDetail::where('team_id', $teamId)
                ->where('location_id', $location)
                ->select(
                    'id',
                    'token_digit',
                    'token_start',
                    'category_estimated_time',
                    'category_level_est',
                    'is_qrcode_ticket',
                    'is_logo_on_print',
                    'is_name_on_print',
                    'is_arrived_on_print',
                    'is_location_on_print',
                    'is_category_on_print',
                    'ticket_text_enable',
                    'is_token_on_print',
                    'print_name_label',
                    'print_token_label',
                    'arrived_time_label',
                    'estimate_time',
                    'ticket_text_2',
                    'ticket_text',
                    'confirm_btn_label',
                    'logo_print_ticket',
                    'use_staff_priority',
                    'team_id',
                    'location_id',
                    'select_timezone',
                    'is_ticket_limit_enabled',
                    'ticket_limit'
                )
                ->first();


            if ($siteDetails->select_timezone) {
                Config::set('app.timezone', $siteDetails->select_timezone);
                date_default_timezone_set($siteDetails->select_timezone);
            }

            $checkTicketLimit = $this->checkTicketLimit($teamId, $location, $siteDetails);

            if ($checkTicketLimit) {
                return response()->json([
                    'error' => 'Unable to process your request. The ticket creation limit exceeded daily limit that is ' . $siteDetails->ticket_limit
                ], 500);
            }

            //   $limitCheck = TenantLimit::checkTicketLimit($teamId);

             $levels = Level::where('team_id',$teamId)
            ->where('location_id',$location)
            ->whereIn('level', [1, 2, 3])
            ->get()
            ->keyBy('level');

           $acronym_level = $levels[1]->acronyms_show_level ?? 1;
              $limitCheck =false;

            if($limitCheck)
            {
              return response()->json([
                    'error' => 'Unable to process your request. The ticket creation limit exceeded daily limit'
                ], 500);


            }

            $enablePriority = $siteDetails->use_staff_priority ?? false;
            $formattedFields = [];
            foreach ($dynamicProperties as $key => $value) {
                $fieldName = preg_replace('/_\d+/', '', $key);
                $fieldName = strtolower($fieldName);
                $formattedFields[$fieldName] = $value;
            }

            $name = $formattedFields['name'] ?? null;
            $phone = $formattedFields['phone'] ?? null;
            $phone_code = $formattedFields['phone_code'] ?? null;
            $email = $formattedFields['email'] ?? ($formattedFields['Email'] ?? ($formattedFields['email_address'] ?? null));
            $jsonDynamicData = json_encode($formattedFields);

            $bookingSetting = $accountSetting->booking_system ?? SiteDetail::STATUS_YES;

            // if (!empty($selectedCategoryId)) {
            //     $acronym = Category::viewAcronym($selectedCategoryId);
            // } else {
            //     $acronym = SiteDetail::DEFAULT_WALKIN_A;
            // }

               if((int)$acronym_level == 1 && !empty($selectedCategoryId)){
                $acronym = Category::viewAcronym($selectedCategoryId);
            }elseif((int)$acronym_level == 2 && !empty($secondChildId)){
                $acronym = Category::viewAcronym($secondChildId);
            }elseif((int)$acronym_level == 3 && !empty($thirdChildId)){
                $acronym = Category::viewAcronym($thirdChildId);
            }else {
                $acronym = SiteDetail::DEFAULT_WALKIN_A;
            }

              if(!empty($selectedCategoryId)){
                $ticket_note_level_first = Category::viewTicketNote($selectedCategoryId) ?? '';
            }
            if(!empty($secondChildId)){
                $ticket_note_level_second = Category::viewTicketNote($secondChildId) ?? '';
            }
            if(!empty($thirdChildId)){
                $ticket_note_level_third = Category::viewTicketNote($thirdChildId) ?? '';
            }


            $lastToken = Queue::getLastToken($teamId, $acronym, $location);

            $tokenDigit = $siteDetails?->token_digit ?? 4;
            $isExistToken = true;

            while ($isExistToken) {
                $newToken = Queue::newGeneratedToken($lastToken, $siteDetails?->token_start, $tokenDigit);
                if (strlen($newToken) > $tokenDigit) {
                    return response()->json(['status' => 'error', 'message' => 'Unable to create more tickets'], 400);
                }

                $isExistToken = Queue::checkToken($teamId, $acronym, $newToken, $location);

                if ($isExistToken) {
                    $lastToken = $newToken;
                } else {
                    $tokenStart = $newToken;
                    $isExistToken = false;
                }
            }

            $nextPrioritySort = QueueStorage::getNextPrioritySort($selectedCategoryId, $teamId, $location);

            if ($enablePriority) {
                // $assigned_staff_id = $this->getNextAgent($siteDetails);
                $assigned_staff_id = User::getNextAgent($teamId, $location);
                if (empty($assigned_staff_id)) {

                    Log::error('Staff is not Available assigned_staff_id');
                    return response()->json(['error' => 'Staff is not Available'], 422);
                }
            }


            $timezone = config('app.timezone');
            // return response()->json(["status"=> "success",'nextPrioritySort' => $nextPrioritySort]);
            $todayDateTime = Carbon::now($timezone);


            $lastcategory = $selectedCategoryId;

                if(!empty($thirdChildId)){
                    $lastcategory = $thirdChildId;
                }elseif(!empty($secondChildId)){
                  $lastcategory = $secondChildId;
                }

            $storeData = [
                'name' => $name,
                'phone' => $phone,
                'phone_code' => $phone_code,
                'category_id' => $selectedCategoryId,
                'sub_category_id' => $secondChildId,
                'child_category_id' => $thirdChildId,
                'team_id' => $teamId,
                'token' => $tokenStart,
                'token_with_acronym' => $bookingSetting == Queue::STATUS_NO ? Queue::LABEL_YES : Queue::LABEL_NO,
                'json' => $jsonDynamicData,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $acronym,
                'locations_id' => $location,
                'priority_sort' => (int)$nextPrioritySort,
                'mode' => 'api'
            ];

            $queueCreated = Queue::storeQueue([
                'team_id' => $teamId,
                'token' => $tokenStart,
                'start_acronym' => $acronym,
                'token_with_acronym' => $storeData['token_with_acronym'],
                'locations_id' => $location,
                'arrives_time' => $todayDateTime,
                'last_category' => $lastcategory,
            ]);

            $queueStorage = QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));

            QueueCreated::dispatch($queueStorage);

            $fieldCatName = 'category_id';
            $countCatID =  $selectedCategoryId;

            if ($siteDetails?->category_estimated_time == SiteDetail::STATUS_YES) {
                if (!empty($thirdChildId)) {
                    if ($siteDetails?->category_level_est == 'automatic') {

                        $fieldCatName = 'child_category_id';
                        $countCatID =  $thirdChildId;
                    } elseif ($siteDetails?->category_level_est == 'child') {
                        $fieldCatName = 'sub_category_id';
                        $countCatID =  $secondChildId;
                    } else {
                        $fieldCatName = 'category_id';
                        $countCatID =  $selectedCategoryId;
                    }
                } else if (!empty($secondChildId)) {

                    if ($siteDetails?->category_level_est == 'child') {
                        $fieldCatName = 'sub_category_id';
                        $countCatID =  $secondChildId;
                    } else {
                        $fieldCatName = 'category_id';
                        $countCatID =  $selectedCategoryId;
                    }
                } else {
                    $fieldCatName = 'category_id';
                    $countCatID =  $selectedCategoryId;
                }
            }


            if ($siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO)
                $counterID  = 0;


            $pendingwaiting = $pendingCount = 0;


            if ($siteDetails->category_estimated_time == SiteDetail::STATUS_YES) {


                $estimatedetail = QueueStorage::countPendingByCategory($teamId, $queueStorage->id, $countCatID, $fieldCatName, '', $location);
                if ($estimatedetail == false) {
                    $pendingCount = QueueStorage::countPending($teamId, $queueStorage->id, $countCatID, $fieldCatName, '', $location);
                } else {
                    $pendingCount = $estimatedetail['customers_before_me'] ?? 0;
                    $pendingwaiting = $estimatedetail['estimated_wait_time'] ?? 0;
                    if ($enablePriority == false) {
                        if (!empty($estimatedetail['assigned_staff_id'])) {
                            $assigned_staff_id = $estimatedetail['assigned_staff_id'];
                        }
                    }
                }
            } else {

                // $pendingCount = QueueStorage::countPending($teamId, $queueStorage->id, $countCatID,  $fieldCatName, '', $location);
                $pendingCountget = (int)QueueStorage::countPending($teamId, $queueStorage->id, '', '', '', $location);
                $counterCount = Counter::where('team_id', $teamId)->whereJsonContains('counter_locations', "$location")->where('show_checkbox', 1)->count();
                if ((int)$pendingCountget > 0 && (int)$counterCount > 0) {
                    $pendingCount = floor((int)$pendingCountget / (int)$counterCount);
                }
            }


            // $pendingCount = QueueStorage::countPending($teamId, $queueStorage->id, $countCatID,  $fieldCatName, '', $location);
            $dateformat = AccountSetting::showDateTimeFormat();
            $thirdCategoryName = $secondCategoryName = $categoryName = $locationName = '';

            // Step 1: Collect all relevant category IDs
            $categoryIds = array_filter([
                $selectedCategoryId ?? null,
                $secondChildId ?? null,
                $thirdChildId ?? null,
            ]);

            // Step 2: Fetch all category names in one query
            $categories = Category::whereIn('id', $categoryIds)->pluck('name', 'id');

            // Step 3: Assign category names using the plucked data
            $categoryName = $selectedCategoryId ? ($categories[$selectedCategoryId] ?? '') : '';
            $secondCategoryName = $secondChildId ? ($categories[$secondChildId] ?? '') : '';
            $thirdCategoryName = $thirdChildId ? ($categories[$thirdChildId] ?? '') : '';

            // Step 4: Fetch location name if location ID is provided
            $locationName = '';
            if (!empty($location)) {
                $locationRecord = Location::find($location);
                $locationName = $locationRecord?->location_name ?? '';
            }

            $data = [
                'team_id' => $teamId,
                'locations_id' => $location,
                'name' => $queueStorage->name,
                'phone' => $queueStorage->phone,
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueCreated->id,
                'nextStorageId' => $queueStorage->id,
                'arrives_time' => $todayDateTime->format($dateformat),
                'category_name' => $categoryName,
                'secondC_name' => $secondCategoryName,
                'thirdC_name' => $thirdCategoryName,
                'pending_count' => $pendingCount,
                'token' => $queueCreated->token,
                'token_with_acronym' => $queueCreated->start_acronym,
                'location_name' => $locationName,
                'to_mail' => $email ?? '',

            ];

            $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $teamId, $location);

            $waitingTime = 0;
            if (!empty($siteDetails)) {

                $showQrcode = $siteDetails->is_qrcode_ticket == 1 ? true : false;
                $showlogo = $siteDetails->is_logo_on_print == 1 ? true : false;
                $showusername = $siteDetails->is_name_on_print == 1 ? true : false;
                $showarrived = $siteDetails->is_arrived_on_print == 1 ? true : false;
                $showlocation = $siteDetails->is_location_on_print == 1 ? true : false;
                $showcategory = $siteDetails->is_category_on_print == 1 ? true : false;
                $showTextmessage = $siteDetails->ticket_text_enable == 1 ? true : false;
                $showToken = $siteDetails->is_token_on_print == 1 ? true : false;

                $nameLabel = $siteDetails->print_name_label ?? 'Name';
                $tokenLabel = $siteDetails->print_token_label ?? 'Token';
                $arrivedLabel = $siteDetails->arrived_time_label ?? 'Arrived';

                $baseencodeQueueId = base64_encode($queueCreated->id);
                $customUrl = url("/visits/{$baseencodeQueueId}");
                $qrcodeSvg = QrCode::format('svg')
                    ->size(150)
                    ->errorCorrection('H')
                    ->generate($customUrl);


                $estimate_time = $siteDetails->estimate_time ?? 0;

                if ($siteDetails->category_estimated_time == SiteDetail::STATUS_YES) { // get esitmate time of category wise
                    $waitingTime =  $pendingwaiting ?? $estimate_time * $data['pending_count'];
                } else {  // get esitmate time of globally set
                    $waitingTime =  $estimate_time * $data['pending_count'];
                }

                if ($siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {

                    // $waitingTime =  $estimate_time * $data['pending_count'];

                    if (!empty($siteDetails->ticket_text_2))
                        $showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $siteDetails->ticket_text_2);

                    if (!empty($siteDetails->ticket_text)) {
                        $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $siteDetails->ticket_text);
                        $showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                    }
                }
            }


            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->served_by = $assigned_staff_id ?? null;
            $queueStorage->assign_staff_id = $assigned_staff_id ?? null;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();

            //store customer data and activity log
            if (!empty($queueStorage->phone)) {
                $existingCustomer = Customer::where('phone', "$queueStorage->phone")
                    ->where('team_id', $queueStorage->team_id)
                    ->where('location_id', $queueStorage->locations_id)
                    ->first();

                // Create customer if not exists
                if (empty($existingCustomer)) {
                    $existingCustomer = Customer::create([
                        'team_id' => $queueStorage->team_id,
                        'location_id' => $queueStorage->locations_id,
                        'name' => $queueStorage->name ?? null,
                        'phone' => $queueStorage->phone,
                        'json_data' => $jsonDynamicData, // casted automatically to JSON
                    ]);
                }

                // Log customer activity with type 'queue'
                CustomerActivityLog::create([
                    'team_id' => $queueStorage->team_id,
                    'location_id' => $queueStorage->locations_id,
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

            $logData = [
                'team_id' => $queueStorage->team_id,
                'location_id' => $queueStorage->locations_id,
                'user_id' => $queueStorage->served_by,
                'customer_id' => $queueStorage->created_by,
                'queue_id' => $queueStorage->queue_id,
                'queue_storage_id' => $queueStorage->id,
                'email' => $email ?? '',
                'contact' => $queueStorage->phone ?? '',
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Ticket Generate',
            ];

            // $this->sendNotification($data, 'ticket created', $logData);

             $htmldata = [
            'showlogo'        => $showlogo,
            'logo'            => $logo,
            'showusername'    => $showusername,
            'showToken'       => $showToken,
            'showarrived'     => $showarrived,
            'showlocation'    => $showlocation,
            'showcategory'    => $showcategory,
            'showTextmessage' => $showTextmessage,
            'showQrcode'      => $showQrcode,
            'nameLabel'       => $nameLabel,
            'tokenLabel'      => $tokenLabel,
            'arrivedLabel'    => $arrivedLabel,
            'acronym'         => $acronym,
            'showTicketText'  => $showTicketText,
            'showTicketText_2'=> $showTicketText_2,
            'qrcodeSvg'       => $qrcodeSvg,
            'confirm_btn_label' => $siteDetails->confirm_btn_label ?? 'Thank you',
        ];

                // merge with original data
                $mergedData = array_merge($data, $htmldata);

            $ticket = [
                'timer' => 8000,
                'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                    ($showlogo ? '<img src="' . $logo . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                    '</div>
                <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                    ' . ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                    ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $acronym . $data['token'] . '</strong></h3></div>' : '') . '
                    ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
                    ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                    ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . $data['category_name'] . '</h3><h3 style="font-size:16px;margin:0">' . $data['secondC_name'] . '</h3><h3 style="font-size:16px;">' . $data['thirdC_name'] . '</h3></div>' : '') . '
                    ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $showTicketText_2 . '</h4></div>' : '') . '
                    ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                </div>',
                'confirmButtonText' => $siteDetails->confirm_btn_label ?? 'Thank you',
                'token_notify' => 'The Generated Token Number is ' . $acronym . $data['token']
            ];

            return response()->json(["status" => "success", 'message' => 'Queue created successfully', 'data' => $mergedData, 'ticket' => $ticket], 200);
        } catch (\Throwable $ex) {
            Log::error('Error storing queue data: ' . $ex->getMessage());
            return response()->json(['error' => 'Unable to process your request-'.$ex->getMessage()], 500);
        }
    }


    // public function sendNotification($data, $type, $logData = null)
    // {

    //     if (isset($data['to_mail']) && $data['to_mail'] != '') {
    //         SmtpDetails::sendMail($data, $type, '',  $data['team_id']);
    //         if (!empty($logData)) {
    //             $logData['channel'] = 'email';
    //             $logData['status'] = MessageDetail::SENT_STATUS;
    //             MessageDetail::storeLog($logData);
    //         }
    //     }
    //     if (!empty($data['phone'])) {
    //         SmsAPI::sendSms($data['team_id'], $data, $type, $type, $logData);
    //     }
    // }

    public function sendNotification($data, $type, $logData = [])
    {
        $data['location_id'] = $data['locations_id'];
        if (isset($data['to_mail']) && $data['to_mail'] != '') {
            $logData['channel'] = 'email';
            $logData['status'] = MessageDetail::SENT_STATUS;
            SmtpDetails::sendMail($data, $type, 'ticket-created', $data['team_id'], $logData);
        }

        if (!empty($data['phone'])) {

            SmsAPI::sendSms($data['team_id'], $data, $type, $type, $logData);
        }
    }


    /**
     * Display screen list
     *
     */

    public function displayscreen(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'teamId' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            $teamId = (int) $request->teamId;
            $locationId = (int) $request->locationId;

            $screens = ScreenTemplate::where(['team_id' => $teamId])->where('location_id', $locationId)->get();

            $pusherDetails = PusherDetail::viewPusherDetails($teamId,$locationId);
            $pusherKey = $pusherDetails->key ?? env('PUSHER_APP_KEY');
            $pusherSecret = $pusherDetails->secret ?? env('PUSHER_APP_SECRET');
            $pusherAppId = $pusherDetails->app_id ?? env('PUSHER_APP_ID');
            $pusherCluster = $pusherDetails->options_cluster ?? env('PUSHER_APP_CLUSTER');

            $pusher['pusher_api_key'] = $pusherKey;
            $pusher['pusher_app_secret'] = $pusherSecret;
            $pusher['pusher_app_id'] = $pusherAppId;
            $pusher['pusher_cluster'] = $pusherCluster;

            if (!$screens) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'No Screen found',
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => $screens,
                'pusher_details' => $pusher
            ], 200);
        } catch (\Throwable $ex) {
            // Handle any other exceptions
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

     /*
     *Display screen data shows
     */

    public function getDisplayScreen(Request $request)
    {
        try {

            $validator = validator($request->all(), [
                'templateId' => 'required|exists:screen_templates,id',
                'locationId' => 'required|exists:locations,id',
                'teamId' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            $templateId = (int) $request->templateId;
            $teamId = (int) $request->teamId;
            $locationId = (int)$request->locationId;
            $selectedTemplate = ScreenTemplate::viewDetails($teamId, $templateId, $locationId);

            if (!$selectedTemplate) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Display template not found',
                ], 404);
            }

            $displaySetting = DisplaySettingModel::getDetails($teamId, $locationId);
            $locationName = Location::locationName($locationId);

            $counterIDs = $selectedTemplate?->counters?->pluck('id')?->toArray() ?? [];
            $categoryID = $selectedTemplate?->categories?->pluck('id')?->toArray() ?? [];


            if ($selectedTemplate->type == "Counter") {
                $queueToDisplay = Queue::displayQueueApi($teamId, (int)$locationId, $selectedTemplate->show_queue_number, $counterIDs);
            } else {
                $queueToDisplay = Queue::displayQueueApi($teamId, (int)$locationId, $selectedTemplate->show_queue_number, null, $categoryID);
            }
            $missedCalls = Queue::getMissedCallId(['team_id' => $teamId], Queue::STATUS_NO, (int)$locationId);
            $holdCalls = QueueStorage::getHoldCall(['team_id' => $teamId], 0, (int)$locationId);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'template' => $selectedTemplate,
                    'settings' => $displaySetting,
                    'queues' => $queueToDisplay,
                    'missed_queues' => $missedCalls,
                    'hold_queues' => $holdCalls,
                    'location_name' => $locationName,
                ],
            ], 200);
        } catch (\Throwable $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    /***
     *Convert Booking to Queue
     *
     */

    public function convertToQueue(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'location' => 'required|integer|exists:locations,id', // Ensure the location exists in the 'locations' table
                'teamId' => 'required|integer', // Ensure the team exists in the 'teams' table
                'refId' => 'required', // Ensure the team exists in the 'teams' table
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            $location = $request->input('location');
            $refId = $request->input('refId');
            $teamId = $request->input('teamId');

            $booking = Booking::where('refID', $refId)
                ->where('location_id', $location)
                ->whereDate('booking_date', date('Y-m-d'))
                ->where('is_convert', Booking::STATUS_NO)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->first();


            if (empty($booking)) {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'No Booking Found',
                ], 404);
            }

            if ($booking->status == Booking::STATUS_PENDING) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking is not Confirmed yet',
                ], 401);
            }

            $bookingDate = Carbon::parse($booking->booking_date)->startOfDay();
            $currentDate = Carbon::now()->startOfDay();
            $readableDate = $bookingDate->format('F j, Y'); // Example: July 22, 2024
            $assigned_staff_id = is_numeric($booking->staff_id) ? (int)$booking->staff_id : null;
            if ($bookingDate->lt($currentDate)) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking date is in the past on ' . $readableDate,
                ], 401);
            } elseif ($bookingDate->gt($currentDate)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking is for a future date on ' . $readableDate,
                ], 401);
            }


            $isAsQueue = QueueStorage::isBookExist($booking->id);

            if ($isAsQueue) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Not found! Already converted',
                ], 401);
            }

            $dynamicProperties = !empty($booking->json) ? json_decode($booking->json, true) : [];
            $selectedCategoryId = $booking->category_id;
            $secondChildId = !empty($booking->sub_category_id) ? (int)$booking->sub_category_id : null;
            $thirdChildId = !empty($booking->child_category_id) ? (int)$booking->child_category_id : null;


            $accountDetails = AccountSetting::where('team_id', $teamId)
                ->where('location_id', $location)
                ->where('slot_type', AccountSetting::BOOKING_SLOT)
                ->select('id', 'booking_system')
                ->first();

            $siteDetails = SiteDetail::where('team_id', $teamId)->where('location_id', $location)
                ->select('id', 'token_digit', 'token_start', 'category_estimated_time', 'category_level_est', 'is_qrcode_ticket', 'is_logo_on_print', 'is_name_on_print', 'is_arrived_on_print', 'is_location_on_print', 'is_category_on_print', 'ticket_text_enable', 'is_token_on_print', 'print_name_label', 'print_token_label', 'arrived_time_label', 'estimate_time', 'ticket_text_2', 'ticket_text', 'confirm_btn_label', 'logo_print_ticket', 'use_staff_priority', 'team_id', 'location_id', 'select_timezone', 'is_ticket_limit_enabled', 'ticket_limit')
                ->first();

            $checkTicketLimit = $this->checkTicketLimit($teamId, $location, $siteDetails);

            if ($checkTicketLimit) {
                return response()->json([
                    'error' => 'Unable to process your request. The ticket creation limit exceeded daily limit that is ' . $siteDetails->ticket_limit
                ], 500);
            }

            //  $limitCheck = TenantLimit::checkTicketLimit($teamId);


            // if($limitCheck)
            // {
            //   return response()->json([
            //         'error' => 'Unable to process your request. The ticket creation limit exceeded daily limit'
            //     ], 500);


            // }


            $formattedFields = [];
            $enablePriority = $siteDetails->use_staff_priority ?? false;
            foreach ($dynamicProperties as $key => $value) {
                $fieldName = preg_replace('/_\d+/', '', $key);
                $fieldName = strtolower($fieldName);
                $formattedFields[$fieldName] = $value;
            }

            $name = $formattedFields['name'] ?? null;
            $phone = $formattedFields['phone'] ?? null;
            $phone_code = $formattedFields['phone_code'] ?? null;
            $email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['Email']) ? $formattedFields['Email'] : null);
            $jsonDynamicData = $booking->json ?? '';

            $bookingSetting = $accountDetails->booking_system ?? SiteDetail::STATUS_YES;


            // $acronym = ($bookingSetting == Queue::STATUS_NO)
            //     ? Category::viewAcronym($selectedCategoryId)
            //     : SiteDetail::DEFAULT_WALKIN_A;

            if (!empty($selectedCategoryId)) {
                $acronym = Category::viewAcronym($selectedCategoryId);
            } else {
                $acronym = SiteDetail::DEFAULT_WALKIN_A;
            }


            $lastToken = QueueStorage::getLastToken($teamId, $acronym, $location);

            $tokenDigit = $siteDetails?->token_digit ?? 4;
            $isExistToken = true;

            while ($isExistToken) {
                $newToken = QueueStorage::newGeneratedToken($lastToken, $siteDetails?->token_start, $tokenDigit);
                if (strlen($newToken) > $tokenDigit) {
                    return response()->json(['error' => 'Unable to create more tickets'], 400);
                }

                $isExistToken = Queue::checkToken($teamId, $acronym, $newToken, $location);

                if ($isExistToken) {
                    $lastToken = $newToken;
                } else {
                    $tokenStart = $newToken;
                    $isExistToken = false;
                }
            }

            $nextPrioritySort = QueueStorage::getNextPrioritySort($selectedCategoryId, $teamId, $location);


            if ($enablePriority) {
                $assigned_staff_id = $this->getNextAgent($siteDetails);
                if (empty($assigned_staff_id)) {

                    Log::error('Staff is not Available assigned_staff_id');
                    return response()->json(['error' => 'Staff is not Available'], 422);
                }
            }

            $todayDateTime = Carbon::now();


                $lastcategory = $selectedCategoryId;

                if(!empty($thirdChildId)){
                    $lastcategory = $thirdChildId;
                }elseif(!empty($secondChildId)){
                  $lastcategory = $secondChildId;
                }

            $storeData = [
                'name' => $name,
                'phone' => $phone,
                'phone_code' => $phone_code,
                'category_id' => $selectedCategoryId,
                'sub_category_id' => $secondChildId,
                'child_category_id' => $thirdChildId,
                'team_id' => $teamId,
                'token' => $tokenStart,
                'token_with_acronym' => $bookingSetting == Queue::STATUS_NO ? Queue::LABEL_YES : Queue::LABEL_NO,
                'json' => $jsonDynamicData,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $acronym,
                'locations_id' => $location,
                'priority_sort' => (int)$nextPrioritySort,
                'booking_id' => $booking->id,
                'served_by' =>  $assigned_staff_id,
                'assign_staff_id' =>  $assigned_staff_id,
                'campaign_id' => is_numeric($booking->campaign_id) ? (int)$booking->campaign_id : null
            ];



            $queueCreated = Queue::storeQueue([
                'team_id' => $teamId,
                'token' => $tokenStart,
                'start_acronym' => $acronym,
                'token_with_acronym' => $storeData['token_with_acronym'],
                'locations_id' => $location,
                'arrives_time' => $todayDateTime,
                'last_category' => $lastcategory,
            ]);

            $queueStorage = QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));

            //upddate booking columns table
            $booking->is_convert = Booking::STATUS_YES;
            $booking->status = Booking::STATUS_CONFIRMED;
            $booking->convert_datetime = $todayDateTime;
            $booking->save();

            QueueCreated::dispatch($queueStorage);

            $fieldCatName = 'category_id';
            $countCatID =  $selectedCategoryId;

            if ($siteDetails?->category_estimated_time == SiteDetail::STATUS_YES) {
                if (!empty($thirdChildId)) {
                    if ($siteDetails?->category_level_est == 'automatic') {

                        $fieldCatName = 'child_category_id';
                        $countCatID =  $thirdChildId;
                    } elseif ($siteDetails?->category_level_est == 'child') {
                        $fieldCatName = 'sub_category_id';
                        $countCatID =  $secondChildId;
                    } else {
                        $fieldCatName = 'category_id';
                        $countCatID =  $selectedCategoryId;
                    }
                } else if (!empty($secondChildId)) {

                    if ($siteDetails?->category_level_est == 'child') {
                        $fieldCatName = 'sub_category_id';
                        $countCatID =  $secondChildId;
                    } else {
                        $fieldCatName = 'category_id';
                        $countCatID =  $selectedCategoryId;
                    }
                } else {
                    $fieldCatName = 'category_id';
                    $countCatID =  $selectedCategoryId;
                }
            }


            if ($siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO)
                $counterID  = 0;

            $pendingCount = QueueStorage::countPending($teamId, $queueStorage->id, $countCatID,  $fieldCatName, '', $location);

            $thirdCategoryName = $secondCategoryName = $categoryName = $locationName = '';

            // Step 1: Collect all relevant category IDs
            $categoryIds = array_filter([
                $selectedCategoryId ?? null,
                $secondChildId ?? null,
                $thirdChildId ?? null,
            ]);

            // Step 2: Fetch all category names in one query
            $categories = Category::whereIn('id', $categoryIds)->pluck('name', 'id');

            // Step 3: Assign category names using the plucked data
            $categoryName = $selectedCategoryId ? ($categories[$selectedCategoryId] ?? '') : '';
            $secondCategoryName = $secondChildId ? ($categories[$secondChildId] ?? '') : '';
            $thirdCategoryName = $thirdChildId ? ($categories[$thirdChildId] ?? '') : '';

            // Step 4: Fetch location name if location ID is provided
            $locationName = '';
            if (!empty($location)) {
                $locationRecord = Location::find($location);
                $locationName = $locationRecord?->location_name ?? '';
            }

            $data = [
                'name' => $queueStorage->name,
                'phone' => $queueStorage->phone,
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueCreated->id,
                'arrives_time' => $todayDateTime->format(AccountSetting::showDateTimeFormat()),
                'category_name' => $categoryName,
                'secondC_name' => $secondCategoryName,
                'thirdC_name' => $thirdCategoryName,
                'pending_count' => $pendingCount,
                'token' => $queueCreated->token,
                'token_with_acronym' => $queueCreated->start_acronym,
                'location_name' => $locationName,
                'locations_id' => $location,
                'team_id' => $teamId,
                'to_mail' => $email ?? '',
                'booking_id' => $booking->id,
                'priority_sort' => $nextPrioritySort,

            ];

            $Ticketlogo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $teamId, $location);

            $logo = isset($Ticketlogo)  ? url($Ticketlogo) : '';

            $waitingTime = 0;
            if (!empty($siteDetails)) {
                $estimate_time = $siteDetails->estimate_time ?? 0;

                if ($siteDetails->category_estimated_time == SiteDetail::STATUS_YES) { // get esitmate time of category wise
                    $waitingTime =  $pendingwaiting ?? $estimate_time * $data['pending_count'];
                } else {  // get esitmate time of globally set
                    $waitingTime =  $estimate_time * $data['pending_count'];
                }

                if ($siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {

                    if (!empty($this->siteDetails->ticket_text_2))
                        $showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $siteDetails->ticket_text_2);

                    if (!empty($siteDetails->ticket_text)) {
                        $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $siteDetails->ticket_text);
                        $showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                    }
                }
            }

            if (empty($booking->created_by)) {
                if (!empty($queueStorage->phone)) {
                    $existingCustomer = Customer::where('phone', $queueStorage->phone)
                        ->where('team_id', $teamId)
                        ->where('location_id', $booking->location_id)
                        ->first();

                    // Create customer if not exists
                    if (!$existingCustomer) {
                        $existingCustomer = Customer::create([
                            'team_id' => $teamId,
                            'location_id' => $booking->location_id,
                            'name' => $this->name ?? null,
                            'phone' => $queueStorage->phone,
                            'json_data' => $jsonDynamicData, // casted automatically to JSON
                        ]);
                    }

                    // Log customer activity with type 'queue'
                    CustomerActivityLog::create([
                        'team_id' => $teamId,
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
            } else {
                $queueStorage->created_by = $booking->created_by;
                $queueStorage->save();
                $data['customer_id'] = $booking->created_by;
            }

            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->served_by = $assigned_staff_id;
            $queueStorage->assign_staff_id = $assigned_staff_id;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();

            //  SendEmailJob::dispatch($data, 'ticket created');

            $showQrcode = $siteDetails->is_qrcode_ticket == 1 ? true : false;
            $showlogo = $siteDetails->is_logo_on_print == 1 ? true : false;
            $showusername = $siteDetails->is_name_on_print == 1 ? true : false;
            $showarrived = $siteDetails->is_arrived_on_print == 1 ? true : false;
            $showlocation = $siteDetails->is_location_on_print == 1 ? true : false;
            $showcategory = $siteDetails->is_category_on_print == 1 ? true : false;
            $showTextmessage = $siteDetails->ticket_text_enable == 1 ? true : false;
            $showToken = $siteDetails->is_token_on_print == 1 ? true : false;

            $nameLabel = $siteDetails->print_name_label ?? 'Name';
            $tokenLabel = $siteDetails->print_token_label ?? 'Token';
            $arrivedLabel = $siteDetails->arrived_time_label ?? 'Arrived';

            $baseencodeQueueId = base64_encode($queueCreated->id);
            $customUrl = url("/visits/{$baseencodeQueueId}");
            $qrcodeSvg = QrCode::format('svg')
                ->size(150)
                ->errorCorrection('H')
                ->generate($customUrl);
            $data = array_merge($data, ['waiting_time' => $waitingTime, 'ticket_link' => $customUrl]);

            $logData = [
                'team_id' => $teamId,
                'location_id' => $location,
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


            // Send notifications
            // Notification::send(User::adminUserDetail($teamId), new \App\Notifications\QueueCreatedNotification($data));

            $ticket = [
                'timer' => 8000,
                'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                    ($showlogo ? '<img src="' . $logo . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                    '</div>
                <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                    ' . ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                    ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $acronym . $data['token'] . '</strong></h3></div>' : '') . '
                    ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
                    ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                    ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . $data['category_name'] . '</h3><h3 style="font-size:16px;margin:0">' . $data['secondC_name'] . '</h3><h3 style="font-size:16px;">' . $data['thirdC_name'] . '</h3></div>' : '') . '
                    ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . (isset($showTicketText_2) ? $showTicketText_2 : '') . '</h4></div>' : '') . '
                    ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                </div>',
                'confirmButtonText' => $siteDetails->confirm_btn_label ?? 'Thank you',
                'token_notify' => 'The Generated Token Number is ' . $acronym . $data['token']
            ];

            return response()->json(["status" => "success", 'message' => 'Queue created successfully', 'data' => $data, 'ticket' => $ticket], 200);
        } catch (\Throwable $ex) {
            Log::error('Error storing queue data: ' . $ex->getMessage());
            return response()->json(['error' => 'Unable to process your request'], 500);
        }
    }

    public function getCountries()
    {

        try {
            $countries = Country::all();

            return response()->json([
                'status' => 'success',
                'countries' => $countries,
            ]);
        } catch (\Throwable $ex) {
            Log::error('Error getting countries: ' . $ex->getMessage());
            return response()->json(['error' => 'Unable to process your request'], 500);
        }
    }



    public function appointmentPage(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'teamId' => 'required|integer',
                'location' => 'required|integer|exists:locations,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            $appointmentScreen = [];

            $siteDetails = SiteDetail::where('team_id', $request->teamId)->where('location_id', $request->location)->select('app_heading_third')->first()->toArray();

            $accountSettings = AccountSetting::where('team_id', $request->teamId)->where('location_id', $request->location)->where('slot_type', 'booking')->first()->toArray();

            $response = array_merge(
                ['status' => 'success'],
                $siteDetails ?? [],
                $accountSettings ?? []
            );

            return response()->json($response);
        } catch (\Throwable $ex) {
            Log::error('Error getting countries: ' . $ex->getMessage());
            return response()->json(['error' => 'Unable to process your request', 'fullError' => $ex->getMessage()], 500);
        }
    }


    public function checkTicketBusinessHoursApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teamId' => 'required|integer',
            'location' => 'required|integer|exists:locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $teamId = $request->teamId;
        $locationId = $request->location;

        // Select only needed columns
        $siteData = SiteDetail::select('select_timezone', 'enable_time_slot')
            ->where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();

        if (!$siteData) {
            return response()->json(['status' => false, 'message' => 'Site details not found'], 404);
        }

        if ($siteData->select_timezone) {
            Config::set('app.timezone', $siteData->select_timezone);
            date_default_timezone_set($siteData->select_timezone);
        }

        if ($siteData->enable_time_slot !== 'ticket') {
            return response()->json(['status' => true, 'message' => 'Time slot check not required'], 200);
        }

        // Select only business_hours column
        $accountSetting = AccountSetting::select('business_hours')
            ->where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::TICKET_SLOT)
            ->first();

        if (!$accountSetting || empty($accountSetting->business_hours)) {
            return response()->json(['status' => false, 'message' => 'Business hours not configured'], 400);
        }

        $businessHours = json_decode($accountSetting->business_hours, true);
        $currentTime = Carbon::now();
        $currentDay = $currentTime->format('l');

        $todayConfig = collect($businessHours)->firstWhere('day', $currentDay);

        if (!$todayConfig || $todayConfig['is_closed'] !== 'open') {
            return response()->json(['status' => false, 'message' => 'Service is closed today'], 403);
        }

        $startTime = Carbon::parse($todayConfig['start_time']);
        $endTime = Carbon::parse($todayConfig['end_time']);

        $isWithinMainTime = $currentTime->between($startTime, $endTime);

        $isWithinInterval = collect($todayConfig['day_interval'] ?? [])
            ->contains(function ($interval) use ($currentTime) {
                return $currentTime->between(
                    Carbon::parse($interval['start_time']),
                    Carbon::parse($interval['end_time'])
                );
            });

        if (!$isWithinMainTime && !$isWithinInterval) {
            return response()->json(['status' => false, 'message' => 'Service is not available at this time'], 403);
        }

        return response()->json(['status' => true, 'message' => 'Service is available'], 200);
    }


    public function check(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'teamId' => 'required|integer',
            'location' => 'required|integer|exists:locations,id',
            'selectedCategoryId' => 'required|integer',
            'secondChildId' => 'nullable',
            'thirdChildId' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $teamId = $request->teamId;
        $locationId = $request->location;
        $firstcategory = $request->selectedCategoryId;
        $secondcategory = $request->secondChildId ?? '';
        $thirdcategory = $request->thirdChildId ?? '';


        $siteData = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->select('select_timezone', 'enable_time_slot', 'category_slot_level')
            ->first();

        $ticketAccountData = AccountSetting::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::TICKET_SLOT)
            ->first();

        $timezone = 'Asia/Kolkata';

        if ($siteData && $siteData->select_timezone) {
            Config::set('app.timezone', $siteData->select_timezone);
            $timezone = $siteData->select_timezone ?? 'Asia/Kolkata';
            date_default_timezone_set($siteData->select_timezone);
        }

        $currentDate = Carbon::now($timezone)->format('Y-m-d');
        $currentDay = Carbon::now($timezone)->format('l');
        $currentTime = Carbon::now($timezone)->format('h:i A');

        $checkticketSystem = $ticketAccountData->booking_system == 1 ? true : false;

        $categoryLevelEnable = $siteData?->enable_time_slot;

        $slotLevel = $siteData?->category_slot_level ?? 1;

        $categoryId = match ($slotLevel) {
            1 => $firstcategory,
            2 => $secondcategory,
            3 => $thirdcategory,

            default => null,
        };
        if ($checkticketSystem ==  false) {
            $categoryLevelEnable = AccountSetting::LOCATION_SLOT;
        }
        $slotType = match ($categoryLevelEnable) {
            'category' => AccountSetting::CATEGORY_SLOT,
            'ticket' => AccountSetting::TICKET_SLOT,
            default => AccountSetting::LOCATION_SLOT
        };

        if ($categoryLevelEnable == 'category' && !$categoryId) {
            return response()->json(['status' => false, 'message' => 'Category ID is required.']);
        }

        // Check Limit
        if (!$this->checkLimit($teamId, $locationId, $currentDate, $request)) {
            return response()->json(['status' => false, 'message' => "The service limit is finished on {$currentDay}."]);
        }

        // Time Slot Check
        $slotData = CustomSlot::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slots_type', $slotType)
            ->where('selected_date', $currentDate)
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->select('business_hours')
            ->first();
        // return response()->json(['status' => true, 'slotData' => $slotData]);
        if (!$slotData) {
            $slotData = AccountSetting::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->where('slot_type', $slotType)
                ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
                ->select('business_hours')
                ->first();
        }

        if (!$slotData) {
            return response()->json(['status' => false, 'message' => 'No time slots found.']);
        }

        $businessHours = json_decode($slotData->business_hours);
        $available = $this->checkBusinessHours($businessHours, $currentDay, $currentTime);

        return response()->json($available);
    }



    private function checkBusinessHours($businessHours, $currentDay, $currentTime)
    {
        foreach ($businessHours as $day) {
            if ($day->day == $currentDay) {
                if ($day->is_closed === 'closed') {
                    return ['status' => false, 'message' => "The service is closed on {$currentDay}."];
                }

                $slots = [];
                if ($this->isTimeInRange($currentTime, $day->start_time, $day->end_time)) {
                    return ['status' => true, 'message' => 'Available'];
                }

                $slots[] = "{$day->start_time} to {$day->end_time}";

                foreach ($day->day_interval ?? [] as $interval) {
                    if ($this->isTimeInRange($currentTime, $interval->start_time, $interval->end_time)) {
                        return ['status' => true, 'message' => 'Available'];
                    }
                    $slots[] = "{$interval->start_time} to {$interval->end_time}";
                }

                return [
                    'status' => false,
                    'message' => "Queueing is only available on {$currentDay} between: " . implode(', ', $slots)
                ];
            }
        }

        return ['status' => false, 'message' => "No business hours found for {$currentDay}."];
    }

    private function isTimeInRange($currentTime, $startTime, $endTime)
    {
        return strtotime($currentTime) >= strtotime($startTime) && strtotime($currentTime) <= strtotime($endTime);
    }

    private function checkLimit($teamId, $locationId, $currentDate, $request)
    {
        $record = AccountSetting::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::TICKET_SLOT)
            ->select('is_waitlist_limit', 'waitlist_limit')
            ->first();

        if (!$record || $record->is_waitlist_limit == 0) {
            return true;
        }

        $query = QueueStorage::where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNull(['start_datetime', 'called_datetime', 'cancelled_datetime', 'closed_datetime'])
            ->where('is_hold', 0)
            ->where('temp_hold', 0)
            ->where('is_missed', 0)
            ->whereDate('arrives_time', $currentDate);

        if ($request->selectedCategoryId) {
            $query->where('category_id', $request->selectedCategoryId);
        }
        if ($request->secondChildId) {
            $query->where('sub_category_id', $request->secondChildId);
        }
        if ($request->thirdChildId) {
            $query->where('child_category_id', $request->thirdChildId);
        }

        return $query->count() < (int) $record->waitlist_limit;
    }

    public function testapi(Request $request)
    {
        $teamId = $request->header('X-Team-ID', 10); // default to 10
        $locationId = $request->header('X-Location-ID', 55); // default to 55

        return response()->json([
            'team_id' => $teamId,
            'location_id' => $locationId,
        ]);
    }



    private function getNextAgent($sitedetail)
    {

        $userTimezone = $siteDetail->select_timezone ?? 'Asia/Kolkata';
        $today = Carbon::today($userTimezone);

        // Get ordered agent IDs
        $allAgents = User::where('level_id', 3)
            ->where('team_id', $sitedetail->team_id)
            ->whereNotNull('locations')
            ->where('locations', '!=', '')
            ->whereRaw("JSON_VALID(locations)")
            ->whereJsonContains('locations', (string) $sitedetail->location_id)
            ->orderBy('priority')
            ->select('id', 'priority')
            ->get();


        // Filter agents by availability
        $agents = $allAgents->filter(function ($agent) use ($sitedetail) {
            return $this->checkStaffAvailability($agent->id, $sitedetail);
        })->pluck('id')->values();

        // Return first agent if no one has been assigned today yet
        $lastTicket = QueueStorage::where('team_id', $sitedetail->team_id)
            ->where('locations_id', $sitedetail->location_id)
            ->whereNotNull('locations_id')
            ->whereNull('cancelled_datetime')
            ->where('status', '!=', 'Cancelled')
            ->whereDate('arrives_time', $today->format('Y-m-d'))
            ->orderBy('id', 'desc')
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

    public function checkStaffAvailability($staffId, $sitedetail)
    {
        $userTimezone = $sitedetail->select_timezone ?? 'Asia/Kolkata'; // Ideally fetch from DB or user settings
        // $userTimezone = 'UTC';
        $currentDate = Carbon::now($userTimezone)->format('Y-m-d');
        $currentDay = Carbon::now($userTimezone)->format('l');
        $currentTime = Carbon::now($userTimezone)->format('h:i A');

        return $this->isWithinTimeSlot($sitedetail, null, AccountSetting::STAFF_SLOT, $currentDate, $currentDay, $currentTime, $staffId);
    }

    private function isWithinTimeSlot($sitedetail, $categoryId = null, $slotType, $currentDate, $currentDay, $currentTime, $userId = null)
    {
        // Check if the waitlist limit allows further processing
        if (!$this->checkLimit($sitedetail->team_id, $sitedetail->location_id, $currentDate, $currentDay, $currentTime)) {
            return false;
        }


        // Query for custom slots
        $query = CustomSlot::where('team_id', $sitedetail->team_id)
            ->where('location_id', $sitedetail->location_id)
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
            $query = AccountSetting::where('team_id', $sitedetail->team_id)
                ->where('location_id', $sitedetail->location_id)
                ->where('slot_type', $slotType);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            if ($userId) {
                $query->where('user_id', $userId);
            }

            $slotData = $query->select('business_hours')->first();
        }

        // If still no slot data, return false
        if (!$slotData) {

            return false;
        }

        // Check if current time is within business hours

        return $this->checkBusinessHours(json_decode($slotData->business_hours), $currentDay, $currentTime);
    }
    public function tenantCreate(Request $request)
    {
        try {
            $validated = $request->validate([
                'domain'       => 'required|string|max:255',
                'fullname'     => 'required|string|max:255',
                'company_name' => 'required|string|max:255',
                'email'        => 'required|email',
                'phone'        => 'required',
                'phone_code'   => 'required',
            ]);


            $slug = Str::slug($validated['domain']);
            $domainName = $slug . '.' . env('PARENT_DOMAIN');
            // Check if domain already exists
            if (Domain::where('domain', $domainName)->exists()) {
                return response()->json(['error' => 'Domain already exists.'], 409);
            }

            // Generate unique username from full name
            $baseUsername = Str::slug($validated['fullname']);
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter++;
            }

            // Create Tenant
            $tenant = Tenant::create([
                'name'  => ucfirst($validated['domain']),
                'brand' => ucfirst($validated['company_name']),
            ]);

            // Create Domain
            $tenant->domains()->create(['domain' => $domainName,'expired'=>now()->addDays(14)]);

            // Create Admin User
            $user = User::create([
                'name'              => $validated['fullname'],
                'username'          => $username,
                'email'             => $validated['email'],
                'phone'             => $validated['phone_code'] . $validated['phone'],
                'is_admin'          => 1,
                'email_verified_at' => now(),
                'password'          => Hash::make('Password@123'),
                'remember_token'    => Str::random(60),
                'address'           => 'Mohali',
                'timezone'          => 'Asia/Kolkata',
                'language'          => 'eng',
                'country'           => '92',
                'locations'         => [],
                'sms_reminder_queue' => 1,
                'team_id'           => $tenant->id,
                'date_format'       => 'Y-m-d',
                'time_format'       => 'H:i',
                'created_at'        => now(),
                'role_id'           => 1,
                'updated_at'        => now()->addDays(3),
                'is_login'          => 1,
                'login_datetime'=>now(),
                'is_active'         => 1,
            ]);


            // Assign Role
            if ($adminRole = Role::where('name', 'Admin')->first()) {
                $user->roles()->attach($adminRole->id);
            }
            Log::info('user :' . $user);
            // Send Email
            try {
                $data = [
                    'domain' => $domainName,
                    'user' => $user,
                    'tenant_id' => $user->team_id,
                    'admin_user_id' => $user->id,
                    'username' => $username,
                    'base_url' => 'https://' . $domainName . '/autologin/' . base64_encode($user->id)
                ];

                Mail::to($validated['email'])->send(new TenantCreated(
                    ucfirst($validated['company_name']),
                    $domainName,
                    $username,
                    $validated['email'],
                    'Password@123'
                ));

                // Success log
                $logData = [
                    'team_id' => $user->team_id,
                    'location_id' => null,
                    'user_id' => $user->id,
                    'email' => $user->email ?? '',
                    'contact' => $user->phone ?? '',
                    'type' => MessageDetail::CUSTOM_TYPE,
                    'event_name' => 'Set up new tenant',
                    'channel' => 'email',
                    'status' => 'sent',
                    'response_status' => json_encode($data),
                    'failed_reason' => null
                ];
            } catch (\Exception $e) {
                // Failed log
                $logData = [
                    'team_id' => $user->team_id,
                    'location_id' => null,
                    'user_id' => $user->id,
                    'email' => $user->email ?? '',
                    'contact' => $user->phone ?? '',
                    'type' => MessageDetail::CUSTOM_TYPE,
                    'event_name' => 'Set up a new tenant',
                    'channel' => 'email',
                    'status' => 'failed',
                    'response_status' => json_encode($data),
                    'failed_reason' => $e->getMessage()
                ];
            }

            MessageDetail::storeLog($logData);



            return response()->json([
                'success' => true,
                'message' => 'Tenant, domain, and admin user created successfully.',
                'domain'  => $domainName,
                'tenant_id' => $tenant->id,
                'admin_user_id' => $user->id,
                'username' => $username,
                'userId' => base64_encode($user->id),
                'base_url' => 'https://' . $domainName . '/autologin/' . base64_encode($user->id),
            ]);
        } catch (\Exception $e) {
            Log::error('Tenant Creation Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong during tenant creation.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function autoLogin(Request $request)
    {
        $userIdget = $request->input('user_id');
        $userId = base64_decode($userIdget);
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        Auth::loginUsingId($user->id);

        return response()->json([
            'success' => true,
            'message' => 'User logged in successfully.',
            'user' => Auth::user()
        ]);
    }


    public function tenantCreateWithLocation(Request $request)
    {
        try {
            $validated = $request->validate([
                'domain'       => 'required|string|max:255',
                'fullname'     => 'required|string|max:255',
                'company_name' => 'required|string|max:255',
                'email'        => 'required|email',
                'phone'        => 'required',
                'phone_code'   => 'required',
            ]);

            $slug = Str::slug($validated['domain']);
            $domainName = $slug . '.' . env('PARENT_DOMAIN');

            if (Domain::where('domain', $domainName)->exists()) {
                return response()->json(['error' => 'Domain already exists.'], 409);
            }

            $baseUsername = Str::slug($validated['fullname']);
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter++;
            }

            // Create Tenant
            $tenant = Tenant::create([
                'name'  => ucfirst($validated['domain']),
                'brand' => ucfirst($validated['company_name']),
            ]);

            // Create Domain
            $tenant->domains()->create(['domain' => $domainName]);

            // Create Admin User
            $user = User::create([
                'name'              => $validated['fullname'],
                'username'          => $username,
                'email'             => $validated['email'],
                'phone'             => $validated['phone_code'] . $validated['phone'],
                'is_admin'          => 1,
                'email_verified_at' => now(),
                'password'          => Hash::make('Password@123'),
                'remember_token'    => Str::random(60),
                'address'           => 'Mohali',
                'timezone'          => 'Asia/Kolkata',
                'language'          => 'eng',
                'country'           => '92',
                'locations'         => [],
                'sms_reminder_queue' => 1,
                'team_id'           => $tenant->id,
                'date_format'       => 'Y-m-d',
                'time_format'       => 'H:i',
                'created_at'        => now(),
                'role_id'           => 1,
                'updated_at'        => now()->addDays(3),
                'is_login'          => 1,
                'login_datetime'=>now(),
                'is_active'         => 1,
            ]);

            // Assign Role
            if ($adminRole = Role::where('name', 'Admin')->first()) {
                $user->roles()->attach($adminRole->id);
            }

            // ✅ Login user temporarily
            Auth::login($user);
            User::where('id', Auth::id())->update(['is_login' => 1,'login_datetime'=>now()]);

            // ✅ Create default location if not exists
            $checkLocation = Location::where([
                'team_id' => $user->team_id,
                'user_id' => $user->id,
            ])->exists();

            if (!$checkLocation) {
                $location = Location::create([
                    'location_name'  => 'Demo Location',
                    'team_id'        => $user->team_id,
                    'user_id'        => $user->id,
                    'address'        => '3 Raffles Pl, #08-01B, Singapore 048617',
                    'country'        => 'Singapore',
                    'city'           => 'Singapore',
                    'state'          => null,
                    'zip'            => '048617',
                    'longitude'      => 103.851175,
                    'latitude'       => 1.284136,
                    'ip_address'     => $request->ip(),
                    'location_image' => null,
                    'status'         => 1,
                ]);

                Log::info('Location created: ', $location->toArray());
            }
            User::where('id', Auth::id())->update(['is_login' => 0]);
            // ✅ Logout after creation
            Auth::logout();

            // Send Email
            try {
                $data = [
                    'domain'        => $domainName,
                    'user'          => $user,
                    'tenant_id'     => $user->team_id,
                    'admin_user_id' => $user->id,
                    'username'      => $username,
                    'base_url'      => 'https://' . $domainName . '/autologin/' . base64_encode($user->id)
                ];

                Mail::to($validated['email'])->send(new TenantCreated(
                    ucfirst($validated['company_name']),
                    $domainName,
                    $username,
                    $validated['email'],
                    'Password@123'
                ));

                $logData = [
                    'team_id'        => $user->team_id,
                    'location_id'    => null,
                    'user_id'        => $user->id,
                    'email'          => $user->email ?? '',
                    'contact'        => $user->phone ?? '',
                    'type'           => MessageDetail::CUSTOM_TYPE,
                    'event_name'     => 'Set up new tenant',
                    'channel'        => 'email',
                    'status'         => 'sent',
                    'response_status' => json_encode($data),
                    'failed_reason'  => null
                ];
            } catch (\Exception $e) {
                $logData = [
                    'team_id'        => $user->team_id,
                    'location_id'    => null,
                    'user_id'        => $user->id,
                    'email'          => $user->email ?? '',
                    'contact'        => $user->phone ?? '',
                    'type'           => MessageDetail::CUSTOM_TYPE,
                    'event_name'     => 'Set up a new tenant',
                    'channel'        => 'email',
                    'status'         => 'failed',
                    'response_status' => json_encode($data ?? []),
                    'failed_reason'  => $e->getMessage()
                ];
            }

            MessageDetail::storeLog($logData);

            return response()->json([
                'success'       => true,
                'message'       => 'Tenant, domain, location, and admin user created successfully.',
                'domain'        => $domainName,
                'tenant_id'     => $tenant->id,
                'admin_user_id' => $user->id,
                'username'      => $username,
                'userId'        => base64_encode($user->id),
                'base_url'      => 'https://' . $domainName . '/autologin/' . base64_encode($user->id),
            ]);
        } catch (\Exception $e) {
            Log::error('Tenant Creation Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong during tenant creation.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function checkTicketLimit($teamId, $locationId, $siteDetails)
    {

        if ($siteDetails->is_ticket_limit_enabled === 1) {
            $today = Carbon::now($siteDetails->select_timezone ?? config('app.timezone'))->toDateString();

            $getTickets = Queue::where('team_id', $teamId)
                ->where('locations_id', $locationId)
                ->whereDate('created_at', $today)
                ->count();

            if ($getTickets == $siteDetails->ticket_limit || $getTickets > $siteDetails->ticket_limit) {
                return true;
            }
        }
    }

    public function ticketgenerate(Request $request){

         try {

             $validator = Validator::make($request->all(), [
               'deviceID'       => 'required|string|max:255',
                'employeeID'     => 'required|string|max:255',
            ]);

              if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            $deviceID = $request->input('deviceID');
            $employeeID = $request->input('employeeID');

            $teamId = 300;

            $siteDetails = SiteDetail::where('team_id', $teamId)
                ->select(
                    'id',
                    'team_id',
                    'location_id',
                    'select_timezone',
                    'token_digit',
                    'token_start',
                )
                ->first();


            if ($siteDetails->select_timezone) {
                Config::set('app.timezone', $siteDetails->select_timezone);
                date_default_timezone_set($siteDetails->select_timezone);
            }


            $location = $siteDetails->location_id ?? 424;
            $servedBy = 1090;
            $counterId = 556;
            $selectedCategoryId = 1087;
            $waitingyId = 1089;
            $collectionId = 1090;
            $rdId = 1091;

          $timezone = config('app.timezone');
            // return response()->json(["status"=> "success",'nextPrioritySort' => $nextPrioritySort]);
         $todayDateTime = Carbon::now($timezone);

            //genearte ticket and service it
            if($deviceID == 'K70798105'){

            $checkEmployeeTodayToken = QueueStorage::whereDate('arrives_time', $todayDateTime)->where('name',$employeeID)->first();

            if (!empty($checkEmployeeTodayToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already Ticket Generate',
                ], 500);
            }


            if (!empty($selectedCategoryId)) {
                $acronym = Category::viewAcronym($selectedCategoryId);
            } else {
                $acronym = SiteDetail::DEFAULT_WALKIN_A;
            }


            $lastToken = QueueStorage::getLastToken($teamId, $acronym, $location);

            $tokenDigit = $siteDetails?->token_digit ?? 4;
            $isExistToken = true;

            while ($isExistToken) {
                $newToken = QueueStorage::newGeneratedToken($lastToken, $siteDetails?->token_start, $tokenDigit);
                if (strlen($newToken) > $tokenDigit) {
                    return response()->json(['error' => 'Unable to create more tickets'], 400);
                }

                $isExistToken = Queue::checkToken($teamId, $acronym, $newToken, $location);

                if ($isExistToken) {
                    $lastToken = $newToken;
                } else {
                    $tokenStart = $newToken;
                    $isExistToken = false;
                }
            }



            $storeData = [
                'name' =>  $employeeID,
                'category_id' => $selectedCategoryId,
                'sub_category_id' =>  $waitingyId,
                'team_id' => $teamId,
                'token' => $tokenStart,
                'counter_id' => $counterId,
                'token_with_acronym' =>  Queue::LABEL_NO,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $acronym,
                'locations_id' => $location,
                'mode' => 'api',
                'called' => 'yes',
            ];

            $queueCreated = Queue::storeQueue([
                'team_id' => $teamId,
                'token' => $tokenStart,
                'start_acronym' => $acronym,
                'token_with_acronym' => $storeData['token_with_acronym'],
                'locations_id' => $location,
                'arrives_time' => $todayDateTime,
            ]);

            $queueStorage = QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));

          QueueCreated::dispatch($queueStorage);
            // call start

            if (!empty($queueStorage)) {

            $conditionTeam = ['team_id' => $queueStorage->team_id];

            $startCallRes = Queue::startCalledField($conditionTeam, $queueStorage->queue_id, $queueStorage->counter_id, false, $queueStorage->location_id, $queueStorage->id);


            if ($startCallRes == 'hold on') {

                return response()->json([
                    'status' => 'error',
                    'message' => 'This call is on Hold temporarily (start)',

                ], 422);
            }


            QueueProgress::dispatch($queueStorage);
            QueueDisplay::dispatch($queueStorage);



            }

             return response()->json([
                'success'       => true,
                'message'       => 'ticket Genearte and served on waiting area',
                'data' =>$queueStorage

            ]);
        }

        // call close for waiting area and served for collection
         if($deviceID == 'K70798106'){

            // $checkEmployeeTodayToken = QueueStorage::whereDate('arrives_time', $todayDateTime)->where('name',$employeeID)->first();

            // if (!empty($checkEmployeeTodayToken)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Already Ticket Generate',
            //     ], 500);
            // }

            $queueStorage = QueueStorage::where('team_id', $teamId)
            ->whereDate('arrives_time', $todayDateTime)
            ->where('name',$employeeID)
            ->where('locations_id', $location)
            ->where('called', 'yes')
            ->whereNotNull('start_datetime')
            ->whereNull('closed_datetime')
            ->first();

            if(!empty($queueStorage)){
             $queueStorage->update([
                'sub_category_id' =>$collectionId
            ]);

            QueueProgress::dispatch($queueStorage);
             QueueDisplay::dispatch($queueStorage);

             return response()->json([
                'success'       => true,
                'message'       => 'served on collection area',
                'data' =>$queueStorage

            ]);
            }else{
                   return response()->json([
                 'success' => false,
                'message' => 'Now no Ticket from Collection Area',

            ]);
            }

         }
        //  served for R&d
         if($deviceID == 'K70798107'){

            // $checkEmployeeTodayToken = QueueStorage::whereDate('arrives_time', $todayDateTime)
            // ->where('name',$employeeID)
            // ->where('status','Close')
            // ->first();

            // if (!empty($checkEmployeeTodayToken)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Already Ticket Generate',
            //     ], 500);
            // }

            $queueStorage = QueueStorage::where('team_id', $teamId)
            ->whereDate('arrives_time', $todayDateTime)
            ->where('locations_id', $location)
            ->where('name',$employeeID)
            ->where('called', 'yes')
            ->whereNotNull('start_datetime')
            ->whereNull('closed_datetime')
            ->first();

            if(!empty($queueStorage)){
             $queueStorage->update([
                  'sub_category_id' =>$rdId,
            ]);

            QueueProgress::dispatch($queueStorage);
             QueueDisplay::dispatch($queueStorage);

             return response()->json([
                'success'       => true,
                'message'       => 'served on collection area',
                'data' =>$queueStorage

            ]);
            }else{
                   return response()->json([
                 'success' => false,
                'message' => 'Now no Ticket from Collection Area',

            ]);
            }

         }


          //  close call
         if($deviceID == 'K70798108'){

          $queueStorage = QueueStorage::where('team_id', $teamId)
            ->whereDate('arrives_time', $todayDateTime)
            ->where('locations_id', $location)
            ->where('name',$employeeID)
            ->where('called', 'yes')
            ->whereNotNull('start_datetime')
            ->whereNull('closed_datetime')
            ->first();

            if(!empty($queueStorage)){
  $queueStorage->update([
                  'sub_category_id' =>$rdId,
                'closed_datetime' =>$todayDateTime,
                'closed_by' =>$queueStorage->served_by ?? null,
                'status' =>"Close",
            ]);

            QueueProgress::dispatch($queueStorage);
             QueueDisplay::dispatch($queueStorage);

             return response()->json([
                'success'       => true,
                'message'       => 'served on R & D area and closed the ticket',
                'data' =>$queueStorage

            ]);
            }else{
                   return response()->json([
                 'success' => false,
                'message' => 'Now no Ticket from Collection Area',

            ]);
            }

         }
 return response()->json([
                'success' => false,
                'message' => 'Device mismatch',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Tenant Creation Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }


    }
  public function generateTicketWithQR(Request $request)
    {

        $code = $request->input('code');

        $decrypted = AesEncryptionHelper::decrypt($code);

        $text = $request->input(
            'qr_payload',
            <<<EOT
                $decrypted
                EOT
        );

        // Remove surrounding triple-quotes and trim
        $text = trim($text, "\"'\n\r\t ");

        // Match lines like "Key: value"
        preg_match_all('/^\s*([^:]+):\s*(.+)$/m', $text, $matches, PREG_SET_ORDER);

        $data = [];

        foreach ($matches as $m) {
            $rawKey = trim($m[1]);
            $value  = trim($m[2]);

            // Convert key to snake_case (letters, numbers, underscores)
            $key = Str::snake(preg_replace('/[^A-Za-z0-9 ]+/', '', $rawKey));

            $data[$key] = $value;
        }

        // Additional structured parsing for specific fields

        // Normalize appointment id (strip leading zeros if you want numeric)
        if (isset($data['appointment_id'])) {
            // keep as string or cast to int: uncomment next line to cast
            // $data['appointment_id'] = ltrim($data['appointment_id'], '0') === '' ? '0' : ltrim($data['appointment_id'], '0');
            $data['appointment_id_raw'] = $data['appointment_id'];
        }

        // Parse Date to ISO (attempt dd/mm/YYYY then mm/dd/YYYY fallback)
        if (isset($data['date'])) {
            $dateStr = $data['date'];
            $parsed = null;

            // try d/m/Y
            try {
                $d = Carbon::createFromFormat('d/m/Y', $dateStr);
                $parsed = $d->toDateString(); // YYYY-MM-DD
            } catch (\Exception $e) {
                try {
                    // try m/d/Y
                    $d = Carbon::createFromFormat('m/d/Y', $dateStr);
                    $parsed = $d->toDateString();
                } catch (\Exception $e2) {
                    $parsed = $dateStr; // leave as-is if parse fails
                }
            }

            $data['date_iso'] = $parsed;
        }

        // Parse Time range like "11:00:00 - 12:00:00"
        if (isset($data['time'])) {
            $time = $data['time'];
            $parts = preg_split('/\s*-\s*/', $time);
            $data['start_time'] = isset($parts[0]) ? trim($parts[0]) : null;
            $data['end_time']   = isset($parts[1]) ? trim($parts[1]) : null;
        }

        $getData = $data;

        // $dynamicProperties = $request->input('dynamicProperties', []);

        // $formattedFields = [];
        // foreach ($dynamicProperties as $key => $value) {
        //     $fieldName = preg_replace('/_\d+/', '', $key);
        //     $fieldName = strtolower($fieldName);
        //     $formattedFields[$fieldName] = $value;
        // }

        $teamId = 376;

        $siteDetails = SiteDetail::where('team_id', $teamId)
            ->select(
                'id',
                'team_id',
                'location_id',
                'select_timezone',
                'token_digit',
                'token_start',
                'country_code',
            )
            ->first();


        if ($siteDetails->select_timezone) {
            Config::set('app.timezone', $siteDetails->select_timezone);
            date_default_timezone_set($siteDetails->select_timezone);
        }

        $timezone = config('app.timezone');
        $todayDateTime = Carbon::now($timezone);
        $selectedCategoryId =1264;
        $counterId = 726;
        $location = 513;

         $appointmentDate = Carbon::parse($data['date_iso'])->format('Y-m-d');
    $today = Carbon::now($timezone)->format('Y-m-d');
     if (isset($data['date_iso'])) {
    if ($appointmentDate !== $today) {
        return response()->json([
            'message' => 'Ticket can only be generated for today\'s appointment',
            'status' => 'error',
            'data' => $getData,
            'today' => $today,
            'appointment_date' => $appointmentDate
        ], 200);
    }
}

        if (!empty($selectedCategoryId)) {
            $acronym = Category::viewAcronym($selectedCategoryId);
        } else {
            $acronym = SiteDetail::DEFAULT_WALKIN_A;
        }


        $lastToken = QueueStorage::getLastToken($teamId, $acronym, $location);

        $tokenDigit = $siteDetails?->token_digit ?? 4;
        $isExistToken = true;

        while ($isExistToken) {
            $newToken = QueueStorage::newGeneratedToken($lastToken, $siteDetails?->token_start, $tokenDigit);
            if (strlen($newToken) > $tokenDigit) {
                return response()->json(['error' => 'Unable to create more tickets'], 400);
            }

            $isExistToken = Queue::checkToken($teamId, $acronym, $newToken, $location);

            if ($isExistToken) {
                $lastToken = $newToken;
            } else {
                $tokenStart = $newToken;
                $isExistToken = false;
            }
        }

           $selectedCountryCode = !empty($siteDetails->country_code) ?  $siteDetails->country_code : null;
        $phone_code = !empty($selectedCountryCode) ? $selectedCountryCode : '91';
        $email = !empty($getData['mail']) ? $getData['mail'] : null;
        $storeData = [
            'name' =>  $getData['name'],
            'phone' =>  $getData['contact'],
            'phone_code' => $phone_code ,
            'category_id' => $selectedCategoryId,
            'sub_category_id' =>  null,
            'team_id' => $teamId,
            'token' => $tokenStart,
            'counter_id' => $counterId,
            'token_with_acronym' =>  Queue::LABEL_NO,
            'arrives_time' => $todayDateTime,
            'datetime' => $todayDateTime,
            'start_acronym' => $acronym,
            'locations_id' => $location,
            'mode' => 'api',
            'called' => 'yes',
            'json' => json_encode($getData),
            'full_phone_number' => (!empty($phone_code) && !empty($getData['contact']))
                                        ? $phone_code . $getData['contact']
                                        : null,
        ];

        $queueCreated = Queue::storeQueue([
            'team_id' => $teamId,
            'token' => $tokenStart,
            'start_acronym' => $acronym,
            'token_with_acronym' => $storeData['token_with_acronym'],
            'locations_id' => $location,
            'arrives_time' => $todayDateTime,
        ]);

        $queueStorage = QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));


        if ($queueStorage) {
            QueueCreated::dispatch($queueStorage);
              QueueProgress::dispatch($queueStorage);
              QueueDisplay::dispatch($queueStorage);


            if (!empty( $selectedCategoryId))
                $categoryName =  Category::viewCategoryName( $selectedCategoryId);

                $logData = [
                'team_id' => $teamId,
                'location_id' =>  $location,
                'user_id' => $queueStorage->served_by,
                'customer_id' => $queueStorage->created_by,
                'queue_id' => $queueStorage->queue_id,
                'queue_storage_id' => $queueStorage->id,
                'email' => $email,
                'contact' => null,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Ticket Generate',
            ];

            $storeData['to_mail'] = $email;
          $dateformat = AccountSetting::showDateTimeFormat();

           $data['ticket_link'] = url('/visits/' . base64_encode($queueCreated->id));

                $data = [
                'name' => $queueStorage->name,
                'phone' => $queueStorage->phone ?? null,
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueCreated->id,
                'queue_storage_id' => $queueStorage->id,
                'arrives_time' => Carbon::parse($queueStorage->arrives_time)->format($dateformat),
                'category_name' =>  $categoryName,
                'thirdC_name' => null,
                'secondC_name' => null,
                'pending_count' => 0,
                'token' => $queueCreated->start_acronym . $queueCreated->token,
                'token_with_acronym' => $queueCreated->start_acronym,
                'to_mail' => $email ?? '',
                'locations_id' => $location,
                'team_id' => $teamId,
                'location_name' => null,
                'priority_sort' => 0,
            ];

            $this->sendNotification($data, 'ticket created', $logData);

            return response()->json([
                'message' => 'Ticket generated successfully',
                'status'=>"success",
                'data' => $getData,

            ], 200);
        }
    }
}