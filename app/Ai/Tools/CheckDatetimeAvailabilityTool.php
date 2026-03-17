<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Support\Facades\Http;
use Stringable;
use Illuminate\Support\Facades\Log;

class CheckDatetimeAvailabilityTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Check if a specific date and time slot is available for a service. Required: service_name, date (YYYY-MM-DD), time (e.g., "09:00 AM").';
    }

    public function handle(Request $request): Stringable|string
    {
        $serviceName = trim($request['service_name'] ?? '');
        $date = trim($request['date'] ?? '');
        $time = trim($request['time'] ?? '');
        
        if (empty($serviceName) || empty($date) || empty($time)) {
            return 'Error: service_name, date, and time are all required.';
        }
        
        // Extract start time if range provided
        if (str_contains($time, '-')) {
            $time = trim(explode('-', $time)[0]);
        }

        Log::info('CheckDatetimeAvailabilityTool', compact('serviceName', 'date', 'time'));

        try {
            // POST with params as URL query string (matches Postman exactly)
            $queryUrl = 'https://qwaiting-ai.thevistiq.com/api/check-datetime-availability?' . http_build_query([
                'service_name' => $serviceName,
                'date'         => $date,
                'time'         => $time,
            ]);

            $response = \Illuminate\Support\Facades\Http::timeout(300)
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->post($queryUrl);

            if ($response->successful()) {
                $data = $response->json();
                $message = strtolower($data['message'] ?? $data['result']['message'] ?? '');
                $isAvailable = str_contains($message, 'available') && !str_contains($message, 'not available');
                
                if ($isAvailable) {
                    return "✅ Great news! The slot at {$time} on {$date} for {$serviceName} is available!";
                } else {
                    return "❌ Sorry, the slot at {$time} on {$date} for {$serviceName} is not available. Please choose another time.";
                }
            }
            
            Log::error('CheckAvailability failed: ' . $response->body());
            return 'Unable to check availability. Please try again.';
            
        } catch (\Exception $e) {
            Log::error('CheckAvailability exception: ' . $e->getMessage());
            return 'Error checking availability. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'service_name' => $schema->string()->description('The service name'),
            'date' => $schema->string()->description('Date in YYYY-MM-DD format'),
            'time' => $schema->string()->description('Time slot (e.g., "09:00 AM" or "09:00 AM-10:00 AM")'),
        ];
    }
}
