<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_template';

    protected $fillable = [
        'team_id',
        'location_id',
        'enable_template_name',
        'ticket_generation_message',
        'reminder_message',
        'next_call_message',
        'skip_call_message',
        'recall_message',
        'feedback_sms_message',
        'form_link_sms',
        'manual_form_link_sms',
        'new_booking_sms',
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
        'new_booking_sms_status',
        'reschedule_booking_sms_status',
        'cancel_booking_sms_status',
        'long_waiting_queues_notification_to_admin_status',
        'ticket_generation_message_template', 
        'reminder_message_template', 
        'next_call_message_template',
        'feedback_sms_message_template',
        'cancel_booking_sms_template', 
        'reschedule_booking_sms_template',
        'new_booking_sms_template',
        'skip_call_message_template',
        'recall_message_template'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}
