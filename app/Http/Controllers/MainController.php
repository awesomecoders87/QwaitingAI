<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Booking;
use App\Models\QueueStorage;
use App\Models\SiteDetail;
use App\Models\Domain;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\SmtpDetails;
use App\Models\Location;
use App\Models\Category;
use Google\Client;
use Google\Service\Calendar;
use Spatie\GoogleCalendar\Event;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;

class MainController extends Controller
{
    public function index (){
        // $teams =  Team::with('users')->latest()->get();
        // $domainSlug = request()->route( 'domainSlug' );

    //    return view('main-page',compact('teams','domainSlug'));
    }



    public function createEvent($id)
{
    $decode_id = base64_decode($id);
    $booking = Booking::with(['categories', 'sub_category', 'child_category', 'createdBy'])
                      ->find($decode_id);

                      $tenantName = tenant('name');
                      $supportEmail = auth()->user()->email ?? '';
                   
                      $locationdetail = Location::where('id',$booking->location_id)->first();
     
    if (!$booking) {
        return response()->json(['error' => 'Booking not found'], 404);
    }

    $siteDetails = SiteDetail::where('team_id', $booking->team_id)
                              ->where('location_id', $booking->location_id)
                              ->select('select_timezone')
                              ->first();

    $timezone = $siteDetails->select_timezone ?? config('app.timezone');
    Config::set('app.timezone', $timezone);
    date_default_timezone_set($timezone);

    $slug = Domain::where('team_id', $booking->team_id)->value('domain');
    $categoryName = Category::viewCategoryName($booking->category_id);
    $customerName = $booking->name ?? 'Customer';
    $staffName = $booking?->staff->name ?? 'N/A';
    $locationName = $locationdetail?->location_name ?? 'N/A';
    $eventLink = 'https://' . $slug . '/booking-confirmed/' . $id;

    $title = "{$categoryName} – {$customerName} | {$booking->refID}";

    $description = <<<EOT
Dear {$customerName},

This is a confirmation for your upcoming appointment.

📅 **Appointment Details**
• **Service**: {$categoryName}
• **Date**: {$booking->booking_date}
• **Time**: {$booking->start_time} – {$booking->end_time}
• **Location**: {$locationName}
• **Staff**: {$staffName}
• **Booking Reference**: {$booking->refID}

🔗 **Meeting Link** (if applicable):  
{$eventLink}

📌 **Important Notes**:  
– Please arrive 5–10 minutes early.  
– Bring necessary documents (if any).  
– For rescheduling or cancellation, use your booking dashboard or contact support.

Thank you,  
{$tenantName} 
$supportEmail
EOT;

    $startDateTime = Carbon::parse("{$booking->booking_date} {$booking->start_time}", $timezone)->utc()->format('Ymd\THis\Z');
    $endDateTime = Carbon::parse("{$booking->booking_date} {$booking->end_time}", $timezone)->utc()->format('Ymd\THis\Z');

    $googleCalendarUrl = 'https://calendar.google.com/calendar/u/0/r/eventedit?';
    $googleCalendarUrl .= 'text=' . urlencode($title);
    $googleCalendarUrl .= '&dates=' . $startDateTime . '/' . $endDateTime;
    $googleCalendarUrl .= '&details=' . urlencode($description);
    $googleCalendarUrl .= '&location=' . urlencode($locationdetail?->location_name.','.$locationdetail?->address);
    $googleCalendarUrl .= '&sf=true&output=xml';

    return redirect()->to($googleCalendarUrl);
}

public function createOutlookEvent($id)
{
    $decode_id = base64_decode($id);
    $booking = Booking::with(['location', 'categories', 'sub_category', 'child_category', 'createdBy', 'staff'])
                      ->find($decode_id);

    if (!$booking) {
        return response()->json(['error' => 'Booking not found'], 404);
    }

    $tenantName = tenant('name');
    $supportEmail = auth()->user()->email ?? '';
    $locationdetail = Location::where('id', $booking->location_id)->first();

    $siteDetails = SiteDetail::where('team_id', $booking->team_id)
                              ->where('location_id', $booking->location_id)
                              ->select('select_timezone')
                              ->first();

    $timezone = $siteDetails->select_timezone ?? config('app.timezone');
    $slug = Domain::where('team_id', $booking->team_id)->value('domain');
    $categoryName = Category::viewCategoryName($booking->category_id);
    $customerName = $booking->name ?? 'Customer';
    $staffName = $booking?->staff->name ?? 'N/A';
    $locationName = $locationdetail?->location_name ?? 'N/A';
    $eventLink = 'https://' . $slug . '/booking-confirmed/' . $id;

    $title = "{$categoryName} – {$customerName} | {$booking->refID}";

    // Use raw string and encode later
    $rawBody = <<<TEXT
Dear {$customerName},

This is a confirmation for your upcoming appointment.

📅 Appointment Details
• Service: {$categoryName}
• Date: {$booking->booking_date}
• Time: {$booking->start_time} – {$booking->end_time}
• Location: {$locationName}
• Staff: {$staffName}
• Booking Reference: {$booking->refID}

🔗 Meeting Link (if applicable):
{$eventLink}

Important Notes:
– Please arrive 5–10 minutes early.
– Bring necessary documents (if any).
– For rescheduling or cancellation, use your booking dashboard or contact support.

Thank you,
{$tenantName}
{$supportEmail}
TEXT;

    // URL encode body preserving line breaks
    $encodedBody = urlencode($rawBody);

    $startDateTimeUTC = Carbon::parse("{$booking->booking_date} {$booking->start_time}", $timezone)
                              ->utc()
                              ->format('Y-m-d\TH:i:s\Z');

    $endDateTimeUTC = Carbon::parse("{$booking->booking_date} {$booking->end_time}", $timezone)
                            ->utc()
                            ->format('Y-m-d\TH:i:s\Z');

    $outlookCalendarUrl = 'https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent';
    $outlookCalendarUrl .= '&startdt=' . urlencode($startDateTimeUTC);
    $outlookCalendarUrl .= '&enddt=' . urlencode($endDateTimeUTC);
    $outlookCalendarUrl .= '&location=' . urlencode($locationName . ', ' . $locationdetail?->address);
    $outlookCalendarUrl .= '&subject=' . urlencode($title);
    $outlookCalendarUrl .= '&body=' . $encodedBody;

    return redirect()->to($outlookCalendarUrl);
}


    
}
