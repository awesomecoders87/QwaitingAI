<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class SiteDetail extends Model
 {
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'business_logo',
        'mobile_logo',
        'logo_print_ticket',
        'logo_footer_ticket_screen',
        'category_text_font_size',
        'ticket_font_family',
        'category_border_size',
        'token_digit',
        'token_start',
        'estimate_time',
        'category_estimated_time',
        'category_level_est',
        'ticket_text_enable',
        'ticket_text',
        'ticket_text_2',
        'show_cat_icon',
        'queue_form_display',
        'counter_estimated_time',
        'hide_button',
        'show_visitor_cat',
        'fixed_visitor_list_queue',
        'fixed_queue_size',
        'ticket_generation_link',
        'total_served',
        'break',
        'activity_log',
        'label_next',
        'label_start',
        'label_recall',
        'label_close',
        'label_skip',
        'label_move_back',
        'label_transfer',
        'label_generate_queue',
        'label_counter',
        'label_no_call',
        'label_total_served_token',
        'label_cancelled_queue_no',
        'label_missed_queue',
        'label_hold_queue',
        'label_visitor_waiting',
        'label_current_serving',
        'label_queue_number',
        'label_serving_time',
        'label_issue_date',
        'missed_queue_history_popup',
        'is_move_back',
        'show_department_missed_queue',
        'counter_assigned_queue',
        'show_send_sms_button',
        'show_call_history',
        'show_next_button',
        'reset_cur_serving',
        'counter_option',
        'staff_rating',
        'manual_ticket',
        'is_waiting_time',
        'is_cancelled_queue',
        'is_transfer_option',
        'is_cat_dept_queues',
        'is_cat_dept_queues',
        'is_transfer_operational',
        'is_display_message_call',
        'is_sound_notification',
        'is_confirm_popup',
        'close_unserved_calls',
        'total_call_count',
        'served_queue_move',
        'late_coming_feature',
        'qrcode_tagline',
        'qrcode_tagline_second',
        'is_qrcode_ticket',
        'is_qr_code',
        'served_queue',
        'notes_add_option',
        'created_at',
        'updated_at',
        'queue_priority',
        'country_code',
        'email_reminder_time',
        'email_reminder_type',
        'email_reminder_status',
        'category_slot_level',
        'choose_time_slot',
        'app_heading_first',
        'app_heading_second',
        'app_heading_third',
        'user_detail',
        'bottom_btn_enable',
        'queue_heading_first',
        'queue_heading_second',
        'submit_btn_text',
        'back_btn_text',
        'enable_time_slot',
        'print_name_label',
        'print_token_label',
        'arrived_time_label',
        'is_logo_on_print',
        'is_name_on_print',
        'is_arrived_on_print',
        'is_location_on_print',
        'is_category_on_print',
        'is_token_on_print',
        'confirm_btn_label',
        'is_enable_waitlist_message',
        'waitlist_heading',
        'waitlist_message_first',
        'waitlist_message_second',
        'is_waitlist_table',
        'is_client_update',
        'is_missed_call',
        'is_recall_button',
        'is_hold',
        'select_timezone',
        'rate_limit_sec',
        'rate_limit_minute',
        'rate_limit_day',
        'concurrency_limit',
        'rate_limit_by',
        'show_country_code',
        'booking_sidebar_heading',
        'hold_message',
        'is_suspension_button',
        'is_customer_login',
        'is_prefer_time_slot',
        'background_image',
        'background_size',
        'background_repeat',
        'background_position',
        'use_staff_priority',
        'is_redirect_print_page',
        'ticket_mode',
        'display_name',
        'disable_print',
        'is_ticket_limit_enabled',
        'ticket_limit',
        'print_mode',
        'enable_priority_pattern',
        'enable_callDepartment',
        'layout_show',
        'logo_size',
        'show_category_first',
        'show_category_second',
        'show_category_third',
        'counter_transfer',
        'mode_transfer_option',
        'login_counters_only',
        'category_transfer',
        'country_options',
        'show_transfer_token',
        'label_transfer_token',
        'enable_active_users_list',
        'ticket_image',
        'estimate_time_mode',
        'enable_waiting_popup',
        'popup_waiting_time',
        'count_all_services',
        'doc_file_label',
        'enable_doc_file',
        'assigned_staff_id',
        'count_by_service',
        'google_map_key',
        'queue_token_list',
        'queue_token_end_time'

    ];

    const FIELD_BUSINESS_LOGO = 'business_logo';
    const FIELD_MOBILE_LOGO = 'mobile_logo';
    const FIELD_LOGO_PRINT_TICKET = 'logo_print_ticket';
    const FIELD_LOGO_FOOTER_TICKET = 'logo_footer_ticket_screen';
    const STATUS_YES = 1;
    const STATUS_NO = 0;
    const LABEL_YES = 'Yes';
    const LABEL_NO = 'No';
    const DEFAULT_WALKIN_A = 'W';
    const DEFAULT_APPOINTMENT_A = 'A';
    const DEFAULT_TOKEN_START = '001';

    public static function hideButton() {
        return [
            'HIDE_START_CLOSE'=>__( 'text.Hide Start/Close Button' ),
            'SHOW_START_CLOSE'=>__( 'text.Show Start/Close Button' ),
            'SHOW_CLOSE'=>__( 'text.Show Close Button' )
        ];
    }

    public function team(): BelongsTo
 {
        return $this->belongsTo( Tenant::class );
    }

    public function location(): BelongsTo
 {
        return $this->belongsTo( Location::class );
    }

    // public static function viewImage( $field, $team_id = '',$locatonId=null ) {

    //     $teamId = $team_id ?: tenant('id');
    //     $logo = '';
    //     if ( !empty( $teamId ) )
    //     $logo = self::where( 'team_id', $teamId )->value( $field );

    //     return $logo != '' ? 'storage/' . $logo : 'images/logo-transparent.png';
    // }

    public static function viewImage($field, $team_id = null, $locationId = null) {
        $teamId =  $team_id ?? tenant('id');
        $logo = '';

        if (!empty($teamId)) {
            // Check if location_id is provided, fetch the logo based on it
            if (!empty($locationId)) {
                $logo = self::where('team_id', $teamId)
                            ->where('location_id', $locationId)
                            ->value($field);
            }

            // If no logo found with location_id, fetch based on team_id
            if (empty($logo)) {
                $logo = self::where('team_id', $teamId)
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->value($field);
            }
        }

        return !empty($logo) ? 'storage/' . $logo : 'images/logo-transparent.png';
    }

    public static function getFontSize() {
        return [
            'text-xs' => 'text-xs',
            'text-sm' => 'text-sm',
            'text-base' => 'text-base',
            'text-lg' => 'text-lg',
            'text-xl' => 'text-xl',
            'text-2xl' => 'text-2xl',
            'text-3xl' => 'text-3xl',
            'text-4xl' => 'text-4xl',
            'text-5xl' => 'text-5xl',
            'text-6xl' => 'text-6xl',
            'text-7xl' => 'text-7xl',
            'text-8xl' => 'text-8xl',
            'text-9xl' => 'text-9xl',
        ];
    }
    public static function getTimeZone() {
        return [
            "Pacific/Midway" => "(GMT-11:00) Midway Island",
            "US/Samoa" => "(GMT-11:00) Samoa",
            "US/Hawaii" => "(GMT-10:00) Hawaii",
            "US/Alaska" => "(GMT-09:00) Alaska",
            "US/Pacific" => "(GMT-08:00) Pacific Time (US & Canada)",
            "America/Tijuana" => "(GMT-08:00) Tijuana",
            "US/Arizona" => "(GMT-07:00) Arizona",
            "US/Mountain" => "(GMT-07:00) Mountain Time (US & Canada)",
            "America/Chihuahua" => "(GMT-07:00) Chihuahua",
            "America/Mazatlan" => "(GMT-07:00) Mazatlan",
            "America/Mexico_City" => "(GMT-06:00) Mexico City",
            "America/Monterrey" => "(GMT-06:00) Monterrey",
            "Canada/Saskatchewan" => "(GMT-06:00) Saskatchewan",
            "US/Central" => "(GMT-06:00) Central Time (US & Canada)",
            "US/Eastern" => "(GMT-05:00) Eastern Time (US & Canada)",
            "US/East-Indiana" => "(GMT-05:00) Indiana (East)",
            "America/Bogota" => "(GMT-05:00) Bogota",
            "America/Lima" => "(GMT-05:00) Lima",
            "America/Caracas" => "(GMT-04:30) Caracas",
            "Canada/Atlantic" => "(GMT-04:00) Atlantic Time (Canada)",
            "America/La_Paz" => "(GMT-04:00) La Paz",
            "America/Santiago" => "(GMT-04:00) Santiago",
            "Canada/Newfoundland" => "(GMT-03:30) Newfoundland",
            "America/Buenos_Aires" => "(GMT-03:00) Buenos Aires",
            "Greenland" => "(GMT-03:00) Greenland",
            "Atlantic/Stanley" => "(GMT-02:00) Stanley",
            "Atlantic/Azores" => "(GMT-01:00) Azores",
            "Atlantic/Cape_Verde" => "(GMT-01:00) Cape Verde Is.",
            "Africa/Casablanca" => "(GMT) Casablanca",
            "Europe/Dublin" => "(GMT) Dublin",
            "Europe/Lisbon" => "(GMT) Lisbon",
            "Europe/London" => "(GMT) London",
            "Africa/Monrovia" => "(GMT) Monrovia",
            "Africa/Libreville" => "(GMT+01:00) Gabon",
            "Europe/Amsterdam" => "(GMT+01:00) Amsterdam",
            "Europe/Belgrade" => "(GMT+01:00) Belgrade",
            "Europe/Berlin" => "(GMT+01:00) Berlin",
            "Europe/Bratislava" => "(GMT+01:00) Bratislava",
            "Europe/Brussels" => "(GMT+01:00) Brussels",
            "Europe/Budapest" => "(GMT+01:00) Budapest",
            "Europe/Copenhagen" => "(GMT+01:00) Copenhagen",
            "Europe/Ljubljana" => "(GMT+01:00) Ljubljana",
            "Europe/Madrid" => "(GMT+01:00) Madrid",
            "Europe/Paris" => "(GMT+01:00) Paris",
            "Europe/Prague" => "(GMT+01:00) Prague",
            "Europe/Rome" => "(GMT+01:00) Rome",
            "Europe/Sarajevo" => "(GMT+01:00) Sarajevo",
            "Europe/Skopje" => "(GMT+01:00) Skopje",
            "Europe/Stockholm" => "(GMT+01:00) Stockholm",
            "Europe/Vienna" => "(GMT+01:00) Vienna",
            "Europe/Warsaw" => "(GMT+01:00) Warsaw",
            "Europe/Zagreb" => "(GMT+01:00) Zagreb",
            "Europe/Athens" => "(GMT+02:00) Athens",
            "Europe/Bucharest" => "(GMT+02:00) Bucharest",
            "Africa/Cairo" => "(GMT+02:00) Cairo",
            "Africa/Harare" => "(GMT+02:00) Harare",
            "Europe/Helsinki" => "(GMT+02:00) Helsinki",
            "Europe/Istanbul" => "(GMT+02:00) Istanbul",
            "Asia/Jerusalem" => "(GMT+02:00) Jerusalem",
            "Europe/Kiev" => "(GMT+02:00) Kyiv",
            "Europe/Minsk" => "(GMT+02:00) Minsk",
            "Europe/Malta" => "(GMT+02:00) Malta",
            "Europe/Riga" => "(GMT+02:00) Riga",
            "Europe/Sofia" => "(GMT+02:00) Sofia",
            "Europe/Tallinn" => "(GMT+02:00) Tallinn",
            "Europe/Vilnius" => "(GMT+02:00) Vilnius",
            "Asia/Baghdad" => "(GMT+03:00) Baghdad",
            "Asia/Kuwait" => "(GMT+03:00) Kuwait",
            "Africa/Nairobi" => "(GMT+03:00) Nairobi",
            "Asia/Riyadh" => "(GMT+03:00) Riyadh",
            "Europe/Moscow" => "(GMT+03:00) Moscow",
            "Asia/Tehran" => "(GMT+03:30) Tehran",
            "Asia/Baku" => "(GMT+04:00) Baku",
            "Europe/Volgograd" => "(GMT+04:00) Volgograd",
            "Asia/Muscat" => "(GMT+04:00) Muscat",
            "Asia/Tbilisi" => "(GMT+04:00) Tbilisi",
            "Asia/Yerevan" => "(GMT+04:00) Yerevan",
            "Asia/Kabul" => "(GMT+04:30) Kabul",
            "Asia/Karachi" => "(GMT+05:00) Karachi",
            "Asia/Tashkent" => "(GMT+05:00) Tashkent",
            "Asia/Kolkata" => "(GMT+05:30) Kolkata",
            "Asia/Kathmandu" => "(GMT+05:45) Kathmandu",
            "Asia/Yekaterinburg" => "(GMT+06:00) Ekaterinburg",
            "Asia/Almaty" => "(GMT+06:00) Almaty",
            "Asia/Dhaka" => "(GMT+06:00) Dhaka",
            "Asia/Novosibirsk" => "(GMT+07:00) Novosibirsk",
            "Asia/Bangkok" => "(GMT+07:00) Bangkok",
            "Asia/Jakarta" => "(GMT+07:00) Jakarta",
            "Asia/Krasnoyarsk" => "(GMT+08:00) Krasnoyarsk",
            "Asia/Chongqing" => "(GMT+08:00) Chongqing",
            "Asia/Hong_Kong" => "(GMT+08:00) Hong Kong",
            "Asia/Kuala_Lumpur" => "(GMT+08:00) Kuala Lumpur (Malaysia)",
            "Australia/Perth" => "(GMT+08:00) Perth",
            "Asia/Singapore" => "(GMT+08:00) Singapore",
            "Asia/Taipei" => "(GMT+08:00) Taipei",
            "Asia/Ulaanbaatar" => "(GMT+08:00) Ulaan Bataar",
            "Asia/Urumqi" => "(GMT+08:00) Urumqi",
            "Asia/Irkutsk" => "(GMT+09:00) Irkutsk",
            "Asia/Seoul" => "(GMT+09:00) Seoul",
            "Asia/Tokyo" => "(GMT+09:00) Tokyo",
            "Australia/Adelaide" => "(GMT+09:30) Adelaide",
            "Australia/Darwin" => "(GMT+09:30) Darwin",
            "Asia/Yakutsk" => "(GMT+10:00) Yakutsk",
            "Australia/Brisbane" => "(GMT+10:00) Brisbane",
            "Australia/Canberra" => "(GMT+10:00) Canberra",
            "Pacific/Guam" => "(GMT+10:00) Guam",
            "Australia/Hobart" => "(GMT+10:00) Hobart",
            "Australia/Melbourne" => "(GMT+10:00) Melbourne",
            "Pacific/Port_Moresby" => "(GMT+10:00) Port Moresby",
            "Australia/Sydney" => "(GMT+10:00) Sydney",
            "Asia/Vladivostok" => "(GMT+11:00) Vladivostok",
            "Asia/Magadan" => "(GMT+12:00) Magadan",
            "Pacific/Auckland" => "(GMT+12:00) Auckland",
        ];
    }

    public static function getFontFamily() {
        return [
            'font-sans' => 'font-sans',
            'font-serif' => 'font-serif',
            'font-mono' => 'font-mono',
        ];
    }
    public static function getBorderSize() {
        return [
            'border-0' => 'border-0',
            'border-2' => 'border-2',
            'border-4' => 'border-4',
            'border-8' => 'border-8',
            'border' => 'border',
        ];
    }
    public static function getTokenDigit() {
        return[
            '0'=>'Default',
            '3'=>'3 Digit',
            '4'=>'4 Digit',

        ];
    }

       public static function getcountryoption() {
        return [
            '1'=>__('text.Single Country Code Mode'),
            '2'=>__('text.Multiple Country Codes Mode'),
        ];
    }

    public static function getCategoryLevelEstimaion() {
        return[
            'parent'=>'First Level',
            'child'=>'Second Level',
            'automatic'=>'Third Level',

        ];
    }
    public static function getCategoryLevelEnable() {
        return[
            '1'=>'First Level',
            '2'=>'Second Level',
            '3'=>'Third Level',
        ];
    }
    public static function getTimeSlotEnable() {
        return[

            'category'=>__('text.service'),
            'staff'=>__('text.staff'),
            'booking'=>__('text.booking'),
        ];
    }
    public static function getQueueTimeSlotEnable() {
        return[
            'category'=>__('text.service'),
            'ticket'=>__('text.ticket'),
        ];
    }

    public static function getYesNo() {
        return[
            '1'=>'Yes',
            '0'=>'No'
        ];
    }

     public static function ticketMode() {
        return[
            '1'=>'Face to Face',
            '2'=>'Virtual'
        ];
    }


    public static function getMyDetails($team_id, $location = null)
    {
        $query = self::where('team_id', $team_id);

        if (!is_null($location)) {
            $query->where('location_id', $location);
        }

        return $query->first();
    }


    public static function storeStaticDetails( $teamId ) {
        self::create( [
            'team_id'=>$teamId
        ] );
    }
    public static function createImage( $dataset, $name ) {

        $encodedDataset = urlencode( json_encode( $dataset ) );
        $chartImageUrl = "https://quickchart.io/chart?c={$encodedDataset}";

        $chartImage = Http::get( $chartImageUrl );

        if ( $chartImage->successful() ) {
            $chartImagePath = 'charts/'.$name;

            if ( !file_exists( public_path( 'charts' ) ) ) {
                mkdir( public_path( 'charts' ), 0755, true );
            }

            file_put_contents( public_path( $chartImagePath ), $chartImage->body() );

            $chartImageLocalUrl = url( $chartImagePath );

            if ( file_exists( public_path( $chartImagePath ) ) ) {
                Log::info( "Chart image successfully saved at: {$chartImageLocalUrl}" );
            } else {
                Log::error( "Failed to save chart image at: {$chartImagePath}" );
                return response()->json( [ 'error' => 'Failed to save chart image' ], 500 );
            }
        } else {
            Log::error( "Failed to download chart image: {$chartImage->body()}" );
            return response()->json( [ 'error' => 'Failed to download chart image' ], 500 );
        }
        return $chartImagePath;

    }


     public static function checkTicketLimit($teamId, $locationId, $siteDetails)
    {

        if ($siteDetails->is_ticket_limit_enabled == 1) {
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

    public static function fetchWaitingTime($location){

        return Self::where('location_id',$location)->value('estimate_time');
    }
}
