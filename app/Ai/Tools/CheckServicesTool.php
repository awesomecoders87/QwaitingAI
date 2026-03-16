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
        Log::info('CheckServicesTool called');
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(300)
                ->get('https://qwaiting-ai.thevistiq.com/api/check-service');

            if ($response->successful()) {
                $data = $response->json();
                $services = $data['services'] ?? $data['data']['services'] ?? [];
                
                if (empty($services)) {
                    return 'No services are currently available. Please try again later.';
                }
                
                $serviceNames = array_map(fn($s) => $s['name'] ?? 'Unknown', $services);
                return "Available services:\n" . implode("\n", array_map(fn($n) => "- {$n}", $serviceNames));
            }
            
            Log::error('CheckServices failed: ' . $response->body());
            return 'Sorry, unable to fetch services right now. Please try again.';
            
        } catch (\Exception $e) {
            Log::error('CheckServices exception: ' . $e->getMessage());
            return 'Service temporarily unavailable. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
