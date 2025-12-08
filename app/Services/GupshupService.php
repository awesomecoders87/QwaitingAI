<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GupshupService
{
    // protected $apiKey;
    // protected $baseUrl;

    // public function __construct()
    // {
    //     $this->apiKey = config('services.gupshup.api_key');
    //     $this->baseUrl = config('services.gupshup.base_url');
    // }

    // public function sendWhatsAppMessage($phone, $message)
    // {
    //     $response = Http::withHeaders([
    //         'apikey' => $this->apiKey,
    //         'Content-Type' => 'application/x-www-form-urlencoded',
    //     ])->asForm()->post("{$this->baseUrl}/msg", [
    //         'channel'     => 'whatsapp',
    //         // 'source'      => '+917834811114',
    //         'source'      => '15558082329',
    //         'destination' => $phone,
    //         'message'     => json_encode([
    //             'type' => 'text',
    //             'text' => $message
    //         ]),
    //         'src.name'    => 'Qwaiting app',
    //     ]);

    //     return $response->json();
    // }

    public function sendTemplateMessage($phoneNumber, $templateName, array $params,$apiUrl)
{
    $apiUrl = 'https://api.gupshup.io/wa/api/v1/msg';
    $apiKey = 'sqdbudao2lbpbxjaadfipj6o7tm6dbmd'; // Sandbox API key
    $source = '15558082329'; // Sandbox source number
    $srcName = 'ORACustomerReminders'; // Sandbox app name

    // Create the message payload for template-like message
    // For sandbox, they use type=text; template parameters need to be manually constructed
    $messageText = vsprintf($templateName, $params); 
    // Example: $templateName = "Hello %s, your order %s is confirmed."
    //          $params = ["John", "#123"]

    $response = Http::withHeaders([
        'apikey' => $apiKey,
        'Cache-Control' => 'no-cache',
        'Content-Type' => 'application/x-www-form-urlencoded',
    ])->asForm()->post($apiUrl, [
        'channel'     => 'whatsapp',
        'source'      => $source,
        'destination' => $phoneNumber, // Must include country code, e.g. 919876543210
        'message'     => json_encode([
            'type' => 'text',
            'text' => $messageText
        ]),
        'src.name'    => $srcName
    ]);

    return $response->json();
}
}