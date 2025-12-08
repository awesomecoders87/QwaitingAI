<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\SmtpDetails;
use App\Models\Category;
use App\Models\AccountSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendBookingReminderEmails extends Command
{
    protected $signature = 'reminder:send-bookings';
    protected $description = 'Send reminder emails for upcoming bookings';

    public function handle()
    {
        Log::info("📢 Booking reminder cron started");

        $timezone = config('app.timezone');
        $now = Carbon::now($timezone);

        $accountDetail = AccountSetting::where('team_id', 10)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->value('booking_reminder');

        $remindMinutes = (int) ($accountDetail ?? 0);
        if ($remindMinutes <= 0) {
            Log::info("No booking reminder set for team_id 10.");
            return;
        }

        $targetTime = $now->copy()->addMinutes($remindMinutes)->format('H:i'); // 24h format for DB match
        Log::info("Target reminder time: {$targetTime}");

        Booking::select([
        'id',
        'name',
        'phone',
        'phone_code',
        'booking_date',
        'booking_time',
        'booked_by',
        'category_id',
        'sub_category_id',
        'child_category_id',
        'location_id',
        'team_id',
        'status',
        'json',
        'refID',
        'email',
    ])->with([
        'categories:id,name',
        'sub_category:id,name',
        'child_category:id,name',
        'location:id,location_name'
    ])->where('team_id', 10)
            ->whereDate('booking_date', $now->toDateString())
            ->where('status', 'Confirmed')
            ->whereRaw("DATE_FORMAT(start_time, '%H:%i') = ?", [$targetTime])
            ->chunk(50, function ($bookings) {
                foreach ($bookings as $booking) {
                    try {

                        $data = [
                            'booking_id' => $booking->id,
                            'name' => $booking->name ?? '',
                            'phone' => $booking->phone ?? '',
                            'phone_code' => $booking->phone_code ?? '91',
                            'booking_date' => $booking->booking_date,
                            'booking_time' => $booking->booking_time,
                            'booked_by' => $booking->booked_by ?? '',
                            'category_name' => $booking->categories?->name ?? '',
                            'secondC_name' => $booking->sub_category?->name ?? '',
                            'thirdC_name' => $booking->child_category?->name ?? '',
                            'location' => $booking->location?->location_name ?? '',
                            'status' => $booking->status,
                            'json' => $booking->json,
                            'refID' => $booking->refID,
                            'view_booking' => url('booking-confirmed', ['id' => base64_encode($booking->id)]),
                            'locations_id' => $booking->location_id,
                            'team_id' => $booking->team_id,
                            'to_mail' => $booking->email,
                        ];

                        if (!empty($booking->email)) {
                            $this->sendNotification($data, 'reminder', 'Reminder Notification for Booking');
                        }
                    } catch (\Exception $e) {
                        Log::error("Reminder email failed for {$booking->email}: " . $e->getMessage());
                    }
                }
            });

        Log::info("✅ Booking reminder cron completed at " . $now->toDateTimeString());
    }

    private function sendNotification($data, $title, $template)
    {
        if (!empty($data['to_mail'])) {
            Log::info("Sending booking reminder email", ['email' => $data['to_mail']]);
            SmtpDetails::sendMail($data, $title, $template, $data['team_id']);
        } else {
            Log::warning("Booking reminder email not sent: no email found", ['booking_id' => $data['booking_id'] ?? null]);
        }
    }
}
