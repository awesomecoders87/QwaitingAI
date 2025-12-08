<?php

// app/Mail/AppointmentConfirmation.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $servingTime;
    public $note;

    public function __construct($name, $servingTime, $note)
    {
        $this->name = $name;
        $this->servingTime = $servingTime;
        $this->note = $note;
    }

    public function build()
    {
        return $this->subject('Your Appointment Confirmation')
                    ->view('emails.appointment_confirmation');
    }
}
