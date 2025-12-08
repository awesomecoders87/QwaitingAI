<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuspensionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'action_type',
        'notification_type',
        'reason',
    ];


    /**
     * Define the possible action types
     */
    public const ACTION_APPOINTMENT = 'appointment';
    public const ACTION_QUEUE = 'queue';
    public const ACTION_APPOINTMENT_AND_QUEUE = 'appointment_and_queue';

    /**
     * Define the possible notification types
     */
    public const NOTIFICATION_SMS = 'sms';
    public const NOTIFICATION_EMAIL = 'email';
    public const NOTIFICATION_SMS_AND_EMAIL = 'sms_and_email';

    /**
     * Get the team associated with the suspension log.
     */
    public function team()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the location associated with the suspension log.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get all possible action types
     */
    public static function getActionTypes(): array
    {
        return [
            self::ACTION_APPOINTMENT,
            self::ACTION_QUEUE,
            self::ACTION_APPOINTMENT_AND_QUEUE,
        ];
    }

    /**
     * Get all possible notification types
     */
    public static function getNotificationTypes(): array
    {
        return [
            self::NOTIFICATION_SMS,
            self::NOTIFICATION_EMAIL,
            self::NOTIFICATION_SMS_AND_EMAIL,
        ];
    }

}