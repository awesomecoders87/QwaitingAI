<?php

namespace App\Services\SMS;

use App\Models\SmsAPI;
use App\Models\SmsReport; // Import the model for logging
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SmsService
{

    protected $apiType;
    protected $apiUrl;
    protected $url_method;
    protected $credentials;
    protected $teamId;
    protected $gettype;
    protected $location;
    protected $isSms;
    protected $authentication;
    protected $token;
    protected $template_id;
    protected $number;
    protected $message;


    public function __construct()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');

        if(empty($this->location)){
             $this->location =Session::get('selectedLocationCron');
        }
        if(empty($this->teamId)){
             $this->teamId =Session::get('TeamIDcron');
        }

        $smsApi = SmsAPI::where([
            'location_id' => $this->location,
            'is_sms' => 1,
            'status' => 1
        ])->first();

        if (!$smsApi) {
            Log::error("sms API configuration not found. and team Id -'{$this->teamId}'");
            return false;
        }

        $this->apiType = $smsApi->type;
        $this->isSms = $smsApi->is_sms;
        $this->apiUrl = $smsApi->sms_api_url ?? '';
        $this->url_method = $smsApi->url_method ?? 'post';
        $this->credentials = json_decode($smsApi->json, true);
        $this->authentication = $smsApi->authentication;
        $this->token = $smsApi->token;
        $this->number = $smsApi->contact ?? '';
        $this->message = $smsApi->message ?? '';

        if (!$this->credentials) {
            Log::error("sms Invalid configuration for '{$this->apiType}' API. team Id -'{$this->teamId}'");
            return false;
        }
    }

    public function sendSms(string $to, string $message, $team = null, $type = null,$logs=[],$templateId=null)
    {

        $userid = '';
        if (Auth::check()) {
            $userid = Auth::id() ?? '';
        }
        if(!empty($templateId)){
            $this->template_id = $templateId;
        }
        $this->teamId = $team ??  tenant('id');
        $this->gettype = $type ??  'other';

        try {
            switch ($this->apiType) {
                case 'twillio':
                    return $this->sendSmsViaTwilio($to, $message);
                case 'oneway':
                    return $this->sendViaOneWay($to, $message);
                case 'msg91':
                    return $this->sendViaMsg91($to, $message);
                case 'custom':
                    return $this->sendCustom($to, $message);
                default:
                    Log::error("Unsupported SMS API type and team Id -'{$this->teamId} ': '{$this->apiType}'");
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("SMS Error ({$this->apiType}): " . $e->getMessage());

            // ❌ Store failed SMS attempt in sms_report table
            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => $userid,
                'message' => $message,
                'contact' => $to,
                'status' => 'failed', // Mark as failed
                'channel' => 'sms',
                'type' => $this->gettype,
                'failed_reason' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function sendSmsViaTwilio(string $to, string $message): bool
    {

        $userid = '';
        if (Auth::check()) {
            $userid = Auth::id() ?? '';
        }

        $credentials = collect($this->credentials)->pluck('parameter_value', 'parameter_key')->toArray();

        if (!isset($credentials['account_sid'], $credentials['auth_token'], $credentials['from'])) {
            Log::error("Twilio SMS Error: Missing required Twilio credentials.");
            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' =>  $userid,
                'message' => $message,
                'contact' => $to,
                'status' => 'failed',
                'channel' => 'sms',
                'type' => $this->gettype,
                'failed_reason' => "Missing required Twilio credentials.",
            ]);
           return false;
        }

        try {
            $client = new Client($credentials['account_sid'], $credentials['auth_token']);
            $client->messages->create('+' . $to, [
                'from' => $credentials['from'],
                'body' => $message,
            ]);

            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => $userid,
                'message' => $message,
                'contact' => $to,
                'status' => 'sent',
                'channel' => 'sms',
                'type' => $this->gettype,
            ]);

            return true;
        } catch (RestException $e) {
            Log::error("Twilio SMS Error: " . $e->getMessage());

            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => $userid,
                'message' => $message,
                'contact' => $to,
                'status' => 'failed',
                'channel' => 'sms',
                'type' => $this->gettype,
                'failed_reason' => $e->getMessage(),
            ]);

            return false;
        }
    }


    protected function sendViaOneWay(string $to, string $message): bool
    {
        $credentials = collect($this->credentials)->pluck('parameter_value', 'parameter_key')->toArray();
        if (!isset($this->apiUrl, $credentials['api_key'])) {
            Log::error("OneWay SMS Error: Missing required OneWay credentials.");
           return false;
        }

        try {
            $response = \Http::get($this->apiUrl, [
                'apiKey' => $credentials['api_key'],
                'number' => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                SmsReport::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->location,
                    'user_id' => Auth::id() ?? '',
                    'message' => $message,
                    'contact' => $to,
                    'status' => 'sent',
                    'type' => $this->gettype,
                    'channel' => 'sms',
                ]);

                return true;
            }

           Log::error("OneWay API Error: " . $response->body());
        } catch (\Exception $e) {
            Log::error("OneWay SMS Error: " . $e->getMessage());

            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => Auth::id() ?? '',
                'message' => $message,
                'contact' => $to,
                'status' => 'failed',
                'channel' => 'sms',
                'type' => $this->gettype,
                'failed_reason' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // protected function sendViaMsg91(string $to, string $message): bool
    // {
    //     $credentials = collect($this->credentials)->pluck('parameter_value', 'parameter_key')->toArray();

    //     if (!isset($credentials['authkey'], $credentials['senderid'], $credentials['route'])) {
    //         Log::error("Msg91 SMS Error: Missing required Msg91 credentials.");
    //         throw new \Exception("Missing required Msg91 credentials.");
    //     }

    //     try {
    //         $response = \Http::post('https://api.msg91.com/api/v2/sendsms', [
    //             'authkey' => $credentials['authkey'],
    //             'sender' => $credentials['senderid'],
    //             'route' => $credentials['route'],
    //             'message' => $message,
    //             'mobiles' => $to,
    //         ]);

    //         if ($response->successful()) {
    //             SmsReport::create([
    //                 'team_id' => $this->teamId,
    //                 'location_id' => $this->location,
    //                 'message' => $message,
    //                 'contact' => $to,
    //                 'status' => 'sent',
    //             ]);

    //             return true;
    //         }

    //         throw new \Exception("Msg91 API Error: " . $response->body());
    //     } catch (\Exception $e) {
    //         Log::error("Msg91 SMS Error: " . $e->getMessage());

    //         SmsReport::create([
    //             'team_id' => $this->teamId,
    //             'location_id' => $this->location,
    //             'message' => $message,
    //             'contact' => $to,
    //             'status' => 'failed',
    //         ]);

    //         return false;
    //     }
    // }


    protected function sendViaMsg91(string $to, string $message): bool
    {
        $credentials = collect($this->credentials)->pluck('parameter_value', 'parameter_key')->toArray();

        if (!isset($credentials['authkey'])) {
            Log::error("Msg91 Error: Missing required credentials.");
            return false;
        }

        try {
            if ($this->isSms == 1) {
                // SMS Sending
                $payload = [
                    'authkey' => $credentials['authkey'],
                    'mobiles' => $to,
                    'message' => $message,
                    'sender' => $credentials['senderid'] ?? '',
                    'route' => $credentials['route'] ?? '4',
                ];

                $response = \Http::post('https://api.msg91.com/api/v2/sendsms', $payload);
            }



            if ($response->successful()) {
                SmsReport::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->location,
                    'user_id' => Auth::id() ?? '',
                    'message' => $message,
                    'contact' => $to,
                    'status' => 'sent',
                    'channel' => 'sms',
                    'type' => $this->gettype,
                    'failed_reason' => $e->getMessage(),
                ]);

                return true;
            }

            throw new \Exception("Msg91 API Error: " . $response->body());
        } catch (\Exception $e) {
            Log::error("Msg91 Send Error: " . $e->getMessage());

            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => Auth::id() ?? '',
                'message' => $message,
                'contact' => $to,
                'status' => 'failed',
                'channel' => 'sms',
                'type' => $this->gettype,
                'failed_reason' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function sendCustom(string $to, string $message)
    {
        $credentials = collect($this->credentials)->pluck('parameter_value', 'parameter_key')->toArray();

        if (count($credentials) < 1) {
            Log::error("SMS Error: Missing API URL or API Key.");
            return false;
        }

        try {
            $payload = [];
            $numberlabel = $this->number ?? 'number';
            $messagelabel = $this->message ?? 'message';
            // Basic required fields
            $payload[$numberlabel] = $to;
            $payload[$messagelabel] = $message;
            if(!empty($this->template_id)){
                $payload['templateid'] = $this->template_id;
            }

            // Merge user defined credentials into payload
            foreach ($credentials as $key => $value) {
                $payload[$key] = $value;
            }

            $apiUrl = $this->apiUrl;
            $apiMethod = $this->url_method ?? 'post'; // Default POST if method not defined

            $response = null;

            if ($this->authentication === "no_auth") {
                $isHeader = 0;
            } else {
                $isHeader = 1;
            }

            if(!empty($this->token)){
               $headers = [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ];
            }else{
                $headers = [
                'Accept' => 'application/json',
            ];
            }


            if ($this->isSms == 1) {
                if (strtoupper($apiMethod) == 'post') {
                    $response = $isHeader ? \Http::withHeaders($headers)->post($apiUrl, $payload) : \Http::post($apiUrl, $payload);
                } else {
                    $response = $isHeader ? \Http::withHeaders($headers)->get($apiUrl, $payload) : \Http::get($apiUrl, $payload);
                }
            }

            if ($response && $response->successful()) {


                return $response;
            }

           Log::error("API Error: " . ($response ? $response->body() : 'No response'));
        } catch (\Exception $e) {
            Log::error("SMS Send Error: " . $e->getMessage());
            return $e->getMessage();
        }
    }
}
