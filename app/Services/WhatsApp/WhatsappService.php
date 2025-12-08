<?php

namespace App\Services\WhatsApp;

use App\Models\SmsAPI;
use App\Models\SmsReport; // Import the model for logging
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class WhatsappService
{

    protected $apiType;
    protected $apiUrl;
    protected $url_method;
    protected $credentials;
    protected $teamId;
    protected $location;
    protected $isWhatsapp;


    public function __construct()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');

        $smsApi = SmsAPI::where([
            'location_id' => $this->location,
            'is_whatsapp' => 1,
            'status' => 1
        ])->first();

        if (!$smsApi) {
            Log::error("Whastsapp API configuration not found. and team Id -'{$this->teamId}'");
            return false;
        }

        $this->apiType = $smsApi->type;
        $this->isWhatsapp = $smsApi->is_whatsapp;
        $this->apiUrl = $smsApi->sms_api_url ?? '';
        $this->url_method = $smsApi->url_method ?? 'post';
        $this->credentials = json_decode($smsApi->json, true);
       
        if (!$this->credentials && $this->apiType != 'custom') {
            Log::error("whatsapp Invalid configuration for '{$this->apiType}' API. team Id -'{$this->teamId}'");
            return false;
        }
    }

    public function sendWhatsappSms(string $to, string $message,$team): bool
    {
       
        $this->teamId = $team ??  tenant('id');
        try {
            switch ($this->apiType) {
                case 'twillio':
                    return $this->sendWhatsappViaTwilio($to, $message);
                case 'oneway':
                    return $this->sendViaOneWay($to, $message);
                case 'msg91':
                    return $this->sendViaMsg91($to, $message);
                case 'custom':
                    return $this->sendCustom($to, $message);
                default:
                    Log::error("Unsupported whatsapp API type and team Id -'{$this->teamId} ': '{$this->apiType}'");
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("SMS Error ({$this->apiType}): " . $e->getMessage());
            
            // ❌ Store failed SMS attempt in sms_report table
            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                 'user_id' => Auth::id() ?? '',
                'message' => $message,
                'contact' => $to,
                'status' => 'failed', // Mark as failed
                 'channel' => 'whatsapp',
                  'failed_reason'=>$e->getMessage(),
            ]);

            return false;
        }
    }

    protected function sendWhatsappViaTwilio(string $to, string $message): bool
    {
        $credentials = collect($this->credentials)->pluck('parameter_value', 'parameter_key')->toArray();

        if (!isset($credentials['account_sid'], $credentials['auth_token'], $credentials['whatsapp_number'])) {
              SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                 'user_id' => Auth::id() ?? '',
                'message' => $message,
                'contact' => $to,
                'status' => 'failed', // Mark as failed
                 'channel' => 'whatsapp',
                  'failed_reason'=>"Missing required WhatsApp credentials.",
            ]);
            return false;
        }

        try {
            $client = new Client($credentials['account_sid'], $credentials['auth_token']);

            $client->messages->create('whatsapp:+' . $to, [
                'from' => 'whatsapp:' . $credentials['whatsapp_number'],
                'body' => $message,
            ]);

            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                 'user_id' => Auth::id() ?? '',
                'message' => $message,
                'contact' => $to,
                'status' => 'sent',
                'channel' => 'whatsapp',
            ]);

            return true;
        } catch (RestException $e) {
            Log::error("Twilio WhatsApp Error: " . $e->getMessage());

            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                 'user_id' => Auth::id() ?? '',
                'message' => $message,
                'contact' => $to,
                'status' => 'failed',
                'channel' => 'whatsapp',
                 'failed_reason'=>$e->getMessage(),
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
                      'channel' => 'whatsapp',
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
                  'channel' => 'whatsapp',
                   'failed_reason'=>$e->getMessage(),
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
      
        if($this->isWhatsapp == 1){
            // WhatsApp Sending
            if (!$credentials['templateId']) {
                Log::error("Template ID required for WhatsApp messages.");
                return false;
            }

            $payload = [
                'authkey' => $credentials['authkey'],
                'template_id' => $credentials['templateId'],
                'mobiles' => $to,
                'short_url' => '1',
                'variables' => [$message], // Assuming message fills template variables
            ];

            $response = \Http::post('https://api.msg91.com/api/v5/whatsapp/send', $payload);
        } else {
             Log::error("Invalid channel specified.");
             return false;
        }

        if ($response->successful()) {
            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                 'user_id' => Auth::id() ?? '',
                'message' => $message,
                'contact' => $to,
                'status' => 'sent',
                'channel' => 'whatsapp',

            ]);

            return true;
        }

        Log::error("Msg91 API Error: " . $response->body());

    } catch (\Exception $e) {
        Log::error("Msg91 Send Error: " . $e->getMessage());

        SmsReport::create([
            'team_id' => $this->teamId,
            'location_id' => $this->location,
             'user_id' => Auth::id() ?? '',
            'message' => $message,
            'contact' => $to,
            'status' => 'failed',
            'channel' => 'whatsapp',
             'failed_reason'=>$e->getMessage(),
        ]);

        return false;
    }
}

    protected function sendCustom(string $to, string $message): bool
{
    $credentials = collect($this->credentials)->pluck('parameter_value', 'parameter_key')->toArray();

    if (count($credentials) < 1) {
        Log::error("SMS/WhatsApp Error: Missing API URL or API Key.");
    }

    try {
        $payload = [];

        // Basic required fields
        $payload['mobile'] = $to;
        $payload['message'] = $message;

        // Merge user defined credentials into payload
        foreach ($credentials as $key => $value) {
                $payload[$key] = $value;
        }

        $apiUrl = $this->apiUrl;
        $apiMethod =$this->url_method ?? 'post'; // Default POST if method not defined

        $response = null;

        if ($this->isWhatsapp == 1) {
            if (strtoupper($apiMethod) === 'post') {
                $response = \Http::post($apiUrl, $payload);
            } else {
                $response = \Http::get($apiUrl, $payload);
            }
        }

        if ($response && $response->successful()) {
            SmsReport::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'user_id' => Auth::id() ?? '',
                'message' => $message,
                'contact' => $to,
                'status' => 'sent',
                'channel' => 'whatsapp',
            ]);

            return true;
        }

        Log::error("API Error: " . ($response ? $response->body() : 'No response'));

    } catch (\Exception $e) {
        Log::error("SMS/WhatsApp Send Error: " . $e->getMessage());

        SmsReport::create([
            'team_id' => $this->teamId,
            'location_id' => $this->location,
             'user_id' => Auth::id() ?? '',
            'message' => $message,
            'contact' => $to,
            'status' => 'failed',
            'channel' => 'whatsapp',
             'failed_reason'=>$e->getMessage(),
        ]);

        return false;
    }
}

}
