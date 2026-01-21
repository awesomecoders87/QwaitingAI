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
    SmsAPI,
    ColorSetting,
    Country,
    Level,
    User,
    CustomSlot,
    Customer,
    QueueFreeSlotCount,
    CustomerActivityLog,
    MessageDetail,
    AllowedCountry,
};
use App\Traits\SendsEmails;
use App\Models\ServiceSetting;
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
use Illuminate\Support\Collection;

// #[Layout('components.layouts.custom-layout')]
class EditBooking extends Component
{
    use SendsEmails;

    #[Title('Booking Rescheduled')]


    public $selectedCategoryId;
    public $booking;
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
    public $email = '';
    public $showFormQueue;
    public $locationslots;

    public $locationStep = true;
    public $firstpage = false;
    public $secondpage = false;
    public $thirdpage = false;
    public $calendarpage = false;
    public $formfieldSection = false;

    public $slots;
    public $selectedYear;
    public $years = [];
    public $appointment_date;
    public $appointment_time;
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
    public $userDetails = [];
    public $mindate = 0;
    public $maxdate = 30;
    public $weekStart = "Sunday";
    public $level1, $level2, $level3;

    public $note;
    public $enable_service;
    public $enable_service_time;

    public $assignedStaffId;

    public $allowed_Countries = [];
    public $country_phone_mode = 1;

    public function mount($id)
    {

        $this->showFormQueue = false;
        $this->user =  Auth::user();
        $this->teamId =  tenant('id');


        $bookingId = base64_decode($id);
        $this->booking = Booking::viewBooking(
            $bookingId,
            $this->teamId
        );

        if (empty($this->booking)) {
            abort(404);
        }
        $this->locationId = Session::get('selectedLocation') ?? $this->booking->location_id;

        if ($this->booking->status == Booking::STATUS_COMPLETED) {
            return redirect('book-appointment');
        }

        if ($this->booking->is_convert == Booking::STATUS_YES)
            abort(403);

        $this->userDetails = json_decode($this->booking->json, true);

        if (!empty($this->locationId)) {
            $this->updatedLocation($this->locationId);
        } else {
            $this->locationId = '';
            $this->location = '';
            $this->allLocations = Location::getLocations($this->teamId);
            $this->locationStep = true;
            $this->firstpage = false;
        }
        $levels = Level::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->whereIn('level', [1, 2, 3])
            ->get()
            ->keyBy('level');

        $this->level1 = $levels[1]->name ?? 'Level 1';
        $this->level2 = $levels[2]->name ?? 'Level 2';
        $this->level3 = $levels[3]->name ?? 'Level 3';
    }

    public function updatedLocation($value)
    {
        $this->locationId = $value;
        Session::forget('selectedLocation');
        Session::put('selectedLocation', $this->locationId);
        $this->dispatch('header-show');

        // Eager load relationships
        $locationWithRel = Location::with([
            'siteDetail',
            'paymentSetting',
            'accountSettings' => function ($query) {
                $query->whereIn('slot_type', [AccountSetting::BOOKING_SLOT, AccountSetting::LOCATION_SLOT]);
            }
        ])->find($this->locationId);

        if (!$locationWithRel) {
            $this->locationName = '';
            $this->location = null;
            return; // Or handle error appropriately
        }

        $this->locationName = $locationWithRel->location_name;
        $this->location = $locationWithRel->withoutRelations();

        $this->locationStep = false;
        $this->firstpage = true;
        $currentYear = date('Y');
        $this->years = range($currentYear, $currentYear + 1);
        $this->selectedYear = $currentYear;

        // Extract from eager loaded relations
        $this->siteSetting = $locationWithRel->siteDetail ?? null;

        if (!$this->siteSetting) {
            abort(500);
        }

        // Filter collection for BOOKING_SLOT
        $this->accountSetting = $locationWithRel->accountSettings
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->first();

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

        // Filter collection for LOCATION_SLOT
        $locationSlotsDetail = $locationWithRel->accountSettings
            ->where('slot_type', AccountSetting::LOCATION_SLOT)
            ->first();

        if ($locationSlotsDetail) {
            $this->locationslots = json_decode($locationSlotsDetail->business_hours, true);
        } else {
            $this->locationslots = [];
        }

        //fetch parent Category
        $this->parentCategory =  Category::getFirstCategorybooking($this->teamId, $this->locationId);

        $this->colorSetting = ColorSetting::where('team_id', $this->teamId)->first();
        $this->totalLevelCount = Category::STEP_1;
        //default today select
        $this->appointment_date = Carbon::today();
        $this->appointment_time = '';
        $this->countryCode = Country::query()->pluck('phonecode');


        $this->selectedCountryCode = !empty($this->siteSetting->country_code) ?  $this->siteSetting->country_code : null;
        $this->phone_code = !empty($this->selectedCountryCode) ? $this->selectedCountryCode : '91';

        $timezone = $this->siteSetting->select_timezone ?? 'UTC';
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        $this->country_phone_mode = $this->siteSetting->country_options ?? 1;

        $this->allowed_Countries = AllowedCountry::where('team_id',  $this->teamId)
            ->where('location_id', $this->locationId)->select('id', 'name', 'phone_code')->get();
        if ($this->country_phone_mode != 1 && !empty($this->allowed_Countries)) {
            $this->phone_code = $this->allowed_Countries[0]->phone_code;
        }
    }

