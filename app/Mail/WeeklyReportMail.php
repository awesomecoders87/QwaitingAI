<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyReportMail extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct(public array $data) {}

    public function build()
    {
        return $this->view('emails.weekly-report')
            ->subject('Weekly Report - '.$this->data['locationName'])
            ->with($this->data);
    }
}

