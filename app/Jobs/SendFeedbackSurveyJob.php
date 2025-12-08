<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\QueueStorage;
use App\Models\MessageDetail;
use App\Models\SmtpDetails;
use App\Models\SmsAPI;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendFeedbackSurveyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $call;

    public function __construct(QueueStorage $call)
    {
        $this->call = $call;
    }

    public function handle()
    {
        $userDetails = json_decode($this->call->json, true);

        $data = [
            'to_mail'      => $userDetails['email'] ?? $userDetails['Email'] ?? null,
            'name'         => $this->call->name,
            'phone'        => $this->call->phone,
            'phone_code'   => $this->call->phone_code ?? '91',
            'locations_id' => $this->call->locations_id,
            'location_id' => $this->call->locations_id,
            'feedback_link'=> request()->getHost() . '/rating/survey?code=' . base64_encode($this->call->id),
        ];

        $logData = [
            'team_id'         => $this->call->team_id,
            'location_id'     => $this->call->locations_id,
            'user_id'         => $this->call->served_by,
            'customer_id'     => $this->call->created_by,
            'queue_id'        => $this->call->queue_id,
            'queue_storage_id'=> $this->call->id,
            'email'           => $data['to_mail'],
            'contact'         => $this->call->phone,
            'type'            => MessageDetail::AUTOMATIC_TYPE,
            'event_name'      => 'Auto Trigger Feedback link',
        ];

        if (!empty($data['to_mail'])) {
            $logData['channel'] = 'email';
            SmtpDetails::sendMail($data, 'rating survey', '', $logData['team_id'], $logData);
        }

        if (!empty($data['phone'])) {
              Log::info("smsapi run".$data['phone']);
            $logData['channel'] = 'sms';
            $logData['status']  = MessageDetail::SENT_STATUS;
            $data['location_id'] = $data['locations_id'];
             SmsAPI::sendSms($logData['team_id'], $data, 'rating survey', 'rating survey', $logData);
        }

        // mark as sent to prevent duplicates
        $this->call->update(['feedback_sent_at' => Carbon::now()]);
    }
}
