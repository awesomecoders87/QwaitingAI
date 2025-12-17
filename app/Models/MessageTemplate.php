<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MessageTemplate extends Model
{

    use HasFactory;

    protected $table = 'message_templates';

    protected $fillable = [
        'team_id',
        'location_id',
        'ticket_generation_message',
        'reminder_message',
        'next_call_message',
        'skip_call_message',
        'recall_message',
        'feedback_sms_message',
        'form_link_sms',
        'manual_form_link_sms',
        'new_booking_sms_message',
        'reschedule_booking_sms',
        'cancel_booking_sms',
        'long_waiting_queues_notification_to_admin',
        'ticket_generation_message_status',
        'reminder_message_status',
        'next_call_message_status',
        'skip_call_message_status',
        'recall_message_status',
        'feedback_sms_message_status',
        'form_link_sms_status',
        'manual_form_link_sms_status',
        'new_booking_sms_message_status',
        'reschedule_booking_sms_status',
        'cancel_booking_sms_status',
        'long_waiting_queues_notification_to_admin_status',
        'enable_template_name',
        'ticket_generation_message_template',
        'reminder_message_template',
        'next_call_message_template',
        'feedback_sms_message_template',
        'cancel_booking_sms_template',
        'reschedule_booking_sms_template',
        'new_booking_sms_message_template',
        'skip_call_message_template',
        'recall_message_template',
        'stripe_sms_message',
        'stripe_sms_message_status',
        'stripe_sms_message_template',
    ];

    public function team()
    {
        return $this->belongsTo(Tenant::class,'team_id','id');
    }

    public static function defaultTemplateContent($teamId, $locationId)
{
    $defaultTemplates = [
        'team_id' => $teamId,
        'location_id' => $locationId,
        'ticket_generation_message' => "Welcome to {{panel_name}}. Your queue number is {{token}}. There are {{queue_count}} queuing before you and the estimated waiting time is {{waiting_time}} minutes. This is your ticket link {{ticket_link}}, {{booking_date}}, {{booking_time}}",
        'reminder_message' => "Your number {{token}} will be called soon. Please kindly return to Customer Service Counter and we will serve you shortly. Thank You",
        'next_call_message' => "Your number {{token}} will be called soon. Please kindly return to {{panel_name}} and we will serve you shortly. Thank you. {{counter_name}}",
        'feedback_sms' => "Your number {{token}} will be called soon. Please kindly return to Customer Service Counter and we will serve you shortly. Thank You  {{feedback_link}}",
        'form_link_sms' => '',
        'enable_template_name' => 0,
        'manual_form_link_sms' => '',
        'new_booking_sms' => '',
        'reschedule_booking_sms' => '',
        'cancel_booking_sms' => '',
        'long_waiting_queues_notification_to_admin' => '',
        'stripe_sms_message' => "Payment successful! Your wallet has been credited with {{ amount }}.
Current balance: {{ wallet_balance }}.
Thank you for using {{ app_name }}.",
        // status fields default values
        'ticket_generation_message_status' => true,
        'reminder_message_status' => true,
        'next_call_message_status' => true,
        'feedback_sms_status' => true,
        'form_link_sms_status' => false,
        'manual_form_link_sms_status' => false,
        'new_booking_sms_status' => false,
        'reschedule_booking_sms_status' => false,
        'cancel_booking_sms_status' => false,
        'long_waiting_queues_notification_to_admin_status' => false,
        'stripe_sms_message_status' => true,
    ];

    try {
        self::create($defaultTemplates);

        // create default whatsapp template too
        WhatsappTemplate::create($defaultTemplates);
    } catch (\Exception $e) {
        Log::error('Error creating default message template: '.$e->getMessage(), [
            'team_id' => $teamId,
            'location_id' => $locationId,
            'trace' => $e->getTraceAsString(),
        ]);
        // Optionally rethrow or handle error further here
    }
}
}
