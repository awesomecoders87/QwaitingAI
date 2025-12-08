<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $companyName;
    public $domainName;
    public $username;
    public $email;
    public $password;

    public function __construct($companyName, $domainName, $username, $email, $password)
    {
        $this->companyName = $companyName;
        $this->domainName = $domainName;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Your Company Setup is Complete')
                    ->view('emails.tenant_created');
    }
}
