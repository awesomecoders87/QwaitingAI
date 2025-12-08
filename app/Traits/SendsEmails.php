<?php

namespace App\Traits;

use App\Models\SmtpDetails;
use Illuminate\Support\Facades\Mail;

trait SendsEmails
{
    /**
     * Send an email.
     *
     * @param  string  $to
     * @param  string  $mailable
     * @param  array   $data
     * @return void
     */
    public function sendEmail($data,$title,$template,$teamId)
    {

        $smtpDetails = [];
        $details = SmtpDetails::viewDetails($teamId);
        
        if (!empty($details)) {
            $smtpDetails['from_email'] = $details->from_email;
            $smtpDetails['from_name'] = $details->from_name;
        }
        
        if(empty($data['to_mail']))
        $data['to_mail'] = $details->from_email;

        Mail::send('emails.' . $template, ['data' => $data], function ($m) use ($data, $title, $smtpDetails) {
            $m->from($smtpDetails['from_email'], $smtpDetails['from_name']);
            $m->to($data['to_mail'])->subject($title);
        });
    }

    public function sendEmailTo($data,$title,$template) {   
        Mail::send('emails.' . $template, ['data' => $data], function ($m) use ($data, $title) {
            $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_USERNAME'));
            $m->to($data['to_mail'])->subject($title);
        });
    }

}
