<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Category,
    Team,
    Booking,
    Location,
    AccountSetting,
    SiteDetail,
    SmtpDetails,
    GenerateQrCode,
    FormField,
    ColorSetting,
    SmsAPI
};
use App\Traits\SendsEmails;
use App\Models\ServiceSetting;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use DB;
use Auth;
use Str;
use DateTime;
use Filament\Facades\Filament;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.custom-layout')]
class MainBooking extends Component
{
    use SendsEmails;

    public function render()
    {
        return view('livewire.main-booking');
    }

    public $selectedCategoryId;
    public $firstChildren;
    public $secondChildren, $secondChildId, $thirdChildId, $teamId;
    public $name = '';
    public $phone = '';
    public $divOne;
    public $categoryName = '';
    public $secondCategoryName = '';
    public $thirdCategoryName = '';

    public $locationStep = true;
    public $firstStep = false;
    public $secondStep = false;
    public $thirdStep = false;
    public $fourthStep = false;
    public $currentPage = Category::STEP_1;

    public $totalLevelCount = Category::STEP_1;
    public $dynamicForm = [];
    public $firstCategories;

    public $dynamicProperties = [];

    public $siteDetails;
    public $showTicketText;
    public $showTicketText_2;
    public $token_start;
    public $last_token;
    public $booking_setting;
    public $acronym = '';
    public $fontSize = 'text-3xl';
    public $fontFamily = '';
    public $borderWidth = 'border-4';
    public $countCatID = 0;
    public $fieldCatName = '';
    public $counterID = 0;
    public $header = true;
    public $email;
    public $is_qr_code;
    public $qrcode_tagline;
    public $qrCodeDetails;
    public $allCategories = [];
    public $allLocations = [];
    public $location;
    public $locationName;
    public  $availableSlots  = ['start_at' => [], 'end_at' => []];
    public $appointment_date;
    public $start_time;
    public $end_time;
    public $start_date;
    public $serviceSetting;
    public $datesList = [];
    public $maxServiceDates = [];
    public $maxSlotDates = [];
    public $businessHours = [];
    public $closedDayIndices = [];
    public $bookingSetting;
    public $category_row;
    public $colorSetting;
    public $text_color_hover = "#000";
    public $category_background_hover = "#fff";
    public $buttons_background_hover  = "#000";

    public static function getNavigationLabel(): string
    {
        return __('sidebar.New Booking');
    }
    public static function getModelLabel(): string
    {
        return __('sidebar.New Booking');
    }

    public function getTitle(): string
    {
        return __('sidebar.New Booking');
    }

    #[On('update-appointment-time')]

    public function updatedAppointmentDate($value)
    {

        $this->start_time = null;
        $this->end_time = null;
        $carbonDate = Carbon::parse($value);

        $getAdvanceBookingDates = $this->datesGet($this->bookingSetting->allow_req_before);

        $this->appointment_date = $carbonDate;
        $dayOfWeek = $carbonDate->format('l');
        $lDayOfWeek = Str::lower($dayOfWeek);
        $this->serviceSetting = ServiceSetting::getDetails($this->teamId, $this->location, $this->selectedCategoryId);
        $this->maxServiceDates = Booking::maxBookingPerService($carbonDate,  $this->teamId, $this->location, $this->selectedCategoryId, $this->serviceSetting?->pax_per_service);
        if (!empty($this->maxServiceDates))
            $this->datesList = array_unique(array_merge($this->datesList, $this->maxServiceDates));


        $this->businessHours = json_decode($this->serviceSetting?->business_hours, true);
        $fieldName = 'break_' . $lDayOfWeek;
        $breakHours = [];
        if (!empty($this->serviceSetting->{$fieldName}))
            $breakHours = json_decode(
                $this->serviceSetting->{$fieldName},
                true
            );
        $indexedBusinessHours = array_column($this->businessHours, null, 'day');

        if (isset($indexedBusinessHours[$dayOfWeek]) && $indexedBusinessHours[$dayOfWeek]['is_closed'] === ServiceSetting::SERVICE_OPEN) {
            if (in_array(date('d-m-Y', strtotime($carbonDate)), $getAdvanceBookingDates) && !in_array(date('d-m-Y', strtotime($carbonDate)), $this->datesList)) {

                $availableSlots = ServiceSetting::getAvailableSlots($carbonDate, $indexedBusinessHours[$dayOfWeek], $breakHours, $this->serviceSetting);

                $availableSlots = $availableSlots?->toArray();
                $this->availableSlots['start_at'] = $availableSlots;
            } else {
                $this->availableSlots = [];
            }
        } else {
            $this->availableSlots = [];
        }

        
    }

