<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\SmsAPI;
use App\Models\Booking;
use App\Models\QueueStorage;
use App\Models\SiteDetail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\SmtpDetails;
use App\Models\Location;
use App\Models\Category;
// use Google\Client;
use Google\Service\Calendar;
use Spatie\GoogleCalendar\Event;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;


class TestController extends Controller
{
    public function index()
    {
        return view('text-to-speech'); // A view that contains the TTS HTML and JS
    }

    public function getTextForSpeech(Request $request)
    {
        $text = $request->input('text');
        $language = $request->input('language');

        // Here you could implement logic to process the text or store preferences
        return response()->json(['text' => $text, 'language' => $language]);
    }

    public function translate(Request $request)
    {
        $text = $request->input('text');
        $targetLanguage = substr($request->input('target_language'), 0, 2); // Extract language code (e.g., 'nl' from 'nl-NL')

        try {
            $tr = new GoogleTranslate();
            $tr->setTarget($targetLanguage);
            $translatedText = $tr->translate($text);

            return response()->json(['translatedText' => $translatedText]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Translation failed.'], 500);
        }
    }

    public function assignAllPermissionsToSuperadmin()
    {
    // Fetch or create the superadmin role
    $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
    // if ($superAdminRole) {
    //     // Remove all assigned permissions
    //     $superAdminRole->syncPermissions([]);

    //     return "All permissions have been removed from the superadmin role.";
    // }

    // Fetch all available permissions
    $allPermissions = Permission::all();

    // Remove all current permissions and assign new ones
    $superAdminRole->syncPermissions($allPermissions);

    return "All permissions have been updated for the superadmin role.";
    }

   public function sendReminderEmail($id)
   {
        $getQueue = QueueStorage::where('status', 'Pending')->get()->toArray();

        if(count($getQueue) > 0)
        {

            foreach($getQueue as $queue)
            {

            $getSiteDetails = SiteDetail::where('team_id', $queue['team_id'])->where('location_id',$queue['locations_id'])->first()->toArray();

            if($getSiteDetails['email_reminder_status'] == '1')
            {

            $currentDateTime = Carbon::now();

            $arriveDateTime = Carbon::parse($queue['arrives_time']);

            $getTime = $getSiteDetails['email_reminder_time'];

            $getType = $getSiteDetails['email_reminder_type'];

            $diff = $arriveDateTime->diff($currentDateTime);

            $currentQueue = json_decode($queue['json'], true);
            $normalizedJsonArray = array_change_key_case($currentQueue, CASE_LOWER);

            $email = isset($normalizedJsonArray['email']) ? $normalizedJsonArray['email'] : '';
            $name = isset($normalizedJsonArray['name']) ? $normalizedJsonArray['name'] : '';
            $getCategory = Category::where('id', $queue['category_id'])->select('name')->first();
            $getSubCategory = Category::where('id', $queue['sub_category_id'])->select('name')->first();
            $getChildCategory = Category::where('id', $queue['child_category_id'])->select('name')->first();
$location = Location::where('id',$queue['locations_id'])->select('location_name')->first();
            $data = [
                'to_mail' => $email,
                'name' => $name,
                'location' => $location['location_name'],
                'token' => $queue['start_acronym'].''.$queue['token'],
                'pending_count' => $queue['queue_count'],
                'waiting_time' => $queue['waiting_time'],
                'category_name' => isset($getCategory['name']) ? $getCategory['name'] : '',
                'secondC_name' => isset($getSubCategory['name']) ? $getSubCategory['name'] : '',
                'thirdC_name' => isset($getChildCategory['name']) ? $getChildCategory['name'] : ''
            ];

            if($getSiteDetails['email_reminder_type'] == 'minutes')
            {
                if($diff->days == 0)
                {
                    if($diff->i == $getSiteDetails['email_reminder_time'])
                    {
                        $this->sendNotification($data, 'reminder');
                    }
                }
            }

            if($getSiteDetails['email_reminder_type'] == 'hours')
            {
                if($diff->days == 0)
                {
                    if($diff->h == $getSiteDetails['email_reminder_time'])
                    {
                        $this->sendNotification($data, 'reminder');
                    }
                }
            }

            if($getSiteDetails['email_reminder_type'] == 'days')
            {
                if($diff->days != 0)
                {
                    if($diff->d == $getSiteDetails['email_reminder_time'])
                    {
                        $this->sendNotification($data, 'reminder');
                    }
                }
            }
            }
        }
        }
   }

   public function sendNotification( $data,$type ) {
    if ( isset( $data[ 'to_mail' ] ) && $data[ 'to_mail' ] != '' ){
        SmtpDetails::sendMail( $data, $type, '',  Auth::user()->team_id);
    }
    // $data[ 'location' ] = Location::find( $this->location )->value( 'location_name' );
    if ( !empty( $this->phone ) ) {
        // SmsAPI::sendSms( $this->teamId, $data );

        // SmsAPI::sendSmsWhatsApp( $this->teamId, $data );
    }
}




    public function createEvent($id)
    {
        // Decode the provided ID to retrieve the booking record
        $decode_id = base64_decode($id);
        $booking = Booking::with(['location', 'categories', 'sub_category', 'child_category'])
                          ->where(['id' => $decode_id])
                          ->first();

        // Check if booking exists
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        $slug= Team::getSlug();

        $categoryname = Category::viewCategoryName($booking->category_id);
        $eventLink = 'https://'.$slug.'.qminder.in/booking-confirmed/' . $id;
        // Prepare event data based on booking details
        $eventData = [
            'title' => $categoryname, // You can customize the title based on your needs
            'description' => "Booking Confirmation: \n\n" . $eventLink . "\nPhone: " . $booking->phone,
            'start_datetime' => $booking->booking_date . ' ' . $booking->start_time, // Format booking start time
            'end_datetime' => $booking->booking_date . ' ' . $booking->end_time, // Format booking end time
            'location' => $booking->location ? $booking->location->name : 'No Location', // Get location name
        ];

        // Convert booking start and end times to UTC format (Google Calendar format)
        $startDateTime = Carbon::parse($eventData['start_datetime'])->utc()->format('Ymd\THis\Z');
        $endDateTime = Carbon::parse($eventData['end_datetime'])->utc()->format('Ymd\THis\Z');

        // Build the Google Calendar URL
        $googleCalendarUrl = 'https://calendar.google.com/calendar/u/0/r/eventedit?';
        $googleCalendarUrl .= 'text=' . urlencode($eventData['title']);
        $googleCalendarUrl .= '&dates=' . $startDateTime . '/' . $endDateTime;
        $googleCalendarUrl .= '&details=' . urlencode($eventData['description']);
        // $googleCalendarUrl .= '&location=' . urlencode($eventData['location']);
        $googleCalendarUrl .= '&sf=true&output=xml';

        // Redirect the user to Google Calendar with pre-filled event data
        return redirect()->to($googleCalendarUrl);
    }

    public function createOutlookEvent($id)
    {
          // Decode the provided ID to retrieve the booking record
    $decode_id = base64_decode($id);
    $booking = Booking::with(['location', 'categories', 'sub_category', 'child_category'])
                      ->where(['id' => $decode_id])
                      ->first();

    if (!$booking) {
        return response()->json(['error' => 'Booking not found'], 404);
    }
    $slug= Team::getSlug();

    $categoryname = Category::viewCategoryName($booking->category_id);
    $eventLink = 'https://'.$slug.'.qminder.in/booking-confirmed/' . $id;
    // Define the correct timezone (adjust according to your region)
    $timezone = 'Asia/Kolkata'; // Example: India Standard Time (IST) UTC+5:30

    // Convert booking start and end times with the correct timezone
    // Convert booking start and end times to UTC (Outlook expects UTC format)
    $startDateTimeUTC = Carbon::parse($booking->booking_date . ' ' . $booking->start_time, $timezone)
                              ->utc()
                              ->format('Y-m-d\TH:i:s\Z'); // 'Z' ensures UTC format

    $endDateTimeUTC = Carbon::parse($booking->booking_date . ' ' . $booking->end_time, $timezone)
                            ->utc()
                            ->format('Y-m-d\TH:i:s\Z'); // 'Z' ensures UTC format

    // Build the Outlook Calendar URL
    $outlookCalendarUrl = 'https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent';
    $outlookCalendarUrl .= '&startdt=' . urlencode($startDateTimeUTC);
    $outlookCalendarUrl .= '&enddt=' . urlencode($endDateTimeUTC);
    $outlookCalendarUrl .= '&subject=' . urlencode($categoryname);
    $outlookCalendarUrl .= '&body=' . urlencode( "Booking Confirmation: \n\n" . $eventLink . "\nPhone: " . $booking->phone);

    // Redirect the user to Outlook Calendar with pre-filled event data
    return redirect()->to($outlookCalendarUrl);       



    }



public function testquery(){
    // $explainResult = DB::select("EXPLAIN SELECT * FROM queues_storage WHERE queue_id = '234'");
    $explainResult =DB::select("SHOW INDEX FROM queues_storage");
    dd($explainResult);
}

public function testSendSMS()
{
    try {
        $location =  Session::get('selectedLocation');
        $smsApi = SmsAPI::where([
            'team_id' => tenant('id'),
            'location_id' => $location,
            'status' => 1
        ])->first();

        $credentials = json_decode($smsApi->json, true);
        $credentials = collect($credentials)->pluck('parameter_value', 'parameter_key')->toArray();

        // $sid = env('TWILIO_SID');
        // $token = env('TWILIO_TOKEN');
        $from = $credentials['from'];
        $to = '+917696396740'; // Replace with a real number for testing

        $twilio = new Client($credentials['account_sid'], $credentials['auth_token']);

        $message = $twilio->messages->create($to, [
            'from' => $from,
            'body' => 'Aksh Test SMS from Twilio!'
        ]);

        return response()->json([
            'status' => 'success',
            'message_sid' => $message->sid
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

public function sendUnifonicSMS() {
    $apiUrl = "https://el.cloud.unifonic.com/rest/SMS/messages";
    $recipient = "+917696396740";
    $message = "Hello, this is a test message!";

    $response = Http::timeout(60)
        ->asForm()
        ->post($apiUrl, [
            'AppSid'    => 'umZBtqNWAhYoF9OfDRVG7ZIfkBDypS',   // Replace with your actual AppSid
            'recipient' => $recipient,
            'body'      => $message,
            'SenderID'  => 'ALJ',           // Optional: your sender name
            // 'encoding'  => '0',                    // Optional: 0 = plain text, 1 = Unicode
        ]);
    if ($response->successful()) {
        return [
            'status' => 'success',
            'response' => $response->json()
        ];
    }


    return [
        'status' => 'error',
        'response' => $response->body()
    ];
}
public function sendUnifonicgetSMS()
{
    $recipient = "917696396740"; // no '+'
    $message = "Hello, this is a test message!";
    $appSid = "umZBtqNWAhYoF9OfDRVG7ZIfkBDypS";
    $sender = "ALJ";

    $apiUrl = "https://el.cloud.unifonic.com/wrapper/sendSMS.php";
    $query = http_build_query([
        'appsid'    => $appSid,
        'sender'    => $sender,
        'to' => $recipient,
        'msg'      => $message,
        'encoding'  => 'utf-8'
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '?' . $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return [
            'status' => 'error',
            'response' => $error
        ];
    }

    return [
        'status' => 'success',
        'response' => $result
    ];
}

}
