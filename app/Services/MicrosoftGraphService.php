<?php 
namespace App\Services;

use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\OnlineMeeting;

class MicrosoftGraphService
{
    protected $graph;

    public function __construct()
    {
        $this->graph = new Graph();
        $this->graph->setAccessToken($this->getAccessToken());
    }

    protected function getAccessToken()
    {
        $client = new Client();

        $url = 'https://login.microsoftonline.com/' . config('services.microsoft.tenant_id') . '/oauth2/v2.0/token';

        $response = $client->post($url, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.microsoft.client_id'),
                'client_secret' => config('services.microsoft.client_secret'),
                'scope' => 'https://graph.microsoft.com/.default',
            ]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        return $body['access_token'];
    }

    public function createTeamsMeeting($organizerEmail, $subject = 'Live Meeting', $start = null, $end = null)
    {
        $start = $start ?? now()->addMinutes(10)->toIso8601String();
        $end   = $end ?? now()->addMinutes(40)->toIso8601String();

        $meetingData = [
            'startDateTime' => $start,
            'endDateTime'   => $end,
            'subject'       => $subject,
        ];
return $this->graph->createRequest('GET', '/users')
    ->setReturnType(\Microsoft\Graph\Model\User::class)
    ->execute();
        return $this->graph
            ->createRequest('POST', "/users/{$organizerEmail}/onlineMeetings")
            ->attachBody($meetingData)
            ->setReturnType(OnlineMeeting::class)
            ->execute();
    }
}
