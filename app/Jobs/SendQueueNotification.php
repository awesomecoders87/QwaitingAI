<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Location;
use App\Models\SmtpDetails;
use App\Models\SmsAPI;

class SendQueueNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $datanew;
    protected $type;
    protected $teamId;
  
    /**
     * Create a new job instance.
     *
     * @param array $data
     * @param string $type
     * @param int $teamId
     * @param int $location
     * @param string|null $phone
     */
    public function __construct($datanew, $type, $teamId)
    {
        $this->datanew = $datanew;
        $this->type = $type;
        $this->teamId = $teamId;
    
    }

    /**
     * Execute the job.
     */
    public function handle(): void

    { // Send email if email exists
       
        if (isset($this->datanew['to_mail']) && $this->datanew['to_mail'] != '') {
            SmtpDetails::sendMail($this->datanew, $this->type, 'ticket-created', $this->teamId);
        }

    }
}
