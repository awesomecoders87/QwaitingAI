<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\SmtpDetails;
use App\Models\SuspensionLog;
use App\Models\AccountSetting;
use Carbon\Carbon;
use App\Mail\SuspensionNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class AutoCancelBookings extends Command
{
    protected $signature = 'bookings:auto-cancel';
    protected $description = 'Auto-cancel bookings 5 mins before if not checked-in';

    public function handle()
    {
        $timezone = config('app.timezone');
        $now = Carbon::now($timezone);

        Log::info("Cron started: Auto-cancel bookings");

        // Get account setting
        $accountSetting = AccountSetting::where('team_id', 10)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->select('booking_auto_cancel')
            ->first();

        $autoCancelMinutes = $accountSetting?->booking_auto_cancel > 0
            ? (int)$accountSetting->booking_auto_cancel
            : null;

        if (!$autoCancelMinutes) {
            Log::info("Auto-cancel is disabled for team 10");
            return;
        }

        $cutoffTime = $now->copy()->subMinutes($autoCancelMinutes);
        Log::info("Auto-cancel cutoff time: {$cutoffTime->format('H:i:s')}");

        // Fetch bookings eligible for auto-cancel
        $bookings = Booking::where('team_id', 10)
            ->where('booking_date', $now->toDateString())
            ->where('status', '!=', 'Cancelled')
            ->whereNull('convert_datetime')
            ->where('is_convert', 'No')
            ->whereTime('start_time', '<=', $cutoffTime->format('H:i:s'))
            ->select('id', 'team_id', 'location_id', 'email', 'booking_date', 'booking_time')
            ->get();

        if ($bookings->isEmpty()) {
            Log::info("No bookings to auto-cancel");
            return;
        }

        // Cache SMTP settings
        $smtpCache = [];

        foreach ($bookings as $booking) {
            $message = 'Your appointment was automatically cancelled because you did not check in within 5 minutes of the scheduled time.';

            // Create suspension log
            $suspensionLog = SuspensionLog::create([
                'team_id' => $booking->team_id,
                'location_id' => $booking->location_id,
                'action_type' => 'Appointment',
                'notification_type' => 'Email',
                'reason' => $message,
            ]);

            // Update booking
            $booking->update([
                'suspension_logs_id' => $suspensionLog->id,
                'status' => 'Cancelled',
                'cancel_remark' => $message,
                'cancel_reason' => $message,
            ]);

            // Skip if email is empty or invalid
            if (empty($booking->email) || !filter_var($booking->email, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Invalid or missing email for booking ID {$booking->id}");
                continue;
            }

            // Load SMTP settings if not cached
            $cacheKey = $booking->team_id . '-' . $booking->location_id;
            if (!array_key_exists($cacheKey, $smtpCache)) {
                $smtpCache[$cacheKey] = SmtpDetails::where('team_id', $booking->team_id)
                    ->where('location_id', $booking->location_id)
                    ->first();
            }

            $details = $smtpCache[$cacheKey];

            if (!$details || empty($details->hostname || $details->port || $details->username || $details->password || $details->from_email || $details->from_name)) {
                Log::warning("SMTP settings missing for team {$booking->team_id}, location {$booking->location_id}");
                continue;
            }

            // Set SMTP config dynamically
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', trim($details->hostname));
            Config::set('mail.mailers.smtp.port', trim($details->port));
            Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
            Config::set('mail.mailers.smtp.username', trim($details->username));
            Config::set('mail.mailers.smtp.password', trim($details->password));
            Config::set('mail.from.address', trim($details->from_email));
            Config::set('mail.from.name', trim($details->from_name));

            // Queue email
            try {
                Mail::to($booking->email)->queue(new SuspensionNotification(
                    $message,
                    'Appointment Cancellation',
                    [
                        'booking_date' => $booking->booking_date,
                        'booking_time' => $booking->booking_time,
                        'team_id' => $booking->team_id,
                        'location_id' => $booking->location_id,
                    ]
                ));
                Log::info("Auto-cancel email queued for booking ID {$booking->id} ({$booking->email})");
            } catch (\Exception $e) {
                Log::error("Failed to send email to {$booking->email}: {$e->getMessage()}");
            }
        }

        Log::info("Cron completed: Auto-cancel bookings");
    }
}
