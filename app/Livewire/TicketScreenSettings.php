<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SiteDetail;
use App\Models\Country;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class TicketScreenSettings extends Component
{
     use WithFileUploads;

    #[Title('Ticket Screen Setting')]

    public $category_text_font_size;
    public $ticket_font_family;
    public $category_border_size;
    public $token_digit;
    public $token_start;
    public $estimate_time;
    public $category_estimated_time;
    public $category_level_est;
    public $counter_estimated_time;
    public $queue_form_display;
    public $ticket_text_enable;
    public $ticket_text;
    public $ticket_text_2;
    public $show_cat_icon;
    public $late_coming_feature;
    public $is_qr_code;
    public $qrcode_tagline;
    public $qrcode_tagline_second;
    public $app_heading_first;
    public $app_heading_second;
    public $name_mandatory;
    public $phone_mandatory;
    public $name_before_appointment_form;
    public $name_after_appointment_form;
    public $phone_before_appointment_form;
    public $phone_after_appointment_form;
    public $name_placeholder;
    public $phone_placeholder;
    public $name_custom_class;
    public $phone_custom_class;
    public $teamId;
    public $locationId;
    public $user_detail;
    public $bottom_btn_enable;
    public $queue_heading_first;
    public $queue_heading_second;
    public $submit_btn_text;
    public $back_btn_text;
    public $is_qrcode_ticket;
    public $category_slot_level;
    public $enable_time_slot;
    public $print_name_label;
    public $print_token_label;
    public $arrived_time_label;
    public $is_logo_on_print;
    public $is_name_on_print;
    public $is_arrived_on_print;
    public $is_location_on_print;
    public $is_category_on_print;
    public $is_token_on_print;
    public $confirm_btn_label;
    public $is_enable_waitlist_message;
    public $waitlist_heading;
    public $waitlist_message_first;
    public $waitlist_message_second;
    public $is_waitlist_table;
    public $select_timezone;
    public $country_code;
    public $categoryLevels = [];
    public $enableTimeSlots = [];
    public $listTimeZones = [];
    public $countryCode = [];
    public $successMessage = null;
    public $show_country_code;
    public $paid_category_level;
    public $background_image;
    public $background_size;
    public $background_repeat;
    public $background_position;
    public $use_staff_priority;
    public $userAuth;
    public $is_redirect_print_page;
    public $ticket_mode;
    public $disable_print;
    public $print_mode;
    public $enable_waitlist_list;
    public $enable_priority_pattern;
    public $enable_callDepartment;
    public $layout_show;
    public $logo_size;
    public $show_category_first;
    public $show_category_second;
    public $show_category_third;
    public $country_options;
    public $countryMode = [];
    public $ticket_image;
    public $estimate_time_mode;
    public $count_all_services;
    public $doc_file_label;
    public $enable_doc_file;
    public $count_by_service;
    public $is_ticket_limit_enabled;
    public $ticket_limit;



    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Ticket Screen Setting')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();
        $this->categoryLevels = SiteDetail::getCategoryLevelEnable();
        $this->enableTimeSlots = SiteDetail::getQueueTimeSlotEnable();
        
        // Initialize count_by_service from database or default to 0
    
            
        $this->count_by_service = $siteDetail->count_by_service ?? 0;
        $this->listTimeZones = SiteDetail::getTimeZone();
        $this->countryCode = Country::query()->select('phonecode','name')->get();
        $this->countryMode = SiteDetail::getcountryoption();

        $siteDetail = SiteDetail::where('team_id', $this->teamId)->where('location_id',$this->locationId)->first();

        if ($siteDetail) {
            $this->fill([
                'category_text_font_size' => $siteDetail?->category_text_font_size,
                'ticket_font_family' => $siteDetail?->ticket_font_family,
                'category_border_size' => $siteDetail?->category_border_size,
                'token_digit' => $siteDetail?->token_digit,
                'token_start' => $siteDetail?->token_start,
                'estimate_time' => $siteDetail?->estimate_time,
                'category_estimated_time' =>$siteDetail?->category_estimated_time,
                'category_level_est' => $siteDetail?->category_level_est,
                'ticket_text_enable' => (bool)$siteDetail?->ticket_text_enable,
                'ticket_text' => $siteDetail?->ticket_text,
                'ticket_text_2' => $siteDetail?->ticket_text_2,
                'show_cat_icon' => (bool)$siteDetail?->show_cat_icon,
                'queue_form_display' => (bool)$siteDetail?->queue_form_display,
                'counter_estimated_time' => (bool)$siteDetail?->counter_estimated_time,
                'use_staff_priority' => (bool)$siteDetail?->use_staff_priority,
                'name_mandatory' => $siteDetail?->counter_estimated_time,
                'phone_mandatory' => $siteDetail?->counter_estimated_time,
                'name_before_appointment_form' => $siteDetail?->counter_estimated_time,
                'name_after_appointment_form' => $siteDetail?->counter_estimated_time,
                'phone_before_appointment_form' => $siteDetail?->counter_estimated_time,
                'phone_after_appointment_form' => $siteDetail?->counter_estimated_time,
                'name_placeholder' => $siteDetail?->name_placeholder,
                'phone_placeholder' => $siteDetail?->phone_placeholder,
                'name_custom_class' => $siteDetail?->name_custom_class,
                'phone_custom_class' => $siteDetail?->phone_custom_class,
                'late_coming_feature' => (bool)$siteDetail?->late_coming_feature,
                'qrcode_tagline' => $siteDetail?->qrcode_tagline,
                'qrcode_tagline_second' => $siteDetail?->qrcode_tagline_second,
                'is_qrcode_ticket' => (bool)$siteDetail?->is_qrcode_ticket,
                'is_qr_code' => (bool)$siteDetail?->is_qr_code,
                'app_heading_first' => $siteDetail?->app_heading_first,
                'app_heading_second' => $siteDetail?->app_heading_second,
                'user_detail' => (bool)$siteDetail?->user_detail,
                'bottom_btn_enable' => (bool)$siteDetail?->bottom_btn_enable,
                'queue_heading_first' =>$siteDetail->queue_heading_first ?? '',
                'queue_heading_second' =>$siteDetail->queue_heading_second ?? '',
                'submit_btn_text' =>$siteDetail->submit_btn_text ?? '',
                'back_btn_text' =>$siteDetail->back_btn_text ?? '',
                'category_slot_level' =>$siteDetail->category_slot_level ?? '1',
                'enable_time_slot' =>$siteDetail->enable_time_slot ?? 'ticket',
                'print_name_label' =>$siteDetail->print_name_label ?? '',
                'print_token_label' =>$siteDetail->print_token_label ?? '',
                'arrived_time_label' =>$siteDetail->arrived_time_label ?? '',
                'confirm_btn_label' =>$siteDetail->confirm_btn_label ?? '',
                'is_logo_on_print' =>(bool)$siteDetail->is_logo_on_print,
                'is_name_on_print' =>(bool)$siteDetail->is_name_on_print,
                'is_arrived_on_print' =>(bool)$siteDetail->is_arrived_on_print,
                'is_location_on_print' =>(bool)$siteDetail->is_location_on_print,
                'is_category_on_print' =>(bool)$siteDetail->is_category_on_print,
                'is_token_on_print' =>(bool)$siteDetail->is_token_on_print,
                'is_enable_waitlist_message' =>(bool)$siteDetail->is_enable_waitlist_message,
                'is_waitlist_table' =>(bool)$siteDetail->is_waitlist_table,
                'waitlist_heading' =>$siteDetail->waitlist_heading ?? '',
                'waitlist_message_first' =>$siteDetail->waitlist_message_first ?? '',
                'waitlist_message_second' =>$siteDetail->waitlist_message_second ?? '',
                'select_timezone' =>$siteDetail->select_timezone ?? 'Asia/Kolkata',
                'country_code' =>!empty($siteDetail->country_code)  ? $siteDetail->country_code : '91',
                'show_country_code' => (bool)$siteDetail->show_country_code,
                'background_image' => $siteDetail->background_image ?? '',
                'background_size' => $siteDetail->background_size,
                'background_repeat' => $siteDetail->background_repeat,
                'background_position' => $siteDetail->background_position,
                'use_staff_priority' => (bool)$siteDetail?->use_staff_priority,
                'is_redirect_print_page' => (bool)$siteDetail?->is_redirect_print_page,
                'ticket_mode' => (bool)$siteDetail?->ticket_mode,
                'disable_print' => (bool)$siteDetail?->disable_print,
                'print_mode' => $siteDetail?->print_mode,
                'enable_waitlist_list' => (bool)$siteDetail?->enable_waitlist_list,
                'enable_priority_pattern' => (bool)$siteDetail?->enable_priority_pattern,
                'enable_callDepartment' => (bool)$siteDetail?->enable_callDepartment,
                'logo_size' => $siteDetail?->logo_size,
                'layout_show' => $siteDetail?->layout_show,
                'show_category_first' => (bool)$siteDetail?->show_category_first,
                'show_category_second' => (bool)$siteDetail?->show_category_second,
                'show_category_third' => (bool)$siteDetail?->show_category_third,
                'country_options' => $siteDetail?->country_options ?? 1,
                'ticket_image' => $siteDetail->ticket_image ?? '',
                'estimate_time_mode' => $siteDetail->estimate_time_mode ?? 1,
                'count_all_services' => $siteDetail->count_all_services ?? 1,
                'doc_file_label' => $siteDetail->doc_file_label ?? '',
                'enable_doc_file' => (bool)$siteDetail?->enable_doc_file,
                'count_by_service' => (bool)$siteDetail?->count_by_service,
                'is_ticket_limit_enabled' => $siteDetail?->is_ticket_limit_enabled ?? 0,
                'ticket_limit' => $siteDetail?->ticket_limit ?? 100,

            ]);
        }
    }

    // public function showPaidCategoriesLvl()
    // {
    //     // if($this->is_paid_categories == 1)
    //     {
    //         // $this->is_paid_categories = 0;
    //         // $this->paid_category_level = 1;
    //     }
    //     // else
    //     // {
    //     //     $this->is_paid_categories = 1;
    //     // }
    // }

