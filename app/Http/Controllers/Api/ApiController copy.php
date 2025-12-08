<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Team;
use App\Models\Tenant;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Events\QueueCreated;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendEmailJob;


class ApiController extends Controller
{
    /**
     * Login API
     * use variable- email and password
     */
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|string|min:2',
    //     ]);

    //     $user = User::with('checkrole')->where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Invalid credentials'
    //         ], 401);
    //     }

    //     if (!$user->team_id) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Team not assigned'
    //         ], 401);
    //     } else {
    //         $team = Tenant::with('domains')->where('id', $user->team_id)->first();
    //         // return response()->json($team);
    //         // $subdomain = $team->domains->domain; // Replace with your actual subdomain
    //         $subdomain = optional($team->domains->first())->domain;

    //         if (!$subdomain) {
    //             return response()->json(['error' => 'Domain not found'], 404);
    //         }

    //         $storageUrl = url('/storage');
    //         // $subdomainUrl = str_replace(url('/'), "https://{$subdomain}." . parse_url(url('/'), PHP_URL_HOST), $storageUrl) . '/';
    //         $subdomainUrl = "https://{$subdomain}"."/storage/";
    //         $url = "https://{$subdomain}";
    //     }

    //     $locations = Location::whereIn('id', $user->locations)
    //         ->pluck('location_name', 'id')
    //         ->toArray();
    //     // ✅ Store team_id in session
    //     session(['team_id' => $user->team_id]);
    //     $sitedetail = SiteDetail::where('team_id', $user->team_id)->select('id', 'country_code', 'app_heading_first', 'app_heading_second', 'logo_print_ticket', 'business_logo', 'mobile_logo', 'logo_print_ticket', 'logo_footer_ticket_screen', 'category_text_font_size', 'ticket_font_family')->first();

    //     $colorSettings = ColorSetting::where('team_id', $user->team_id)->select('page_layout', 'categories_background_layout', 'text_layout', 'buttons_layout', 'theme_color', 'button_color', 'font_color', 'mobile_page_layout', 'mobile_header_background_color', 'mobile_heading_text_color', 'mobile_category_button_color', 'mobile_button_text_color', 'mobile_button_color', 'mobile_font_color')->first();

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Logged-IN',
    //         'access_token' => $token,
    //         'token_type' => 'Bearer',
    //         'storage_url' => $subdomainUrl,
    //         'url' => $url,
    //         'user' => $user,
    //         'locations' => $locations,
    //         'site' => $sitedetail,
    //         'colors' => $colorSettings
    //     ]);
    // }

  
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
    // $subdomainUrl = "https://{$subdomain}/storage/";
    // $url = "https://{$subdomain}";
    $subdomainUrl = "https://sandbox.qwaiting.com/storage/";
    $url = "https://sandbox.qwaiting.com";

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

     $sitedetail = SiteDetail::where('team_id', $teamId)->where('location_id',$locationId)
        // ->select('id', 'country_code', 'app_heading_first', 'app_heading_second', 'logo_print_ticket', 'business_logo', 'mobile_logo', 'logo_print_ticket', 'logo_footer_ticket_screen', 'category_text_font_size', 'ticket_font_family')
        ->first();

    $colorSettings = ColorSetting::where('team_id', $teamId)->where('location_id',$locationId)
        ->select('page_layout', 'categories_background_layout', 'text_layout', 'buttons_layout', 'theme_color', 'button_color', 'font_color', 'mobile_page_layout', 'mobile_header_background_color', 'mobile_heading_text_color', 'mobile_category_button_color', 'mobile_button_text_color', 'mobile_button_color', 'mobile_font_color')
        ->first();

    $pusherDetail = PusherDetail::where('team_id', $teamId)->where('location_id',$locationId)->first();
 
    $accountDetails = AccountSetting::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->where('slot_type', AccountSetting::BOOKING_SLOT)
                ->value('booking_system');
     
