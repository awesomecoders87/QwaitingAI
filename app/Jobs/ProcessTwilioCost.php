<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ProcessTwilioCost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messageSid;
    protected $userId;
    protected $credentials;
    protected $messageDetailId;

    /**
     * Create a new job instance.
     *
     * @param string $messageSid
     * @param int|string $userId
     * @param array $credentials
     * @return void
     */
    public function __construct($messageSid, $userId, $credentials, $messageDetailId = null)
    {
        $this->messageSid = $messageSid;
        $this->userId = $userId;
        $this->credentials = $credentials;
        $this->messageDetailId = $messageDetailId;

        Log::info(" In side constructor messageDetailId: " . $this->messageDetailId);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $accountSid = $this->credentials['account_sid'] ?? env('TWILIO_ACCOUNT_SID');
            $authToken = $this->credentials['auth_token'] ?? env('TWILIO_AUTH_TOKEN');

            // Fetch message details from Twilio
            $response = Http::timeout(30)
                ->withBasicAuth($accountSid, $authToken)
                ->get("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages/{$this->messageSid}.json");

            if (!$response->successful()) {
                Log::error("Job: Failed to fetch Twilio message {$this->messageSid}: " . $response->body());
                
                // If 404, the message might not exist yet (rare) or wrong SID. 
                // If Server Error (5xx), we should retry.
                if ($response->serverError()) {
                    $this->release(10);
                }
                return;
            }

            $messageData = $response->json();
            
            // Check if price is available
            // Twilio returns null if not ready
            if (!isset($messageData['price']) || $messageData['price'] === null) {
                $this->release(5); // Retry after 5 seconds
                return;
            }

            $totalCost = abs(floatval($messageData['price']));

            // Deduct from User Balance
            if ($this->userId && $totalCost > 0) {
                $user = User::select('id', 'sms_credits_balance')->where('id', $this->userId)->first();

                if ($user) {
                    $newBalance = $user->sms_credits_balance - $totalCost;

                    if ($newBalance >= 0) {
                        User::where('id', $this->userId)
                            ->update(['sms_credits_balance' => $newBalance]);
                    }

                    Log::info("Job: Deducted {$totalCost} from User {$this->userId}. New Balance: {$newBalance}");
                }
            }

            // Store cost in message_detail table using the ID

            // Log::info("Job: messageDetailId: " . $this->messageDetailId);
            if ($this->messageDetailId) {
                \App\Models\MessageDetail::where(['id' => $this->messageDetailId ,'status' => 'sent'])
                    ->update(['sms_sent_cost' => $totalCost]);
                
                Log::info("Job: Updated sms_sent_cost for MessageDetail ID: {$this->messageDetailId}, Cost: {$totalCost}");
            }
 

        } catch (\Exception $e) {
            Log::error("Job: Exception processing Twilio cost: " . $e->getMessage());
            // Retry on exception
            $this->release(10);
        }
    }
}
