<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphServiceNew
{
    public function createMeetingLink(array $staff, array $postData)
    {
        $responseData = [];

        try {
            $tokenResponse = $this->getAccessToken($staff);

            if ($tokenResponse['success']) {
                $accessToken = $tokenResponse['access_token'];
                $client = new Client();

                $startTime = date('Y-m-d', strtotime($postData['booking_date'])) . 'T' . date('H:i:s.u', strtotime($postData['booking_start_time'])) . '-07:00';
                $endTime = date('Y-m-d', strtotime($postData['booking_date'])) . 'T' . date('H:i:s.u', strtotime($postData['booking_end_time'])) . '-07:00';

                $response = $client->request('POST', 'https://graph.microsoft.com/v1.0/me/onlineMeetings', [
                    'headers' => [
                        'Authorization' => "Bearer {$accessToken}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'startDateTime' => $startTime,
                        'endDateTime' => $endTime,
                        'subject' => 'User meeting',
                    ],
                ]);

                $data = json_decode($response->getBody());

                $responseData = [
                    'status' => 'success',
                    'meeting_id' => $data->joinMeetingIdSettings->joinMeetingId ?? null,
                    'meeting_password' => $data->joinMeetingIdSettings->passcode ?? null,
                    'meeting_join_url' => $data->joinWebUrl ?? null,
                    'team_meeting_id' => $data->id ?? null,
                    'meeting_start_time' => $startTime,
                    'meeting_end_time' => $endTime,
                ];
            } else {
                $responseData = [
                    'status' => 'failure',
                    'message' => $tokenResponse['message'],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Microsoft Graph Meeting Error: ' . $e->getMessage());
            $responseData = [
                'status' => 'failure',
                'message' => $e->getMessage(),
            ];
        }

        return $responseData;
    }

    private function getAccessToken(array $staff)
    {
        try {
            $client = new Client();

            $response = $client->post("https://login.microsoftonline.com/{$staff['tenantId']}/oauth2/v2.0/token", [
                'form_params' => [
                    'client_id' => $staff['clientId'],
                    'client_secret' => $staff['clientSecret'],
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'password',
                    'username' => $staff['username'],
                    'password' => $staff['password'],
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'access_token' => $body['access_token'],
            ];
        } catch (\Exception $e) {
            Log::error('Microsoft Graph Token Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