    public function mount()
    {

        $this->showFormQueue = false;
        $this->teamId =  tenant('id');
        $this->locationName = '';
        $this->booking_setting = SiteDetail::STATUS_YES;
        $this->location = Session::get('selectedLocation');
        if (!empty($this->location))
            $this->locationName =  Location::locationName($this->location);
        $this->locationStep = false;
        $this->firstStep = true;
        if ($this->location == '' || !Auth::check()) {
            $this->location = '';
            $this->allLocations = Location::where('team_id', $this->teamId)->pluck('location_name', 'id');
            $this->locationStep = true;
            $this->firstStep = false;
        }

        $this->firstCategories = Category::getFirstCategoryN($this->teamId, $this->location);
        $this->bookingSetting = AccountSetting::where('team_id', $this->teamId)->first();
        $this->category_row = $this->bookingSetting->show_category_per_row;
        $this->siteDetails = SiteDetail::getMyDetails($this->teamId);
        $this->qrCodeDetails = GenerateQrCode::viewGeneratorCode($this->teamId);
        $this->fontSize  = $this->siteDetails?->category_text_font_size ?? $this->fontSize;
        $this->borderWidth  = $this->siteDetails?->category_border_size ?? $this->borderWidth;
        $this->fontFamily  = $this->siteDetails?->ticket_font_family ?? $this->fontFamily;
        $this->booking_setting =  $this->bookingSetting?->booking_system ?? SiteDetail::STATUS_YES;
        $this->is_qr_code =  $this->siteDetails?->is_qr_code ?? SiteDetail::STATUS_NO;
        $this->qrcode_tagline =  $this->siteDetails?->qrcode_tagline;

        if ($this->booking_setting == SiteDetail::STATUS_NO) {
            abort(403);
        }

        $this->colorSetting = ColorSetting::where('team_id', $this->teamId)->first();
    }

    public function datesGet($adv_date)
    {
        // Initialize an array with today's date
        $datesArray = [];
        // Create a DateTime object for today
        $startDate = new DateTime(); // This sets the start date to today
        if ($adv_date == 0) {
            $datesArray[] = $startDate->format('d-m-Y');
            return $mergedArray = array_unique($datesArray);
        }

        // Loop to add today’s date and the next $adv_date days
        for ($i = 0; $i <= $adv_date; $i++) {
            $datesArray[] = $startDate->format('d-m-Y');
            // Move to the next day
            $startDate->modify('+1 day');
        }

        // Remove any duplicate dates
        return $mergedArray = array_unique($datesArray);
    }

    #[On('handleTimeClicked')]

