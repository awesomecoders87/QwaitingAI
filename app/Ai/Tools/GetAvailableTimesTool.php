<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Support\Facades\Http;
use Stringable;
use Illuminate\Support\Facades\Log;

class GetAvailableTimesTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get available time slots for a service on a specific date. Required parameters: service_name and date (YYYY-MM-DD format).';
    }

    public function handle(Request $request): Stringable|string
    {
        $serviceName = trim($request['service_name'] ?? '');
        $date = trim($request['date'] ?? '');
        
        if (empty($serviceName)) {
            return 'Error: service_name is required. Please provide the service name first.';
        }
        
        if (empty($date)) {
            return 'Error: date is required. Please provide the date in YYYY-MM-DD format.';
        }
        
        Log::info('GetAvailableTimesTool', ['service' => $serviceName, 'date' => $date]);

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->timeout(300)
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->post('https://qwaiting-ai.thevistiq.com/api/get-available-times', [
                    'service_name' => $serviceName,
                    'date'         => $date,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $times = $data['result']['available_times'] 
                    ?? $data['available_times'] 
                    ?? $data['result']['times'] 
                    ?? $data['times'] 
                    ?? [];
                
                if (empty($times)) {
                    return "No available times for {$serviceName} on {$date}. Please select another date.";
                }
                
                $timeLabels = array_map(function($t) {
                    return is_array($t) ? ($t['time'] ?? $t['value'] ?? '') : $t;
                }, $times);
                
                return "Available times for {$serviceName} on {$date}:\n" . implode("\n", array_map(fn($t) => "- {$t}", $timeLabels));
            }
            
            Log::error('GetAvailableTimes failed: ' . $response->body());
            return 'Unable to fetch available times. Please try again.';
            
        } catch (\Exception $e) {
            Log::error('GetAvailableTimes exception: ' . $e->getMessage());
            return 'Error fetching times. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'service_name' => $schema->string()->description('The service name selected by the user'),
            'date' => $schema->string()->description('The selected date in YYYY-MM-DD format'),
        ];
    }
}
