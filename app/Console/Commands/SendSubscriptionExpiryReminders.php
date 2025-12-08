<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Domain;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
 use Illuminate\Support\Facades\Config;
 use Illuminate\Support\Facades\Log;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscription:send-expiry-reminders';

    protected $description = 'Send reminder emails 7 days before subscription expiry';

    public function handle()
    {

        $timezone = config('app.timezone');
        $now = Carbon::now($timezone);
        $targetDate = $now->addDays(7)->startOfDay();

       $domains = Domain::whereDate('expired', $targetDate)
            ->select('id', 'domain', 'plan_name', 'stripe_email', 'team_id', 'expired')->get();
   if(!empty($domains)){
        foreach ($domains as $domain) {
            if (!$domain->stripe_email) continue;
           $url = 'https://' . $domain->domain . '/buy-subscription';
            $data = [
                'plan' => $domain->plan_name ?? 'Your Subscription',
                'expiryDate' => Carbon::parse($domain->expired)->format('Y-m-d'),
                'to_mail' => "aksh@stelleninfotech.in",
                'team_id'=>$domain->team_id,
                'renew_url' =>  $url,
            ];

            // Send reminder email
            try {
                Mail::send('emails.subscription_reminder', ['data' => $data], function ($message) use ($data) {
                    $message->to($data['to_mail'])
                            ->subject('Your Subscription Will Expire Soon');
                });

                $this->info("Reminder sent to: {$data['to_mail']}");
            } catch (\Exception $e) {
                Log::error('Failed to send expiry reminder: ' . $e->getMessage());
            }
        }
    }
    }
}
