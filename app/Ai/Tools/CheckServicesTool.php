<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Support\Facades\Http;
use Stringable;
use Illuminate\Support\Facades\Log;

class CheckServicesTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'List all available services for booking. Call this when user wants to book an appointment or asks what services are available.';
    }

    public function handle(Request $request): Stringable|string
    {
        Log::info('[CheckServicesTool] Tool called');
        
        try {
            Log::info('[CheckServicesTool] Making HTTP request to fetch services');
            $response = \Illuminate\Support\Facades\Http::timeout(300)
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->get('https://qwaiting-ai.thevistiq.com/api/check-service');

            if ($response->successful()) {
                Log::info('[CheckServicesTool] API response successful');
                $data = $response->json();
                Log::info('[CheckServicesTool] Raw API response', ['data' => $data]);
                
                $services = $data['services'] ?? $data['data']['services'] ?? [];
                Log::info('[CheckServicesTool] Extracted services', ['count' => count($services)]);
                
                if (empty($services)) {
                    Log::warning('[CheckServicesTool] No services found in response');
                    return 'No services are currently available. Please try again later.';
                }
                
                $serviceNames = array_map(fn($s) => $s['name'] ?? 'Unknown', $services);
                Log::info('[CheckServicesTool] Service names extracted', ['services' => $serviceNames]);
                
                return "Available services:\n" . implode("\n", array_map(fn($n) => "- {$n}", $serviceNames));
            }
            
            Log::error('[CheckServicesTool] API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return 'Sorry, unable to fetch services right now. Please try again.';
            
        } catch (\Exception $e) {
            Log::error('[CheckServicesTool] Exception occurred: ' . $e->getMessage());
            Log::error('[CheckServicesTool] Stack trace: ' . $e->getTraceAsString());
            return 'Service temporarily unavailable. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
