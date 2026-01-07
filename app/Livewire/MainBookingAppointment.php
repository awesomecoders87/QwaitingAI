<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Category,
    Booking,
    Location,
    AccountSetting,
    SiteDetail,
    SmtpDetails,
    GenerateQrCode,
    FormField,
    ColorSetting,
    SmsAPI,
    Country,
    ServiceSetting,
    PaymentSetting,
    Customer,
    CustomerActivityLog,
    StripeResponse,
    User,
    CustomSlot,
    SuspensionLog,
    Queue,
    PreferBooking,
    LanguageSetting,
    MetaAdsAndCampaignsLink,
    MessageDetail,
    ActivityLog,
    QueueFreeSlotCount,
    AllowedCountry,
};
use App\Traits\SendsEmails;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use DB;
use Str;
use DateTime;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\Title;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmation;
use Illuminate\Support\Collection;
use Carbon\CarbonPeriod;
use App\Services\MicrosoftGraphService;
use App\Mail\SuspensionNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;


// #[Layout('components.layouts.custom-layout')]
class MainBookingAppointment extends Component
{
    use SendsEmails;

    #[Title('Online Booking')]
    // protected $middleware = ['auth'];

    public $selectedCategoryId;
    public $teamId;
    public $user;
    public $locationId;
    public $location;
    public $locationName;
    public $accountSetting;
    public $parentCategory;
    public $colorSetting;
    public $siteSetting;
    public $firstChildren;
    public $secondChildren, $thirdChildren, $secondChildId, $thirdChildId;
    public $name = '';
    public $phone = '';
    public $email;
    public $showFormQueue;
    public $locationslots;

    public $locationStep = true;
    public $firstpage = false;
    public $secondpage = false;
    public $thirdpage = false;
    public $calendarpage = false;
    public $formfieldSection = false;
    public $paymentStep = false;


    public $slots;
    public $selectedYear;
    public $years = [];
    public $appointment_date;
    public $appointment_time;
    public $booking_type;
    public $disabledDate = [];
    public $allCategories = [];
    public $allLocations = [];
    public $fontSize = 'text-3xl';
    public $fontFamily = 'font-sans';
    public $borderWidth = 'border-4';

    public $dynamicForm = [];
    public $dynamicProperties = [];
    public $totalLevelCount = Category::STEP_1;
    public $phone_code = null;
    public $selectedCountryCode;
    public $countryCode = [];
    public $start_time;
    public $end_time;
    public $mindate = 0;
    public $maxdate = 30;
    public $weekStart = "Sunday";

    public $categoryName = '';
    public $secondCategoryName = '';
    public $thirdCategoryName = '';

    public $isFree = true;
    public $amount;
    public $stripeCategory;
    public $paymentMethodId;
    public $successMessage;
    public $errorMessage;
    public $stripeResponeID;
    public $paymentSetting;
    public $timezone;

    public $note;
    public $enable_service;
    public $enable_service_time;
    public $serviceSetting;

    //staff type and get staff id to assign booking
    public $assignedStaffId;

    //video link variable
    public string $organizerEmail = 'rajendra@stelleninfotech.in'; // Office 365 email of RE
    public string $meetingLink = '';

    //customer login
    public $isCustomerLogin = false;
    public $mobile;
    public $otp;
    public $showOtpField = false;
    public $verificationId;
    public $customer_phone_code;

    public $isPreferTimeModel = false;
    public $preferTimeBooking = false;
    public $preferStartTime;
    public $showPreferButton = false;
    public $utm_source;
    public $utm_medium;
    public $utm_campaign;
    public $userAuth;


    public $allowed_Countries = [];
    public $country_phone_mode = 1;


    public function mount(Request $request, $location_id = null)
    {
        $this->booking_type = $request->query('booking_type');
        $this->utm_source = $request->query('utm_source');
        $this->utm_medium = $request->query('utm_medium');
        $this->utm_campaign = $request->query('utm_campaign');

        Queue::timezoneSet();

        $this->timezone = Session::get('timezone_set') ?? 'UTC';
        $this->showFormQueue = false;
        $this->user = Auth::user();
        $this->teamId = tenant('id');
        $this->userAuth = Auth::user();
        //    dd(App::getLocale());
        //  $videoLink = 'https://teams.microsoft.com/l/meetup-join/' . Str::uuid();
        //  dd($videoLink);
        // $this->locationId = Session::get('selectedLocation');

        // Check for route parameter
        if (!Session::has('selectedLocation') && $location_id !== null) {
            $this->locationId = base64_decode($location_id, true);
            Session::put('selectedLocation', $this->location);
        } else {
            $this->locationId = Session::get('selectedLocation');
        }

        $this->stripeResponeID = '';
        $this->totalLevelCount = Category::STEP_1;

        if (!empty($this->locationId)) {
            $this->updatedLocation($this->locationId);
        } else {
            $this->locationId = '';
            $this->location = '';
            $this->allLocations = Location::select('id', 'location_name', 'address', 'location_image')->where('team_id', $this->teamId)->where('status', 1)->get();
            $this->locationStep = true;
            $this->firstpage = false;
        }

        $setting = LanguageSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->first();


        if ($setting && $setting->enabled_language_settings && !empty($setting->default_language)) {
            App::setLocale($setting->default_language);
            Session::put('app_locale', $setting->default_language);

            if (!Session::has('language_applied_once') && $setting->default_language !== 'en') {
                Session::put('language_applied_once', true);

                // Dispatch JavaScript to reload the page once
                $this->dispatch('reload');
            }
        }
        //  $this->createVideoCall();
        //  dd($this->meetingLink );

    }

