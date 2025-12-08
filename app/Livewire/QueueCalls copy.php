<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Category,
    Counter,
    Queue as QueueDB,
    SiteDetail,
    SmtpDetails,
    SmsAPI,
    GenerateQrCode,
    QueueStorage,
    Location,
    ColorSetting,
    User,
    Country,
    AccountSetting,
    CustomSlot,
    PaymentSetting,
    StripeResponse,
    Customer,
    CustomerActivityLog,
    LanguageSetting,
    MessageDetail,
    TicketPrint,
    Translation,
    MetaAdsAndCampaignsLink,
    AutomationSetting,
    ActivityLog,
    Tenant,
    SalesforceSetting,
    SalesforceConnection,
    QueueCategoryGrouping,
    Level,
};
use Illuminate\Validation\Rule;
use Livewire\Rules\Numeric;
use Carbon\Carbon;
use Livewire\Attributes\On;
use App\Events\QueueCreated;
use App\Events\DesktopNotification;
use App\Models\FormField;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use App\Jobs\SendQueueNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use DateTime;
use DateTimeZone;


#[Layout('components.layouts.custom-layout')]
class Queue extends Component
{

    #[Title('Queue')]
    public $selectedCategoryId;

    public $secondChildId, $thirdChildId, $teamId;
    public $name = '';
    public $phone = '';
    public $divOne;
    public $categoryName = '';
    public $secondCategoryName = '';
    public $thirdCategoryName = '';

    public $qrCodeDetailsCache;
    public $locationStep = true;
    public $firstStep = false;
    public $secondStep = false;
    public $thirdStep = false;
    public $fourthStep = false;
    public $paymentStep = false;
    public $currentPage = Category::STEP_1;

    public $totalLevelCount = Category::STEP_1;
    public $dynamicForm = [];
    // public $firstCategories;

    public $dynamicProperties = [];

    public $showTicketText;
    public $showTicketText_2;
    public $token_start;
    public $last_token;
    public $booking_setting;
    public $acronym = '';
    public $fontSize = 'text-3xl';
    public $fontFamily = 'font-sans';
    public $borderWidth = 'border-4';
    public $countCatID = 0;
    public $fieldCatName = '';
    public $counterID = 0;
    public $header = true;
    protected $listeners = ['changeDate' => 'changeDate'];
    public $email;
    public $is_qr_code;
    public $qrcode_tagline;
    public $allCategories = [];
    public $allLocations = [];
    public $location;
    public $locationName;
    public $colorSetting;
    public $countryCode = [];
    public $siteData;
    public $phone_code = '91';
    public $selectedCountryCode;
    public $latitude;
    public $longitude;
    public $accountSetting;
    public $validDistance = 100;
    public $unavailableMessage;
    public bool $isMobile = false;

    public $isFree = true;
    public $amount;
    public $stripeCategory;
    public $paymentMethodId;
    public $successMessage;
    public $errorMessage;
    public $stripeResponeID;
    public $paymentSetting;
    public $paymentSettingKey;
    public $paymentSettingSecret;
    public $payment;
    public $translations = [];
    public $qrcode_tagline_second;
    public $utm_source;
    public $utm_medium;
    public $utm_campaign;
    public $userAuth;
    public $enablePriority = false;
    public $enableVirtual = false;
    public $disable_print = false;

    //Limit check
    public $isTicketLimitEnabled;
    public $ticketLimit;
    public $ticketDisabled = false;

    // salesforces variable
    public $client_id;
    public $client_secret;
    public $redirect_uri;
    public $auth_url;
    public $token_url;
    public $access_token;
    public $instance_url;

    public $enable_callDepartment = false;
    public $categoryGroupData = [];

    public $level1,$level2,$level3,$tag_line1,$tag_line2,$tag_line3;
    public $layout_show ='column';


    public function mount(Request $request, $location_id = null)
    {

        $this->utm_source = $request->query('utm_source');
        $this->utm_medium = $request->query('utm_medium');
        $this->utm_campaign = $request->query('utm_campaign');

        // $this->showFormQueue = false;
        $this->teamId = tenant('id');


        if (empty($this->teamId)) {
            abort(404);
        }

        $this->isMobile = $request->query('mobile') === 'true';

        $this->locationName = '';
        $this->booking_setting = SiteDetail::STATUS_YES;

        // Check for route parameter
        if (!Session::has('selectedLocation') && $location_id !== null) {
            $this->location = base64_decode($location_id, true);
            Session::put('selectedLocation', $this->location);
        } else {
            $this->location = Session::get('selectedLocation');
        }

        $this->stripeResponeID = '';

        if (!empty($this->location)) {
            $this->locationName = Location::locationName($this->location);
        }

        $this->locationStep = false;
        $this->firstStep = true;

        if (empty($this->location) && !Auth::check()) {
            $this->location = '';
            $this->allLocations = Location::select('id', 'location_name', 'address', 'location_image')->where('team_id', $this->teamId)->where('status', 1)->get();

            if (empty($this->allLocations)) {
                abort(403);
            }
            $this->locationStep = true;
            $this->firstStep = false;
        } else {
            $this->accountSetting = AccountSetting::where('team_id', $this->teamId)
                ->where('location_id', $this->location)
                ->where('slot_type', AccountSetting::TICKET_SLOT)
                //   ->select('is_waitlist_limit', 'waitlist_limit')
                ->first();
            $this->paymentSetting = PaymentSetting::where('team_id', $this->teamId)
                ->where('location_id', $this->location)
                ->first();

            if ($this->paymentSetting) {

                if (!empty($this->paymentSetting->api_key) && !empty($this->paymentSetting->api_secret)) {
                    $this->paymentSettingKey = $this->paymentSetting->api_key;
                    $this->paymentSettingSecret = $this->paymentSetting->api_secret;

                    config([
                        'services.stripe.key' => $this->paymentSetting->api_key,
                        'services.stripe.secret' => $this->paymentSetting->api_secret,
                    ]);
                }
            }

            if (isset($this->accountSetting)) {
                $this->validDistance = $this->accountSetting->geofence_max_distance ?? '100';
            }
            $this->dispatch('getLocation');
        }


        if (!empty($this->siteDetails)) {
            $this->fontSize = $this->siteDetails->category_text_font_size ?? $this->fontSize;
            $this->borderWidth = $this->siteDetails->category_border_size ?? $this->borderWidth;
            $this->fontFamily = $this->siteDetails->ticket_font_family ?? $this->fontFamily;
            $this->booking_setting = $this->siteDetails->booking_system ?? SiteDetail::STATUS_YES;


            if (isset($this->siteDetails)) {
                $this->qrCodeDetailsCache = GenerateQrCode::viewGeneratorCode($this->teamId);

                if (empty($this->qrCodeDetailsCache)) {
                    $this->is_qr_code = SiteDetail::STATUS_NO;
                } else {

                    $this->is_qr_code = $this->siteDetails->is_qr_code ?? SiteDetail::STATUS_NO;
                }
            }

            $this->qrcode_tagline = $this->siteDetails->qrcode_tagline;
            $this->qrcode_tagline_second = $this->siteDetails->qrcode_tagline_second;
            $this->colorSetting = ColorSetting::where('team_id', $this->teamId)->where('location_id',$this->location)->first();
            $this->enablePriority = $this->siteDetails->use_staff_priority ?? false;
            $this->enableVirtual = $this->siteDetails->ticket_mode ?? SiteDetail::STATUS_NO;
            $this->disable_print = $this->siteDetails->disable_print ?? SiteDetail::STATUS_NO;
             $this->enable_callDepartment = $this->siteDetails->enable_callDepartment ?? false;
             $this->layout_show = $this->siteDetails->layout_show ?? 'column';
        }
        $this->countryCode = Country::query()->pluck('phonecode');
        if(empty($this->siteDetails)){

            $this->siteData = SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->location)->first();
        }else{
            $this->siteData = $this->siteDetails;
        }
        $this->selectedCountryCode = !empty($this->siteDetails->country_code) ?  $this->siteDetails->country_code : null;
        $this->phone_code = !empty($this->selectedCountryCode) ? $this->selectedCountryCode : '91';


        if ($this->siteData && $this->siteData->select_timezone) {
            Config::set('app.timezone', $this->siteData->select_timezone);
            date_default_timezone_set($this->siteData->select_timezone);
        }
        if ($this->siteData && $this->siteData->enable_time_slot == 'ticket') {
            $this->checkTicketBusinessHours();
        }

        $this->translations = Translation::where('team_id', $this->teamId)
            ->get()
            ->groupBy('name') // Group by category name
            ->map(function ($items) {
                return $items->pluck('value', 'language'); // ['es' => 'Categoría 1']
            })
            ->toArray();

        $this->userAuth = Auth::user();

        $salesforcessettings = SalesforceSetting::where('team_id',  $this->teamId)
                ->where('location_id', $this->location)
                ->first();

            if ($salesforcessettings) {
                $this->client_id     = $salesforcessettings->client_id ?? '';
                $this->client_secret = $salesforcessettings->client_secret ?? '';
                $this->redirect_uri = !empty($salesforcessettings->redirect_uri) ? $salesforcessettings->redirect_uri :'';
            }

        $this->auth_url  = env('SALESFORCE_LOGIN_URL', 'https://test.salesforce.com/services/oauth2/authorize');
        $this->token_url = env('SALESFORCE_TOKEN_URL', 'https://test.salesforce.com/services/oauth2/token');

           $connectionData = SalesforceConnection::where('team_id',$this->teamId)
            ->where('location_id',$this->location)
            ->where('status',1)
            ->first();

            if(!empty($connectionData) && !empty($connectionData->salesforce_refresh_token) && !empty($connectionData->salesforce_instance_url)){
                   $this->access_token = $connectionData->salesforce_refresh_token;
                   $this->instance_url = $connectionData->salesforce_instance_url;
            }

        $levels = Level::where('team_id',$this->teamId)
            ->where('location_id',$this->location)
            ->whereIn('level', [1, 2, 3])
            ->get()
            ->keyBy('level');

        $this->level1 = $levels[1]->name ?? null;
        $this->level2 = $levels[2]->name ?? null;
        $this->level3 = $levels[3]->name ?? null;
        $this->tag_line1= $levels[1]->tag_line ?? null;
        $this->tag_line2 = $levels[2]->tag_line ?? null;
        $this->tag_line3 = $levels[3]->tag_line ?? null;