public function updatedPrintMode($value): void
{
    if($value == 'silent'){
        $this->disable_print = true;
    }else{
        $this->disable_print = false;

    }
}

    public function save()
{
    // ✅ Always validate common fields
    $rules = [
        'country_code' => 'required',
    ];

    // ✅ Conditionally validate background_image if uploaded
    if (is_object($this->background_image)) {
        $rules['background_image'] = 'nullable|image|max:2048';
    }

    // ✅ Conditionally validate ticket_image if uploaded
    if (is_object($this->ticket_image)) {
        $rules['ticket_image'] = 'nullable|image|max:2048';
    }

    $this->validate($rules);

    // ✅ Handle print mode toggle
    $this->disable_print = $this->print_mode === 'silent';

    // ✅ Data array (explicit fields only)
    $data = [
        'category_text_font_size' => $this->category_text_font_size,
        'ticket_font_family' => $this->ticket_font_family,
        'category_border_size' => $this->category_border_size,
        'token_digit' => $this->token_digit,
        'token_start' => $this->token_start,
        'estimate_time' => $this->estimate_time,
        'category_estimated_time' => $this->category_estimated_time,
        'category_level_est' => $this->category_level_est,
        'counter_estimated_time' => (bool)$this->counter_estimated_time,
        'queue_form_display' => (bool)$this->queue_form_display,
        'ticket_text_enable' => (bool)$this->ticket_text_enable,
        'ticket_text' => $this->ticket_text,
        'ticket_text_2' => $this->ticket_text_2,
        'show_cat_icon' => (bool)$this->show_cat_icon,
        'late_coming_feature' => (bool)$this->late_coming_feature,
        'is_qrcode_ticket' => (bool)$this->is_qrcode_ticket,
        'is_qr_code' => (bool)$this->is_qr_code,
        'qrcode_tagline' => $this->qrcode_tagline,
        'qrcode_tagline_second' => $this->qrcode_tagline_second,
        'app_heading_first' => $this->app_heading_first,
        'app_heading_second' => $this->app_heading_second,
        'name_mandatory' => $this->name_mandatory,
        'phone_mandatory' => $this->phone_mandatory,
        'name_before_appointment_form' => $this->name_before_appointment_form,
        'name_after_appointment_form' => $this->name_after_appointment_form,
        'count_by_service' => (bool)$this->count_by_service,
        'phone_before_appointment_form' => $this->phone_before_appointment_form,
        'phone_after_appointment_form' => $this->phone_after_appointment_form,
        'name_placeholder' => $this->name_placeholder,
        'phone_placeholder' => $this->phone_placeholder,
        'name_custom_class' => $this->name_custom_class,
        'phone_custom_class' => $this->phone_custom_class,
        'user_detail' => (bool)$this->user_detail,
        'bottom_btn_enable' => (bool)$this->bottom_btn_enable,
        'queue_heading_first' => $this->queue_heading_first ?? '',
        'queue_heading_second' => $this->queue_heading_second ?? '',
        'submit_btn_text' => $this->submit_btn_text ?? '',
        'back_btn_text' => $this->back_btn_text ?? '',
        'category_slot_level' => $this->category_slot_level ?? '1',
        'enable_time_slot' => $this->enable_time_slot ?? 'ticket',
        'print_name_label' => $this->print_name_label ?? '',
        'print_token_label' => $this->print_token_label ?? '',
        'arrived_time_label' => $this->arrived_time_label ?? '',
        'confirm_btn_label' => $this->confirm_btn_label ?? '',
        'is_logo_on_print' => (bool)$this->is_logo_on_print,
        'is_name_on_print' => (bool)$this->is_name_on_print,
        'is_arrived_on_print' => (bool)$this->is_arrived_on_print,
        'is_location_on_print' => (bool)$this->is_location_on_print,
        'is_category_on_print' => (bool)$this->is_category_on_print,
        'is_token_on_print' => (bool)$this->is_token_on_print,
        'is_enable_waitlist_message' => (bool)$this->is_enable_waitlist_message,
        'is_waitlist_table' => (bool)$this->is_waitlist_table,
        'waitlist_heading' => $this->waitlist_heading ?? '',
        'waitlist_message_first' => $this->waitlist_message_first ?? '',
        'waitlist_message_second' => $this->waitlist_message_second ?? '',
        'select_timezone' => $this->select_timezone ?? 'Asia/Kolkata',
        'country_code' => !empty($this->country_code) ? $this->country_code : '91',
        'show_country_code' => (bool)$this->show_country_code,
        'background_size' => $this->background_size ?? 'cover',
        'background_repeat' => $this->background_repeat ?? 'no-repeat',
        'background_position' => $this->background_position ?? 'center',
        'use_staff_priority' => (bool)$this->use_staff_priority,
        'is_redirect_print_page' => (bool)$this->is_redirect_print_page,
        'ticket_mode' => (bool)$this->ticket_mode,
        'disable_print' => (bool)$this->disable_print,
        'print_mode' => $this->print_mode,
        'enable_waitlist_list' => (bool)$this->enable_waitlist_list,
        'enable_priority_pattern' => (bool)$this->enable_priority_pattern,
        'enable_callDepartment' => (bool)$this->enable_callDepartment,
        'layout_show' => $this->layout_show,
        'logo_size' => $this->logo_size,
        'show_category_first' => (bool)$this->show_category_first,
        'show_category_second' => (bool)$this->show_category_second,
        'show_category_third' => (bool)$this->show_category_third,
        'country_options' => $this->country_options ?? 1,
         'estimate_time_mode' => $this->estimate_time_mode ?? 1,
         'count_all_services' => $this->count_all_services ?? 1,
        'doc_file_label' => $this->doc_file_label ?? '',
        'enable_doc_file' => (bool)$this->enable_doc_file,
        'is_ticket_limit_enabled' => $this->is_ticket_limit_enabled ?? 0,
        'ticket_limit' => $this->ticket_limit ?? 100,
    ];

    // ✅ Upload images if present
    if (is_object($this->background_image)) {
        $data['background_image'] = $this->background_image->store('logo', 'public');
    } elseif (is_string($this->background_image)) {
        $data['background_image'] = $this->background_image;
    }

    if (is_object($this->ticket_image)) {
        $data['ticket_image'] = $this->ticket_image->store('logo', 'public');
    } elseif (is_string($this->ticket_image)) {
        $data['ticket_image'] = $this->ticket_image;
    }

    // ✅ Save or update
    SiteDetail::updateOrCreate(
        ['team_id' => $this->teamId, 'location_id' => $this->locationId],
        $data
    );

    // Log activity
    ActivityLog::storeLog(
        $this->teamId,
        $this->userAuth->id,
        null,
        null,
        'Ticket Screen Settings',
        $this->locationId,
        ActivityLog::SETTINGS,
        null,
        $this->userAuth
    );

    $this->dispatch('updated');
}

      public function deleteBackgroundImage()
{
    // Delete file from storage if it's not a temporary upload
    if (is_string($this->background_image) && Storage::disk('public')->exists($this->background_image)) {
        Storage::disk('public')->delete($this->background_image);
    }

    // Clear the property
    $this->background_image = null;

    // Optionally also remove it from DB
    SiteDetail::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->update(['background_image' => null]);

        $this->dispatch('deleted');
}
      public function deleteticketBackgroundImage()
{
    // Delete file from storage if it's not a temporary upload
    if (is_string($this->ticket_image) && Storage::disk('public')->exists($this->ticket_image)) {
        Storage::disk('public')->delete($this->ticket_image);
    }

    // Clear the property
    $this->ticket_image = null;

    // Optionally also remove it from DB
    SiteDetail::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->update(['ticket_image' => null]);

        $this->dispatch('deleted');
}

    public function render()
    {
        return view('livewire.ticket-screen-settings');
    }
}