    public function updatedLocation($value)
    {

        $this->locationId = $value;
        Session::forget('selectedLocation');
        Session::put('selectedLocation', $this->locationId);

        $this->locationName = Location::locationName($this->locationId);
        $this->locationStep = false;
        $this->firstpage = true;
        $currentYear = date('Y');
        $this->years = range($currentYear, $currentYear + 1);
        $this->selectedYear = $currentYear;

        $this->siteSetting = SiteDetail::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

        if (!$this->siteSetting) {
            abort(402);
        }
        $this->accountSetting = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)->first();

        $this->paymentSetting = PaymentSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

        if ($this->paymentSetting) {
            config([
                'services.stripe.key' => $this->paymentSetting->api_key,
                'services.stripe.secret' => $this->paymentSetting->api_secret,
            ]);
        }

        if (empty($this->accountSetting) || $this->accountSetting->booking_system == 0) {
            abort(403);
        }
        if (isset($this->accountSetting)) {
            $this->mindate = empty($this->accountSetting->allow_req_min_before) ? 0 : $this->accountSetting->allow_req_min_before;
            $this->maxdate = empty($this->accountSetting->allow_req_before) ? 30 : $this->accountSetting->allow_req_before;
            $this->weekStart = empty($this->accountSetting->week_start) ? "Sunday" : $this->accountSetting->week_start;
        }

        $this->resetDynamic();

        if (!empty($this->siteSetting)) {
            $this->fontSize = $this->siteSetting->category_text_font_size ?? $this->fontSize;
            $this->borderWidth = $this->siteSetting->category_border_size ?? $this->borderWidth;
            $this->fontFamily = $this->siteSetting->ticket_font_family ?? $this->fontFamily;
        }
        //get location detail
        $this->location = Location::find($this->locationId);

        // get Account detail of current location
        $locationSlotsDetail = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slot_type', AccountSetting::LOCATION_SLOT)
            ->select('id', 'business_hours')
            ->first();

        $this->locationslots = json_decode($locationSlotsDetail['business_hours'], true);

        //fetch parent Category
        $this->parentCategory = Category::getFirstCategorybooking($this->teamId, $this->locationId);

        $this->colorSetting = ColorSetting::where('team_id', $this->teamId)->first();
        $this->totalLevelCount = Category::STEP_1;
        //default today select
        $this->appointment_date = Carbon::today($this->timezone);
        $this->appointment_time = '';
        $this->countryCode = Country::query()->pluck('phonecode');

        $this->isCustomerLogin = $this->siteSetting->is_customer_login == 1 ? true : false;
        $this->showPreferButton = $this->siteSetting->is_prefer_time_slot == 1 ? true : false;
        $this->selectedCountryCode = !empty($this->siteSetting->country_code) ? $this->siteSetting->country_code : null;
        $this->phone_code = !empty($this->selectedCountryCode) ? $this->selectedCountryCode : '91';

        if (Session::has('login_customer_detail')) {

            $this->isCustomerLogin = false;
        }
        // $this->firstpage = true;

        $timezone = $this->siteSetting->select_timezone ?? 'UTC';
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        $this->country_phone_mode = $this->siteSetting->country_options ?? 1;

