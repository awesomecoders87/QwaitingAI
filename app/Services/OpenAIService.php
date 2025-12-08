<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    /**
     * Get both AI reply and booking intent from a user message, project-specific.
     */
    public function chatbotReplyWithIntent($userMessage, $projectContext = null, $contextVars = [])
    {
        // Compose system prompt with project context
    $systemPrompt = $projectContext ?? "You are an expert AI assistant for a virtual queue and appointment booking system. Your job is to help users book appointments, answer questions about queue status, available services, agent details, and location information. Always keep responses relevant to these topics.\n\nWhen a user wants to book an appointment, extract these details if possible: service_type, preferred_date, preferred_time, name, phone, email.\n\nAlways reply conversationally, confirming what you understood and asking for any missing details. If the user’s message is not about booking, answer with information relevant to the queue, services, or agents.\n\nIf the user greets you, greet them back. If the user asks for help, explain your capabilities. If the user asks something unrelated, politely redirect to project topics.\n\nFor every user message, respond in this format:\n---\nReply: <your conversational reply>\nIntent: <JSON object with extracted fields, null for missing>\n---";

        $messages = [
            [ 'role' => 'system', 'content' => $systemPrompt ],
            [ 'role' => 'user', 'content' => $userMessage ]
        ];

        $response = $this->generateResponse($messages);
        \Log::info('OpenAI raw response', ['response' => $response, 'userMessage' => $userMessage]);
        if (!$response) {
            return [ 'reply' => null, 'intent' => null ];
        }

        // Parse response for reply and intent
        $reply = null;
        $intent = null;
        if (preg_match('/Reply:(.*)Intent:/s', $response, $matches)) {
            $reply = trim($matches[1]);
            $intentJson = trim(str_replace('---', '', substr($response, strpos($response, 'Intent:') + 7)));
            $intent = json_decode($intentJson, true);
        } else {
            $reply = $response;
        }
        return [ 'reply' => $reply, 'intent' => $intent ];
    }
    protected $apiKey;
    protected $model = 'gpt-4o-mini'; // Using faster, cheaper model
    protected $endpoint = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');        
    }

    public function generateResponse($messages, $systemPrompt = null)
    {
        try {
            $messageArray = [];

            // Add system prompt if provided
            if ($systemPrompt) {
                $messageArray[] = [
                    'role' => 'system',
                    'content' => $systemPrompt
                ];
            }

            // Add conversation messages
            foreach ($messages as $message) {
                $messageArray[] = [
                    'role' => $message['role'],
                    'content' => $message['content']
                ];
            }

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->endpoint, [
                'model' => $this->model,
                'messages' => $messageArray,
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI Service Error', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function parseBookingIntent($userMessage)
    {
        $systemPrompt = "You are a booking assistant. Extract booking-related information from the user's message. Return a JSON object with these fields if found: service_type, preferred_date, preferred_time, name, phone, email. Return null for missing fields.";

        $messages = [
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ];

        $response = $this->generateResponse($messages, $systemPrompt);
        return json_decode($response, true);
    }

    public function getBookingRecommendation($serviceType, $availableSlots)
    {
        $systemPrompt = "You are a booking assistant helping to find the best appointment slot. Consider the service type and available slots to make a recommendation.";

        $messages = [
            [
                'role' => 'user',
                'content' => json_encode([
                    'service_type' => $serviceType,
                    'available_slots' => $availableSlots
                ])
            ]
        ];

        return $this->generateResponse($messages, $systemPrompt);
        
    }
}