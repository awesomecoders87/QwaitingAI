<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Support\Facades\Http;
use Stringable;
use Illuminate\Support\Facades\Log;

class CancelAppointmentTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Cancel an existing appointment. Required parameter: booking_refID.';
    }

    public function handle(Request $request): Stringable|string
    {
        $bookingRefID = trim($request['booking_refID'] ?? '');
        
        if (empty($bookingRefID)) {
            return 'Error: booking_refID is required. Please provide your booking reference ID.';
        }
        
        Log::info('CancelAppointmentTool', ['booking_refID' => $bookingRefID]);

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->timeout(30)
                ->post('https://qwaiting-ai.thevistiq.com/api/cancel-booking', [
                'booking_refID' => $bookingRefID
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $status = $result['status'] ?? $result['result']['status'] ?? '';
                
                if ($status === 'success') {
                    return "✅ **Booking Cancelled Successfully!**\n\n"
                        . "Your appointment (Ref ID: `{$bookingRefID}`) has been cancelled.\n\n"
                        . "If you need to book again in the future, just let me know! 😊";
                }
            }

            Log::error('Cancel failed: ' . $response->body());
            $errorMsg = $result['message'] ?? $result['result']['message'] ?? 'Unknown error';
            return "❌ Cancellation failed: {$errorMsg}. Please check your reference ID and try again.";
            
        } catch (\Exception $e) {
            Log::error('CancelAppointment exception: ' . $e->getMessage());
            return 'Error processing cancellation. Please try again later.';
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'booking_refID' => $schema->string()->description('The booking reference ID to cancel'),
        ];
    }
}
