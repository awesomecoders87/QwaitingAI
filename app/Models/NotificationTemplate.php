<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotificationTemplate extends Model
{
    use HasFactory;
    protected $table = 'notification_templates';

    protected $fillable = [
        'team_id',
        'location_id',
        'ticket_notification',
        'ticket_notification_subject',
        'ticket_notification_status',
        'service_call_notification',
        'service_call_notification_subject',
        'service_call_notification_status',
        'service_recall_notification',
        'service_recall_notification_subject',
        'service_recall_notification_status',
        'feedback_notification',
        'feedback_notification_subject',
        'feedback_notification_status',
        'call_skip_notification',
        'call_skip_notification_subject',
        'call_skip_notification_status',
        'booking_confirmed_notification',
        'booking_confirmed_notification_subject',
        'booking_confirmed_notification_status',
        'booking_reschedule_notification',
        'booking_reschedule_notification_subject',
        'booking_reschedule_notification_status',
        'booking_cancel_notification',
        'booking_cancel_notification_subject',
        'booking_cancel_notification_status',
        'reminder_notification',
        'reminder_notification_subject',
        'reminder_notification_status',
        'booking_confirmed_admin_notification',
        'booking_confirmed_admin_notification_subject',
        'booking_confirmed_admin_notification_status',
    ];

    // Define any date casting if necessary
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class,'team_id','id');
    }

     public static function createDefaultTemplates($teamId, $locationId)
{
    try {
        $defaultTemplates = [
            [
                'team_id' => $teamId,
                'location_id' => $locationId,
                'ticket_notification_subject' => 'Ticket generation',
                'ticket_notification' => <<<EOT
Dear {{customer_name}},

Thank you for your recent visit to the {{panel_name}} 

Your queue number is {{token}}. You are number {{queue_count}} in the queue and the estimated waiting time is {{waiting_time}} minutes.

Queue number will be flashed on display TV, it may not be called in sequence. Thank you.123

{{category_1}}
{{category_2}}
{{category_3}}

Have a pleasant day.
Best regards,
{{panel_name}} team123
EOT,
                'ticket_notification_status' => 1,
                'service_call_notification_subject' => 'Service Call Notifications',
                'service_call_notification' => <<<EOT
Dear {{customer_name}},

Your number {{token}} will be called soon. {{counter_name}} Please kindly return to and we will serve you shortly. Thank you.

{{customer_name}}, {{panel_name}}, {{token}} keyword
Have a pleasant day.
Best regards,
{{panel_name}} team
EOT,
                'service_call_notification_status' => 1,
                'service_recall_notification_subject' => 'Service Recall Notifications',
                'service_recall_notification' => <<<EOT
Dear {{customer_name}},

Your number {{token}} will be called soon. {{counter_name}} Please kindly return to and we will serve you shortly. Thank you.

{{customer_name}}, {{panel_name}}, {{token}} keyword
Have a pleasant day.
Best regards,
{{panel_name}} team
EOT,
                'service_recall_notification_status' => 1,
                'feedback_notification_subject' => 'Feedback Message',
                'feedback_notification' => <<<EOT
Hi {{customer_name}},

Thank you again for choosing {{panel_name}}. Please leave us a review on our profile on {{feedback_link}}. It will only take a minute, but your valuable feedback will help us improve and make a huge difference to our company.

Thank you!
EOT,
                'feedback_notification_status' => 1,
                'call_skip_notification_subject' => 'Call Skip Notifications',
                'call_skip_notification' => <<<EOT
Dear {{customer_name}},

Your number {{token}} will be called soon. Please kindly return to {{panel_name}} and we will serve you shortly. Thank you.

Keywords: {{customer_name}}, {{panel_name}}, {{token}}
Have a pleasant day.
Best regards,
{{panel_name}} team
EOT,
                'call_skip_notification_status' => 1,
                'booking_confirmed_notification_subject' => 'Booking Confirmed Notifications',
                'booking_confirmed_notification' => <<<EOT
Dear {{customer_name}},

Thank you for booking a visit to the {{panel_name}}.

Booking Confirmation:
Booking ID: {{booking_id}}
Visit Date: {{booking_date}}
Admission Time: {{booking_time}}

{{category_1}}
{{category_2}}
{{category_3}}

Things to note:
Present your e-mail confirmation to our staff to enter the garden at your appointment timeslot.
Please arrive no later than 30 minutes from your selected time. If the garden’s capacity has been reached, you may be required to join the walk-in queue and wait till there is availability.
All parties are required to be present to gain entry into the garden.
Maximum number of people permitted to gain entry per booking will be subjected to the prevailing group size limit for social gathering on the day of visit.

We look forward to your visit to the Gardens. Have a great day!

Thanks,
EOT,
                'booking_confirmed_notification_status' => 1,
                'booking_reschedule_notification_subject' => 'Booking Reschedule',
                'booking_reschedule_notification' => <<<EOT
Dear {{customer_name}},

Thank you for booking a visit to the {{panel_name}}.

Booking Confirmation:
Booking ID: {{booking_id}}
Visit Date: {{booking_date}}
Admission Time: {{booking_time}}

{{category_1}}
{{category_2}}
{{category_3}}

Things to note:
Present your e-mail confirmation to our staff to enter the garden at your appointment timeslot.
Please arrive no later than 30 minutes from your selected time. If the garden’s capacity has been reached, you may be required to join the walk-in queue and wait till there is availability.
All parties are required to be present to gain entry into the garden.
Maximum number of people permitted to gain entry per booking will be subjected to the prevailing group size limit for social gathering on the day of visit.

We look forward to your visit to the Gardens. Have a great day!

Thanks,
Qwaiting Team
EOT,
                'booking_reschedule_notification_status' => 1,
                'booking_cancel_notification_subject' => 'Booking Cancelled',
                'booking_cancel_notification' => <<<EOT
Dear {{customer_name}},

Thank you for booking a visit to the {{panel_name}}.

Booking Confirmation:
Booking ID: {{booking_id}}
Visit Date: {{booking_date}}
Admission Time: {{booking_time}}

{{category_1}}
{{category_2}}
{{category_3}}

Things to note:
Present your e-mail confirmation to our staff to enter the garden at your appointment timeslot.
Please arrive no later than 30 minutes from your selected time. If the garden’s capacity has been reached, you may be required to join the walk-in queue and wait till there is availability.
All parties are required to be present to gain entry into the garden.
Maximum number of people permitted to gain entry per booking will be subjected to the prevailing group size limit for social gathering on the day of visit.

We look forward to your visit to the Gardens. Have a great day!

Thanks,
Qwaiting Team
EOT,
                'booking_cancel_notification_status' => 1,
                'reminder_notification_subject' => 'Reminder Notifications',
                'reminder_notification' => <<<EOT
Dear {{customer_name}}

Welcome to {{panel_name}}.

Your queue number is {{token}}. Queue number will be flashed on display TV, it may not be called in sequence.

Thank you.{{customer_name}}. Reminder SMS
EOT,
                'reminder_notification_status' => 1,
                'booking_confirmed_admin_notification_subject' => 'Booking Confirmed (Admin)',
                'booking_confirmed_admin_notification' => null,
                'booking_confirmed_admin_notification_status' => 1,
            ]
        ];

        foreach ($defaultTemplates as $template) {
            NotificationTemplate::create($template);
        }

    } catch (\Exception $e) {
        Log::error("Error creating default notification templates for team_id: {$teamId}, location_id: {$locationId}. Error: " . $e->getMessage());
    }
}

}