    public function handleTimeClicked($value)
    {
        if ($this->start_time === $value) {
            $this->start_time = null;
            $this->end_time = null;
        } else {
            $this->start_time = $value;
            $interval = $this->serviceSetting?->slot_period;
            $current = Carbon::parse($value);
            $current->addMinutes($interval);
            $this->end_time = $current->format('h:i A');
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
        return array_merge($rules, ['start_time' => 'required']);
        // return $rules;
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

        $messages['start_time.required'] = 'Please select the slot.';
        return $messages;
    }

    public function updatedLocation($value)
    {
        $this->location = $value;
        $this->firstCategories = Category::getFirstCategoryN($this->teamId, $this->location);
        $this->locationName =   Location::locationName($value);
        $this->locationStep = false;
        $this->firstStep = true;
    }

    public function showFirstChild($categoryId)
    {
        $this->selectedCategoryId = $categoryId;

        $this->firstChildren = Category::getPluckNames($categoryId, $this->location);

        if (!empty($this->firstChildren))
            $this->firstChildren =  $this->firstChildren->toArray();

        $this->serviceSetting = ServiceSetting::getDetails($this->teamId, $this->location, $this->selectedCategoryId);
        $dayOffs =  json_decode($this->serviceSetting?->day_off, true);
        // Assuming $this->serviceSetting->business_hours is valid JSON
        $businessHours = json_decode($this->serviceSetting->business_hours, true);

        // Filter closed days and map them to day indices
        $this->closedDayIndices = array_reduce(
            $businessHours,
            function ($carry, $day) {
                if ($day['is_closed'] === 'closed') {
                    $dayIndex = date('N', strtotime($day['day'])) % 7;
                    // Ensure index wraps around correctly
                    $carry[] = $dayIndex;
                }
                return $carry;
            },
            []
        );

        if (!empty($dayOffs))
            $this->datesList =  ServiceSetting::dayOffList($dayOffs);

        $this->maxServiceDates = Booking::maxBookingPerService(null,  $this->teamId, $this->location, $this->selectedCategoryId, $this->serviceSetting?->pax_per_service);

        if (!empty($this->maxServiceDates))
            $this->datesList = array_merge($this->datesList, $this->maxServiceDates);

        if (empty($this->firstChildren))
            $this->updateCurrentPage(Category::STEP_4);
        else
            $this->updateCurrentPage(Category::STEP_2);

        $this->currentPageFn($this->currentPage);
        $this->secondChildId = null;
        $this->totalLevelIncFn();
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

        $this->secondChildren = Category::getPluckNames($childId, $this->location);
        if (!empty($this->secondChildren))
            $this->secondChildren =  $this->secondChildren->toArray();

        if (empty($this->secondChildren))
            $this->updateCurrentPage(Category::STEP_4);
        else
            $this->updateCurrentPage(Category::STEP_3);

        $this->currentPageFn($this->currentPage);
        $this->totalLevelIncFn();
    }

    public function showQueueForm($secondCId)
    {
        $this->thirdChildId = $secondCId;

        $this->updateCurrentPage(Category::STEP_4);
        $this->currentPageFn($this->currentPage);
    }

    public function saveAppointmentForm()
    {
        $this->validate();

        $this->dispatch('swal:saving-booking', [
            'title' => 'Saving',
            'icon' => 'success',
        ]);
        $formattedFields = [];
        foreach ($this->dynamicProperties as $key => $value) {
            $fieldName = preg_replace('/_\d+/', '', $key);

            $formattedFields[$fieldName] = $value;
        }
        $this->name = $formattedFields['name'] ?? null;
        $this->phone = $formattedFields['phone'] ?? null;
        $this->email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['Email']) ? $formattedFields['Email'] : null);

        $jsonDynamicData = json_encode($formattedFields);