    public function goBackFn($page)
    {

        $this->totalLevelDecFn();

        switch ($page) {

            case Category::STEP_1:
                $this->secondChildId =  $this->thirdChildId  = null;
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_1;
                $this->firstpage  = true;
                break;
            case Category::STEP_2:
                $this->resetallpages();
                $this->thirdChildId = null;
                $this->totalLevelCount = Category::STEP_2;
                $this->secondpage = true;
                break;
            case Category::STEP_3:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_3;
                $this->thirdpage = true;
                break;
            case Category::STEP_4:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_4;
                $this->calendarpage = true;
                break;
            case Category::STEP_5:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_5;
                $this->formfieldSection = true;
                break;
            default:
                $this->secondChildId = $this->selectedCategoryId =    $this->thirdChildId  = null;
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_1;
                $this->firstpage = true;
        }
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
        $this->formfieldSection = false;
    }

    public function showFirstChild($categoryId)
    {
        $this->selectedCategoryId = $categoryId;

        $this->firstChildren = Category::getchildDetailBooking($categoryId, $this->locationId);

        if (count($this->firstChildren) > 0) {
            $this->firstpage = false;
            $this->thirdpage = false;
            $this->calendarpage = false;
            $this->formfieldSection = false;
            $this->secondpage = true;
        } else {

            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->formfieldSection = false;
            $this->calendarpage = true;
            $this->timeSlots();

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

        if (count($this->secondChildren) > 0) {
            $this->firstpage = false;
            $this->secondpage = false;
            $this->calendarpage = false;
            $this->formfieldSection = false;
            $this->thirdpage = true;
        } else {
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->formfieldSection = false;
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
            $this->formfieldSection = false;
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
        $timeslotsExlplode = explode('-', $current);
        if ($this->start_time == $timeslotsExlplode[0]) {
            $this->start_time = null;
            $this->end_time = null;
        } else {
            $interval = (int)$this->accountSetting?->slot_period ?? 10;
            $this->start_time = $timeslotsExlplode[0];
            $this->end_time = $timeslotsExlplode[1];
        }

        if (!empty($value)) {

            $this->locations = false;
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->calendarpage = false;
            $this->formfieldSection = true;
        }

        $this->resetDynamic();
    }




    // get time slots
    public function timeSlots()
    {

        if ($this->siteSetting->category_slot_level == 1 && $this->selectedCategoryId) {
            $categoryId =  $this->selectedCategoryId;
        } elseif ($this->siteSetting->category_slot_level == 2 &&  $this->secondChildId) {
            $categoryId = $this->secondChildId;
        } elseif ($this->siteSetting->category_slot_level == 3 &&  $this->thirdChildId) {
            $categoryId = $this->thirdChildId;
        } else {
            $categoryId =  $this->selectedCategoryId;
        }

        $this->slots = AccountSetting::checktimeslot($this->teamId, $this->locationId, $this->appointment_date, $categoryId, $this->siteSetting);

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
                $this->slots = AccountSetting::checkStafftimeslot($this->teamId, $this->locationId, $this->appointment_date, $categoryId, $this->siteSetting, $staffIds);
            }
        }

        $this->disabledDate = $this->slots['disabled_date'] ?? [];
        //    dd( $this->slots );
    }

