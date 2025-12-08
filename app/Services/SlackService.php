<?php

namespace App\Services;

use App\Models\SlackSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SlackService
{
    protected $userAuthToken;
    protected $userBotAuthToken;
    protected $enabled = false;

    public function __construct($teamId = null, $locationId = null)
    {
        $teamId = $teamId ?? tenant('id');
        $locationId = $locationId ?? Session::get('selectedLocation');

        $slackSetting = SlackSetting::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('status', 1) // only active
            ->first();

        if ($slackSetting && !empty($slackSetting->slack_user_auth_token) && !empty($slackSetting->slack_user_bot_auth_token)) {
            $this->userAuthToken = $slackSetting->slack_user_auth_token;
            $this->userBotAuthToken = $slackSetting->slack_user_bot_auth_token;
            $this->enabled = true;
        }
    }

    /**
     * Send Slack DM to user by email (safe).
     */
    public function sendSms($email, $message)
    {
        if (!$this->enabled) {
            // return safe response without erroring out
            return ['status' => 'skipped', 'reason' => 'Slack not configured or inactive'];
        }

        try {
            $email = str_replace("grab.co", "grabtaxi.com", $email);

            $memberId = $this->getSlackUserId($email);

            if ($memberId) {
                return $this->sendMessageOnSlack($memberId, $message);
            }

            return ['status' => 'failed', 'reason' => 'Unable to get Member ID from Slack'];
        } catch (\Throwable $ex) {
            // Never break main flow, just return failure
            return ['status' => 'failed', 'reason' => $ex->getMessage()];
        }
    }

    /**
     * Get Slack user ID by email.
     */
    protected function getSlackUserId($email)
    {
        $response = Http::withToken($this->userBotAuthToken)
            ->get("https://slack.com/api/users.lookupByEmail", [
                'email' => $email,
            ]);

        $data = $response->json();

        return $data['user']['id'] ?? null;
    }

    /**
     * Send message on Slack to user.
     */
    protected function sendMessageOnSlack($memberId, $message)
    {
        $response = Http::withToken($this->userBotAuthToken)
            ->post("https://slack.com/api/chat.postMessage", [
                'channel' => $memberId,
                'text'    => $message,
                'as_user' => true,
            ]);

        return $response->json();
    }

    /**
     * Send Slack invite by email.
     */
    public function sendInviteToSlackUser($email)
    {
        if (!$this->enabled) {
            return ['status' => 'skipped', 'reason' => 'Slack not configured or inactive'];
        }

        try {
            $response = Http::withToken($this->userAuthToken)
                ->post("https://slack.com/api/users.admin.invite", [
                    'email' => $email,
                ]);

            return $response->json();
        } catch (\Throwable $ex) {
            return ['status' => 'failed', 'reason' => $ex->getMessage()];
        }
    }
}
