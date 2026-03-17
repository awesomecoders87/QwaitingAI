<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Support\Facades\Http;
use Stringable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GetAvailableDatesTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get available dates for a specific service. Required parameter: service_name (the name of the service selected by user).';
    }

    public function handle(Request $request): Stringable|string
    {
        $serviceName = trim($request['service_name'] ?? '');
        
        if (empty($serviceName)) {
            return 'Error: service_name is required. Please provide the service name first.';
        }
        
        Log::info('GetAvailableDatesTool', ['service' => $serviceName]);

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->timeout(300)
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->post('https://qwaiting-ai.thevistiq.com/api/get-available-dates', [
                    'service_name' => $serviceName,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $dates = $data['available_dates'] 
                    ?? $data['dates'] 
                    ?? $data['result']['available_dates'] 
                    ?? $data['result']['dates'] 
                    ?? [];
                
                if (empty($dates)) {
                    return "No available dates found for {$serviceName}. Please try another service or check back later.";
                }
                
                $formattedDates = array_map(function($d) {
                    $raw = is_array($d) ? ($d['date'] ?? $d['value'] ?? '') : $d;
                    try {
                        return Carbon::parse($raw)->format('D, M d Y');
                    } catch (\Throwable $e) {
                        return $raw;
                    }
                }, $dates);
                
                return "Available dates for {$serviceName}:\n" . implode("\n", array_map(fn($d) => "- {$d}", $formattedDates));
            }
            
            Log::error('GetAvailableDates failed: ' . $response->body());
            return 'Unable to fetch available dates. Please try again.';
            
        } catch (\Exception $e) {
            Log::error('GetAvailableDates exception: ' . $e->getMessage());
            return 'Error fetching dates. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'service_name' => $schema->string()->description('The service name selected by the user (e.g., "School Management", "Consultation")'),
        ];
    }
}