        try {
            DB::beginTransaction();

            $status = Booking::STATUS_PENDING;
            if ($this->bookingSetting?->req_accept_mode == Booking::AUTO_CONFIRM) {
                $status = Booking::STATUS_CONFIRMED;
            }

            if ($this->bookingSetting?->custom_booking_id == Booking::ENABLE) {
                $refID = time();
            } else {
                if (isset($this->email) && $this->email != '') {
                    $refID = $this->email;
                } else if (isset($this->phone) && $this->phone != '') {
                    $refID = $this->phone;
                } else {
                    $refID = time();
                }
            }

            $userAuth = Auth::user();

            $booking = Booking::create([
                'team_id' => $this->teamId,
                'booking_date' => $this->appointment_date,
                'booking_time' => $this->start_time . ' - ' . $this->end_time,
                'name' => $this->name ?? '',
                'phone' => $this->phone ?? '',
                'email' => $this->email ?? '',
                'category_id' => $this->selectedCategoryId ?? null,
                'sub_category_id' => !empty($this->secondChildId) ? $this->secondChildId : null,
                'child_category_id' => !empty($this->thirdChildId) ? $this->thirdChildId : null,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'location_id' => $this->location,
                'json' => $jsonDynamicData,
                'status' => $status,
                'created_by' => $userAuth?->id ?? null,
                'refID' => $refID ?? time()
            ]);
            // $url = route('booking-confirmed',['id' => Crypt::encrypt( $booking->id  )]);
            $url = route('booking-confirmed', ['id' => base64_encode($booking->id)]);
            $cleanedUrl = str_replace('/public', '', $url);


            $data = [
                'booking_id' => $booking->id,
                'name' => $booking->name,
                'phone' => $booking->phone,
                'phone_code' => $booking->phone_code ?? '91',
                'booking_date' => $booking->booking_date,
                'booking_time' => $booking->booking_time,
                'booked_by' => $userAuth?->name,
                'category_name' => $booking->categories?->name,
                'secondC_name' => $booking->sub_category?->name,
                'thirdC_name' => $booking->child_category?->name,
                'location' => $booking->location?->location_name,
                'status' => $booking->status,
                'json' => $booking->json,
                'refID' => $booking->refID,
                'view_booking' => $cleanedUrl,
            ];

            $data = array_merge($data, ['to_mail' => $booking->email]);

            if ($status == Booking::STATUS_CONFIRMED) {
                $message = 'Appointment Booked Successfully';
                // $this->sendEmail( $data, 'Appointment Booked Successfully', 'booking-confirmation', $this->teamId );
                $this->sendNotification($data, 'booking confirmed', $message);
            } else {
                // $this->sendEmail( $data, 'Appointment Request', 'admin_booking_approval', $this->teamId );
                $message = 'Appointment request has been successfully sent. Please wait for confirmation';
                $this->sendNotification($data, 'booking confirmed', $message);
            }
            DB::commit();
            $this->resetForm();


            if ($status == Booking::STATUS_CONFIRMED  && $this->bookingSetting->booking_confirmation_page == 1) {
                return  $this->redirect($cleanedUrl);
            } else {
                $this->dispatch('swal:saved-booking', [
                    'title' => $message,
                    'icon' => 'success',
                ]);
            }
        } catch (\Throwable $ex) {
            DB::rollBack();
            $this->dispatch('swal:exist-booking', [
                'title' => $ex->getMessage(),
                'icon' => 'error',
            ]);
            return;
        }
    }

    public function resetDynamic()
    {
        $this->dynamicForm = FormField::getFields($this->teamId, true);
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

    public function determineCategoryColumn()
    {
        if (!empty($this->thirdChildId)) {
            $this->fieldCatName = 'child_category_id';
            $this->countCatID =  $this->thirdChildId;
        } else if (!empty($this->secondChildId)) {
            $this->fieldCatName = 'sub_category_id';
            $this->countCatID =  $this->secondChildId;
        } else {
            $this->fieldCatName = 'category_id';
            $this->countCatID =  $this->selectedCategoryId;
        }
    }

    public function sendNotification($data, $title, $template)
    {
        if (isset($data['to_mail']) && $data['to_mail'] != '')
            SmtpDetails::sendMail($data, $title, $template, $this->teamId);
        $data['location'] = Location::find($this->location)->value('location_name');
        if (!empty($this->phone)) {
            SmsAPI::sendSms($this->teamId, $data, $title,$title);

            // SmsAPI::sendSmsWhatsApp( $this->teamId, $data );
        }
    }

    public function resetForm()
    {
        $this->name = $this->phone = $this->start_time = $this->end_time = $this->appointment_date = null;
        $this->dynamicProperties = [];
        $this->resetDynamic();
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
            $this->dispatch('event-datepicker', ['disabled_dates' => $this->datesList, 'closed_days' => $this->closedDayIndices, 'account' => $this->bookingSetting]);

            if ($this->siteDetails?->queue_form_display == SiteDetail::STATUS_NO) {
                $this->saveAppointmentForm();
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
                $this->secondChildId = $this->selectedCategoryId =     $this->thirdChildId  = null;
                $this->locationStep  = true;
                $this->firstStep = $this->secondStep = $this->thirdStep = $this->fourthStep = false;
                break;
            case Category::STEP_2:
                $this->thirdChildId = null;
                $this->firstStep  = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;
                break;
            case Category::STEP_3:
                $this->secondStep = true;
                $this->thirdChildId = null;
                $this->firstStep = $this->thirdStep = $this->fourthStep = false;
                break;
            case Category::STEP_4:
                $this->thirdStep = true;
                $this->firstStep = $this->secondStep = $this->fourthStep = false;
                break;
            default:
                $this->secondChildId = $this->selectedCategoryId =    $this->thirdChildId  = null;
                $this->firstStep = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;
        }
        $this->resetDynamic();
    }

    // #[ On( 'change-date' ) ]

    // public function resetForm() {
    //     $this->name = $this->email = $this->phome = $this->appointment_date = $this->select_time_slot = '';
    // }
}
