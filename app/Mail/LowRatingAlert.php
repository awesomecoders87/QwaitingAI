<?php 
// app/Mail/LowRatingAlert.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowRatingAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $average, $comment, $teamId, $locationId, $submittedAt;
    public $response, $token, $customer, $threshold,$domain,$teamName;

    public function __construct(
        $average,
        $comment,
        $teamId,
        $locationId,
        $submittedAt,
        $response,
        $token,
        $customer,
        $threshold,
        $domain,
        $teamName
    ) {
        $this->average = $average;
        $this->comment = $comment;
        $this->teamId = $teamId;
        $this->locationId = $locationId;
        $this->submittedAt = $submittedAt;
        $this->response = $response;
        $this->token = $token;
        $this->customer = $customer;
        $this->threshold = $threshold;
        $this->domain = $domain;
        $this->teamName = $teamName;
         
    }
    public function build()
    {
        return $this->subject('Alert: Low Feedback Rating Received')
            ->view('emails.low-rating-alert');
    }
}
