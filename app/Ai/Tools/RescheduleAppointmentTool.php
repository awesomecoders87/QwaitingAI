<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Support\Facades\Http;
use Stringable;
use Illuminate\Support\Facades\Log;

class RescheduleAppointmentTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Reschedule an existing appointment. Required: booking_refID, service_name, date (YYYY-MM-DD), time. Optional: name, phone, email.';
    }

    public function handle(Request $request): Stringable|string
    {
        $data = [
            'booking_refID' => trim($request['booking_refID'] ?? ''),
            'service_name' => trim($request['service_name'] ?? ''),
            'date' => trim($request['date'] ?? ''),
            'time' => trim($request['time'] ?? ''),
            'name' => trim($request['name'] ?? ''),
            'phone' => trim($request['phone'] ?? ''),
            'email' => trim($request['email'] ?? ''),
        ];
        
        // Extract start time if range provided
        if (str_contains($data['time'], '-')) {
            $data['time'] = trim(explode('-', $data['time'])[0]);
        }
        
        // Validate required fields
        $required = ['booking_refID', 'service_name', 'date', 'time'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return 'Error: Missing required fields: ' . implode(', ', $missing);
        }
        
        // Filter out empty optional fields
        $data = array_filter($data, fn($v) => $v !== '' && $v !== null);
        
        Log::info('RescheduleAppointmentTool', $data);

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->timeout(300)
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->post('https://qwaiting-ai.thevistiq.com/api/edit-booking', $data);

            if ($response->successful()) {
                $result = $response->json();
                $status = $result['status'] ?? $result['result']['status'] ?? '';
                
                if ($status === 'success') {
                    return "✅ **Appointment Rescheduled Successfully!**\n\n"
                        . "Your booking (Ref ID: `{$data['booking_refID']}`) has been rescheduled to:\n"
                        . "📅 New Date: {$data['date']}\n"
                        . "🕐 New Time: {$data['time']}\n"
                        . "🏥 Service: {$data['service_name']}";
                }
            }

            Log::error('Reschedule failed: ' . $response->body());
            $errorMsg = $result['message'] ?? $result['result']['message'] ?? 'Unknown error';
            return "❌ Reschedule failed: {$errorMsg}. Please try again.";
            
        } catch (\Exception $e) {
            Log::error('RescheduleAppointment exception: ' . $e->getMessage());
            return 'Error processing reschedule. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'booking_refID' => $schema->string()->description('The booking reference ID to reschedule'),
            'service_name' => $schema->string()->description('Service name for the rescheduled appointment'),
            'date' => $schema->string()->description('New date in YYYY-MM-DD format'),
            'time' => $schema->string()->description('New time slot'),
            'name' => $schema->string()->description('Updated name (optional)'),
            'phone' => $schema->string()->description('Updated phone (optional)'),
            'email' => $schema->string()->description('Updated email (optional)'),
        ];
    }
}
