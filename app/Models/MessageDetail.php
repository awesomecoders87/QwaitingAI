<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\WhatsApp\InteraktService;
use Illuminate\Support\Facades\Log;

class MessageDetail extends Model
{
    use HasFactory;

    protected $table = 'message_detail';

    protected $fillable = [
        'team_id',
        'location_id',
        'user_id',
        'customer_id',
        'booking_id',
        'queue_id',
        'queue_storage_id',
        'message',
        'contact',
        'email',
        'channel',
        'segment',
        'type',
        'event_name',
        'status',
        'failed_reason',
        'response_status',
        'sms_sent_cost',
    ];

    const CUSTOM_TYPE = 'custom';
    const TRIGGERED_TYPE = 'triggered';
    const AUTOMATIC_TYPE = 'automatic';
    const PROCESSING_STATUS = 'processing';
    const PENDING_STATUS = 'pending';
    const SENT_STATUS = 'sent';
    const FAILED_STATUS = 'failed';


    public static function sendInteraktMessage(array $data = [])
    {
        try {
            $to = $data['fullPhoneNumber'] ?? null;
            $teamId = $data['teamId'] ?? null;
            $locationId = $data['locationId'] ?? null;
            $templateName = $data['template'] ?? null;
            $bodyValues = $data['bodyValues'] ?? [];
            $queue_id = $data['queue_id'] ?? null;
            $queue_storage_id = $data['storage_id'] ?? null;
            $user_id = $data['user_id'] ?? null;
            $customer_id = $data['customer_id'] ?? null;
            $booking_id = $data['booking_id'] ?? null;
            $type = $data['type'] ?? 'triggered';
            $event = $data['event'] ?? 'other';

            if (!$to || !$templateName || empty($bodyValues)) {
                return [
                    'status' => 'failed',
                    'error' => 'Missing required fields (phone number / template / body values)'
                ];
            }

            $response = app(InteraktService::class)->sendTemplateMessageToUser(
                $to,
                $bodyValues,
                $templateName
            );

            $status = $response->successful() ? 'sent' : 'failed';
            $responseBody = $response->json();
            $message = $responseBody['message'] ?? null;

            self::create([
                'team_id' => $teamId,
                'location_id' => $locationId,
                'user_id' => $user_id,
                'customer_id' => $customer_id,
                'queue_id' => $queue_id,
                'queue_storage_id' => $queue_storage_id,
                'message' => $message,
                'contact' => $to,
                'status' => $status,
                'channel' => 'whatsapp',
                'type' => $type,
                'event_name' => $event,
            ]);

            return [
                'status' => $status,
                'response' => $responseBody
            ];
        } catch (\Throwable $e) {
            Log::error('Interakt Send Message Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $data
            ]);

            self::create([
                'team_id' => $teamId ?? null,
                'location_id' => $locationId ?? null,
                'user_id' => $user_id ?? null,
                'customer_id' => $customer_id ?? null,
                'queue_id' => $queue_id ?? null,
                'queue_storage_id' => $queue_storage_id ?? null,
                'message' => $e->getMessage(),
                'contact' => $to ?? '',
                'status' => 'failed',
                'channel' => 'whatsapp',
                'type' => $type ?? 'triggered',
                'event_name' => $event ?? 'other',
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    public static function storeLog(array $data = [])
    {
        try {
            self::create([
                'team_id' => $data['team_id'] ?: tenant('id'),
                'location_id' => $data['location_id'],
                'user_id' => $data['user_id'] ?? null,
                'customer_id' =>  !empty($data['customer_id']) ? $data['customer_id'] : null,
                'booking_id' => $data['booking_id'] ?? null,
                'queue_id' => $data['queue_id'] ?? null,
                'queue_storage_id' => $data['queue_storage_id'] ?? null,
                'message' => $data['message'] ?? null,
                'email' => $data['email'] ?? null,
                'contact' => $data['contact'] ?? null,
                'channel' => $data['channel'],
                'type' => $data['type'],
                'segment' => $data['segment'] ?? null,
                'event_name' => $data['event_name'],
                'status' => $data['status'],
                'failed_reason' => $data['failed_reason'] ?? null
            ]);
        } catch (\Throwable $e) {

            $channel = $data['channel'] == 'sms' ? 'sms' : 'email';

            Log::error('Failed to send ' . $channel . ' :' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $data
            ]);

            self::create([
                'team_id' => $data['team_id'] ?: tenant('id'),
                'location_id' => $data['location_id'],
                'user_id' => $data['user_id'] ?? null,
                'customer_id' =>  !empty($data['customer_id']) ? $data['customer_id'] : null,
                'booking_id' => $data['booking_id'] ?? null,
                'queue_id' => $data['queue_id'] ?? null,
                'queue_storage_id' => $data['queue_storage_id'] ?? null,
                'message' => $data['message'] ?? null,
                'email' => $data['email'] ?? null,
                'contact' => $data['contact'] ?? null,
                'channel' => $data['channel'],
                'type' => $data['type'],
                'segment' => $data['segment'] ?? null,
                'event_name' => $data['event_name'],
                'status' => 'failed',
                'failed_reason' => $e->getMessage()
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
}
