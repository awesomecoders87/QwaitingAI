<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Support\Facades\Http;
use Stringable;
use Illuminate\Support\Facades\Log;

class GetBookingDetailsTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get details of an existing booking. Required parameter: booking_refID. Use this for reschedule or cancel flows.';
    }

    public function handle(Request $request): Stringable|string
    {
        $bookingRefID = trim($request['booking_refID'] ?? '');
        
        if (empty($bookingRefID)) {
            return 'Error: booking_refID is required. Please provide your booking reference ID.';
        }
        
        Log::info('GetBookingDetailsTool', ['booking_refID' => $bookingRefID]);

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->timeout(300)
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->post('https://qwaiting-ai.thevistiq.com/api/get-booking-details', [
                'booking_refID' => $bookingRefID
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $status = $result['status'] ?? $result['result']['status'] ?? '';
                
                if ($status === 'success') {
                    $data = $result['data'] ?? $result['result']['data'] ?? [];
                    
                    $service = $data['service_name'] ?? 'N/A';
                    $date = $data['booking_date'] ?? $data['date'] ?? 'N/A';
                    $time = $data['booking_time'] ?? $data['time'] ?? 'N/A';
                    $name = $data['name'] ?? 'N/A';
                    $phone = $data['phone'] ?? 'N/A';
                    $email = $data['email'] ?? 'N/A';
                    
                    return "📋 **Booking Details** (Ref ID: `{$bookingRefID}`)\n\n"
                        . "✅ Service: {$service}\n"
                        . "📅 Date: {$date}\n"
                        . "🕐 Time: {$time}\n"
                        . "👤 Name: {$name}\n"
                        . "📞 Phone: {$phone}\n"
                        . "📧 Email: {$email}";
                }
            }

            Log::error('GetBookingDetails failed: ' . $response->body());
            return "I couldn't find a booking with Reference ID `{$bookingRefID}`. Please check and try again.";
            
        } catch (\Exception $e) {
            Log::error('GetBookingDetails exception: ' . $e->getMessage());
            return 'Error fetching booking details. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'booking_refID' => $schema->string()->description('The booking reference ID to lookup'),
        ];
    }
}
