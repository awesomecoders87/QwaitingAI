<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\SmtpDetails;
use App\Models\SmsAPI;

class SendEmailJob implements ShouldQueue
{
     use Queueable;
     protected array $data;
     protected string $template;
    
    public function __construct(array $data, $template)
    {
        $this->data = $data;
        $this->template = $template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       $this->sendNotification($this->data, $this->template);
    }

    public function sendNotification($data, $type)
    {
        if (isset($data['to_mail']) && $data['to_mail'] != '') {
            SmtpDetails::sendMail($data, $type, 'ticket-created', $data['team_id']);
        }
       
        if (!empty($data['phone'])) {
            SmsAPI::sendSms($data['team_id'], $data, $type);
        }
    }

}
