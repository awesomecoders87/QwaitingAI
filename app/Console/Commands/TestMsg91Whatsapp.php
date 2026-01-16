<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestMsg91Whatsapp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'msg91:test-whatsapp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MSG91 WhatsApp API with static credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting MSG91 WhatsApp Test...');

        // STATIC CREDENTIALS - REPLACE THESE MANUALLY
        $authKey = '<authkey>'; // User to replace this
        $integratedNumber = '916207773'; // From provided curl
        $namespace = 'ba5b3cc0_e127_4ac6_902a_1f8e25483e3a'; // From provided curl
        $templateName = 'counter_call'; // From provided curl
        $recipientNumber = '7870359434'; // From user request

        $url = 'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/';

        $payload = [
            "integrated_number" => $integratedNumber,
            "content_type" => "template",
            "payload" => [
                "messaging_product" => "whatsapp",
                "type" => "template",
                "template" => [
                    "name" => $templateName,
                    "language" => [
                        "code" => "en",
                        "policy" => "deterministic"
                    ],
                    "namespace" => $namespace,
                    "to_and_components" => [
                        [
                            "to" => [
                                $recipientNumber
                            ],
                            "components" => [
                                "body_1" => [
                                    "type" => "text",
                                    "value" => "value1"
                                ],
                                "body_2" => [
                                    "type" => "text",
                                    "value" => "value1"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'authkey' => $authKey,
            ])->post($url, $payload);

            if ($response->successful()) {
                $this->info('Message sent successfully!');
                $this->info('Response: ' . $response->body());
                Log::info('MSG91 Test Success: ' . $response->body());
            } else {
                $this->error('Failed to send message.');
                $this->error('Status: ' . $response->status());
                $this->error('Response: ' . $response->body());
                Log::error('MSG91 Test Failed: ' . $response->body());
            }

        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            Log::error('MSG91 Test Exception: ' . $e->getMessage());
        }
    }
}
