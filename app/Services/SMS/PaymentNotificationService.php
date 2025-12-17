<?php

namespace App\Services\SMS;

use App\Models\SmsAPI;
use App\Models\SmtpDetails;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\SMS\SmsService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;


class PaymentNotificationService
{
    public function sendPaymentFullSuccessNotification(User $user, array $paymentDetails)
    {
        $data = $this->prepareData($user, $paymentDetails);

        Log::info("Preparing to send payment success notifications", $data);
        // 1. User Notifications
        $this->sendUserEmail($data);
        $this->sendUserSms($data);

        // 2. Admin Notifications
        $this->sendAdminNotifications($data);
    }

    protected function prepareData(User $user, array $paymentDetails)
    {
        return [
            'to_mail' => $user->email,
            'phone' => $user->phone, 
            'phone_code' => $user->phone_code ?? '91',
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_id' => $user->id,
            'amount' => $paymentDetails['amount'],
            'payment_id' => $paymentDetails['payment_id'],
            'payment_date' => $paymentDetails['payment_date'],
            'wallet_balance' => $paymentDetails['wallet_balance'],
            'app_name' => config('app.name'),
            'team_id' => $user->team_id ?? tenant('id'),
            'locations_id' => $user->location_id ?? Session::get('selectedLocation'),
        ];
    }

    protected function sendUserEmail(array $data)
    {
        try {
            Log::info("Sending user email with data:", $data);
            
            // Prepare logData to prevent undefined key errors in MessageDetail::storeLog
            $logData = $data;
            $logData['location_id'] = $data['locations_id'] ?? null;
            $logData['type'] = 'payment_success';

            // SmtpDetails::sendMail uses $data to populate template
            // Type matches what we added in SmtpDetails
            SmtpDetails::sendMail($data, 'payment_success', '', $data['team_id'] ?? null, $logData);
        } catch (\Exception $e) {
            Log::error("Failed to send payment success email to user: " . $e->getMessage());
        }
    }

    protected function sendUserSms(array $data)
    {
        try {
            // SmsAPI::sendSms uses $data and type to find template
            SmsAPI::sendSms($data['team_id'], $data, 'payment_success', 'payment_success');
        } catch (\Exception $e) {
            Log::error("Failed to send payment success SMS to user: " . $e->getMessage());
        }
    }

    protected function sendAdminNotifications(array $data)
    {
        $superAdmins = SuperAdmin::get();

        foreach ($superAdmins as $admin) {
            $this->sendAdminEmail($admin, $data);
            $this->sendAdminSms($admin, $data);
        }
    }

    protected function sendAdminEmail($admin, $data)
    {
        if (empty($admin->email)) return;

        $subject = "Prepaid Wallet Payment Successful";
        
        $attempts = 4;
        $delay = 2; // Initial delay in seconds

        for ($i = 0; $i < $attempts; $i++) {
            try {
                if ($i > 0) {
                    sleep($delay * $i);
                    // Force close old connection to avoid rate limits on the same socket
                    Mail::purge('smtp');
                }

                // Configure Mailer using Team's SMTP details
                $this->configureMailer($data);

                Mail::send('emails.admin_payment_notification', ['data' => $data], function ($message) use ($admin, $subject) {
                    $message->to($admin->email)
                        ->subject($subject);
                });
                
                // If successful, break the retry loop
                break; 

            } catch (\Exception $e) {
                // If this was the last attempt, log error
                if ($i == $attempts - 1) {
                     Log::error("Failed to send payment admin email after {$attempts} attempts: " . $e->getMessage());
                } else {
                     Log::warning("Attempt " . ($i + 1) . " failed to send admin email. Retrying... Error: " . $e->getMessage());
                }
            }
        }
    }

    protected function configureMailer($data)
    {
        $teamId = $data['team_id'] ?? null;
        $locationId = $data['locations_id'] ?? null;

        if (!$teamId) return;

        $details = SmtpDetails::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();

        if ($details && !empty($details->hostname)) {
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', trim($details->hostname));
            Config::set('mail.mailers.smtp.port', trim($details->port));
            Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
            Config::set('mail.mailers.smtp.username', trim($details->username));
            Config::set('mail.mailers.smtp.password', trim($details->password));
            
            Config::set('mail.from.address', trim($details->from_email));
            Config::set('mail.from.name', trim($details->from_name));
        }
    }

    protected function sendAdminSms($admin, $data)
    {
        // Assuming 'phone' or 'mobile' field exists.
        // $phone = $admin->phone ?? $admin->mobile ?? null; 
        // $phone = $admin->phone ?? $admin->mobile ?? null; 
        $phone = '917870359434'; // Temporary for testing

        if (empty($phone)) return;

        // $content = "Wallet top-up alert: {$data['user_name']} credited ${$data['amount']}. Payment ID: {$data['payment_id']}.";
$content = "Wallet top-up alert: {$data['user_name']} credited \${$data['amount']}. Payment ID: {$data['payment_id']}.";
        try {
            $smsService = new SmsService();
            // Assuming no special country code logic needed for super admin 
            // or included in phone. Passing team_id might be relevant for logging.
            $smsService->sendSms($phone, $content, $data['team_id'], 'admin_alert');
        } catch (\Exception $e) {
             Log::error("Failed to send payment admin SMS: " . $e->getMessage());
        }
    }
}