        $this->allowed_Countries = AllowedCountry::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)->select('id', 'name', 'phone_code')->get();
        if ($this->country_phone_mode != 1 && !empty($this->allowed_Countries)) {
            $this->phone_code = $this->allowed_Countries[0]->phone_code;
        }


        $this->dispatch('header-show');
    }

    public function goBackFn($page)
    {

        $this->totalLevelDecFn();

        switch ($page) {

            case Category::STEP_2:
                $this->secondChildId = $this->thirdChildId = null;
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_1;
                $this->firstpage = true;
                break;
            case Category::STEP_3:
                $this->resetallpages();
                $this->thirdChildId = null;
                $this->totalLevelCount = Category::STEP_2;
                $this->secondpage = true;
                break;
            case Category::STEP_4:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_3;
                $this->thirdpage = true;
                break;
            case Category::STEP_5:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_4;
                $this->calendarpage = true;
                break;
            case Category::STEP_6:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_5;
                $this->formfieldSection = true;
                break;
            default:
                $this->secondChildId = $this->selectedCategoryId = $this->thirdChildId = null;
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_1;
                $this->firstpage = true;
        }
    }

    public function modelPreferTimeSlot()
    {
        $this->isPreferTimeModel = true;
        $this->dispatch('open-modal', id: 'preferTimeModel');
    }

    public function addPreferTime()
    {
        if (empty($this->preferStartTime)) {
            $this->dispatch('close-modal', id: 'preferTimeModel');
            $this->dispatch('swal:time-required');
            return;
        }
        $this->preferTimeBooking = true;
        $this->preferStartTime = Carbon::createFromFormat('H:i', $this->preferStartTime)->format('h:i A');
        $this->appointment_time = '';
        $timeslotsExlplode = $this->preferStartTime;
        if (empty($timeslotsExlplode)) {
            $this->start_time = null;
            $this->end_time = null;
        } else {
            $interval = (int) $this->accountSetting?->slot_period ?? 10;
            $this->start_time = $timeslotsExlplode;
            $this->end_time = null;
        }

        $this->getAmount();

        if (!empty($this->preferStartTime)) {

            $this->locations = false;
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->calendarpage = false;
            $this->paymentStep = false;
            $this->formfieldSection = true;
        }



        $this->resetDynamic();
        $this->dispatch('close-modal', id: 'preferTimeModel');
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

    public function resetallpages()
    {
        $this->locationStep = false;
        $this->firstpage = false;
        $this->secondpage = false;
        $this->thirdpage = false;
        $this->calendarpage = false;
        $this->paymentStep = false;
        $this->formfieldSection = false;
    }

    public function showFirstChild($categoryId)
    {

        $this->selectedCategoryId = $categoryId;

        $this->firstChildren = Category::getchildDetailBooking($categoryId, $this->locationId);
        $this->totalLevelIncFn();
        if (count($this->firstChildren) > 0) {
            $this->firstpage = false;
            $this->thirdpage = false;
            $this->calendarpage = false;
            $this->formfieldSection = false;
            $this->paymentStep = false;
            $this->secondpage = true;
        } else {

            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->formfieldSection = false;
            $this->paymentStep = false;
            $this->calendarpage = true;
            $this->timeSlots();
            $category = Category::find($this->selectedCategoryId);

            $this->note = $category?->note ?? '';
            $this->enable_service = $category?->is_service_template ?? '';
            $this->enable_service_time = $category?->service_time ?? 'N/A';
            $this->dispatch('update-calendar', [
                'year' => now()->year,  // Get current year dynamically
                'month' => now()->month - 1,
                'disabledDate' => $this->disabledDate,
            ]);
        }
    }



    public function showSecondChild($categoryId)
    {

        $this->secondChildId = $categoryId;

        $this->secondChildren = Category::getchildDetailBooking($categoryId, $this->locationId);
        $this->totalLevelIncFn();

        if (count($this->secondChildren) > 0) {
            $this->firstpage = false;
            $this->secondpage = false;
            $this->calendarpage = false;
            $this->formfieldSection = false;
            $this->paymentStep = false;
            $this->thirdpage = true;
        } else {
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->formfieldSection = false;
            $this->paymentStep = false;

            $category = Category::find($this->secondChildId);

            $this->note = $category?->note ?? '';
            $this->enable_service = $category?->is_service_template ?? '';
            $this->enable_service_time = $category?->service_time ?? 0;

            $this->calendarpage = true;
            $this->timeSlots();

            $this->dispatch('update-calendar', [
                'year' => now()->year,  // Get current year dynamically
                'month' => now()->month - 1,
                'disabledDate' => $this->disabledDate,
            ]);
        }
    }
    public function showThirdChild($categoryId)
    {
        $this->thirdChildId = $categoryId;
        $this->thirdChildren = Category::getchildDetailBooking($categoryId, $this->locationId);
        if (count($this->thirdChildren) == 0) {
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->paymentStep = false;
            $this->formfieldSection = false;
            $category = Category::find($this->thirdChildId);
            $this->note = $category?->note ?? '';
            $this->enable_service = $category?->is_service_template ?? '';
            $this->enable_service_time = $category?->service_time ?? 0;
            $this->calendarpage = true;
            $this->timeSlots();
            $this->dispatch('update-calendar', [
                'year' => now()->year,  // Get current year dynamically
                'month' => now()->month - 1,
                'disabledDate' => $this->disabledDate,
            ]);
        }
    }

    public function updatedAppointmentTime($value)
    {
        $this->appointment_time = $value;
        $current = $value;

        $this->preferTimeBooking = false;
        $this->preferStartTime = '';

        $timeslotsExlplode = explode('-', $current);
        if ($this->start_time == $timeslotsExlplode[0]) {
            $this->start_time = null;
            $this->end_time = null;
        } else {
            $interval = (int) $this->accountSetting?->slot_period ?? 10;
            $this->start_time = $timeslotsExlplode[0];
            $this->end_time = $timeslotsExlplode[1];
        }

        $this->getAmount();

        if (!empty($value)) {

            $this->locations = false;
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->calendarpage = false;
            $this->paymentStep = false;
            $this->formfieldSection = true;
        }

        $this->resetDynamic();
    }

    // get time slots
    public function timeSlots()
    {

        if ($this->siteSetting->category_slot_level == 1 && $this->selectedCategoryId) {
            $categoryId = $this->selectedCategoryId;
        } elseif ($this->siteSetting->category_slot_level == 2 && $this->secondChildId) {
            $categoryId = $this->secondChildId;
        } elseif ($this->siteSetting->category_slot_level == 3 && $this->thirdChildId) {
            $categoryId = $this->thirdChildId;
        } else {
            $categoryId = $this->selectedCategoryId;
        }
        if ($this->siteSetting->category_level_est == "parent" && $this->selectedCategoryId) {
            $estimatecategoryId = $this->selectedCategoryId;
        } elseif ($this->siteSetting->category_level_est == "child" && $this->secondChildId) {
            $estimatecategoryId = $this->secondChildId;
        } elseif ($this->siteSetting->category_level_est == "automatic" && $this->thirdChildId) {
            $estimatecategoryId = $this->thirdChildId;
        } else {
            $estimatecategoryId = $this->selectedCategoryId;
        }

        if ($this->siteSetting->choose_time_slot != 'staff') {

            $this->slots = AccountSetting::checktimeslot($this->teamId, $this->locationId, $this->appointment_date, $categoryId, $this->siteSetting);
        } else {
            // Remove null values from category array
            $selectedCategories = array_filter([
                $this->selectedCategoryId ?? null,
                $this->secondChildId ?? null,
                $this->thirdChildId ?? null
            ], fn($val) => !is_null($val));

            $staffIds = User::whereHas('categories', function ($query) use ($selectedCategories) {
                $query->whereIn('categories.id', $selectedCategories);
            })->pluck('id')->toArray();
            if (!empty($staffIds)) {
                $this->slots = AccountSetting::checkStafftimeslot($this->teamId, $this->locationId, $this->appointment_date, $estimatecategoryId, $this->siteSetting, $staffIds);
            }
        }
        $this->disabledDate = $this->slots['disabled_date'] ?? [];
    }


    protected $rules = [
        'mobile' => 'required|digits:10',
        'otp' => 'required|digits:6'
    ];

    public function sendOtp()
    {
        $this->validate(['mobile' => 'required|digits:10']);

        $phone_code = isset($this->customer_phone_code) ? ltrim($this->customer_phone_code, '+') : '91';
        //    $phone_code ='91';
        $contactWithCode = $phone_code . $this->mobile;

        // Send OTP (implementation depends on your SMS gateway)
        $this->verificationId = random_int(100000, 999999);
        // $this->verificationId = 123456;
        $status = SmsAPI::currentQueueSms($contactWithCode, $this->verificationId, $this->teamId, 'Send customer login otp');

        $this->showOtpField = true;
        session()->flash('message', 'OTP sent to your mobile number');
    }

    public function verifyOtp()
    {
        $this->validate(['otp' => 'required|digits:6']);

        if ($this->verificationId == $this->otp) {
            // OTP verified - log in the customer
            // auth()->loginUsingId($this->findCustomerByMobile($this->mobile));
            // return redirect()->route('dashboard'); // Redirect to dashboard
            $customer = $this->findCustomerByMobile($this->mobile);
            Session::put('login_customer_detail', $customer);
            $this->isCustomerLogin = false;
            $this->firstpage = true;
            $this->reset('verificationId', 'otp');
        } else {
            Session::forget('login_customer_detail');
            $this->addError('otp', 'Invalid OTP. Please try again.');
        }
    }

    protected function findCustomerByMobile($mobile)
    {
        // Implement your logic to find customer by mobile
        return Customer::where('phone', $mobile)->first();
    }



    public function checkstaffId()
    {
        if ($this->siteSetting->choose_time_slot == 'staff' || $this->siteSetting->assigned_staff_id == 1) {
            $selectedCategories = array_filter([
                $this->selectedCategoryId ?? null,
                $this->secondChildId ?? null,
                $this->thirdChildId ?? null
            ], fn($val) => !is_null($val));

            $staffIds = User::whereHas('categories', function ($query) use ($selectedCategories) {
                $query->whereIn('categories.id', $selectedCategories);
            })->pluck('id')->toArray();

            if (!empty($staffIds)) {
                $staffAvailability = [];

                foreach ($staffIds as $staffId) {
                    // if ($this->CheckstaffAvailabilty($staffId)) {
                    //     $staffAvailability[] = $staffId;
                    // }
                    $staffAvailability[] = $staffId;
                }
            }


            if (count($staffAvailability) > 0) {
                $capacityPerSlot = (int) $this->accountSetting->req_per_slot ?? 1;

                // 6. Get already booked staff for this date and time
                $bookedStaffs = Booking::where('booking_date', $this->appointment_date)
                    ->where('team_id', $this->teamId)
                    ->where('location_id', $this->locationId)
                    ->where('start_time', $this->start_time)
                    ->where('end_time', $this->end_time)
                    ->whereIn('staff_id', $staffAvailability)
                    ->pluck('staff_id')
                    ->toArray();

                // 7. If already reached capacity, reject
                if (count($bookedStaffs) >= count($staffAvailability) * $capacityPerSlot) {
                    $this->assignedStaffId = '';
                    throw new \Exception('All staff are fully booked for this time slot (checkstaffId -first error).');
                }

                // 8. Find last assigned staff
                $lastBooking = Booking::where('booking_date', $this->appointment_date)
                    ->where('start_time', $this->start_time)
                    ->where('end_time', $this->end_time)
                    ->whereIn('staff_id', $staffAvailability)
                    ->latest('id')
                    ->first();

                // 9. Find next staff (round-robin style)
                if ($lastBooking && in_array($lastBooking->staff_id, $staffAvailability)) {
                    $lastStaffIndex = array_search($lastBooking->staff_id, $staffAvailability);

                    $nextIndex = ($lastStaffIndex + 1) % count($staffAvailability);
                    $this->assignedStaffId = $staffAvailability[$nextIndex];
                } else {
                    // If no previous booking, assign the first available staff
                    $this->assignedStaffId = $staffAvailability[0];
                }
            }
        } else {
            $this->assignedStaffId = '';
        }
    }



    public function CheckstaffAvailabilty($staffId)
    {

        $availableSlots = [];
        $date = $this->appointment_date;
        $periodOfSlot = $this->accountSetting->slot_period ?: '10';
        $type = "staff";
        // Check for custom slots
        $customSlotQuery = CustomSlot::whereDate('selected_date', $this->appointment_date)
            ->where('slots_type', $type)->where('team_id', $this->teamId)->where('location_id', $this->locationId);

        // Apply additional filtering based on $type
        if ($type == "staff") {
            $customSlotQuery->where('user_id', $staffId);
        }

        $customSlot = $customSlotQuery->first();

        $dayOfWeek = Carbon::parse($this->appointment_date)->format('l');

        // Use business hours from custom slots if available
        if (isset($customSlot)) {
            $businessHours_get = json_decode($customSlot->business_hours, true);
            $businessHours = $businessHours_get[0];
        } else {

            // Retrieve all account settings for the staff
            $staffAccount = AccountSetting::where('team_id', $this->teamId)
                ->where('location_id', $this->locationId)
                ->where('user_id', $staffId)
                ->where('slot_type', AccountSetting::STAFF_SLOT)
                ->first();

            $businessHours = json_decode($staffAccount->business_hours, true);
            $indexedBusinessHours = collect($businessHours)->keyBy('day');
            $businessHours = $indexedBusinessHours[$dayOfWeek];
        }

        if (isset($businessHours) && $businessHours['is_closed'] == ServiceSetting::SERVICE_OPEN) {
            $availableSlots = new Collection();
            $mainSlots = AccountSetting::generateSlots($businessHours['start_time'], $businessHours['end_time'], $periodOfSlot);
            $availableSlots = $availableSlots->concat($mainSlots);

            if (!empty($businessHours['day_interval'])) {
                foreach ($businessHours['day_interval'] as $interval) {
                    $intervalSlots = AccountSetting::generateSlots($interval['start_time'], $interval['end_time'], $periodOfSlot);
                    $availableSlots = $availableSlots->concat($intervalSlots);
                }
            }

            // Now check if the selected slot is fully within available slots
            $selectedStart = Carbon::parse($this->start_time)->format('H:i');
            $selectedEnd = Carbon::parse($this->end_time)->format('H:i');
            $slotRange = AccountSetting::generateSlots($selectedStart, $selectedEnd, $periodOfSlot);

            $allAvailable = true;
            foreach ($slotRange as $slot) {
                if (!$availableSlots->contains($slot)) {
                    $allAvailable = false;
                    break;
                }
            }

            return $allAvailable;
        }

        return false;
    }


    public function changemonthandyear($month, $year)
    {
        $current = Carbon::now($this->timezone);
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        // Set the appointment date based on whether it's current or not
        if ($selectedDate->isSameMonth($current)) {
            // $this->appointment_date = $current->format('Y-m-d');
            $this->appointment_date = Carbon::today($this->timezone);
        } else {
            // $this->appointment_date = $selectedDate->format('Y-m-d');
            $this->appointment_date = Carbon::parse($selectedDate);
        }

        // If selected month/year is *before* current, skip timeSlots
        if ($selectedDate->lessThan($current->copy()->startOfMonth())) {
            $this->appointment_time = '';
            $this->start_time = null;
            $this->end_time = null;
            $this->slots['start_at'] = [];
            return;
        }

        $this->serviceSetting = ServiceSetting::getDetails(
            $this->teamId,
            $this->locationId,
            $this->selectedCategoryId
        );

        $this->appointment_time = '';
        $this->start_time = null;
        $this->end_time = null;
        $this->slots['start_at'] = [];

        // $this->appointment_date = Carbon::parse($this->appointment_date);
        $this->timeSlots();
        $this->dispatch('update-calendar', [
            'year' => $year,  // Get current year dynamically
            'month' => $month - 1,
            'disabledDate' => $this->disabledDate,
        ]);
    }

    #[On('selected-date')]
    public function SelectedDate($date)
    {
        $this->appointment_date = Carbon::parse($date);
        $this->serviceSetting = ServiceSetting::getDetails($this->teamId, $this->locationId, $this->selectedCategoryId);
        $this->appointment_time = '';
        $this->start_time = null;
        $this->end_time = null;
        $this->timeSlots();
    }


    public function resetDynamic()
    {
        $this->allCategories = [
            'thirdChildId' => $this->thirdChildId ?? '',
            'secondChildId' => $this->secondChildId ?? '',
            'selectedCategoryId' => $this->selectedCategoryId,
        ];
        $this->dynamicForm = FormField::getFieldsbooking($this->teamId, true, $this->locationId, $this->allCategories);


        foreach ($this->dynamicForm as $field) {
            $propertyName = $field['title'] . '_' . $field['id'];
            $this->dynamicProperties[$propertyName] = '';
        }
        // dd($this->dynamicProperties);
    }

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
                'text' => 'Unable to generate ticket due to invalid rules. Please contact to the admin',
                'icon' => 'error'
            ]);

            return $rules = [];
        }
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


    public function saveAppointmentForm()
    {


        if (!empty($this->dynamicProperties)) {
            $this->validate();
        }

        $this->dispatch('swal:saving-booking', [
            'title' => 'Saving',
            'icon' => 'success',
        ]);

        $formattedFields = [];
        foreach ($this->dynamicProperties as $key => $value) {
            $fieldName = preg_replace('/_\d+/', '', $key);
            $fieldName = strtolower($fieldName);
            $formattedFields[$fieldName] = $value;
        }

        $this->name = $formattedFields['name'] ?? null;
        $possiblePhoneKeys = [
            'phone',
            'phone number',
            'phonenumber',
            'phone_no',
            'phoneno',
            'mobile',
            'mobile number',
            'mobileno',
            'cell',
            'cellphone',
            'telephone',
            'tel',
            'contact',
            'contact number',
            'whatsapp',
        ];

        $this->phone = null;

        foreach ($possiblePhoneKeys as $key) {
            if (isset($formattedFields[$key]) && !empty($formattedFields[$key])) {
                $this->phone = $formattedFields[$key];
                // $formattedFields[$key] = $this->phone_code.$formattedFields[$key];
                break;
            }
        }
        $this->email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['email address']) ? $formattedFields['email address'] : null);

        $jsonDynamicData = json_encode($formattedFields);

        try {
            DB::beginTransaction();
            $capacityPerSlot = (int) $this->accountSetting->req_per_slot ?? 1;

            if (($this->siteSetting->choose_time_slot == 'staff') || ($this->siteSetting->assigned_staff_id == 1)) {

                $this->checkstaffId();

                if (empty($this->assignedStaffId)) {
                    // Log the exception with stack trace and context
                    \Log::error('Booking save failed', [
                        'message' => "No staff Available",
                        'team_id' => $this->teamId,
                        'user_id' => auth()->id(),
                        'category_id' => $this->selectedCategoryId,
                        'appointment_date' => $this->appointment_date,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                    ]);

                    $this->dispatch('swal:exist-booking', [
                        'title' => "No staff Available",
                        'icon' => 'error',
                    ]);
                    return;
                }
            }



            $last_category = $this->selectedCategoryId;
            if (!empty($this->secondChildId)) {
                $last_category = $this->secondChildId;
            }

            if (!empty($this->thirdChildId)) {
                $last_category = $this->thirdChildId;
            }



            $limitData = [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'category_id' => $this->selectedCategoryId,
                'last_category' => $last_category,
                'appointment_date' => $this->appointment_date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'staff_id' => $this->assignedStaffId ?? null,
                'capacity_per_slot' => $capacityPerSlot,
            ];

            $count = 1;
            $freeslotId = '';
            //     $checkcount = Booking::checkBookingSlotsLimit($limitData);
            // if($checkcount['status'] == true){
            //     $count = $checkcount['count'];
            // }else{


            // }

            $status = Booking::STATUS_PENDING;
            if ($this->accountSetting?->req_accept_mode == Booking::AUTO_CONFIRM && $this->preferTimeBooking == false) {
                $status = Booking::STATUS_CONFIRMED;
            }

            if ($this->accountSetting?->custom_booking_id == 'default') {
                $refID = time();
            } elseif ($this->accountSetting?->custom_booking_id == 'email') {
                if (isset($this->email) && $this->email != '') {
                    $refID = $this->email;
                } else {
                    $refID = time();
                }
            } elseif ($this->accountSetting?->custom_booking_id == 'phone') {
                if (isset($this->phone) && $this->phone != '') {
                    $refID = $this->phone;
                } else {
                    $refID = time();
                }
            } else {
                $refID = time();
            }


            $userAuth = '';
            if (Auth::check()) {
                $userAuth = Auth::id();
            }

            if (!empty($this->utm_source) && !empty($this->utm_medium) && !empty($this->utm_campaign)) {
                $getCampaign = MetaAdsAndCampaignsLink::where('source', $this->utm_source)->where('medium', $this->utm_medium)->where('campaign', $this->utm_campaign)->first();

                $campaignId = $getCampaign->id;
            }

            // dd($this->appointment_date, $this->start_time, $this->end_time);
            // 1. Prepare Request Data

            $meetingLink = null;

            if ($this->booking_type === 'virtual') {
                $baseUrl = config('services.meeting.base_url');
                $apiKey = config('services.meeting.api_key');
                // Combine Date and Time
                // Assuming $this->appointment_date is a Carbon instance or date string
                $dateStr = \Carbon\Carbon::parse($this->appointment_date)->format('Y-m-d');
                $combinedStart = $dateStr . ' ' . $this->start_time; // e.g., "2026-01-07 04:00 PM"
                // Calculate Duration
                $startTime = \Carbon\Carbon::parse($this->start_time);
                $endTime = \Carbon\Carbon::parse($this->end_time);
                $durationMinutes = $startTime->diffInMinutes($endTime);

                // dd($combinedStart, $durationMinutes, $this->email, $this->name);
                // 2. Call the API
                try {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'X-API-Key' => $apiKey,
                        'Accept' => 'application/json',
                    ])->post($baseUrl . '/api/external/meeting/schedule', [
                                'title' => 'Meeting with ' . ($this->name ?? 'Client'),
                                'description' => 'Scheduled via Booking System',
                                'scheduled_at' => $combinedStart,
                                'duration_minutes' => $durationMinutes,
                                'timezone' => 'Asia/Kolkata', // Matches your +05:30 offset
                                'external_user_email' => $this->email,
                                'external_user_name' => $this->name ?? 'Qmeeting Client',
                            ]);
                    if ($response->successful()) {
                        $meetingData = $response->json();
                        $meetingLink = $meetingData['data']['join_link'] ?? null;

                        // Save to JSON data
                        if ($meetingLink) {
                            $jsonDynamicData['meeting_link'] = $meetingLink;
                        }
                    } else {
                        // Handle error (log it, but maybe don't stop booking creation?)
                        \Log::error('Video Meeting API Error: ' . $response->body());
                    }
                } catch (\Exception $e) {
                    \Log::error('Video Meeting Phone Call Failed: ' . $e->getMessage());
                }
            }
            if ($this->preferTimeBooking == false) {
                $booking = Booking::create([
                    'team_id' => $this->teamId,
                    'booking_date' => $this->appointment_date,
                    'booking_time' => $this->start_time . '-' . $this->end_time,
                    'name' => $this->name ?? '',
                    'phone' => $this->phone ?? '',
                    'phone_code' => $this->phone_code ?? '91',
                    'email' => $this->email ?? '',
                    'category_id' => $this->selectedCategoryId ?? null,
                    'sub_category_id' => !empty($this->secondChildId) ? $this->secondChildId : null,
                    'child_category_id' => !empty($this->thirdChildId) ? $this->thirdChildId : null,
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                    'location_id' => $this->locationId,
                    'json' => $jsonDynamicData,
                    'status' => $status,
                    // 'created_by' => $userAuth ?? '',
                    'refID' => $refID ?? time(),
                    'staff_id' => $this->assignedStaffId ?? '',
                    'campaign_id' => isset($campaignId) ? $campaignId : null,
                    'last_category' => $last_category ?? null,
                    'count' => $count ?? null,
                ]);
            } else {
                $booking = PreferBooking::create([
                    'team_id' => $this->teamId,
                    'booking_date' => $this->appointment_date,
                    'name' => $this->name ?? '',
                    'phone' => $this->phone ?? '',
                    'phone_code' => $this->phone_code ?? '91',
                    'email' => $this->email ?? '',
                    'category_id' => $this->selectedCategoryId ?? null,
                    'sub_category_id' => !empty($this->secondChildId) ? $this->secondChildId : null,
                    'child_category_id' => !empty($this->thirdChildId) ? $this->thirdChildId : null,
                    'start_time' => $this->start_time,
                    'location_id' => $this->locationId,
                    'json' => $jsonDynamicData,
                    'status' => $status,
                    // 'created_by' => $userAuth ?? '',
                    'refID' => $refID ?? time(),
                    'staff_id' => $this->assignedStaffId ?? '',
                    'campaign_id' => isset($campaignId) ? $campaignId : '',
                    'last_category' => $last_category ?? null,
                    'count' => $count ?? null,
                ]);
            }


            if (!empty($this->thirdChildId))
                $this->thirdCategoryName = Category::viewCategoryName($this->thirdChildId);
            if (!empty($this->secondChildId))
                $this->secondCategoryName = Category::viewCategoryName($this->secondChildId);
            if (!empty($this->selectedCategoryId))
                $this->categoryName = Category::viewCategoryName($this->selectedCategoryId);

            $url = url('booking-confirmed', ['id' => base64_encode($booking->id)]);
            // $cleanedUrl = str_replace('/', '', $url);
            $cleanedUrl = $url;

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
            $data = [
                'booking_id' => $booking->id,
                'name' => $booking->name ?? '',
                'phone' => $booking->phone ?? '',
                'phone_code' => $this->phone_code ?? '91',
                'booking_date' => \Carbon\Carbon::parse($booking->booking_date)->format('d-m-Y'),
                'booking_time' => $booking?->booking_time ?? $booking->start_time,
                'booked_by' => $userAuth,
                'category_name' => $this->categoryName,
                'thirdC_name' => $this->thirdCategoryName,
                'secondC_name' => $this->secondCategoryName,
                'location' => $booking->location?->location_name,
                'status' => $booking->status,
                'json' => $booking->json,
                'refID' => $booking->refID,
                'view_booking' => $cleanedUrl,
                'locations_id' => $this->locationId,
                'team_id' => $this->teamId,
            ];

            if (!empty($this->stripeResponeID)) {
                StripeResponse::where('id', $this->stripeResponeID)->update([
                    'booking_id' => $booking->id,
                ]);

                $this->stripeResponeID = '';
            }


            $meetingLink = $booking->json['meeting_link'] ?? null;
            $data = array_merge($data, ['to_mail' => $booking->email, 'service_time' => $this->enable_service_time, 'service_note' => $this->note, 'meeting_link' => $meetingLink]);

            $message = 'Appointment request has been successfully sent.But Email is not sent';
            // Send email
            if ($this->preferTimeBooking == false) {

                $logData = [
                    'team_id' => $this->teamId,
                    'location_id' => $this->locationId,
                    'customer_id' => $booking->created_by,
                    'booking_id' => $booking->id,
                    'email' => $booking->email,
                    'contact' => $booking->phone,
                    'type' => MessageDetail::TRIGGERED_TYPE,
                    'event_name' => 'Booking Confirmed',
                ];
                \Log::info('step 2');


                if ($status == Booking::STATUS_CONFIRMED) {
                    $message = 'Appointment Booked Successfully';
                    \Log::info('step 4');
                    // $this->sendEmail( $data, 'Appointment Booked Successfully', 'booking-confirmation', $this->teamId );
                    $this->sendNotification($data, 'booking confirmed', $message, $logData);
                } else {
                    // $this->sendEmail( $data, 'Appointment Request', 'admin_booking_approval', $this->teamId );
                    $message = 'Appointment request has been successfully sent. Please wait for confirmation';
                    $this->sendNotification($data, 'booking confirmed', $message, $logData);
                }
            }





            DB::commit();
            $this->preferTimeBooking = false;
            $this->preferStartTime = '';
            $this->resetForm();

            //delete freeslot data
            //  if($checkcount['status'] == true && !empty($checkcount['freeslotId'])){
            //        QueueFreeSlotCount::where('id',$checkcount['freeslotId'])->delete();
            //  }

            if ($status == Booking::STATUS_CONFIRMED && $this->accountSetting->booking_confirmation_page == 1) {
                return $this->redirect($cleanedUrl);
            } else {
                $this->dispatch('swal:saved-booking', [
                    'title' => $message,
                    'icon' => 'success',
                ]);
            }
        } catch (\Throwable $ex) {
            DB::rollBack();

            // Log the exception with stack trace and context
            \Log::error('Booking save failed', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
                'team_id' => $this->teamId,
                'user_id' => auth()->id(),
                'category_id' => $this->selectedCategoryId,
                'appointment_date' => $this->appointment_date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
            ]);

            $this->dispatch('swal:exist-booking', [
                'title' => $ex->getMessage(),
                'icon' => 'error',
            ]);

            // ActivityLog::storeLog($this->teamId, null, null, null, 'Booking', $this->locationId, ActivityLog::TYPE_BOOKING, null, null);

            return;
        }
    }



    public function resetForm()
    {
        $this->name = $this->phone = $this->start_time = $this->end_time = $this->appointment_date = null;
        $this->dynamicProperties = [];
        $this->resetDynamic();
    }

    public function sendNotification($data, $title, $template, $logData = null)
    {
        $data['team_id'] = $this->teamId;
        if (isset($data['to_mail']) && $data['to_mail'] != '') {

            \Log::info('email send', ['message' => 'booking email send']);

            if (!empty($logData)) {
                $logData['channel'] = 'email';
                $logData['status'] = MessageDetail::SENT_STATUS;
                // MessageDetail::storeLog($logData);
            }
            SmtpDetails::sendMail($data, $title, $template, $this->teamId, $logData);

        } else {
            \Log::error('email not send', ['message' => 'no booking email send']);
        }
        \Log::info('sms first', ['message' => 'booking first sms send']);
        $data['location'] = Location::find($this->locationId)->value('location_name');
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



    public function showPaymentPage()
    {
        if (!empty($this->dynamicProperties)) {
            $this->validate();
        }

        $this->formfieldSection = false;
        $this->paymentStep = true;

        $this->dispatch('cardElement');
        //   dd($this->siteDetails->is_paid_categories,$this->siteDetails->paid_category_level);
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
                'location_id' => $this->locationId,
                'category_id' => $this->stripeCategory,
                'payment_intent_id' => $paymentIntent->id,
                'customer_email' => $this->email,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'full_response' => $paymentIntent->toArray(),
            ]);
            $this->stripeResponeID = $stripeResponse->id;
            \Log::info("Booking payment done " . $this->teamId);
            $this->saveAppointmentForm();

            $this->paymentStep = false;
            $this->isFree = 0;
            $this->amount = 0;
            $this->stripeCategory = '';
            $this->email = '';

            $this->successMessage = 'Payment successful!';
        } catch (\Exception $e) {
            \Log::error('Payment booking failed: teamID' . $this->teamId . '= ' . $e->getMessage());
            $this->errorMessage = 'Payment failed: Something went Wrong';
        }
    }

    public function createVideoCall()
    {
        $graph = new MicrosoftGraphService();
        $meeting = $graph->createTeamsMeeting(
            $this->organizerEmail,
            'Appointment with Customer',
            now()->addMinutes(5)->toIso8601String(),
            now()->addMinutes(35)->toIso8601String()
        );

        $this->meetingLink = $meeting->getJoinWebUrl(); // e.g. https://teams.live.com/meet/...
    }




    public function render()
    {
        // $layout = Auth::check() ? 'components.layouts.app' : 'components.layouts.custom-layout';
        $layout = 'components.layouts.custom-booking-layout';

        return view('livewire.main-booking-appointment')->layout($layout);
    }
}