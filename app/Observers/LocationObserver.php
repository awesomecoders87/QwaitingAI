<?php

namespace App\Observers;

use App\Models\Location;
use App\Models\User;
use App\Models\Counter;
use App\Models\Category;
use App\Models\Queue;
use App\Models\CustomSlot;
use App\Models\ColorSetting;
use App\Models\AccountSetting;
use App\Models\FormField;
use App\Models\SiteDetail;
use App\Models\SmsAPI;
use App\Models\GenerateQrCode;
use App\Models\Domain;
use App\Models\NotificationTemplate;
use App\Models\MessageTemplate;
use App\Models\WhatsappTemplate;
use App\Models\LanguageSetting;
use App\Models\Level;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Role;
use App\Models\Permission;

class LocationObserver
{
   
     /**
     * Handle the Location "creating" event.
     */
    public function created(Location $location)
    {
  
        if(Auth::check()){
        $authUser = Auth::user();

        if ($authUser) {
            $locations = $authUser->locations;
    
            // Normalize the value to an array
            if (is_string($locations) && !empty($locations)) {
                $currentLocations = json_decode($locations, true);
            } elseif (is_array($locations)) {
                $currentLocations = $locations;
            } else {
                $currentLocations = [];
            }
    
            // Add the new location ID if not already present
            if (!in_array($location->id, $currentLocations)) {
                $currentLocations[] = "$location->id";
    
                // Save as array if you're using cast, or encode manually if not
                $authUser->locations = $currentLocations;
                $authUser->save();
            }
        }
        }
        $defaultBusinessHours = [
            ["day" => "Monday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Tuesday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Wednesday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Thursday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Friday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Saturday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Sunday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []]
        ];

        $slottypes = ['location','booking','ticket'];
        foreach($slottypes as $slot){
            AccountSetting::create([
                'location_id' => "$location->id",
                'team_id' => $location->team_id, // Assuming the location has a `team_id`
                'slot_type' => $slot, // Assuming the location has a `team_id`
                'business_hours' => json_encode($defaultBusinessHours),
                'booking_system' => $slottypes =='location' ? 1 : 0,
                'booking_time_hold' => 1,
                'booking_approval_by_staff' => 1,
                'allow_reschedule' => 1,
                'cancel_booking_cus' => 1,
                'edit_cancel_book_cus' => 1,
                'show_con_app_form' => 1,
                'booking_confirmation_page' => 1,
                'custom_booking_id' => 'default',
                'booking_convert_manually' => 1,
                'google_calendar' => 1,
                'outlook_calendar' => 1,
                'slot_period' => 30,
                'req_per_slot' => 10,
                'req_per_slot' => 10,
                'allow_req_before' => 150,
                'allow_req_min_before' => 0,
                'req_accept_mode' => 'Auto Confirm',
                'is_geofence' => '0',
                'is_waitlist_limit' => '0',
                'walk_in_label' => 'Walk In',
                'appointment_label' => 'Appointment',
                'created_by' => Auth::id(),
            ]);
        }

        SiteDetail::create([
            'team_id' => $location->team_id,
            'location_id' => $location->id,
            'business_logo' => 'logo/qwaiting.png',
            'mobile_logo' => 'logo/qwaiting.png',
            'logo_print_ticket' => 'logo/qwaiting.png',
            'logo_footer_ticket_screen' => 'logo/qwaiting.png',
            'category_text_font_size' => 'text-xl',
            'ticket_font_family' => 'font-sans',
            'category_border_size' => 'border-2',
            'token_digit' => 4,
            'token_start' => '01',
            'estimate_time' => 2,
            'category_estimated_time' => 0,
            "country_code" => "91",
            'category_level_est' => 'parent',
            'ticket_text_enable' => 1,
            'ticket_text' => 'There are {{QUEUE COUNT}} queuing before you.',
            'ticket_text_2' => 'Your estimated waiting time is {{Waiting Time}} min.',
            'show_cat_icon' => 1,
            'queue_form_display' => 1,
            'counter_estimated_time' => 1,
            'hide_button' => 'SHOW_CLOSE',
            'show_visitor_cat' => 1,
            'fixed_visitor_list_queue' => 1,
            'ticket_generation_link' => 1,
            'total_served' => 1,
            'served_queue' => 1,
            'break' => 0,
            'activity_log' => 1,
            'label_next' => 'Next',
            'label_start' => 'Start',
            'label_recall' => 'Recall',
            'label_close' => 'Close',
            'label_skip' => 'Missed',
            'label_move_back' => 'Move Back',
            'label_transfer' => 'Transfer',
            'label_generate_queue' => 'Generate Queue',
            'label_counter' => 'Counter',
            'label_no_call' => 'No Call',
            'label_total_served_token' => 'Total Served Tokens',
            'label_cancelled_queue_no' => 'Cancelled Queue No.',
            'label_missed_queue' => 'Missed Queue',
            'label_visitor_waiting' => 'Visitors are waiting',
            'label_current_serving' => 'Current Serving',
            'label_queue_number' => 'Queue Number',
            'label_serving_time' => 'Serving Time',
            'label_issue_date' => 'Issue Date',
            'missed_queue_history_popup' => 0,
            'is_move_back' => 1,
            'show_department_missed_queue' => 1,
            'counter_assigned_queue' => 1,
            'show_send_sms_button' => 0,
            'show_call_history' => 0,
            'show_next_button' => 1,
            'reset_cur_serving' => 1,
            'counter_option' => 0,
            'staff_rating' => 0,
            'manual_ticket' => 0,
            'submit_btn_text' =>'Submit',
            'back_btn_text' =>'Back',
            'back_btn_text' =>'Back',
            'enable_time_slot' =>'ticket',
            'print_name_label' =>'Name',
            'print_token_label' =>'Token',
            'arrived_time_label' =>'Arrived',
            'confirm_btn_label' =>'Print',
            'waitlist_message_first' =>'{{QUEUE COUNT}}  waiting',
            'waitlist_message_second' =>'Estimated wait {{Waiting Time}} mins',
            'rate_limit_sec' =>5,
            'rate_limit_minute' =>60,
            'rate_limit_day' =>300,
            'concurrency_limit'=>3,
            'rate_limit_by' =>'ip',
            'use_staff_priority'=>0,
            'is_redirect_print_page'=>0,
            'ticket_mode'=>0,
            'is_waitlist_table'=>0,
            'is_enable_waitlist_message'=>0,
        ]);


        FormField::create([
            'team_id' => $location->team_id,
            'location_id' => $location->id,
            "type" => "Text",
            "title" => "name",
            "options" => null,
            "ticket_screen" => 1,
            "after_scan_screen" => 0,
            "mandatory" => 1,
            "placeholder" => "Name",
            "custom_class" => null,
            "sort" => 1,
            "default_value" => null,
            "label" => "Name",
            "before_appointment_form" => 1,
            "after_appointment_form" => 0,
            "minimum_number_allowed" => 1,
            "maximum_number_allowed" => 100,
            "policy_content" => null,
            "policy_url" => null,
            "policy" => null,
            "show_on_form" => 1,
            "is_edit_remove" => 0,
            "validation" => null,
    
        ],[
            'team_id' => $location->team_id,
            'location_id' => $location->id,
            "type" => "Phone",
            "title" => "phone",
            "options" => null,
            "ticket_screen" => 1,
            "after_scan_screen" => 1,
            "mandatory" => 1,
            "placeholder" => "Phone Number",
            "custom_class" => null,
            "sort" => 2,
            "default_value" => null,
            "label" => "Phone Number",
            "before_appointment_form" => 1,
            "after_appointment_form" => 1,
            "minimum_number_allowed" => 5,
            "maximum_number_allowed" => 16,
            "policy_content" => null,
            "policy_url" => null,
            "policy" => null,
            "show_on_form" => 1,
            "is_edit_remove" => 0,
            "validation" => null,
        ]);
        $levels =['1'=>'Level 1','2'=>'Level 2','3'=>'Level 3'];
        foreach($levels as $key=>$level){

            Level::create([
            'team_id' => $location->team_id,
            'location_id' => $location->id,
            'name' => $level,
            'level' => $key,
            'acronyms_show_level' => 1,
            ]);
        }
        //Qrcode 
         Queue::timezoneSet();

         $timezone = Session::get('timezone_set') ?? 'UTC';
        $current_time = Carbon::now($timezone);
        $timeInSeconds = strtotime($current_time->toDateTimeString());
        $domain = Domain::where('team_id',$location->team_id)->first();
       
        $customizeUrl = 'https://' . $domain->domain .'/mobile/queue/' . $location->id . '/' . $timeInSeconds;

        GenerateQrCode::create([
                'team_id' => $location->team_id,
                'location_id' => $location->id,
                'qrcode_url_status' =>GenerateQrCode::STATUS_INACTIVE,
                'url_validity_str' => 'queue',
                'url' => $customizeUrl,
                'level_ecc' => 'L',
                'scan_valid_distance' =>  100,
                'size' => 4,
                'created_by' => auth()->user()->id,
                'qr_update_time' => 15,
                'qrupdated_at' => $current_time,
                'status' => 1,
            ]);

            ColorSetting::create([
            'team_id' => $location->team_id,
            'location_id' => $location->id,
            'page_layout' => '#ffffff',
            'categories_background_layout' => '#f5f5f5',
            'text_layout' => '#000000',
            'hover_background_layout' => '#cccccc',
            'hover_text_layout' => '#333333',
            'buttons_layout' => '#007bff',
            'hover_button_layout' => '#0056b3',
            'mobile_page_layout' => '#ffffff',
            'mobile_header_background_color' => '#007bff',
            'mobile_heading_text_color' => '#ffffff',
            'mobile_category_button_color' => '#007bff',
            'mobile_button_text_color' => '#ffffff',
            'mobile_button_color' => '#007bff',
            'mobile_font_color' => '#000000',
            'theme_color' => '#007bff',
            'font_color' => '#000000',
            'button_color' => '#007bff', 
        ]);


        LanguageSetting::create([
                'team_id' => $location->team_id,
                'location_id' => $location->id,
                "enabled_language_settings" => true,
                "available_languages" => [],
                "default_language" => "en",
            ]);

             $defaultRoles = ['Manager', 'Staff'];
            $permissions = Permission::whereNotNull('team_id')->get(); // assumes Spatie permissions are set

    foreach ($defaultRoles as $roleName) {
        $role = Role::firstOrCreate([
            'name' => $roleName, 
            'team_id' => $location->team_id, // if using teams
            'location_id' => $location->id,
        ]);


        // $role->permissions()->sync($permissions);

       $role->syncPermissions($permissions);
}
            NotificationTemplate::createDefaultTemplates($location->team_id, $location->id);
            MessageTemplate::defaultTemplateContent($location->team_id, $location->id);
    }


    /**
     * Handle the Location "deleted" event.
     */
    public function deleting(Location $location)
    {
   
        // Find users who have the deleted location ID in their locations array
        $users = User::whereNotNull('locations')
        ->where('locations', '!=', '')
        ->whereRaw("JSON_VALID(locations)")
        ->whereJsonContains('locations', (string) $location->id)
        ->orWhereJsonContains('locations', (int) $location->id)
        ->get();

    foreach ($users as $user) {
        $locations = is_array($user->locations)
            ? $user->locations
            : json_decode($user->locations, true);

        // Filter out the deleted location (string and int match)
        $updated = collect($locations)
            ->reject(fn($id) => (string) $id === (string) $location->id)
            ->values()
            ->all();

        // Update as array (Laravel handles JSON encoding via $casts)
        $user->update(['locations' => $updated]);
    }
        $counters = Counter::whereJsonContains('counter_locations', $location->id)->get();
        if(!empty($counters)){
        foreach ($counters as $counter) {
            $updatedLocations = array_filter($counter->counter_locations, function ($locId) use ($location) {
                return (string) $locId !== (string) $location->id;
            });

            // Update the counter's locations
            $counter->update(['counter_locations' => array_values($updatedLocations)]);
        }
    }
    $categories = Category::whereJsonContains('category_locations', "$location->id")->get();
    if(!empty($categories)){

        foreach ($categories as $category) {
            $updatedLocations = array_filter($category->category_locations, function ($locId) use ($location) {
                return (string) $locId !== (string) $location->id;
            });

            // Update the category's locations
            $category->update(['category_locations' => array_values($updatedLocations)]);
        }
    }
         AccountSetting::where('location_id', "$location->id")->delete();
         CustomSlot::where('location_id', "$location->id")->delete();
         SiteDetail::where('location_id', $location->id)->delete();
         FormField::where('location_id', $location->id)->delete();
         SmsAPI::where('location_id', $location->id)->delete();
         GenerateQrCode::where('location_id', $location->id)->delete();
         NotificationTemplate::where('location_id', $location->id)->delete();
         MessageTemplate::where('location_id', $location->id)->delete();
         WhatsappTemplate::where('location_id', $location->id)->delete();
         ColorSetting::where('location_id', $location->id)->delete();
         LanguageSetting::where('location_id', $location->id)->delete();
         Level::where('location_id', $location->id)->delete();
    }


   
    
}
