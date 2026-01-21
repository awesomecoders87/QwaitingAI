<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SmtpDetails;
use App\Models\QueueStorage;
use App\Models\Booking;
use App\Models\SmsAPI;
use App\Mail\SuspensionNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendSuspensionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queuestorages;
    public $bookings;
    public $message;
    public $suspensionLogId;
    public $subject;
    public $notificationType;
    public $team_id;
    public $location;

    /**
     * Create a new job instance.
     */
    public function __construct($queuestorages, $bookings, $message, $suspensionLogId, $subject, $notificationType, $team_id, $location)
    {
        $this->queuestorages = $queuestorages;
        $this->bookings = $bookings;
        $this->message = $message;
        $this->suspensionLogId = $suspensionLogId;
        $this->subject = $subject;
        $this->notificationType = $notificationType;
        $this->team_id = $team_id;
        $this->location = $location;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Process Email notifications
        if ($this->notificationType == 'email' || $this->notificationType == 'sms_and_email') {
            $this->processEmailNotifications($this->queuestorages, $this->bookings, $this->message, $this->suspensionLogId, $this->subject);
        }

        // Process SMS notifications
        if ($this->notificationType == 'sms' || $this->notificationType == 'sms_and_email') {
            $this->processSmsNotifications($this->queuestorages, $this->bookings, $this->message, $this->suspensionLogId);
        }
    }

    protected function processEmailNotifications($queuestorages, $bookings, $message, $suspensionLogId, $subject)
    {
        $details = SmtpDetails::where('team_id', $this->team_id)->where('location_id', $this->location)->first();
        if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', trim($details->hostname));
            Config::set('mail.mailers.smtp.port', trim($details->port));
            Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
            Config::set('mail.mailers.smtp.username', trim($details->username));
            Config::set('mail.mailers.smtp.password', trim($details->password));

            Config::set('mail.from.address', trim($details->from_email));
            Config::set('mail.from.name', trim($details->from_name));
        }
        // Process queue storage emails
        foreach ($queuestorages as $queuestorage) {
            $email = $this->extractEmailFromQueueStorage($queuestorage);

            if (!empty($email)) {
                try {
                    $recipientName = $this->extractNameFromQueueStorage($queuestorage);
                    $queueData = [
                        'arrives_time' => $queuestorage->arrives_time,
                        'token' => $queuestorage->token ?? null,
                        'team_id' => $queuestorage->team_id ?? null,
                        'location_id' => $queuestorage->locations_id ?? null,
                        // Add other minimal required fields
                    ];

                    if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
                        // dd( $email,$recipientName,$queueData);
                        Mail::to($email)->send(new SuspensionNotification(
                            $message,
                            $subject,
                            $queueData
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send email to queue storage (ID: {$queuestorage->id}): " . $e->getMessage());
                }
            }
        }


        // Process booking emails
        foreach ($bookings as $booking) {
            if (!empty($booking->email)) {
                try {
                    $bookingData = [
                        'booking_date' => $booking->booking_date,
                        'booking_time' => $booking->booking_time,
                        'team_id' => $booking->team_id,
                        'location_id' => $booking->location_id,
                    ];
                    if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
                        Mail::to($booking->email)->send(new SuspensionNotification(
                            $message,
                            'Appointment Cancellation',
                            $bookingData,
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send email to booking: " . $e->getMessage());
                }
            }
        }
    }

    protected function processSmsNotifications($queuestorages, $bookings, $message, $suspensionLogId)
    {
        // Process queue storage SMS
        foreach ($queuestorages as $queuestorage) {
            if (!empty($queuestorage->phone)) {
                $phone_code = isset($queuestorage->phone_code) ? ltrim($queuestorage->phone_code, '+') : '91';
                $contactWithCode = $phone_code . $queuestorage->phone;
                SmsAPI::currentQueueSms($contactWithCode, $message, $this->team_id, 'suspensions queue');
            }
        }

        // Process booking SMS
        foreach ($bookings as $booking) {
            if (!empty($booking->phone)) {
                $phone_code = '91';
                $contactWithCode = $phone_code . $booking->phone;
                SmsAPI::currentQueueSms($contactWithCode, $message, $this->team_id, 'suspensions appointment');
            }
        }
    }

    protected function extractNameFromQueueStorage($queuestorage)
    {
        if ($queuestorage->json) {
            $jsonData = is_string($queuestorage->json) ? json_decode($queuestorage->json, true) : $queuestorage->json;

            return $jsonData['name'] ??
                $jsonData['Name'] ??
                $jsonData['Full Name'] ??
                $jsonData['full_name'] ?? null;
        }

        return null;
    }

    protected function extractEmailFromQueueStorage($queuestorage)
    {
        if ($queuestorage->json) {
            $jsonData = is_string($queuestorage->json) ? json_decode($queuestorage->json, true) : $queuestorage->json;

            return $jsonData['Email'] ??
                $jsonData['email'] ??
                $jsonData['Email Address'] ??
                $jsonData['email_address'] ??
                $queuestorage->email ?? null;
        }

        return $queuestorage->email ?? null;
    }
}