         $this->checkTicketLimit();


    }

    public function checkTicketLimit()
    {
        if(!empty($this->siteData))
        {
            if($this->siteData->is_ticket_limit_enabled)
            {
            $today = Carbon::now($this->siteData?->select_timezone ?? config('app.timezone'))->toDateString();

            $getTickets = QueueDB::where('team_id', $this->teamId)
                ->where('locations_id', $this->location)
                ->whereDate('created_at', $today)
                ->count();

            if($getTickets == $this->siteData->ticket_limit || $getTickets > $this->siteData->ticket_limit)
                {
                $this->ticketDisabled = true;
                $this->dispatch('swal:limit-exceed', [
                    'title' => 'Oops...',
                    'text' => 'You have reached your daily ticket limit of ' . $this->siteData->ticket_limit,
                    'icon' => 'error'
                ]);

            }
        }

        }
    }

     public function checkStaffAvailability($staffId)
    {
        $userTimezone = $this->siteDetails->select_timezone ?? 'Asia/Kolkata'; // Ideally fetch from DB or user settings
        // $userTimezone = 'UTC';
        $currentDate = Carbon::now($userTimezone)->format('Y-m-d');
        $currentDay = Carbon::now($userTimezone)->format('l');
        $currentTime = Carbon::now($userTimezone)->format('h:i A');

        return $this->isWithinTimeSlot(null, AccountSetting::STAFF_SLOT, $currentDate, $currentDay, $currentTime,$staffId);

    }

    public function checkTicketBusinessHours()
    {
        if ($this->siteData && $this->siteData->enable_time_slot === 'ticket') {
            $businessHours = json_decode($this->accountSetting->business_hours, true);
            $currentDay = Carbon::now()->format('l'); // e.g., 'Thursday'
            $currentTime = Carbon::now();

            $todayConfig = collect($businessHours)->firstWhere('day', $currentDay);

            if (!$todayConfig || $todayConfig['is_closed'] !== 'open') {
                return  redirect('no-service');
            }

            // Check main working hours
            $startTime = Carbon::parse($todayConfig['start_time']);
            $endTime = Carbon::parse($todayConfig['end_time']);

            $isWithinMainTime = $currentTime->between($startTime, $endTime);

            // Check intervals if any
            $hasIntervals = !empty($todayConfig['day_interval']);
            $isWithinInterval = false;

            if ($hasIntervals) {
                foreach ($todayConfig['day_interval'] as $interval) {
                    $intervalStart = Carbon::parse($interval['start_time']);
                    $intervalEnd = Carbon::parse($interval['end_time']);

                    if ($currentTime->between($intervalStart, $intervalEnd)) {
                        $isWithinInterval = true;
                        break;
                    }
                }
            }

            if (!$isWithinMainTime && !$isWithinInterval) {
                return  redirect('no-service');
            }

            // Passed all checks
            // Proceed with your logic
        }
    }


    #[Computed]
    public function siteDetails()
    {

        // return SiteDetail::getMyDetails($this->teamId);
        return SiteDetail::where(['team_id' => $this->teamId, 'location_id' => $this->location])->first();
    }

    #[Computed]
    public function qrCodeDetails()
    {

        if (isset($this->siteDetails)) {
            $this->qrCodeDetailsCache = GenerateQrCode::viewGeneratorCode($this->teamId);
            if (empty($this->qrCodeDetailsCache)) {
                $this->is_qr_code = SiteDetail::STATUS_NO;
            } else {
                $this->is_qr_code = $this->siteDetails->is_qr_code ?? SiteDetail::STATUS_NO;
            }
        }

        return $this->qrCodeDetailsCache;
    }

    #[Computed]
    public function firstCategories()
    {
        return Category::getFirstCategoryN($this->teamId, $this->location);
    }


    // public function rules()
    // {

    //     try {
    //         $rules = [];
    //         if (!empty($this->dynamicProperties)) {
    //             foreach ($this->dynamicProperties as $fieldName => $value) {
    //                 $fieldId = explode('_', $fieldName)[1];

    //                 $field = FormField::findDynamicFormField($this->dynamicForm, $fieldId);

    //                 if ($field) {
    //                     FormField::addDynamicFieldRules($rules, $fieldName, $field, $this->allCategories);
    //                 }
    //             }
    //         }

    //         return $rules;
    //     } catch (\Throwable $ex) {
    //         $this->dispatch('swal:ticket-generate', [
    //             'title' => 'Oops...',
    //             'text' => 'Unable to generate ticket due to invalid rules. Please contact to the admin',
    //             'icon' => 'error'
    //         ]);
    //     }
    // }

    public function rules()
{
    try {
        $rules = [];

        if (!empty($this->dynamicProperties)) {
            foreach ($this->dynamicProperties as $fieldName => $value) {
                $fieldId = explode('_', $fieldName)[1];

                $field = FormField::findDynamicFormField($this->dynamicForm, $fieldId);

                if ($field) {
                    FormField::addDynamicFieldRules($rules, $fieldName, $field, $this->allCategories);
                }
            }
        }

        return $rules;
    } catch (\Throwable $ex) {
        $this->dispatch('swal:ticket-generate', [
            'title' => 'Oops...',
            'text'  => 'Unable to generate ticket due to invalid rules. Please contact admin',
            'icon'  => 'error'
        ]);

        // 👇 Always return an array so Livewire doesn’t throw MissingRulesException
        return [];
    }
}
    public function messages()
    {
        $messages = [];

        foreach ($this->dynamicProperties as $fieldName => $value) {
            $fieldId = explode('_', $fieldName)[1] ?? null;

            if (!$fieldId) {
                continue;
            }

            $field = FormField::findDynamicFormField($this->dynamicForm, $fieldId);
            if ($field) {
                $fieldTitle = $field['label'];
                $translatedLabel = $fieldTitle;

                if (
                    isset($this->translations[$fieldTitle]) &&
                    isset($this->translations[$fieldTitle][session('app_locale')])
                ) {
                    $translatedLabel = $this->translations[$fieldTitle][session('app_locale')];
                }

                $messages["dynamicProperties.$fieldName.required"] =
                    __('text.The') . ' ' . $translatedLabel . ' ' . __('text.field is required.');

                if (str_contains(strtolower($fieldTitle), 'email')) {
                    $messages["dynamicProperties.$fieldName.email"] =
                        __('text.Invalid email address for') . ' ' . $translatedLabel . '.';
                }

                $messages["dynamicProperties.$fieldName.regex"] =
                    __('text.The') . ' ' . $translatedLabel . ' ' . __('text.field is invalid.');

                $messages["dynamicProperties.$fieldName.min"] =
                    __('text.The') . ' ' . $translatedLabel . ' ' . __('text.field must be at least :min characters.');

                $messages["dynamicProperties.$fieldName.max"] =
                    __('text.The') . ' ' . $translatedLabel . ' ' . __('text.field must be at most :max characters.');
            }
        }

        return $messages;
    }

    public function render()
    {

        return view('livewire.queue');
    }

    public function updatedLocation($value)
    {
        $this->location = $value;
        $this->locationName =  Location::locationName($value);
        $this->locationStep = false;
        $this->firstStep = true;
        Session::forget('selectedLocation');
        Session::put('selectedLocation', $this->location);

        $this->accountSetting = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('slot_type', AccountSetting::TICKET_SLOT)
            //   ->select('is_waitlist_limit', 'waitlist_limit')
            ->first();
        if (empty($this->accountSetting)) {
            abort(403);
        }

        $this->paymentSetting = PaymentSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->first();

        if ($this->paymentSetting) {
           if (!empty($this->paymentSetting->api_key) && !empty($this->paymentSetting->api_secret)) {
                    $this->paymentSettingKey = $this->paymentSetting->api_key;
                    $this->paymentSettingSecret = $this->paymentSetting->api_secret;

                    config([
                        'services.stripe.key' => $this->paymentSetting->api_key,
                        'services.stripe.secret' => $this->paymentSetting->api_secret,
                    ]);
                }
        }

        if (isset($this->accountSetting)) {
            $this->validDistance = $this->accountSetting->geofence_max_distance ?? '100';
        }

        $this->dispatch('getLocation');
        $this->dispatch('header-show');
    }

   public function showFirstChild($categoryId)
    {

        $this->selectedCategoryId = $categoryId;
        if (empty($this->firstChildren) || $this->enable_callDepartment) {

            if($this->enable_callDepartment){

            $this->categoryGroupData = QueueCategoryGrouping::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('category_id', $categoryId)
            ->first();

            if(empty( $this->categoryGroupData)){
                 $this->dispatch('checkAvailability', message: 'Call Department Workflow is not set');
                return false;
            }

            }
            $checkAvailability = $this->checkAvailability();
            if ($checkAvailability == false) {
                $this->dispatch('checkAvailability', message: $this->unavailableMessage ?? 'Service not available at this time.');
                return false;
            }
            $this->getAmount();
            $this->updateCurrentPage(Category::STEP_4);
        } else {
            $this->getAmount();
            $this->updateCurrentPage(Category::STEP_2);
        }

        // $this->closeTheWaitlist();

        $this->currentPageFn($this->currentPage);
        $this->secondChildId = null;
        $this->totalLevelIncFn();
    }
    public function showPaymentPage()
    {
        if (!empty($this->dynamicProperties)) {
            $this->validate();
        }

        $this->fourthStep = false;
        $this->paymentStep = true;

        $this->dispatch('cardElement');
        //   dd($this->siteDetails->is_paid_categories,$this->siteDetails->paid_category_level);
    }

    #[Computed]
    public function firstChildren()
    {
        $children = Category::getCategories($this->selectedCategoryId, $this->location);

        return !empty($children) ? $children->toArray() : [];
    }
    #[Computed]
    public function secondChildren()
    {
        $children = Category::getSubCategories($this->secondChildId, $this->location);
        return !empty($children) ? $children->toArray() : [];
    }


    public function totalLevelIncFn()
    {
        $this->totalLevelCount++;
    }

    public function totalLevelDecFn()
    {
        if ($this->totalLevelCount > 0)
            $this->totalLevelCount--;
    }

    public function showSecondChild($childId)
    {

        $this->secondChildId = $childId;


        if (empty($this->secondChildren)) {
            $this->getAmount();
            $this->updateCurrentPage(Category::STEP_4);
        } else {
            $this->getAmount();
            $this->updateCurrentPage(Category::STEP_3);
        }
        $checkAvailability = $this->checkAvailability();
        if ($checkAvailability == false) {
            $this->dispatch('checkAvailability', message: $this->unavailableMessage ?? 'Service not available at this time.');
            return false;
        }
        $this->currentPageFn($this->currentPage);
        $this->totalLevelIncFn();
    }

    public function showQueueForm($secondCId)
    {
        $this->thirdChildId = $secondCId;

        $this->updateCurrentPage(Category::STEP_4);
        $this->currentPageFn($this->currentPage);
    }

    public function saveQueueForm()
    {


        if (!empty($this->dynamicProperties)) {
            // $this->validate();
            $this->validate($this->rules());
        }

        if($this->enable_callDepartment){
               $this->saveQueueFormDepartment();
            return;
            }
        try {

               if($this->ticketDisabled){
                $this->dispatch('swal:limit-exceed', [
                    'title' => 'Oops...',
                    'text' => 'You have reached your daily ticket limit of ' . $this->siteData->ticket_limit,
                    'icon' => 'error'
                ]);

            }

             DB::beginTransaction();

            $this->dispatch('queue:refresh');
            $newToken = $lastToken = '';
            $formattedFields = [];
            $assigned_staff_id = null;

            foreach ($this->dynamicProperties as $key => $value) {
                $trimmedKey = trim($key);
                $fieldName = preg_replace('/_\d+/', '', $trimmedKey);
                $fieldName = strtolower($fieldName); // normalize to lowercase
                $formattedFields[$fieldName] = $value;
            }



 $possiblePhoneKeys = FormField::possiblePhoneKeys();
$this->phone = null;

foreach ($possiblePhoneKeys as $key) {
    if (isset($formattedFields[$key]) && !empty($formattedFields[$key])) {
        $this->phone = $formattedFields[$key];
        break;
    }
}
            $this->email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['Email']) ? $formattedFields['Email'] : null);

            $jsonDynamicData = json_encode($formattedFields);

            if (!empty($this->selectedCategoryId)) {
                $this->acronym = Category::viewAcronym($this->selectedCategoryId);
            } else {
                $this->acronym = SiteDetail::DEFAULT_WALKIN_A;
            }

            $lastToken = QueueDB::getLastToken($this->teamId, $this->acronym, $this->location);

            $token_digit = $this->siteDetails?->token_digit ?? 4;  //4
            $isExistToken = true;

            while ($isExistToken) {
                $newToken = QueueDB::newGeneratedToken($lastToken, $this->siteDetails?->token_start, $token_digit);

                if (strlen($newToken) > $token_digit) {
                    $this->dispatch('swal:ticket-generate', [
                        'title' => 'Oops...',
                        'text' => 'Unable to create more tickets',
                        'icon' => 'error'
                    ]);
                    return;
                }

                $isExistToken = QueueDB::checkToken($this->teamId, $this->acronym, $newToken, $this->location);

                if ($isExistToken) {
                    $lastToken = $newToken;
                } else {
                    $this->token_start = $newToken;
                    $isExistToken = false;
                }
            }


            $seniorCitiizen = 'No';
            $isSeniorCitiizen = $this->siteDetails->enable_priority_pattern == 0 ? true :false;
            if($isSeniorCitiizen){

                if(isset($formattedFields['are you a senior citizen']) && !empty($formattedFields['are you a senior citizen'])){

                    $seniorCitiizen = $formattedFields['are you a senior citizen'];
                    $nextPrioritySort = $this->getNextSeniorPrioritySort($this->selectedCategoryId,$seniorCitiizen);
                }else{
                    $nextPrioritySort = $this->getNextSeniorPrioritySort($this->selectedCategoryId,$seniorCitiizen);

                }
            }else{
                $nextPrioritySort = $this->getNextPrioritySort($this->selectedCategoryId);
            }

               if($this->enablePriority){
                 $assigned_staff_id = User::getNextAgent($this->teamId,$this->location);
                if(empty($assigned_staff_id)){
                     $this->dispatch('swal:ticket-generate', [
                        'title' => 'Oops...',
                        'text' => 'Staff is not Available',
                        'icon' => 'error'
                    ]);
                    return;
                }
            }


            $timezone = config('app.timezone'); // fallback if not set

            if ($this->siteData && $this->siteData->select_timezone) {
                $timezone = $this->siteData->select_timezone;
                Config::set('app.timezone', $timezone);
                date_default_timezone_set($timezone); // optional but good for consistency
            }

            if (!empty($this->utm_source) && !empty($this->utm_medium) && !empty($this->utm_campaign)) {
                $getCampaign = MetaAdsAndCampaignsLink::where('source', $this->utm_source)->where('medium', $this->utm_medium)->where('campaign', $this->utm_campaign)->first();

                $campaignId = $getCampaign->id;
            }

            $decodedJson = json_decode($jsonDynamicData, true);

            $todayDateTime = Carbon::now($timezone);

                $is_virtual_meeting =0;
                // if($this->enableVirtual){
                if (isset($decodedJson['type']) && $decodedJson['type'] === 'Virtual') {
                $is_virtual_meeting =1;
                }

            $storeData = [
                'name' => $this->name,
                'phone' => $this->phone,
                'ticket_mode' => $this->isMobile ? QueueDB::TICKET_MODE_MOBILE : QueueDB::TICKET_MODE_Walk_IN,
                'is_virtual_meeting' =>$is_virtual_meeting,
                'phone_code' => $this->phone_code ?? '91',
                'category_id' => $this->selectedCategoryId ?? null,
                'sub_category_id' => $this->secondChildId ?? null,
                'child_category_id' => $this->thirdChildId ?? null,
                'team_id' => (int)$this->teamId,
                'token' => $this->token_start,
                'token_with_acronym' => $this->booking_setting == QueueDB::STATUS_NO ? QueueDB::LABEL_YES : QueueDB::LABEL_NO,
                'json' => $jsonDynamicData,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $this->acronym,
                'locations_id' => (int)$this->location,
                'priority_sort' => $nextPrioritySort ?? 0,
                'campaign_id' => isset($campaignId) ? $campaignId : null,
                'mode' => $this->isMobile ? 'mobile' : 'web',
                'senior_citizen' => $seniorCitiizen ?? 'No',

            ];

            $queueCreated = QueueDB::storeQueue([
                'team_id' => (int) $this->teamId, // Cast to integer
                'token' => (string) $this->token_start, // Ensure it's a string
                'start_acronym' => (string) $this->acronym, // Ensure it's a string
                'token_with_acronym' => $this->booking_setting == QueueDB::STATUS_NO
                    ? QueueDB::LABEL_YES
                    : QueueDB::LABEL_NO, // Conditional assignment
                'locations_id' => (int) $this->location, // Cast to integer
                'arrives_time' => $todayDateTime, // Ensure valid datetime format
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

            // QueueCreated::dispatch($queueStorage);

            if (!empty($this->thirdChildId))
                $this->thirdCategoryName = Category::viewCategoryName($this->thirdChildId);
            if (!empty($this->secondChildId))
                $this->secondCategoryName = Category::viewCategoryName($this->secondChildId);
            if (!empty($this->selectedCategoryId))
                $this->categoryName =  Category::viewCategoryName($this->selectedCategoryId);

            if ($this->siteDetails?->category_estimated_time == SiteDetail::STATUS_YES)
                $this->determineCategoryColumn();

            // if ($this->siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO)
            //     $this->counterID  = 0;

            $pendingwaiting = $pendingCount = 0;


            if ($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES) {


                $estimatedetail = QueueStorage::countPendingByCategory($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                if ($estimatedetail == false) {
                    $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                } else {
                    $pendingCount = $estimatedetail['customers_before_me'] ?? 0;
                    $pendingwaiting = $estimatedetail['estimated_wait_time'] ?? 0;
                    if($this->enablePriority == false){
                        $assigned_staff_id = $estimatedetail['assigned_staff_id'] ?? null;
                    }
                }
            } else {

                $pendingCountget = (int)QueueStorage::countPending($this->teamId, $queueStorage->id, '', '', '', $this->location);
                $counterCount = Counter::where('team_id',$this->teamId)->whereJsonContains('counter_locations', "$this->location")->where('show_checkbox',1)->count();
                if((int)$pendingCountget > 0 && (int)$counterCount > 0){
                        $pendingCount = floor((int)$pendingCountget / (int)$counterCount);

                    }

            }

            $dateformat = AccountSetting::showDateTimeFormat();
            $data = [
                'name' => $queueStorage->name,
                'phone' => $queueStorage->phone,
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueCreated->id,
                'queue_storage_id' => $queueStorage->id,
                'arrives_time' => Carbon::parse($queueStorage->arrives_time)->format($dateformat),
                'category_name' => $this->categoryName,
                'thirdC_name' => $this->thirdCategoryName,
                'secondC_name' => $this->secondCategoryName,

                'pending_count' => $pendingCount,
                'token' => $queueCreated->start_acronym . $queueCreated->token,
                'token_with_acronym' => $queueCreated->start_acronym,
                'to_mail' => $this->email ?? '',
                'locations_id' => $this->location,
                'location_name' => $this->locationName,
                'priority_sort' => $nextPrioritySort ?? 0,
            ];

            $language = session('app_locale');

            $showQrcode = $this->siteDetails->is_qrcode_ticket == 1 ? true : false;
            $showlogo = $this->siteDetails->is_logo_on_print == 1 ? true : false;
            $showusername = $this->siteDetails->is_name_on_print == 1 ? true : false;
            $showarrived = $this->siteDetails->is_arrived_on_print == 1 ? true : false;
            $showlocation = $this->siteDetails->is_location_on_print == 1 ? true : false;
            $showcategory = $this->siteDetails->is_category_on_print == 1 ? true : false;
            $showTextmessage = $this->siteDetails->ticket_text_enable == 1 ? true : false;
            $showToken = $this->siteDetails->is_token_on_print == 1 ? true : false;

            if ($language !== 'en') {
                $nameLabel = isset($this->translations['Print Name Label'][$language]) ? $this->translations['Print Name Label'][$language] : ($this->siteDetails->print_name_label ?? 'Name');
                $tokenLabel = isset($this->translations['Print Token Label'][$language]) ? $this->translations['Print Token Label'][$language] : ($this->siteDetails->print_token_label ?? 'Token');
                $arrivedLabel = isset($this->translations['Arrived Time Label'][$language]) ? $this->translations['Arrived Time Label'][$language] : ($this->siteDetails->arrived_time_label ?? 'Arrived');
            } else {
                $nameLabel = $this->siteDetails->print_name_label ?? 'Name';
                $tokenLabel = $this->siteDetails->print_token_label ?? 'Token';
                $arrivedLabel = $this->siteDetails->arrived_time_label ?? 'Arrived';
            }

            $baseencodeQueueId = base64_encode($queueCreated->id);
            $customUrl = url("/visits/{$baseencodeQueueId}");
            $qrcodeSvg = QrCode::format('svg')
                ->size(150)
                ->errorCorrection('H')
                ->generate($customUrl);



            $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId, $this->location);

            $waitingTime = 0;

            if (!empty($this->siteDetails)) {
                $estimate_time = $this->siteDetails->estimate_time ?? 0;

                if ($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES) { // get esitmate time of category wise
                    $waitingTime =  $pendingwaiting ?? $estimate_time * $data['pending_count'];
                } else {  // get esitmate time of globally set
                    $waitingTime =  $estimate_time * $data['pending_count'];
                }

                if ($this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {

                    if (!empty($this->siteDetails->ticket_text_2)) {
                        if ($language !== 'en' && isset($this->translations['Ticket Message 1'][$language])) {
                            $this->showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $this->translations['Ticket Message 1'][$language]);
                        } else {
                            $this->showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $this->siteDetails->ticket_text_2);
                        }
                    }
                    if (!empty($this->siteDetails->ticket_text)) {

                        if ($language !== 'en' && isset($this->translations['Ticket Message 2'][$language])) {
                            $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $this->translations['Ticket Message 2'][$language]);
                            $this->showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                        } else {
                            $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $this->siteDetails->ticket_text);
                            $this->showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                        }
                    }
                }
            }
            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->assign_staff_id = $assigned_staff_id ?? null;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();

            //update queue_id in stripe response table
            if (!empty($this->stripeResponeID)) {
                StripeResponse::where('id', $this->stripeResponeID)->update([
                    'queue_id' => $queueCreated->id,
                ]);

                $this->stripeResponeID = '';
            }

             $data = array_merge($data, ['waiting_time' => $waitingTime,'ticket_link' => $customUrl]);

            $data['meeting_link'] = $storeData['meeting_link'];

            //store customer data and activity log
            if (!empty($this->phone)) {
                $existingCustomer = Customer::where('phone', "$this->phone")
                    ->where('team_id', $this->teamId)
                    ->where('location_id', $this->location)
                    ->first();

                // Create customer if not exists
                if (empty($existingCustomer)) {
                    $existingCustomer = Customer::create([
                        'team_id' => $this->teamId,
                        'location_id' => $this->location,
                        'name' => $this->name ?? null,
                        'phone' => $this->phone,
                        'json_data' => $jsonDynamicData, // casted automatically to JSON
                    ]);
                }

                // Log customer activity with type 'queue'
                CustomerActivityLog::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->location,
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

            $data['ticket_link'] = url('/visits/' . base64_encode($queueCreated->id));

            $logData = [
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => $queueStorage->served_by,
                'customer_id' => $queueStorage->created_by,
                'queue_id' => $queueStorage->queue_id,
                'queue_storage_id' => $queueStorage->id,
                'email' => $this->email,
                'contact' => $this->phone,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Ticket Generate',
            ];

            $this->sendNotification($data, 'ticket created', $logData);
            QueueCreated::dispatch($queueStorage);
            // if(!empty($storeData['meeting_link']))
            // {
            //     $this->sendNotification($data, 'virtual meeting', $logData);
            // }

            // create lead in salesforce
               if(!empty($queueStorage) && !empty($this->client_id) && !empty($this->client_secret) && !empty($this->access_token) && !empty($this->instance_url)){
             $datetime11 = new DateTime($queueStorage->arrives_time); // Uses default server timezone
                        $datetime11->setTimezone(new DateTimeZone('UTC')); // Convert to UTC
                        $Qwaiting_Sync_Date__c = $datetime11->format('Y-m-d\TH:i:s\Z'); // Salesforce ISO 8601 format


                        $salesForceData = array();
                        $salesForceData['refresh_token']          = $this->access_token;
                        $salesForceData['instance_url']           = $this->instance_url;
                        $salesForceData['queue_storage_id']                  = $queueStorage->id;
                        $salesForceData['token']                  = $queueStorage->token;
                        $salesForceData['FirstName']              = $queueStorage->name;
                        $salesForceData['Phone']                  = $queueStorage->phone ?? '';
                        $salesForceData['Created']                 = $queueStorage->arrives_time;
                        $salesForceData['Qwaiting_Sync_Date__c']  = $Qwaiting_Sync_Date__c;
                        $salesForceData['Token']  =  $queueStorage->token;
                        $salesForceData['Service_Name__c']        = $this->categoryName ?? 'Queue Service';
                        $salesForceData['Company']        =  tenant('name');
                        $salesForceData['Page']        = 'Queue Page';
                        $salesForceData['Email']        = $this->email ?? '';


                    // Call Salesforce Lead creation
                    $leadResponse = $this->createLead($salesForceData);

                    $queueStorage->salesforce_lead = json_encode($leadResponse);
                    $queueStorage->save();

                    }

            //$this->isMobile is true for mobile url then redirect to visit page ,otherwise preview ticket show on queue page
            if (!$this->isMobile && $this->siteDetails->is_redirect_print_page == SiteDetail::STATUS_NO && !$this->disable_print) {

                    // $this->dispatch('swal:saved-queue', [
                    //     'timer' => 8000,
                    //     'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                    //         ($showlogo ? '<img src="' . asset($logo) . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                    //         '</div>
                    // <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                    //     ' . ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                    //     ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $data['token'] . '</strong></h3></div>' : '') . '
                    //     ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
                    //     ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                    //     ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['category_name']][session('app_locale')]) ? $this->translations[$data['category_name']][session('app_locale')] : $data['category_name']) . '</h3><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['secondC_name']][session('app_locale')]) ? $this->translations[$data['secondC_name']][session('app_locale')] : $data['secondC_name']) . '</h3><h3 style="font-size:16px;">' . (isset($this->translations[$data['thirdC_name']][session('app_locale')]) ? $this->translations[$data['thirdC_name']][session('app_locale')] : $data['thirdC_name']) . '</h3></div>' : '') . '
                    //     ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') . '
                    //     ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                    // </div>',
                    //     'confirmButtonText' => $language !== 'en' ? (isset($this->translations['Confirm Button Label'][$language]) ? $this->translations['Confirm Button Label'][$language] :  $this->siteDetails->confirm_btn_label) : ($this->siteDetails->confirm_btn_label ?? 'Thank you'),
                    //     'token_notify' => 'The Generated Token Number is ' . $this->acronym . $data['token']

                    // ]);



                      if($this->siteDetails->print_mode === 'default')
                {
                    $this->dispatch('swal:saved-queue-no-print', [
                        'timer' => 8000,
                        'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                            ($showlogo ? '<img src="' . asset($logo) . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                            '</div>
                    <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                        ' . ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                        ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $data['token'] . '</strong></h3></div>' : '') . '
                        ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
                        ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                        ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['category_name']][session('app_locale')]) ? $this->translations[$data['category_name']][session('app_locale')] : $data['category_name']) . '</h3><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['secondC_name']][session('app_locale')]) ? $this->translations[$data['secondC_name']][session('app_locale')] : $data['secondC_name']) . '</h3><h3 style="font-size:16px;">' . (isset($this->translations[$data['thirdC_name']][session('app_locale')]) ? $this->translations[$data['thirdC_name']][session('app_locale')] : $data['thirdC_name']) . '</h3></div>' : '') . '
                        ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') . '
                        ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                    </div>',
                        'confirmButtonText' => $language !== 'en' ? (isset($this->translations['Confirm Button Label'][$language]) ? $this->translations['Confirm Button Label'][$language] :  $this->siteDetails->confirm_btn_label) : ($this->siteDetails->confirm_btn_label ?? 'Thank you'),
                        'token_notify' => 'The Generated Token Number is ' . $this->acronym . $data['token']

                    ]);
                }
                else
                {



                    // $this->dispatch('silent-print', html: '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                    //         ($showlogo ? '<img src="' . asset($logo) . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                    //         '</div>
                    // <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                    //     ' . ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                    //     ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $data['token'] . '</strong></h3></div>' : '') . '
                    //     ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
                    //     ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                    //     ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['category_name']][session('app_locale')]) ? $this->translations[$data['category_name']][session('app_locale')] : $data['category_name']) . '</h3><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['secondC_name']][session('app_locale')]) ? $this->translations[$data['secondC_name']][session('app_locale')] : $data['secondC_name']) . '</h3><h3 style="font-size:16px;">' . (isset($this->translations[$data['thirdC_name']][session('app_locale')]) ? $this->translations[$data['thirdC_name']][session('app_locale')] : $data['thirdC_name']) . '</h3></div>' : '') . '
                    //     ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') . '
                    //     ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                    // </div>');

                                $html =
                '<div style="padding-top:20px;text-align:center;display:flex;align-items:center;justify-content:center;gap:16px;">' .
                    ($showlogo ? '<img src="' . asset($logo) . '" style="margin:auto;max-width:160px;width:100%;height:100%;"/>' : '') .
                '</div>' .
                '<div style="display:flex;flex-direction:column;gap:8px;color:#333;padding-top:20px;line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family:\'Simplified Arabic Fixed\',sans-serif;">' .
                    ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') .
                    ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $data['token'] . '</strong></h3></div>' : '') .
                    ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') .
                    ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') .
                    ($showcategory ?
                        '<div>' .
                            '<h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['category_name']][session('app_locale')]) ? $this->translations[$data['category_name']][session('app_locale')] : $data['category_name']) . '</h3>' .
                            '<h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['secondC_name']][session('app_locale')]) ? $this->translations[$data['secondC_name']][session('app_locale')] : $data['secondC_name']) . '</h3>' .
                            '<h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['thirdC_name']][session('app_locale')]) ? $this->translations[$data['thirdC_name']][session('app_locale')] : $data['thirdC_name']) . '</h3>' .
                        '</div>'
                    : '') .
                    ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') .
                    ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') .
                '</div>';

            $this->dispatch('silent-print', html: $html);

            ActivityLog::storeLog($this->teamId, $this->userAuth->id ?? null, null, null, 'Ticket Generate',  $this->location, ActivityLog::QUEUE, null, $this->userAuth ?? null);

            DB::commit();
            $this->resetForm();
 return;

                }

            }

            ActivityLog::storeLog($this->teamId, $this->userAuth->id ?? null, null, null, 'Ticket Generate',  $this->location, ActivityLog::QUEUE, null, $this->userAuth ?? null);

            $this->resetForm();

            DB::commit();
           if ($this->isMobile) {
                $this->redirect('/visits/' . base64_encode($queueCreated->id));
            }
          if (!$this->isMobile && $this->siteDetails->is_redirect_print_page == SiteDetail::STATUS_YES && !$this->disable_print &&  $this->siteDetails->print_mode == 'default') {

                 $this->redirect('/ticket-print/' . base64_encode($queueStorage->id));
            }

             if (!$this->isMobile && $this->siteDetails->is_redirect_print_page == SiteDetail::STATUS_NO && $this->disable_print && $this->siteDetails->print_mode == 'default') {

                    $this->dispatch('swal:saved-queue-no-print', [
                        'timer' => 8000,
                        'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                            ($showlogo ? '<img src="' . asset($logo) . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                            '</div>
                    <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                        ' . ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                        ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $data['token'] . '</strong></h3></div>' : '') . '
                        ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
                        ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                        ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['category_name']][session('app_locale')]) ? $this->translations[$data['category_name']][session('app_locale')] : $data['category_name']) . '</h3><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['secondC_name']][session('app_locale')]) ? $this->translations[$data['secondC_name']][session('app_locale')] : $data['secondC_name']) . '</h3><h3 style="font-size:16px;">' . (isset($this->translations[$data['thirdC_name']][session('app_locale')]) ? $this->translations[$data['thirdC_name']][session('app_locale')] : $data['thirdC_name']) . '</h3></div>' : '') . '
                        ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') . '
                        ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                    </div>',
                        'confirmButtonText' => $language !== 'en' ? (isset($this->translations['Confirm Button Label'][$language]) ? $this->translations['Confirm Button Label'][$language] :  $this->siteDetails->confirm_btn_label) : ($this->siteDetails->confirm_btn_label ?? 'Thank you'),
                        'token_notify' => 'The Generated Token Number is ' . $this->acronym . $data['token']

                    ]);

            }

        } catch (\Throwable $ex) {

            DB::rollback();

            Log::emergency('Queue generate issue on desktop');
            Log::emergency($ex);
            $this->dispatch('swal:ticket-generate', [
                'title' => 'Oops...',
                'text' => 'Unable to generate ticket. Please contact to the admin',
                'icon' => 'error'
            ]);
        }
    }

    public function resetDynamic()
    {
        $this->allCategories = [
            'thirdChildId' => $this->thirdChildId,
            'secondChildId' => $this->secondChildId,
            'selectedCategoryId' => $this->selectedCategoryId,
        ];
        $this->dynamicForm = FormField::getFields($this->teamId, false, $this->location, [], $this->allCategories);

        foreach ($this->dynamicForm as $field) {
            $propertyName = $field['title'] . '_' . $field['id'];
            $this->dynamicProperties[$propertyName] = '';
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

    public function sendNotification($data, $type, $logData = null)
    {
        $data['locations_id'] = $this->location;
        if (isset($data['to_mail']) && $data['to_mail'] != '') {
            SmtpDetails::sendMail($data, $type, '',  $this->teamId,$logData);

        }
        if (!empty($data['phone'])) {

            SmsAPI::sendSms($this->teamId, $data, $type, $type, $logData);
        }
    }

      public function resetForm()
    {
        $this->dynamicProperties = [];
        $this->reset(['name', 'phone', 'email']);

        // $this->name = $this->phone = '';
    }

    #[On('refresh-component')]
    public function refreshComponent()
    {
        if (Auth::check()) {
            $this->locationStep = false;
            $this->firstStep = true;
        } else {

            if (!empty($this->allLocations)) {
                $this->location = '';
                $this->locationStep = true;
                $this->firstStep = false;
            } else {
                $this->locationStep = false;
                $this->firstStep = true;
            }
        }
        $this->secondStep = $this->thirdStep = $this->fourthStep = false;
        $this->currentPage = $this->totalLevelCount = Category::STEP_1;
        $this->selectedCategoryId = $this->secondChildId = $this->thirdChildId = null;
        $this->thirdCategoryName =  $this->secondCategoryName = $this->categoryName = null;
    }

    public function updateCurrentPage($page)
    {
        if ($page == Category::STEP_4) {
            if ($this->siteDetails?->queue_form_display == SiteDetail::STATUS_NO) {
                $this->saveQueueForm();
                return;
            }
        }
        $this->currentPage = $page;
        if ($this->currentPage == Category::STEP_4)
            $this->resetDynamic();
    }

    public function currentPageFn($page)
    {

        switch ($page) {
            case Category::STEP_2:
                $this->secondStep  = true;
                $this->firstStep = $this->thirdStep = $this->fourthStep = false;
                break;
            case Category::STEP_3:
                $this->thirdStep = true;
                $this->firstStep = $this->secondStep = $this->fourthStep = false;
                break;
            case Category::STEP_4:
                $this->fourthStep = true;
                $this->firstStep = $this->secondStep = $this->thirdStep = false;
                break;
            default:
                $this->firstStep = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;
        }
    }

    public function goBackFn($page)
    {

        $this->totalLevelDecFn();

        switch ($page) {
            case Category::STEP_1:
                $this->secondChildId = $this->selectedCategoryId = $this->thirdChildId  = null;
                $this->locationStep  = true;
                $this->firstStep = $this->secondStep = $this->thirdStep = $this->fourthStep = $this->paymentStep = false;
                break;
            case Category::STEP_2:
                $this->secondChildId =  $this->thirdChildId  = null;
                $this->firstStep  = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = $this->paymentStep = false;
                break;
            case Category::STEP_3:
                $this->secondStep = true;
                $this->thirdChildId = null;
                $this->firstStep = $this->thirdStep = $this->fourthStep = $this->paymentStep = false;
                break;
            case Category::STEP_4:
                $this->thirdStep = true;
                $this->firstStep = $this->secondStep = $this->fourthStep = $this->paymentStep = false;
                break;
            default:
                $this->secondChildId = $this->selectedCategoryId =    $this->thirdChildId  = null;
                $this->firstStep = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = $this->paymentStep = false;
        }
        $this->resetDynamic();
    }

    public function changeDate($selectedDate)
    {

        foreach ($this->dynamicForm as $form) {
            if ($form['type'] == FormField::DATE_FIELD) {
                $this->dynamicProperties[$form['title'] . '_' . $form['id']] = $selectedDate;
                break;
            }
        }
    }

    /***  set soring of queue code */
    protected function generateSequencePattern()
    {
        // Fetching category data only once and returning as a key-value pair
        return $cateogry =  Category::where('team_id', $this->teamId)
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhere('parent_id', '');
            })
            ->whereJsonContains('category_locations', "$this->location")
            ->orderBy('sort')
            ->pluck('visitor_in_queue', 'id');
    }


    public function getNextPrioritySort($categoryId)
    {
        // Get the category details for the given categoryId
        $category = Category::find($categoryId);
        $nextserial = 1;

        // Generate the sequence pattern dynamically
        $sequencePattern = $this->generateSequencePattern();
        // Exclude the current category from the sequence pattern
        $filteredCategories = $sequencePattern->except($category->id);
        // Sum of 'visitor_in_queue' values from the other categories
        $sumVisitorInQueue = $filteredCategories->sum() + ($sequencePattern[$category->id] ?? 0);
        // Fetch existing queues for the current team and location in one query
        $queues = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->where('category_id', $category->id)
            ->whereNotNull('priority_sort')
            ->whereDate('created_at', Carbon::today())
            ->pluck('priority_sort')
            ->toArray();

        // Get the max priority_sort value or set to nextserial if no queues exist

        $maxValue = !empty($queues) ? max($queues) : $nextserial;
        // Adjust max value if it's 0, and initialize queues if necessary
        if (empty($queues) || $maxValue == 0) {
            $maxValue = $nextserial;
            $queues = [];
        }

        // If the category sequence pattern is 1, we calculate priority differently
        if ($sequencePattern[$category->id] == 1) {
            if (!empty($queues)) {
                return $nextserial = $maxValue + $sumVisitorInQueue;
            } else {
                // Calculate the sum of all categories before the current one in the sequence
                $sumBefore = $this->sumCategoriesBefore($category->id, $sequencePattern);
                return $nextserial = $maxValue + $sumBefore;
            }
        }

        // Logic for when the category sequence pattern is greater than 1
        if ($sequencePattern[$category->id] > 1) {
            // For categories with sequence > 1, we check if the max value has been fully assigned
            if (!empty($queues)) {
                $countserial = $this->countAssignedPrioritySorts($category->id, $maxValue);

                // If all priority sorts are assigned, return the adjusted nextserial
                if ($countserial == $sequencePattern[$category->id]) {
                    return $nextserial = $maxValue + $sumVisitorInQueue - 1;
                } else {
                    return $nextserial = $maxValue + 1;
                }
            } else {
                // If no queues, calculate sum of categories before this category in sequence
                $sumBefore = $this->sumCategoriesBefore($category->id, $sequencePattern);
                return $nextserial = $maxValue + $sumBefore;
            }
        }
    }

    //  public function getNextSeniorPrioritySort($categoryId, $isSenior)
    // {
    //     // Get the category details for the given categoryId
    //     $category = Category::where('id', $categoryId)->select('id', 'sort')->first();
    //     $serialNumber = 1;

    //     if ($category) {
    //         if ((int)$category->sort == 1) {
    //             $serialNumber = $category->sort;
    //         } elseif ((int)$category->sort > 1) {
    //             if ($isSenior === 'Yes') {
    //                 $serialNumber = 1;
    //             } else {
    //                 $serialNumber = (int)$category->sort;
    //             }
    //         }
    //     }

    //     return $serialNumber;
    // }

  public function getNextSeniorPrioritySort($categoryId, $isSenior)
{
    $serialNumber = 1;

    $category = Category::where('id', $categoryId)->select('id', 'sort')->first();

    if ($category) {
        $sort = (int) $category->sort;

        // Always force serial = 1 if category sort is 1
        if ($sort == 1) {
            return 1;
        }

        // If third child exists and category sort is not 1
        if (!empty($this->thirdChildId)) {
            $thirdCategoryName = strtolower(Category::viewCategoryName($this->thirdChildId));

            $serialNumber = $thirdCategoryName == 'yes'
                ? 1
                : $sort;
        } else {
            // Otherwise fallback to isSenior flag
            $serialNumber = ($isSenior == 'Yes') ? 1 : $sort;
        }
    }

    return $serialNumber;
}

    // Helper method to calculate sum of all categories before a given category in the sequence
    protected function sumCategoriesBefore($categoryId, $sequencePattern)
    {
        $categoriesArray = $sequencePattern->toArray();
        $slicedArray = array_slice($categoriesArray, 0, array_search($categoryId, array_keys($categoriesArray)));
        return array_sum($slicedArray);
    }

    // Helper method to count assigned priority_sort values
    protected function countAssignedPrioritySorts($categoryId, $maxValue)
    {
        $countserial = 0;
        for ($i = $maxValue; $i >= 1; $i--) {
            $checkSort = QueueStorage::where('team_id', $this->teamId)
                ->where('locations_id', $this->location)
                ->where('category_id', $categoryId)
                ->whereNotNull('priority_sort')
                ->whereDate('created_at', Carbon::today())
                ->where('priority_sort', $i)
                ->exists();

            if ($checkSort) {
                $countserial++;
            } else {
                break;
            }
        }
        return $countserial;
    }

     public function checkAvailability()
    {
        $userTimezone = $this->siteDetails->select_timezone ?? 'Asia/Kolkata'; // Ideally fetch from DB or user settings
        // $userTimezone = 'UTC';
        $currentDate = Carbon::now($userTimezone)->format('Y-m-d');
        $currentDay = Carbon::now($userTimezone)->format('l');
        $currentTime = Carbon::now($userTimezone)->format('h:i A');

        $categoryLevelEnable = $this->siteData?->enable_time_slot;
      if($this->accountSetting->booking_system == 1 ){
        if ($categoryLevelEnable === 'category') {
            $categoryId = match ($this->siteData?->category_slot_level) {
                1 => $this->selectedCategoryId,
                2 => $this->secondChildId,
                3 => $this->thirdChildId,
                default => null
            };

            if (!$categoryId) return false;

            return $this->isWithinTimeSlot($categoryId, AccountSetting::CATEGORY_SLOT, $currentDate, $currentDay, $currentTime);
        }

        if ($categoryLevelEnable === 'ticket') {
            return $this->isWithinTimeSlot(null, AccountSetting::TICKET_SLOT, $currentDate, $currentDay, $currentTime);
        }

        return $this->isWithinTimeSlot(null, AccountSetting::LOCATION_SLOT, $currentDate, $currentDay, $currentTime);
    }else{
        return $this->isWithinTimeSlot(null, AccountSetting::LOCATION_SLOT, $currentDate, $currentDay, $currentTime);

    }
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
        // $checkRecord = AccountSetting::where('team_id', $this->teamId)
        //     ->where('location_id', $this->location)
        //     ->where('slot_type', AccountSetting::TICKET_SLOT)
        //     ->select('is_waitlist_limit', 'waitlist_limit')
        //     ->first();

         $checkRecord = $this->accountSetting;

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


    #[On('locationCodChange')]
    public function locationCodChange($latitude, $longitude)
    {

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        if (!empty($this->location) && !empty($this->accountSetting) && $this->isWithinRadius($this->latitude, $this->longitude)) {
            // Allow QR code scanning
            $this->dispatch('enable-qr-scanning');
        } else {
            // Deny QR code scanning
            $this->dispatch('deny-qr-scanning');
        }
    }

    #[On('locationError')]
    public function locationError()
    {
        \Log::info("Latitude: $this->latitude, Longitude: $this->longitude");
        // $this->dispatch('deny-qr-scanning');
    }

    private function isWithinRadius($lat, $lng, $radius = 100)
    {

        if ($this->accountSetting->is_geofence != 1 || is_null($this->accountSetting->is_geofence)) {
            return true;
        }
        $radius = $this->validDistance;
        // Fetch the central location details from the database
        $location_detail = Location::where('id', $this->location)->first();

        if (isset($location_detail) && !empty($location_detail->latitude) && !empty($location_detail->longitude)) {
            $centralLat = $location_detail->latitude; // Latitude of the central point
            $centralLng = $location_detail->longitude; // Longitude of the central point

            // Calculate the distance between the central point and the given coordinates
            $distance = $this->calculateDistance($centralLat, $centralLng, $lat, $lng);

            // Convert radius to meters (radius is already in meters, no conversion needed)
            $radiusInMeters = $radius;

            // Log the values for debugging
            // dd(round($distance) . ' / ' . round($radiusInMeters));

            // Compare the distance with the radius

            return round($distance) <= round($radiusInMeters);
        } else {
            return false;
        }
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        // Convert latitude and longitude from degrees to radians
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        // Haversine formula to calculate the distance
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c; // Distance in meters

        return $distance;
    }

    public function getAmount()
    {
        // Check if paymentSetting exists
        if (!empty($this->paymentSetting)) {
            // Check if both API key and secret are set
            if (!empty($this->paymentSetting->api_key) && !empty($this->paymentSetting->api_secret) && ($this->paymentSetting->stripe_enable == 1)) {
                config([
                    'services.stripe.key' => $this->paymentSetting->api_key,
                    'services.stripe.secret' => $this->paymentSetting->api_secret,
                ]);
            } else {
                // Show error message if keys are missing
                $this->dispatch('show-toast', type: 'error', message: 'Payment service keys are missing. Please set API Key and Secret in payment settings.');
                $this->isFree = 0;
                $this->paymentStep = false;
                return;
            }
        } else {
            // Show error message if payment setting is completely missing
            $this->dispatch('show-toast', type: 'error', message: 'Payment setting not configured. Please configure payment settings.');
            $this->isFree = 0;
            $this->paymentStep = false;
            return;
        }

        // Determine amount and stripe category based on selected category level
        if (!empty($this->thirdChildId) && $this->paymentSetting?->category_level === 3) {
            $this->amount = Category::where('id', $this->thirdChildId)->value('amount') ?? 0;
            $this->stripeCategory = $this->thirdChildId;
        } elseif (!empty($this->secondChildId) && $this->paymentSetting?->category_level >= 2) {
            $this->amount = Category::where('id', $this->secondChildId)->value('amount') ?? 0;
            $this->stripeCategory = $this->secondChildId;
        } elseif (!empty($this->selectedCategoryId)) {
            $this->amount = Category::where('id', $this->selectedCategoryId)->value('amount') ?? 0;
            $this->stripeCategory = $this->selectedCategoryId;
        } else {
            $this->amount = 0;
            $this->stripeCategory = null;
        }

        // Check if category is paid
        if ($this->paymentSetting->enable_payment == 1 && $this->amount > 0) {
            $this->isFree = Category::where('id', $this->stripeCategory)->value('is_paid') ?? 0;
        } else {
            $this->isFree = 0;
            $this->paymentStep = false;
        }
    }

    #[On('stripe-payment-method')]
    public function setPaymentMethod(string $paymentMethodId)
    {
        $this->getAmount();
        $this->paymentMethodId = $paymentMethodId;
        $this->handleCheckout();
    }


    public function handleCheckout()
    {
        try {

            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) round($this->amount * 100),
                'currency' => strtolower($this->paymentSetting->currency) ?? 'usd',
                'payment_method' => $this->paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'receipt_email' => $this->email,
                'return_url' => route('payment.success'),
            ]);

            $stripeResponse = StripeResponse::create([
                'team_id' => $this->teamId,
                'location_id' =>  $this->location,
                'category_id' => $this->stripeCategory,
                'payment_intent_id' => $paymentIntent->id,
                'customer_email' => $this->email,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'full_response' => $paymentIntent->toArray(),
            ]);
            $this->stripeResponeID = $stripeResponse->id;
            \Log::info("payment done");
            $this->saveQueueForm();

            $this->paymentStep = false;
            $this->isFree = 0;
            $this->amount = 0;
            $this->stripeCategory = '';
            $this->email = '';

            $this->successMessage = 'Payment successful!';
        } catch (\Exception $e) {
            \Log::error('Payment failed: teamID' . $this->teamId . '= ' . $e->getMessage());
            $this->errorMessage = 'Payment failed: Something went Wrong';
        }
    }

    public function handleLocationError($errorMessage)
    {
        \Log::error("Geolocation error: $errorMessage");
        dd("Geolocation error:" . $errorMessage);
        // You can handle the error message as needed, e.g., show it to the user
    }

    public function closeTheWaitlist()
    {
        $pendingQueues = QueueDB::getPendingQueues(
            ['team_id' => $this->teamId],
            $this->siteDetails?->fixed_visitor_list_queue == SiteDetail::STATUS_YES,
            $this->location,
            null,
            null,
            $this->teamId,
            QueueDB::DEFAULT_QUEUE
        );

        $currentServingQueue = QueueDB::currentVisitorRecord(
            ['team_id' => $this->teamId],
            null,
            null,
            null,
            (int) $this->location,
            null
        );

        $automationSettings = AutomationSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->first();

        $serviceTime = 0;

        if ($automationSettings?->is_close_waitlist_early == '1') {
            if ($this->siteDetails?->enable_time_slot === 'category') {
                // Calculate service time from pending queues
                foreach ($pendingQueues as $queue) {
                    $categories = array_filter([
                        $this->siteDetails?->category_slot_level == '1' ? $queue->category_id : '',
                        $this->siteDetails?->category_slot_level == '2' ? $queue->sub_category_id : '',
                        $this->siteDetails?->category_slot_level == '3' ? $queue->child_category_id : ''
                    ]);

                    if (!empty($categories)) {
                        $serviceTimes = Category::whereIn('id', $categories)->pluck('service_time');

                        $sum = $serviceTimes
                            ->filter() // removes empty strings, nulls, and falsy values
                            ->map(fn($time) => (int) $time) // cast all to int
                            ->sum();
                        $serviceTime += $sum;
                    }
                }

                // Add service time for current serving queue
                if (!empty($currentServingQueue)) {
                    $categories = array_filter([
                        $this->siteDetails?->category_slot_level == '1' ? $currentServingQueue->category_id : '',
                        $this->siteDetails?->category_slot_level == '2' ? $currentServingQueue->sub_category_id : '',
                        $this->siteDetails?->category_slot_level == '3' ? $currentServingQueue->child_category_id : ''
                    ]);

                    if (!empty($categories)) {
                        $serviceTimes = Category::whereIn('id', $categories)->pluck('service_time');
                        $sum = $serviceTimes
                            ->filter() // removes empty strings, nulls, and falsy values
                            ->map(fn($time) => (int) $time) // cast all to int
                            ->sum();
                        $serviceTime += $sum;
                    }
                }
            } else if ($this->siteDetails?->enable_time_slot === 'ticket') {
                foreach ($pendingQueues as $queue) {
                    $serviceTime += $this->siteDetails?->estimate_time;
                }

                $getTicketSlots = AccountSetting::where('team_id', $this->teamId)->where('location_id', $this->location)->where('slot_type', 'category')->value('business_hours');

                $ticketslots = json_decode($getTicketSlots, true);

                $today = Carbon::now()->format('l'); // Get today's day name like 'Monday'

                foreach ($ticketslots as $slots) {

                    if ($slots['day'] === $today && $slots['is_closed'] === 'open') {

                        // Get today's end time by combining today's date with the end_time
                        $endTimeToday = Carbon::parse(Carbon::now()->toDateString() . ' ' . $slots['end_time']);

                        // Get difference in minutes (can be negative if current time > end_time)
                        $minutesLeft = Carbon::now()->diffInMinutes($endTimeToday, false); // false => signed value

                        // ✅ Trigger if remaining time is exactly equal to custom minutes
                        if (abs($minutesLeft - $serviceTime) <= 1) {
                        } else {
                            $this->dispatch('a-lot-of-queues');
                            return false;
                        }
                    }
                }
            }
        }
    }

    /**
     * Step 4: Refresh access token using stored refresh token
     */
    public function getAccessTokenViaRefreshToken($refresh_token)
    {
        $response = Http::asForm()->post($this->token_url, [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['access_token'])) {

            // Store tokens in user table

                return [
                    'status'       => 'success',
                    'access_token' => $data['access_token'],
                    'instance_url' => $data['instance_url']
                ];
            }
        }

        return [
            'status'  => 'error',
            'message' => 'Unable to refresh access token',
            'raw'     => $response->json()
        ];
    }


    public function createLead(array $salesForceData)
{
    try {
        // Refresh token call
        $refreshToken = $salesForceData['refresh_token'];
        $data = $this->getAccessTokenViaRefreshToken($refreshToken);
        $access_token = $data['access_token'] ?? '';
        $instance_url = $data['instance_url'] ?? '';


        if (!isset($access_token) || empty($access_token)) {
            return [
                'status' => 'error',
                'message' => 'Unable to refresh access token',
                'response' => $data
            ];
        }

        // Salesforce API endpoint for Lead
        $url = $instance_url . '/services/data/v63.0/sobjects/Lead/';

        // Prepare payload
         $payload = [
            // 'FirstName'             => $salesForceData['FirstName'] ?? 'Guest',
            'LastName'              => !empty($salesForceData['FirstName']) ? $salesForceData['FirstName'] : 'Guest',
            'MobilePhone'                 => $salesForceData['Phone'] ?? null,
            'QwaitingSyncDate__c' => $salesForceData['Qwaiting_Sync_Date__c'] ?? now()->toDateTimeString(),
            'ServiceName__c'       => $salesForceData['Service_Name__c'] ?? null,
            'Token__c'       => $salesForceData['Token'] ?? null,
            'Page__c'       => $salesForceData['Page'] ?? null,
            'Created__c'       => $salesForceData['Created'] ?? null,
            'QueueStorageId__c'       => (int)$salesForceData['queue_storage_id'] ?? null,
            'Company'   => $salesForceData['Company'] ?? 'Qwaiting Customer',

        ];

        // if (empty($salesForceData['Email'])) {
        //   $payload['Email_Not_Available__c'] = true;
        // } else {
        //     $payload['Email'] = $salesForceData['Email'];
        // }



        // Make request using Laravel HTTP client
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        $responseData = $response->json();

        if ($response->successful() && isset($responseData['id'])) {
            return [
                'status' => 'success',
                'message' => "Lead created successfully. Salesforce ID: " . $responseData['id'],
                'id' => $responseData['id'],
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to create lead',
                'response' => $responseData
            ];
        }

    } catch (\Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

 public function saveQueueFormDepartment()
    {

        if (!empty($this->dynamicProperties)) {
            // $this->validate();
            $this->validate($this->rules());
        }


        // try {

             DB::beginTransaction();

              $selected = json_decode($this->categoryGroupData->grouping_data, true) ?? [];

              if(empty($selected)){
                 $this->dispatch('swal:ticket-generate', [
                        'title' => 'Oops...',
                        'text' => 'Workflow department is not set',
                        'icon' => 'error'
                    ]);
                    return;
              }

              uasort($selected, function ($a, $b) {
    return $a['priority'] <=> $b['priority'];
});


            $this->dispatch('queue:refresh');

            $newToken = $lastToken = '';
            $formattedFields = [];
            $assigned_staff_id = null;
            foreach ($this->dynamicProperties as $key => $value) {
                $trimmedKey = trim($key);
                $fieldName = preg_replace('/_\d+/', '', $trimmedKey);
                $fieldName = strtolower($fieldName); // normalize to lowercase
                $formattedFields[$fieldName] = $value;
            }

            $this->name = $formattedFields['name'] ?? null;

            $possiblePhoneKeys = FormField::possiblePhoneKeys();
           $this->phone = null;

            foreach ($possiblePhoneKeys as $key) {
                if (isset($formattedFields[$key]) && !empty($formattedFields[$key])) {
                    $this->phone = $formattedFields[$key];
                    break;
                }
            }
            $this->email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['Email']) ? $formattedFields['Email'] : null);

            $jsonDynamicData = json_encode($formattedFields);

            if (!empty($this->selectedCategoryId)) {
                $this->acronym = Category::viewAcronym($this->selectedCategoryId);
            } else {
                $this->acronym = SiteDetail::DEFAULT_WALKIN_A;
            }

            $lastToken = QueueDB::getLastToken($this->teamId, $this->acronym, $this->location);

            $token_digit = $this->siteDetails?->token_digit ?? 4;  //4
            $isExistToken = true;

            while ($isExistToken) {
                $newToken = QueueDB::newGeneratedToken($lastToken, $this->siteDetails?->token_start, $token_digit);

                if (strlen($newToken) > $token_digit) {
                    $this->dispatch('swal:ticket-generate', [
                        'title' => 'Oops...',
                        'text' => 'Unable to create more tickets',
                        'icon' => 'error'
                    ]);
                    return;
                }

                $isExistToken = QueueDB::checkToken($this->teamId, $this->acronym, $newToken, $this->location);

                if ($isExistToken) {
                    $lastToken = $newToken;
                } else {
                    $this->token_start = $newToken;
                    $isExistToken = false;
                }
            }





            $timezone = config('app.timezone'); // fallback if not set

            if ($this->siteData && $this->siteData->select_timezone) {
                $timezone = $this->siteData->select_timezone;
                Config::set('app.timezone', $timezone);
                date_default_timezone_set($timezone); // optional but good for consistency
            }

            if (!empty($this->utm_source) && !empty($this->utm_medium) && !empty($this->utm_campaign)) {
                $getCampaign = MetaAdsAndCampaignsLink::where('source', $this->utm_source)->where('medium', $this->utm_medium)->where('campaign', $this->utm_campaign)->first();

                $campaignId = $getCampaign->id;
            }

            $decodedJson = json_decode($jsonDynamicData, true);

            $todayDateTime = Carbon::now($timezone);

                $is_virtual_meeting =0;
                // if($this->enableVirtual){
                if (isset($decodedJson['type']) && $decodedJson['type'] === 'Virtual') {
                $is_virtual_meeting =1;
                }

            $queueCreated = QueueDB::storeQueue([
                'team_id' => (int) $this->teamId, // Cast to integer
                'token' => (string) $this->token_start, // Ensure it's a string
                'start_acronym' => (string) $this->acronym, // Ensure it's a string
                'token_with_acronym' => $this->booking_setting == QueueDB::STATUS_NO
                    ? QueueDB::LABEL_YES
                    : QueueDB::LABEL_NO, // Conditional assignment
                'locations_id' => (int) $this->location, // Cast to integer
                'arrives_time' => $todayDateTime, // Ensure valid datetime format
            ]);

            $called_first =true;
            foreach($selected as $catkey=>$queuestore){

                $called ='no';
                 $nextPrioritySort = (int)$queuestore['priority'];
                 $assigned_staff_id = (int)$queuestore['staff'];
                 $counter_id = (int)$queuestore['counter'];
                 $this->secondChildId = (int)$catkey;
                 if($called_first){
                    $called ='yes';
                 }

                 $called_first =false;
                  $storeData = [
                'name' => $this->name,
                'phone' => $this->phone,
                'ticket_mode' => $this->isMobile ? QueueDB::TICKET_MODE_MOBILE : QueueDB::TICKET_MODE_Walk_IN,
                'is_virtual_meeting' =>$is_virtual_meeting,
                'phone_code' => $this->phone_code ?? '91',
                'category_id' => $this->selectedCategoryId ?? null,
                'sub_category_id' => $this->secondChildId ?? null,
                'child_category_id' => null,
                'team_id' => (int)$this->teamId,
                'token' => $this->token_start,
                'token_with_acronym' => $this->booking_setting == QueueDB::STATUS_NO ? QueueDB::LABEL_YES : QueueDB::LABEL_NO,
                'json' => $jsonDynamicData,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $this->acronym,
                'locations_id' => (int)$this->location,
                'priority_sort' => $nextPrioritySort ?? 0,
                'campaign_id' => isset($campaignId) ? $campaignId : null,
                'mode' => $this->isMobile ? 'mobile' : 'web',
                'senior_citizen' => $seniorCitiizen ?? 'No',
                'counter_id' => $counter_id,
                'called' => $called,
            ];


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

            if (!empty($this->secondChildId))
                $this->secondCategoryName = Category::viewCategoryName($this->secondChildId);
            if (!empty($this->selectedCategoryId))
                $this->categoryName =  Category::viewCategoryName($this->selectedCategoryId);

            if ($this->siteDetails?->category_estimated_time == SiteDetail::STATUS_YES)
                $this->determineCategoryColumn();

            if ($this->siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO)
                $this->counterID  = 0;


             $pendingwaiting = $pendingCount = 0;


            if ($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES) {


                $estimatedetail = QueueStorage::countPendingByCategory($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                if ($estimatedetail == false) {
                    $pendingCount = QueueStorage::countPending($this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '', $this->location);
                } else {
                    $pendingCount = $estimatedetail['customers_before_me'] ?? 0;
                    $pendingwaiting = $estimatedetail['estimated_wait_time'] ?? 0;

                }
            } else {

            $pendingCountget = (int)QueueStorage::countPending($this->teamId, $queueStorage->id, '', '', '', $this->location);
            $counterCount = Counter::where('team_id',$this->teamId)->whereJsonContains('counter_locations', "$this->location")->where('show_checkbox',1)->count();
              if((int)$pendingCountget > 0 && (int)$counterCount > 0){
                     $pendingCount = floor((int)$pendingCountget / (int)$counterCount);
                }
            }

            $dateformat = AccountSetting::showDateTimeFormat();
            $data = [
                'name' => $queueStorage->name,
                'phone' => $queueStorage->phone,
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueCreated->id,
                'queue_storage_id' => $queueStorage->id,
                'arrives_time' => Carbon::parse($queueCreated->created_at)->format($dateformat),
                'category_name' => $this->categoryName,
                'thirdC_name' => $this->thirdCategoryName,
                'secondC_name' => $this->secondCategoryName,
                'pending_count' => $pendingCount,
                'token' => $queueCreated->start_acronym . $queueCreated->token,
                'token_with_acronym' => $queueCreated->start_acronym,
                'to_mail' => $this->email ?? '',
                'locations_id' => $this->location,
                'location_name' => $this->locationName,
                'priority_sort' => $nextPrioritySort ?? 0,
            ];


              $language = session('app_locale');

            $showQrcode = $this->siteDetails->is_qrcode_ticket == 1 ? true : false;
            $showlogo = $this->siteDetails->is_logo_on_print == 1 ? true : false;
            $showusername = $this->siteDetails->is_name_on_print == 1 ? true : false;
            $showarrived = $this->siteDetails->is_arrived_on_print == 1 ? true : false;
            $showlocation = $this->siteDetails->is_location_on_print == 1 ? true : false;
            $showcategory = $this->siteDetails->is_category_on_print == 1 ? true : false;
            $showTextmessage = $this->siteDetails->ticket_text_enable == 1 ? true : false;
            $showToken = $this->siteDetails->is_token_on_print == 1 ? true : false;

            if ($language !== 'en') {
                $nameLabel = isset($this->translations['Print Name Label'][$language]) ? $this->translations['Print Name Label'][$language] : ($this->siteDetails->print_name_label ?? 'Name');
                $tokenLabel = isset($this->translations['Print Token Label'][$language]) ? $this->translations['Print Token Label'][$language] : ($this->siteDetails->print_token_label ?? 'Token');
                $arrivedLabel = isset($this->translations['Arrived Time Label'][$language]) ? $this->translations['Arrived Time Label'][$language] : ($this->siteDetails->arrived_time_label ?? 'Arrived');
            } else {
                $nameLabel = $this->siteDetails->print_name_label ?? 'Name';
                $tokenLabel = $this->siteDetails->print_token_label ?? 'Token';
                $arrivedLabel = $this->siteDetails->arrived_time_label ?? 'Arrived';
            }

            $baseencodeQueueId = base64_encode($queueCreated->id);
            $customUrl = url("/visits/{$baseencodeQueueId}");
            $qrcodeSvg = QrCode::format('svg')
                ->size(150)
                ->errorCorrection('H')
                ->generate($customUrl);



            $logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId, $this->location);

            $waitingTime = 0;

            if (!empty($this->siteDetails)) {
                $estimate_time = $this->siteDetails->estimate_time ?? 0;

                if ($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES) { // get esitmate time of category wise
                    $waitingTime =  $pendingwaiting ?? $estimate_time * $data['pending_count'];
                } else {  // get esitmate time of globally set
                    $waitingTime =  $estimate_time * $data['pending_count'];
                }

                if ($this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {

                    if (!empty($this->siteDetails->ticket_text_2)) {
                        if ($language !== 'en' && isset($this->translations['Ticket Message 1'][$language])) {
                            $this->showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $this->translations['Ticket Message 1'][$language]);
                        } else {
                            $this->showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $this->siteDetails->ticket_text_2);
                        }
                    }
                    if (!empty($this->siteDetails->ticket_text)) {

                        if ($language !== 'en' && isset($this->translations['Ticket Message 2'][$language])) {
                            $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $this->translations['Ticket Message 2'][$language]);
                            $this->showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                        } else {
                            $text = str_replace('{{QUEUE COUNT}}', $data['pending_count'], $this->siteDetails->ticket_text);
                            $this->showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                        }
                    }
                }
            }
            $queueStorage->waiting_time = $waitingTime;
            $queueStorage->assign_staff_id = $assigned_staff_id ?? null;
            $queueStorage->queue_count = $pendingCount;
            $queueStorage->save();



             $data = array_merge($data, ['waiting_time' => $waitingTime,'ticket_link' => $customUrl]);

            $data['meeting_link'] = $storeData['meeting_link'];

            //store customer data and activity log

            if (!empty($this->phone)) {
                $existingCustomer = Customer::where('phone', "$this->phone")
                    ->where('team_id', $this->teamId)
                    ->where('location_id', $this->location)
                    ->first();

                // Create customer if not exists
                if (empty($existingCustomer)) {
                    $existingCustomer = Customer::create([
                        'team_id' => $this->teamId,
                        'location_id' => $this->location,
                        'name' => $this->name ?? null,
                        'phone' => $this->phone,
                        'json_data' => $jsonDynamicData, // casted automatically to JSON
                    ]);
                }

                // Log customer activity with type 'queue'
                CustomerActivityLog::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->location,
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

            $data['ticket_link'] = url('/visits/' . base64_encode($queueCreated->id));

            $logData = [
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => $queueStorage->served_by,
                'customer_id' => $queueStorage->created_by,
                'queue_id' => $queueStorage->queue_id,
                'queue_storage_id' => $queueStorage->id,
                'email' => $this->email,
                'contact' => $this->phone,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Ticket Generate',
            ];

            // $this->sendNotification($data, 'ticket created', $logData);
            // if(!empty($storeData['meeting_link']))
            // {
                //     $this->sendNotification($data, 'virtual meeting', $logData);
                // }

                ActivityLog::storeLog($this->teamId, $this->userAuth->id ?? null, null, null, 'Ticket Generate',  $this->location, ActivityLog::QUEUE, null, $this->userAuth ?? null);


                DB::commit();

            }
            $this->resetForm();
            QueueCreated::dispatch($queueStorage);



          if (!$this->isMobile && $this->siteDetails->is_redirect_print_page == SiteDetail::STATUS_YES && !$this->disable_print) {

                 $this->redirect('/ticket-print/' . base64_encode($queueStorage->id));
            }

             if (!$this->isMobile && $this->siteDetails->is_redirect_print_page == SiteDetail::STATUS_NO && !$this->disable_print) {

                if($this->siteDetails->print_mode === 'default')
                {

                    $this->dispatch('swal:saved-queue-no-print', [
                        'timer' => 8000,
                        'html' => '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                            ($showlogo ? '<img src="' . asset($logo) . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                            '</div>
                    <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                        ' . ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                        ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $data['token'] . '</strong></h3></div>' : '') . '

                        ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                        ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['category_name']][session('app_locale')]) ? $this->translations[$data['category_name']][session('app_locale')] : $data['category_name']) . '</h3></div>' : '') . '
                        ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') . '
                        ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                    </div>',
                        'confirmButtonText' => $language !== 'en' ? (isset($this->translations['Confirm Button Label'][$language]) ? $this->translations['Confirm Button Label'][$language] :  $this->siteDetails->confirm_btn_label) : ($this->siteDetails->confirm_btn_label ?? 'Thank you'),
                        'token_notify' => 'The Generated Token Number is ' . $this->acronym . $data['token']

                    ]);
                }
                else
                {



                    // $this->dispatch('silent-print', html: '<div style="padding-top:20px;text-align:center" class="flex content-center gap-4">' .
                    //         ($showlogo ? '<img src="' . asset($logo) . '" class="w-100 h-100" style="margin:auto;max-width:160px"/>' : '') .
                    //         '</div>
                    // <div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;">
                    //     ' . ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') . '
                    //     ' . ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $data['token'] . '</strong></h3></div>' : '') . '
                    //     ' . ($showarrived ? '<div><h5 style="font-size:16px;margin:0">' . $arrivedLabel . ': ' . $data['arrives_time'] . '</h5></div>' : '') . '
                    //     ' . ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') . '
                    //     ' . ($showcategory ? '<div><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['category_name']][session('app_locale')]) ? $this->translations[$data['category_name']][session('app_locale')] : $data['category_name']) . '</h3><h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['secondC_name']][session('app_locale')]) ? $this->translations[$data['secondC_name']][session('app_locale')] : $data['secondC_name']) . '</h3><h3 style="font-size:16px;">' . (isset($this->translations[$data['thirdC_name']][session('app_locale')]) ? $this->translations[$data['thirdC_name']][session('app_locale')] : $data['thirdC_name']) . '</h3></div>' : '') . '
                    //     ' . ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') . '
                    //     ' . ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') . '
                    // </div>');

            $html =
                '<div style="padding-top:20px;text-align:center;display:flex;align-items:center;justify-content:center;gap:16px;">' .
                    ($showlogo ? '<img src="' . asset($logo) . '" style="margin:auto;max-width:160px;width:100%;height:100%;"/>' : '') .
                '</div>' .
                '<div style="display:flex;flex-direction:column;gap:8px;color:#333;padding-top:20px;line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family:\'Simplified Arabic Fixed\',sans-serif;">' .
                    ($showusername ? '<h3 style="font-size:16px;margin:0">' . $nameLabel . ': ' . $data['name'] . '</h3>' : '') .
                    ($showToken ? '<div><h3 style="font-size:16px;margin:0"><strong>' . $tokenLabel . ': ' . $data['token'] . '</strong></h3></div>' : '') .
                    ($showlocation ? '<div><h3 style="font-size:16px;margin:0">' . $data['location_name'] . '</h3></div>' : '') .
                    ($showcategory ?
                        '<div>' .
                            '<h3 style="font-size:16px;margin:0">' . (isset($this->translations[$data['category_name']][session('app_locale')]) ? $this->translations[$data['category_name']][session('app_locale')] : $data['category_name']) . '</h3>' .
                        '</div>'
                    : '') .
                    ($showTextmessage ? '<div><h4 style="font-size:16px;margin:0">' . $this->showTicketText . '</h4><h4 style="font-size:16px;margin:0">' . $this->showTicketText_2 . '</h4></div>' : '') .
                    ($showQrcode ? '<div style="display:flex;justify-content:center;align-items:center;margin-top:10px;"><img src="data:image/svg+xml;base64,' . base64_encode($qrcodeSvg) . '" style="width:120px;height:120px;"/></div>' : '') .
                '</div>';

             $limitchecked =$this->checkedTicketLimit();
             if($limitchecked){
               $this->dispatch('swal:limit-exceed', [
              'title' => 'Oops...',
              'text' => 'You have reached daily ticket limit',
              'icon' => 'error'
            ]);
           return;
        }




                }

            }

            $this->dispatch('swal:limit-exceed');

        // } catch (\Throwable $ex) {

        //     DB::rollback();

        //     Log::emergency('Queue generate issue on desktop');
        //     Log::emergency($ex);
        //     $this->dispatch('swal:ticket-generate', [
        //         'title' => 'Oops...',
        //         'text' => 'Unable to generate ticket. Please contact to the admin',
        //         'icon' => 'error'
        //     ]);
        // }
    }
}
