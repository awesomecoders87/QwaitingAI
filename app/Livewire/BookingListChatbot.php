<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use App\Ai\Agents\BookingListAssistant;
use App\Models\SiteDetail;
use App\Models\AiActivityLog;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BookingListChatbot extends Component
{
    public array  $messages   = [];
    public string $chatInput  = '';
    public bool   $isLoading  = false;
    public bool   $isChatOpen = false;

    #[Locked]
    public int $teamId     = 0;

    #[Locked]
    public int $locationId = 0;

    // ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->teamId     = (int) tenant('id');
        $this->locationId = (int) Session::get('selectedLocation');

        $tz = SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->locationId)->value('select_timezone') ?? config('app.timezone', 'UTC');

        $this->messages[] = [
            'role'    => 'assistant',
            'content' => "Hi! Ask me anything about your bookings — counts, trends, available slots, cancellations, and more. I'm now powered by our advanced AI Agent architecture!",
            'time'    => now()->timezone($tz)->format('h:i A'),
        ];
    }

    // ─────────────────────────────────────────────────────────────

    public function sendMessage(): void
    {
        $userInput = trim($this->chatInput);
        if (empty($userInput)) return;

        if (!$this->teamId)     $this->teamId     = (int) tenant('id');
        if (!$this->locationId) $this->locationId = (int) Session::get('selectedLocation');

        $tz = SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->locationId)->value('select_timezone') ?? config('app.timezone', 'UTC');

        $this->messages[] = [
            'role'    => 'user',
            'content' => $userInput,
            'time'    => now()->timezone($tz)->format('h:i A'),
        ];

        $this->chatInput = '';
        $this->isLoading = true;

        $this->dispatch('booking-chat-updated');
        $this->dispatch('trigger-ai-response');
    }

    #[On('trigger-ai-response')]
    public function processAiResponse(): void
    {
        $lastUserMsg = null;
        foreach (array_reverse($this->messages) as $msg) {
            if ($msg['role'] === 'user') {
                $lastUserMsg = $msg['content'];
                break;
            }
        }

        if (!$lastUserMsg) {
            $this->isLoading = false;
            return;
        }

        // Log::info('[BookingListChatbot] Starting AI Processing for user text: ' . $lastUserMsg);

        try {
            $agent = new BookingListAssistant($this->teamId, $this->locationId, auth()->user());
            
            // Limit history to last 6 messages to save tokens
            $history = [];
            $recentMessages = array_slice($this->messages, -7); // Includes the current user message
            
            foreach ($recentMessages as $msg) {
                // Only include user and assistant messages in history
                if (in_array($msg['role'], ['user', 'assistant'])) {
                    $history[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            }

            $response = $this->callOpenAiWithTools($agent, $history);
            
            // Log::info('[BookingListChatbot] OpenAI Response Success: ' . ($response['success'] ? 'Yes' : 'No'), ['reason' => $response['message'] ?? '']);

            $reply = $response['success'] ? $response['message'] : 'Sorry, I encountered an error. Please try again.';

            if ($response['success']) {
                $this->logActivity($lastUserMsg, $reply, $response['usage'] ?? null);
            }

        } catch (\Throwable $e) {
            Log::error('[BookingListChatbot] Error: ' . $e->getMessage(), [
                'teamId'     => $this->teamId,
                'locationId' => $this->locationId,
                'trace'      => $e->getTraceAsString(),
            ]);
            $reply = 'Sorry, I encountered an error. Please contact support.';
        }

        $tz = SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->locationId)->value('select_timezone') ?? config('app.timezone', 'UTC');

        $this->messages[] = [
            'role'    => 'assistant',
            'content' => $reply,
            'time'    => now()->timezone($tz)->format('h:i A'),
        ];

        $this->isLoading = false;
        $this->dispatch('booking-chat-updated');
    }

    /**
     * Send a predefined chip query directly.
     */
    public function sendQuickQuery(string $query): void
    {
        $this->chatInput = $query;
        $this->sendMessage();
    }

    // ─────────────────────────────────────────────────────────────
    // AI SDK Execution Loop
    // ─────────────────────────────────────────────────────────────

    protected function callOpenAiWithTools(BookingListAssistant $agent, array $messages): array
    {
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) throw new \Exception('OpenAI API key not configured');

        // Prepare context
        $apiMessages = [
            ['role' => 'system', 'content' => $agent->instructions()]
        ];
        
        foreach ($messages as $msg) {
            $apiMessages[] = $msg;
        }

        // We need to properly generate definitions for OpenAI
        $toolsDefinition = $this->getToolDefinitions($agent);

        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => $apiMessages,
            'tools' => $toolsDefinition,
            'tool_choice' => 'auto',
            'temperature' => 0.3,
            'max_tokens' => 1000,
        ];

        // Log::info('[BookingListChatbot] Sending context to OpenAI API', ['payload_size' => strlen(json_encode($payload))]);

        $response = Http::withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/chat/completions', $payload);

        if (!$response->successful()) {
            Log::error('[BookingListChatbot] OpenAI API failed: ' . $response->body());
            return ['success' => false, 'message' => 'API error. Failed to reach OpenAI.'];
        }

        $data = $response->json();
        $message = $data['choices'][0]['message'] ?? null;

        if (!empty($message['tool_calls'])) {
            Log::info('[BookingListChatbot] Tool calls requested by OpenAI', ['count' => count($message['tool_calls'])]);
            return $this->handleToolCalls($agent, $message['tool_calls'], $apiMessages, $apiKey);
        }

        // Log::info('[BookingListChatbot] Standard completion received without tools');
        
        return [
            'success' => true,
            'message' => $message['content'] ?? 'I cannot answer that right now.',
            'usage' => $data['usage'] ?? null,
        ];
    }

    protected function handleToolCalls(BookingListAssistant $agent, array $toolCalls, array $apiMessages, string $apiKey): array
    {
        // Add the tool calls from the assistant
        $apiMessages[] = [
            'role' => 'assistant',
            'tool_calls' => $toolCalls,
        ];

        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'] ?? '';
            $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true);
            $toolCallId = $toolCall['id'] ?? '';

            // Log::info("[BookingListChatbot] Executing tool locally: {$functionName}", ['arguments' => $arguments]);

            $result = $this->executeTool($agent, $functionName, $arguments);

            // Log::info("[BookingListChatbot] Finished tool {$functionName}, result length: " . strlen($result));

            $apiMessages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCallId,
                'content' => $result,
            ];
        }

        // Log::info('[BookingListChatbot] Sending tool results back to OpenAI');

        // Send the tool results back to get the final response
        $response = Http::withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => $apiMessages,
            'temperature' => 0.3,
            'max_tokens' => 1000,
        ]);

        if (!$response->successful()) {
            Log::error('[BookingListChatbot] OpenAI API failed on returning tools: ' . $response->body());
            return ['success' => false, 'message' => 'Tool handling error.'];
        }

        $data = $response->json();
        
        // Log::info('[BookingListChatbot] Final completion received after tools');
        
        return [
            'success' => true,
            'message' => $data['choices'][0]['message']['content'] ?? 'Error returning response.',
            'usage' => $data['usage'] ?? null,
        ];
    }

    protected function executeTool(BookingListAssistant $agent, string $name, array $arguments): string
    {
        foreach ($agent->tools() as $tool) {
            if (class_basename($tool) === $name) {
                try {
                    $request = new \Laravel\Ai\Tools\Request($arguments);
                    return (string) $tool->handle($request);
                } catch (\Exception $e) {
                    return json_encode(['error' => $e->getMessage()]);
                }
            }
        }
        return json_encode(['error' => "Unknown tool: {$name}"]);
    }

    protected function getToolDefinitions(BookingListAssistant $agent): array
    {
        $defs = [];
        foreach ($agent->tools() as $tool) {
            $name = class_basename($tool);
            $desc = (string) $tool->description();
            
            // Map schemas carefully to OpenAI specs
            if ($name === 'AnalyzeBookingsTool') {
                $parameters = [
                    'type' => 'object',
                    'properties' => [
                        'action' => ['type' => 'string', 'enum' => ['count', 'list']],
                        'status' => ['type' => 'string'],
                        'is_checkin' => ['type' => 'boolean'],
                        'date_from' => ['type' => 'string'],
                        'date_to' => ['type' => 'string'],
                        'service_name' => ['type' => 'string'],
                        'group_by' => ['type' => 'string', 'enum' => ['status', 'date', 'month', 'week', 'year', 'service']]
                    ]
                ];
            } else if ($name === 'GetAvailableDatesTool') {
                $parameters = [
                    'type' => 'object',
                    'properties' => ['service_name' => ['type' => 'string']],
                    'required' => ['service_name']
                ];
            } else if ($name === 'GetAvailableTimesTool') {
                $parameters = [
                    'type' => 'object',
                    'properties' => ['service_name' => ['type' => 'string'], 'date' => ['type' => 'string']],
                    'required' => ['service_name', 'date']
                ];
            } else if ($name === 'CheckDatetimeAvailabilityTool') {
                $parameters = [
                    'type' => 'object',
                    'properties' => ['service_name' => ['type' => 'string'], 'date' => ['type' => 'string'], 'time' => ['type' => 'string']],
                    'required' => ['service_name', 'date', 'time']
                ];
            } else {
                $parameters = ['type' => 'object', 'properties' => (object)[]]; // Fallback
            }

            $defs[] = [
                'type' => 'function',
                'function' => [
                    'name' => $name,
                    'description' => $desc,
                    'parameters' => $parameters
                ]
            ];
        }
        return $defs;
    }

    protected function logActivity(string $prompt, string $response, ?array $usage): void
    {
        try {
            $promptTokens = $usage['prompt_tokens'] ?? 0;
            $completionTokens = $usage['completion_tokens'] ?? 0;
            $totalTokens = $usage['total_tokens'] ?? 0;
            $creditsConsumed = ($totalTokens / 1000) * 0.001;

            AiActivityLog::create([
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'chatbot_name' => 'BookingListChatbot',
                'type' => 'booking_list_assistant',
                'prompt' => $prompt,
                'response' => $response,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'credits_consumed' => $creditsConsumed,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI Activity: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.booking-list-chatbot');
    }
}
