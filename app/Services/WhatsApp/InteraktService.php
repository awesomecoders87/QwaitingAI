<?php 

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InteraktService
{
    protected $interaktToken;
    protected $rasaUrl;

    public function __construct()
    {
        // $this->interaktToken = config('services.interakt.token');
        // $this->rasaUrl = config('services.rasa.url');
    }

    public function sendTemplateMessageToUser($url,$token,$to, array $bodyValues, string $templateName = 'qwaitingticket', string $languageCode = 'en', string $callbackData = 'auto_template', string $userId = '')
    {
        Log::debug('Sending WhatsApp Template Message', [
        'url' => $url,
        'token' => $token,
        'to' => $to,
        'bodyValues' => $bodyValues,
        'templateName' => $templateName,
        'languageCode' => $languageCode,
        'callbackData' => $callbackData,
        'userId' => $userId,
    ]);
        return Http::withHeaders([
        'Authorization' => $token
    ])->post($url, [
        'userId' => $userId,
        'fullPhoneNumber' => $to,
        'callbackData' => $callbackData,
        'type' => 'Template',
        'template' => [
            'name' => $templateName,
            'languageCode' => $languageCode,
            'bodyValues' => $bodyValues
        ]
    ]);
    }
}
