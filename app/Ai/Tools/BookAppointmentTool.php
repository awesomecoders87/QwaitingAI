<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Support\Facades\Http;
use Stringable;
use Illuminate\Support\Facades\Log;

class BookAppointmentTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Book a new appointment. Required: service_name, appointment_date (YYYY-MM-DD), time, name, phone, email. Optional: phone_code (default: +91).';
    }

    public function handle(Request $request): Stringable|string
    {
        $data = [
            'service_name' => trim($request['service_name'] ?? ''),
            'appointment_date' => trim($request['appointment_date'] ?? $request['date'] ?? ''),
            'time' => trim($request['time'] ?? ''),
            'name' => trim($request['name'] ?? ''),
            'phone' => trim($request['phone'] ?? ''),
            'email' => trim($request['email'] ?? ''),
            'phone_code' => trim($request['phone_code'] ?? '+91'),
        ];
        
        // Extract start time if range provided
        if (str_contains($data['time'], '-')) {
            $data['time'] = trim(explode('-', $data['time'])[0]);
        }
        
        // Validate required fields
        $required = ['service_name', 'appointment_date', 'time', 'name', 'phone', 'email'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return 'Error: Missing required fields: ' . implode(', ', $missing);
        }
        
        Log::info('BookAppointmentTool', $data);

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->timeout(300)
                ->post('https://qwaiting-ai.thevistiq.com/api/check-and-book', $data);

            if ($response->successful()) {
                $result = $response->json();
                $status = $result['status'] ?? $result['result']['status'] ?? '';
                $refID = $result['data']['refID'] ?? $result['refID'] ?? $result['result']['refID'] ?? '';
                
                if ($status === 'success' && !empty($refID)) {
                    Log::info('Booking successful', ['refID' => $refID]);
                    return "🎉 **Booking Confirmed!**\n\n"
                        . "✅ Service: {$data['service_name']}\n"
                        . "📅 Date: {$data['appointment_date']}\n"
                        . "🕐 Time: {$data['time']}\n"
                        . "👤 Name: {$data['name']}\n\n"
                        . "🔖 **Reference ID:** `{$refID}`\n\n"
                        . "Please save this Reference ID for future reference!";
                }
            }

            Log::error('Booking failed: ' . $response->body());
            $errorMsg = $result['message'] ?? $result['result']['message'] ?? 'Unknown error';
            return "❌ Booking failed: {$errorMsg}. Please try again.";
            
        } catch (\Exception $e) {
            Log::error('BookAppointment exception: ' . $e->getMessage());
            return 'Error processing booking. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'service_name' => $schema->string()->description('Name of the service to book'),
            'appointment_date' => $schema->string()->description('Date in YYYY-MM-DD format'),
            'date' => $schema->string()->description('Alternative to appointment_date'),
            'time' => $schema->string()->description('Time slot (e.g., "09:00 AM")'),
            'name' => $schema->string()->description('Full name of the person'),
            'phone' => $schema->string()->description('Phone number'),
            'email' => $schema->string()->description('Email address'),
            'phone_code' => $schema->string()->description('Phone country code (default: +91)'),
        ];
    }
}