    // Store in session (or use cookies if preferred)
    session(['location_id' => $request->location_id]);

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
        $QrCode = GenerateQrCode::where('team_id',$teamId)->where('location_id',$locationId)->first();

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
                        'id', 'token_digit', 'token_start', 'category_estimated_time',
                        'category_level_est', 'is_qrcode_ticket', 'is_logo_on_print',
                        'is_name_on_print', 'is_arrived_on_print', 'is_location_on_print',
                        'is_category_on_print', 'ticket_text_enable', 'is_token_on_print',
                        'print_name_label', 'print_token_label', 'arrived_time_label',
                        'estimate_time', 'ticket_text_2', 'ticket_text', 'confirm_btn_label', 
                        'logo_print_ticket'
                    )
                    ->first();


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
                
            if (!empty($selectedCategoryId)) {
                $acronym = Category::viewAcronym($selectedCategoryId);
            }else{
                $acronym = SiteDetail::DEFAULT_WALKIN_A;
            }

            $lastToken = Queue::getLastToken($teamId, $acronym, $location);

            $tokenDigit = $siteDetails?->token_digit ?? 4;
            $isExistToken = true;

            while ($isExistToken) {
                $newToken = Queue::newGeneratedToken($lastToken, $siteDetails?->token_start, $tokenDigit);
                if (strlen($newToken) > $tokenDigit) {
                    return response()->json(['status'=>'error','message' => 'Unable to create more tickets'], 400);
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
            $todayDateTime = Carbon::now();

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
                'name' => $queueStorage->name,
                'phone' => $queueStorage->phone,
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueCreated->id,
                'arrives_time' => $todayDateTime->format($dateformat),
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

            ];

            $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $teamId,$location);

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

                $nameLabel =$siteDetails->print_name_label ?? 'Name';
                $tokenLabel =$siteDetails->print_token_label ?? 'Token';
                $arrivedLabel =$siteDetails->arrived_time_label ?? 'Arrived';

                $baseencodeQueueId = base64_encode($queueCreated->id);
                $customUrl = url("/visits/{$baseencodeQueueId}");
                $qrcodeSvg = QrCode::format('svg')
                ->size(150)
                ->errorCorrection('H')
                ->generate($customUrl);
              

                if ($siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {
                    $estimate_time = $siteDetails->estimate_time ?? 0;

                    $waitingTime =  $estimate_time * $data['pending_count'];

                    if (!empty($siteDetails->ticket_text_2))
                        $showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $siteDetails->ticket_text_2);

                    if (!empty($siteDetails->ticket_text)) {
                        $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $siteDetails->ticket_text);
                        $showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                    }
                }
            }
            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();

            // $this->sendNotification($data, 'ticket created');
            // Send notifications
            // Notification::send(User::adminUserDetail($teamId), new \App\Notifications\QueueCreatedNotification($data));

            SendEmailJob::dispatch($data, 'ticket created');

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

            return response()->json(["status"=> "success",'message' => 'Queue created successfully', 'data' => $data, 'ticket' => $ticket], 200);
        } catch (\Throwable $ex) {
            Log::error('Error storing queue data: ' . $ex->getMessage());
            return response()->json(['error' => 'Unable to process your request'], 500);
        }
    }

    public function sendNotification($data, $type)
    {
        if (isset($data['to_mail']) && $data['to_mail'] != '') {
            SmtpDetails::sendMail($data, $type, 'ticket-created', $data['team_id']);
        }
        // $data[ 'location' ] = Location::find( $this->location )->value( 'location_name' );
        if (!empty($data['phone'])) {
            SmsAPI::sendSms($data['team_id'], $data, $type,$type);
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

            $screens = ScreenTemplate::where(['team_id' => $teamId])->where('location_id',$locationId)->get();

            if (!$screens) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'No Screen found',
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => $screens,
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
            $selectedTemplate = ScreenTemplate::viewDetails($teamId, $templateId,$locationId);

            if (!$selectedTemplate) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Display template not found',
                ], 404);
            }

            $displaySetting = DisplaySettingModel::getDetails($teamId,$locationId);
            $locationName = Location::locationName($locationId);

            $counterIDs = $selectedTemplate?->counters?->pluck('id')?->toArray() ?? [];
             $categoryID = $selectedTemplate?->categories?->pluck('id')?->toArray() ?? [];

              if($selectedTemplate->type == "Counter"){
            $queueToDisplay = Queue::displayQueueApi($teamId, (int)$locationId, $selectedTemplate->show_queue_number, $counterIDs);
             }else{
                  $queueToDisplay = Queue::displayQueueApi($teamId, (int)$locationId, $selectedTemplate->show_queue_number, null,$categoryID);

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

            $siteDetails = SiteDetail::where('team_id', $teamId)->where('location_id',$location)
              ->select('id','token_digit','token_start','category_estimated_time','category_level_est','is_qrcode_ticket','is_logo_on_print','is_name_on_print','is_arrived_on_print','is_location_on_print','is_category_on_print','ticket_text_enable','is_token_on_print','print_name_label','print_token_label','arrived_time_label','estimate_time','ticket_text_2','ticket_text','confirm_btn_label')
            ->first();

            $formattedFields = [];

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
                }else{
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
            $todayDateTime = Carbon::now();

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

            $Ticketlogo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $teamId,$location);

            $logo = isset($Ticketlogo)  ? url($Ticketlogo) : '';

            $waitingTime = 0;
            if (!empty($siteDetails)) {
                if ($siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {
                    $estimate_time = $siteDetails->estimate_time ?? 0;

                    $waitingTime =  $estimate_time * $data['pending_count'];

                    if (!empty($siteDetails->ticket_text_2))
                        $showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $siteDetails->ticket_text_2);

                    if (!empty($siteDetails->ticket_text)) {
                        $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $siteDetails->ticket_text);
                        $showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                    }
                }
            }
            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();

            // $this->sendNotification($data, 'ticket created');
             SendEmailJob::dispatch($data, 'ticket created');

            $showQrcode =$siteDetails->is_qrcode_ticket == 1 ? true : false;
            $showlogo =$siteDetails->is_logo_on_print == 1 ? true : false;
            $showusername =$siteDetails->is_name_on_print == 1 ? true : false;
            $showarrived =$siteDetails->is_arrived_on_print == 1 ? true : false;
            $showlocation =$siteDetails->is_location_on_print == 1 ? true : false;
            $showcategory =$siteDetails->is_category_on_print == 1 ? true : false;
            $showTextmessage =$siteDetails->ticket_text_enable == 1 ? true : false;
            $showToken =$siteDetails->is_token_on_print == 1 ? true : false;

            $nameLabel =$siteDetails->print_name_label ?? 'Name';
            $tokenLabel =$siteDetails->print_token_label ?? 'Token';
            $arrivedLabel =$siteDetails->arrived_time_label ?? 'Arrived';

            $baseencodeQueueId = base64_encode($queueCreated->id);
            $customUrl = url("/visits/{$baseencodeQueueId}");
            $qrcodeSvg = QrCode::format('svg')
            ->size(150)
            ->errorCorrection('H')
            ->generate($customUrl);

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
                    ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $showTicketText_2 . '</h4></div>' : '') . '
                    ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                </div>',
                'confirmButtonText' => $siteDetails->confirm_btn_label ?? 'Thank you',
                'token_notify' => 'The Generated Token Number is ' . $acronym . $data['token']
            ];

            return response()->json(["status"=> "success",'message' => 'Queue created successfully', 'data' => $data, 'ticket' => $ticket], 200);
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
    $secondcategory= $request->secondChildId ?? '';
    $thirdcategory = $request->thirdChildId ?? '';


    $siteData = SiteDetail::where('team_id', $teamId)
    ->where('location_id', $locationId)
    ->select('select_timezone','enable_time_slot','category_slot_level')
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

    $categoryLevelEnable = $siteData?->enable_time_slot;

    $slotLevel = $siteData?->category_slot_level ?? 1;

    $categoryId = match ($slotLevel) {
        1 => $firstcategory ,
        2 => $secondcategory,
        3 => $thirdcategory,

        default => null,
    };

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
    public function testapi2(Request $request)
    {
        $teamId = $request->header('X-Team-ID', 10); // default to 10
        $locationId = $request->header('X-Location-ID', 55); // default to 55

        return response()->json([
            'team_id' => $teamId,
            'location_id' => $locationId,
            'api'=>2
        ]);
    }
    public function testapi3(Request $request)
    {
        $teamId = $request->header('X-Team-ID', 10); // default to 10
        $locationId = $request->header('X-Location-ID', 55); // default to 55

        return response()->json([
            'team_id' => $teamId,
            'location_id' => $locationId,
            'api'=>3
        ]);
    }
}