    #[On('change-month-year')]
    public function changemonthandyear($month, $year)
    {
        $current = Carbon::now();
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        // Set the appointment date based on whether it's current or not
        if ($selectedDate->isSameMonth($current)) {
            $this->appointment_date = $current->format('Y-m-d');
        } else {
            $this->appointment_date = $selectedDate->format('Y-m-d');
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

        $this->appointment_date = Carbon::parse($this->appointment_date);
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
        $this->dynamicForm = FormField::getFieldsbooking($this->teamId, true, $this->locationId);

        $this->allCategories = [
            'thirdChildId' => $this->thirdChildId ?? '',
            'secondChildId' => $this->secondChildId ?? '',
            'selectedCategoryId' => $this->selectedCategoryId,
        ];

        // Decode the booking JSON
        $bookingJsonRaw = json_decode($this->booking['json'] ?? '{}', true);

        // Normalize JSON keys to lowercase for comparison
        $bookingJson = [];
        foreach ($bookingJsonRaw as $key => $value) {
            $bookingJson[strtolower($key)] = $value;
        }

        foreach ($this->dynamicForm as $field) {
            $propertyName = $field['title'] . '_' . $field['id'];

            // Normalize field title to lowercase for matching
            $fieldKey = strtolower($field['title']);

            // Set value if found
            if (isset($bookingJson[$fieldKey])) {
                $this->dynamicProperties[$propertyName] = $bookingJson[$fieldKey];
            } else {
                $this->dynamicProperties[$propertyName] = '';
            }
        }
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
        $this->validate();

        $this->dispatch('swal:saving-booking', [
            'title' => 'Saving',
            'icon' => 'success',
        ]);

        $capacityPerSlot = (int)$this->accountSetting->req_per_slot ?? 1;

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

        $getCategory = Category::where('id', $this->booking->category_id)->first();
        $getSubCategory = Category::where('id', $this->booking->sub_category_id)->first();
        $getChildCategory = Category::where('id', $this->booking->child_category_id)->first();
        $this->assignedStaffId = $this->booking->staff_id ?? null;
        $refID = $this->booking->refID;
        try {
            DB::beginTransaction();

            if ($this->siteSetting->choose_time_slot == 'staff') {

                if (empty($this->booking->staff_id)) {

                    $this->checkstaffId();
                }

                if (empty($this->assignedStaffId)) {
                    // Log the exception with stack trace and context
                    \Log::error('Booking save failed', [
                        'message' => "NO staff Available",
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
                'last_category' =>  $last_category,
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

            //     $this->dispatch('swal:exist-booking', [
            //         'title' => "The booking limit has been reached",
            //         'icon' => 'error',
            //     ]);
            // }

            //                   //Add Free slot table
            //    QueueFreeSlotCount::create([
            //     'team_id'=>$this->booking->team_id,
            //     'location_id'=>$this->booking->location_id ?? $this->location,
            //     'booking_date'=>$this->booking->booking_date,
            //     'last_category'=>$this->booking->last_category,
            //     'sb_start_time'=>$this->booking->start_time,
            //     'sb_end_time'=>$this->booking->end_time,
            //     'count'=>$this->booking->count ?? 1,
            //    'user_id' => intval($this->booking->staff_id) ?: null,
            // ]);

            // if ($this->accountSetting?->custom_booking_id == 'default') {
            //     $refID = time();
            // } elseif($this->accountSetting?->custom_booking_id == 'email') {
            //     if (isset($this->email) && $this->email != '') {
            //         $refID = $this->email;
            //     } else {
            //         $refID = time();
            //     }
            // }elseif($this->accountSetting?->custom_booking_id == 'phone') {
            //      if (isset($this->phone) && $this->phone != '') {
            //         $refID = $this->phone;
            //     } else {
            //         $refID = time();
            //     }
            // }else{
            //     $refID = time();
            // }



            $userAuth = '';
            if (Auth::check()) {
                $userAuth = Auth::id();
            }



            $this->booking->team_id =  $this->teamId;
            $this->booking->booking_date = $this->appointment_date;
            $this->booking->booking_time =  $this->start_time . ' - ' . $this->end_time;
            $this->booking->name =  $this->name;
            $this->booking->phone =  $this->phone;
            $this->booking->phone_code =  $this->phone_code ?? 91;
            $this->booking->email = $this->email;
            $this->booking->category_id =  $this->selectedCategoryId ?? null;
            $this->booking->sub_category_id =  !empty($this->secondChildId) ? $this->secondChildId : null;
            $this->booking->child_category_id =  !empty($this->thirdChildId) ? $this->thirdChildId : null;
            $this->booking->start_time =  $this->start_time;
            $this->booking->end_time =  $this->end_time;
            $this->booking->location_id =  $this->locationId;
            $this->booking->created_by = $userAuth;
            $this->booking->json =  $jsonDynamicData;
            $this->booking->is_rescheduled = 1;
            $this->booking->refID = $refID ?? time();
            $this->booking->staff_id = $this->assignedStaffId ?? '';
            $this->booking->last_category = $last_category;
            $this->booking->count = $count ?? '';
            $this->booking->save();


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
                    'booking_id' =>  $this->booking->id ?? null,
                    'type' => 'booking',
                    'customer_id' => $existingCustomer->id,
                    'note' => 'Customer joined the booking.',
                ]);
                $this->booking->update([
                    'created_by' => $existingCustomer->id,
                ]);
            }
            //delete freeslot data
            //  if($checkcount['status'] == true && !empty($checkcount['freeslotId'])){
            //        QueueFreeSlotCount::where('id',$checkcount['freeslotId'])->delete();
            //  }

            $url = url('edit-booking', ['id' => base64_encode($this->booking->id)]);


            DB::commit();
            // $this->resetForm();

            $data = [
                'to_mail' => $this->booking->email,
                'booking_id' => $this->booking->id,
                'name' => $this->booking->name,
                'phone' => $this->booking->phone,
                'phone_code' => $this->booking?->phone_code ?? '91',
                'booking_date' => \Carbon\Carbon::parse($this->booking->booking_date)->format('d-m-Y'),
                'booking_time' => $this->booking->booking_time,
                'booked_by' => $userAuth,
                'category_name' =>  isset($getCategory['name']) ? $getCategory['name'] : '',
                'secondC_name' =>  isset($getSubCategory['name']) ? $getSubCategory['name'] : '',
                'thirdC_name' => isset($getChildCategory['name']) ? $getChildCategory['name'] : '',
                'location' => $this->booking->location_id,
                'status' => $this->booking->status,
                'json' => $this->booking->json,
                'refID' => $this->booking->refID,
                'view_booking' => $url,
                'locations_id' => $this->locationId,
                'team_id' => $this->teamId,
            ];

            $message = 'Appointment Rescheduled Successfully';

            $logData = [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'customer_id' => $this->booking->created_by ?? null,
                'booking_id' => $this->booking->id,
                'email' => $this->booking->email,
                'contact' => $this->booking->phone,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Booking Rescheduled',
            ];



            $this->sendNotification($data, 'booking rescheduled', $message, $logData);
            $this->dispatch('booking-updated');

            // return  $this->redirect($url);

        } catch (\Throwable $ex) {
            DB::rollBack();
            \Log::error('Booking updated failed', [
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
        if (isset($data['to_mail']) && $data['to_mail'] != '') {
            $data['location'] = Location::find($this->locationId)->value('location_name');
            if (!empty($logData)) {
                $logData['channel'] = 'email';
                $logData['status'] = MessageDetail::SENT_STATUS;
                // MessageDetail::storeLog($logData);
            }
            SmtpDetails::sendMail($data, $title, $template, $this->teamId, $logData);
        }
        if (!empty($this->phone)) {
            $logData['channel'] = 'sms';
            $logData['status'] = MessageDetail::SENT_STATUS;
            SmsAPI::sendSms($this->teamId, $data, $title, $title, $logData);

            // SmsAPI::sendSmsWhatsApp( $this->teamId, $data );
        }
    }


    public function checkstaffId()
    {
        if ($this->siteSetting->choose_time_slot == 'staff') {
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
                    if (checkStaffAvailability(
                        $staffId,
                        $this->appointment_date,
                        $this->accountSetting->slot_period ?: '10',
                        $this->teamId,
                        $this->locationId,
                        $this->start_time,
                        $this->end_time
                    )) {
                        $staffAvailability[] = $staffId;
                    }
                }
            }

            $alreadyAssignedStaffId = $this->booking->staff_id;

            if (!empty($alreadyAssignedStaffId)) {
                if (in_array($alreadyAssignedStaffId, $staffAvailability)) {
                    $this->assignedStaffId = $alreadyAssignedStaffId;
                    return;
                }
            }

            if (count($staffAvailability) > 0) {
                $capacityPerSlot = (int)$this->accountSetting->req_per_slot ?? 1;

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



    public function render()
    {
        // $layout = Auth::check() ? 'components.layouts.app' : 'components.layouts.custom-layout';
        $layout = 'components.layouts.custom-booking-layout';
        return view('livewire.edit-booking')->layout($layout);
    }
}
