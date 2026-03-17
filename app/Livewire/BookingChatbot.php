<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Ai\Agents\AppointmentAssistant;
use App\Models\AiActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingChatbot extends Component
{
    public $messages = [];
    public $userInput = '';
    public $isOpen = false;
    public $isAiTyping = false;
    public $sessionId;

    /**
     * Selectable options rendered as clickable cards in the UI
     */
    public $workflowOptions = [];

    /**
     * Current workflow step context
     */
    public $workflowStep = '';

    /**
     * Tracks booking context across interactions
     */
    public $bookingContext = [
        'service_name' => '',
        'date' => '',
        'time' => '',
        'name' => '',
        'phone' => '',
        'email' => '',
        'booking_refID' => '',
    ];

    // Quick Questions for new users
    public $quickQuestions = [
        'What services do you offer?',
        'I want to book an appointment',
        'Cancel my booking',
        'Reschedule my booking',
    ];

    public function mount()
    {
        // Generate unique session ID for conversation tracking
        $this->sessionId = session()->getId() . '_' . Str::random(8);
        
        // Initial AI greeting
        $this->messages[] = [
            'role' => 'assistant',
            'content' => "Hi! I'm your AI booking assistant. I can help you book, reschedule, or cancel appointments. How can I assist you today?",
            'time' => now()->format('h:i A'),
        ];
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen) {
            $this->dispatch('booking-chat-updated');
        }
    }

    public function sendQuickQuestion($question)
    {
        $this->userInput = $question;
        $this->sendMessage();
    }

    /**
     * Handle option card clicks - extract data and send to AI
     */
    public function selectOption($value)
    {
        // Clear options
        $this->workflowOptions = [];
        $this->workflowStep = '';

        // Add as user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $value,
            'time' => now()->format('h:i A'),
        ];

        $this->isAiTyping = true;
        $this->dispatch('booking-chat-updated');

        // Process through AI
        $this->processAiTurn();
    }

    public function sendMessage()
    {
        Log::info('[Chatbot] sendMessage called', ['input' => $this->userInput]);
        
        if (trim($this->userInput) === '') {
            Log::warning('[Chatbot] Empty input detected');
            return;
        }

        // Clear any pending options when user types manually
        $this->workflowOptions = [];
        $this->workflowStep = '';

        $userText = $this->userInput;
        Log::info('[Chatbot] Adding user message to history', ['message' => $userText]);
        
        $this->messages[] = [
            'role' => 'user',
            'content' => $userText,
            'time' => now()->format('h:i A'),
        ];

        $this->userInput = '';
        $this->isAiTyping = true;

        Log::info('[Chatbot] Dispatching booking-chat-updated event');
        $this->dispatch('booking-chat-updated');
        
        Log::info('[Chatbot] Calling processAiTurn');
        $this->processAiTurn();
    }

    /**
     * Main AI processing method - integrates with Laravel AI SDK
     */
    protected function processAiTurn(): void
    {
        Log::info('[Chatbot] processAiTurn started');
        
        try {
            $user = auth()->user();
            Log::info('[Chatbot] User context', ['user_id' => $user?->id, 'has_user' => !!$user]);
            
            $agent = new AppointmentAssistant($user);

            // Get conversation history
            $history = [];
            if ($user) {
                $history = $agent->messages();
                Log::info('[Chatbot] Loaded conversation history', ['count' => count($history)]);
            }

            // Get last user message
            $lastUserMessage = '';
            foreach (array_reverse($this->messages) as $msg) {
                if ($msg['role'] === 'user') {
                    $lastUserMessage = $msg['content'];
                    break;
                }
            }

            Log::info('AI Chat Processing', [
                'session_id' => $this->sessionId,
                'user_id' => $user?->id,
                'message' => $lastUserMessage,
            ]);

            // Save user message to database
            Log::info('[Chatbot] Saving user message to DB');
            $this->saveMessageToDatabase('user', $lastUserMessage);

            // Call the AI Agent with OpenAI Function Calling
            Log::info('[Chatbot] Calling OpenAI with tools');
            $response = $this->callOpenAiWithTools($lastUserMessage, $history);
            Log::info('[Chatbot] OpenAI response received', ['success' => $response['success']]);

            if ($response['success']) {
                $aiMessage = $response['message'];
                Log::info('[Chatbot] Processing successful AI response', ['message_length' => strlen($aiMessage)]);
                
                // Store AI response
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $aiMessage,
                    'time' => now()->format('h:i A'),
                ];

                // Save AI response to database
                Log::info('[Chatbot] Saving AI response to DB');
                $this->saveMessageToDatabase('assistant', $aiMessage);

                // Extract options from response for clickable cards
                Log::info('[Chatbot] Extracting options from response');
                $this->extractOptionsFromResponse($aiMessage);

                // Log activity
                Log::info('[Chatbot] Logging activity');
                $this->logActivity($lastUserMessage, $aiMessage, $response['usage'] ?? null);

            } else {
                Log::error('[Chatbot] AI response failed', ['response' => $response]);
                $errorMessage = "I'm sorry, I'm having trouble processing your request. Please try again.";
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $errorMessage,
                    'time' => now()->format('h:i A'),
                ];
                $this->saveMessageToDatabase('assistant', $errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            $errorMessage = "I apologize, but I encountered an error. Please try again in a moment.";
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $errorMessage,
                'time' => now()->format('h:i A'),
            ];
            $this->saveMessageToDatabase('assistant', $errorMessage);
        }

        $this->isAiTyping = false;
        Log::info('[Chatbot] Process completed, dispatching update');
        $this->dispatch('booking-chat-updated');
    }

    /**
     * Save message to database for conversation history
     */
    protected function saveMessageToDatabase(string $role, string $content, ?string $toolName = null, ?array $toolArgs = null): void
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('ai_conversations')) {
                return;
            }

            $user = auth()->user();
            
            // Find or create conversation
            $conversation = DB::table('ai_conversations')
                ->where(function ($q) use ($user) {
                    if ($user) {
                        $q->where('user_id', $user->id);
                    } else {
                        $q->where('session_id', session()->getId());
                    }
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$conversation) {
                $conversationId = DB::table('ai_conversations')->insertGetId([
                    'user_id' => $user?->id,
                    'session_id' => session()->getId(),
                    'model' => 'gpt-4o-mini',
                    'driver' => 'openai',
                    'agent_name' => 'AppointmentAssistant',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $conversationId = $conversation->id;
                DB::table('ai_conversations')
                    ->where('id', $conversationId)
                    ->update(['updated_at' => now()]);
            }

            // Insert message
            DB::table('ai_messages')->insert([
                'conversation_id' => $conversationId,
                'role' => $role,
                'content' => $content,
                'tool_name' => $toolName,
                'tool_arguments' => $toolArgs ? json_encode($toolArgs) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save message to database: ' . $e->getMessage());
        }
    }

    /**
     * Call OpenAI with tool definitions (Function Calling)
     */
    protected function callOpenAiWithTools(string $userMessage, array $history): array
    {
        Log::info('[OpenAI] Starting OpenAI call', ['userMessage' => substr($userMessage, 0, 50)]);
        
        try {
            $apiKey = config('services.openai.api_key');
            if (empty($apiKey)) {
                Log::error('[OpenAI] API key not configured');
                throw new \Exception('OpenAI API key not configured');
            }
            Log::info('[OpenAI] API key found, building messages');

            // Build messages array
            $messages = [
                [
                    'role' => 'system',
                    'content' => $this->buildSystemPrompt(),
                ]
            ];

            // Add conversation history
            foreach ($history as $msg) {
                if ($msg instanceof \Laravel\Ai\Messages\Message) {
                    $messages[] = [
                        'role' => $msg->role instanceof \Laravel\Ai\Messages\MessageRole ? $msg->role->value : $msg->role,
                        'content' => $msg->content,
                    ];
                }
            }
            Log::info('[OpenAI] Added history messages', ['count' => count($messages)]);

            // Add current conversation from this session
            foreach ($this->messages as $msg) {
                if (empty($msg['is_hidden'])) {
                    $messages[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content'],
                    ];
                }
            }
            Log::info('[OpenAI] Total messages to send', ['count' => count($messages)]);

            // Define tools for function calling
            $tools = $this->getToolDefinitions();
            Log::info('[OpenAI] Tool definitions prepared', ['tool_count' => count($tools)]);

            $payload = [
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
                'tools' => $tools,
                'tool_choice' => 'auto',
                'temperature' => 0.3,
                'max_tokens' => 1000,
            ];

            Log::info('[OpenAI] Sending request to OpenAI API');
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if (!$response->successful()) {
                Log::error('[OpenAI] API Error: ' . $response->body());
                return ['success' => false, 'message' => 'API error'];
            }

            $data = $response->json();
            Log::info('[OpenAI] Raw API response received', ['usage' => $data['usage'] ?? null]);
            
            $choice = $data['choices'][0] ?? null;
            $message = $choice['message'] ?? null;

            // Handle tool calls
            if (!empty($message['tool_calls'])) {
                Log::info('[OpenAI] Tool calls detected', ['count' => count($message['tool_calls'])]);
                return $this->handleToolCalls($message['tool_calls'], $messages, $apiKey);
            }

            // Regular response
            Log::info('[OpenAI] Returning regular response');
            return [
                'success' => true,
                'message' => $message['content'] ?? 'I apologize, I could not process that.',
                'usage' => $data['usage'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('[OpenAI] Call Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle tool/function calls from OpenAI
     */
    protected function handleToolCalls(array $toolCalls, array $messages, string $apiKey): array
    {
        Log::info('[ToolHandler] Starting tool call handling', ['tool_count' => count($toolCalls)]);
        
        // Add assistant message with tool calls
        $messages[] = [
            'role' => 'assistant',
            'tool_calls' => $toolCalls,
        ];

        // Execute each tool
        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'] ?? '';
            $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true);
            $toolCallId = $toolCall['id'] ?? '';

            Log::info('[Tool Call] Executing tool', [
                'tool'      => $functionName,
                'arguments' => $arguments,
            ]);

            // Execute the tool
            Log::info('[ToolExecutor] About to execute ' . $functionName);
            $result = $this->executeTool($functionName, $arguments);

            Log::info('[Tool Result] Raw response from ' . $functionName, [
                'result' => $result,
                'result_length' => strlen($result),
            ]);

            // Save tool result to database only (NOT the internal "Calling tool" message)
            Log::info('[ToolHandler] Saving tool result to DB');
            $this->saveMessageToDatabase('tool', $result, $functionName);

            // Add tool response
            $messages[] = [
                'role'         => 'tool',
                'tool_call_id' => $toolCallId,
                'content'      => $result,
            ];
        }

        // Get final response from OpenAI
        Log::info('[OpenAI] Sending tool results back to OpenAI for final response');

        $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => 'gpt-4o-mini',
                'messages'    => $messages,
                'temperature' => 0.3,
                'max_tokens'  => 1000,
            ]);

        if (!$response->successful()) {
            Log::error('[OpenAI] Final response failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'message' => 'Tool handling error'];
        }

        $data    = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? 'I apologize, I could not complete that action.';

        Log::info('[OpenAI] Final AI response received', [
            'message' => $content,
            'usage'   => $data['usage'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => $content,
            'usage'   => $data['usage'] ?? null,
        ];
    }

    /**
     * Execute a tool by name
     */
    protected function executeTool(string $name, array $arguments): string
    {
        Log::info('[ExecuteTool] Executing tool: ' . $name, ['arguments' => $arguments]);
        
        $toolMap = [
            'check_services' => \App\Ai\Tools\CheckServicesTool::class,
            'get_available_dates' => \App\Ai\Tools\GetAvailableDatesTool::class,
            'get_available_times' => \App\Ai\Tools\GetAvailableTimesTool::class,
            'check_datetime_availability' => \App\Ai\Tools\CheckDatetimeAvailabilityTool::class,
            'book_appointment' => \App\Ai\Tools\BookAppointmentTool::class,
            'get_booking_details' => \App\Ai\Tools\GetBookingDetailsTool::class,
            'reschedule_appointment' => \App\Ai\Tools\RescheduleAppointmentTool::class,
            'cancel_appointment' => \App\Ai\Tools\CancelAppointmentTool::class,
        ];

        if (!isset($toolMap[$name])) {
            Log::error('[ExecuteTool] Unknown tool: ' . $name);
            return json_encode(['error' => "Unknown tool: {$name}"]);
        }

        try {
            $tool = new $toolMap[$name]();
            $request = new \Laravel\Ai\Tools\Request($arguments);
            Log::info('[ExecuteTool] Calling tool handle method');
            $result = $tool->handle($request);
            Log::info('[ExecuteTool] Tool executed successfully', ['result_type' => gettype($result)]);
            return (string) $result;
        } catch (\Exception $e) {
            Log::error("[ExecuteTool] Tool execution error for {$name}: " . $e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Build system prompt with instructions
     */
    protected function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an AI Appointment Booking Assistant. Your ONLY job is to help users book, reschedule, cancel, or check appointments.

## STRICT SCOPE RULE
You ONLY respond to appointment-related requests. If the user asks ANYTHING unrelated (tech questions, general knowledge, jokes, weather, coding, etc.), politely let them know you can only assist with appointments and redirect them back to booking-related topics. Do NOT provide answers to off-topic questions.

## YOUR CAPABILITIES (8 Tools Available)
You can call these tools to perform actions:
1. check_services - List all available services
2. get_available_dates - Get dates for a service (needs: service_name)
3. get_available_times - Get times for a date (needs: service_name, date)
4. check_datetime_availability - Verify slot availability (needs: service_name, date, time)
5. book_appointment - Create booking (needs: service_name, appointment_date, time, name, phone, email)
6. get_booking_details - Lookup booking (needs: booking_refID)
7. reschedule_appointment - Change booking (needs: booking_refID, service_name, date, time)
8. cancel_appointment - Cancel booking (needs: booking_refID)

## BOOKING FLOW (MUST FOLLOW)
1. Call check_services → Show list → Ask user to pick
2. Call get_available_dates with selected service → Show dates → Ask user to pick
3. Call get_available_times with service+date → Show times → Ask user to pick
4. Call check_datetime_availability → Confirm slot is available
5. Collect details ONE BY ONE: name → phone → email
6. Show summary, ask "Type YES to confirm or NO to cancel"
7. If YES, call book_appointment

## RESCHEDULE FLOW
1. Ask for booking_refID
2. Call get_booking_details → Show current details
3. Ask what to change
4. Get new service/date/time as needed
5. Show summary, ask for confirmation
6. If YES, call reschedule_appointment

## CANCEL FLOW
1. Ask for booking_refID
2. Call get_booking_details → Show details
3. Ask "Type YES to confirm cancellation or NO to keep"
4. If YES, call cancel_appointment

## CRITICAL RULES
- NEVER skip steps in booking flow
- ALWAYS use tools to fetch data - never make up services, dates, or times
- ALWAYS confirm before booking, rescheduling, or canceling
- Ask ONE question at a time
- Format lists with "- " prefix for each item
- Be friendly and professional
PROMPT;
    }

    /**
     * Get tool definitions for OpenAI function calling
     */
    protected function getToolDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_services',
                    'description' => 'List all available services for booking',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_available_dates',
                    'description' => 'Get available dates for a specific service',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'service_name' => [
                                'type' => 'string',
                                'description' => 'The service name selected by user',
                            ],
                        ],
                        'required' => ['service_name'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_available_times',
                    'description' => 'Get available time slots for a service on a date',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'service_name' => [
                                'type' => 'string',
                                'description' => 'The service name',
                            ],
                            'date' => [
                                'type' => 'string',
                                'description' => 'Date in YYYY-MM-DD format',
                            ],
                        ],
                        'required' => ['service_name', 'date'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_datetime_availability',
                    'description' => 'Check if a specific date/time is available',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'service_name' => ['type' => 'string'],
                            'date' => ['type' => 'string'],
                            'time' => ['type' => 'string'],
                        ],
                        'required' => ['service_name', 'date', 'time'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'book_appointment',
                    'description' => 'Create a new booking appointment',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'service_name' => ['type' => 'string'],
                            'appointment_date' => ['type' => 'string'],
                            'time' => ['type' => 'string'],
                            'name' => ['type' => 'string'],
                            'phone' => ['type' => 'string'],
                            'email' => ['type' => 'string'],
                            'phone_code' => ['type' => 'string'],
                        ],
                        'required' => ['service_name', 'appointment_date', 'time', 'name', 'phone', 'email'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_booking_details',
                    'description' => 'Get details of an existing booking',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'booking_refID' => ['type' => 'string'],
                        ],
                        'required' => ['booking_refID'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'reschedule_appointment',
                    'description' => 'Reschedule an existing appointment',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'booking_refID' => ['type' => 'string'],
                            'service_name' => ['type' => 'string'],
                            'date' => ['type' => 'string'],
                            'time' => ['type' => 'string'],
                        ],
                        'required' => ['booking_refID', 'service_name', 'date', 'time'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'cancel_appointment',
                    'description' => 'Cancel an existing appointment',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'booking_refID' => ['type' => 'string'],
                        ],
                        'required' => ['booking_refID'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Extract clickable options from AI response
     */
    protected function extractOptionsFromResponse(string $message): void
    {
        $this->workflowOptions = [];
        $this->workflowStep = '';

        // Check for service list pattern
        if (preg_match('/available services?:(.+?)(?:\n\n|$)/is', $message, $matches)) {
            $lines = explode("\n", $matches[1]);
            $services = [];
            foreach ($lines as $line) {
                if (preg_match('/^[-*•]\s*(.+)$/', trim($line), $m)) {
                    $services[] = ['label' => $m[1], 'value' => $m[1]];
                }
            }
            if (!empty($services)) {
                $this->workflowStep = 'select_service';
                $this->workflowOptions = $services;
                return;
            }
        }

        // Check for date list pattern
        if (preg_match('/available dates?[^:]*:(.+?)(?:\n\n|$)/is', $message, $matches)) {
            $lines = explode("\n", $matches[1]);
            $dates = [];
            foreach ($lines as $line) {
                if (preg_match('/^[-*•]\s*(.+)$/', trim($line), $m)) {
                    $dates[] = ['label' => $m[1], 'value' => $m[1]];
                }
            }
            if (!empty($dates)) {
                $this->workflowStep = 'select_date';
                $this->workflowOptions = $dates;
                return;
            }
        }

        // Check for time list pattern
        if (preg_match('/available times?[^:]*:(.+?)(?:\n\n|$)/is', $message, $matches)) {
            $lines = explode("\n", $matches[1]);
            $times = [];
            foreach ($lines as $line) {
                if (preg_match('/^[-*•]\s*(.+)$/', trim($line), $m)) {
                    $times[] = ['label' => $m[1], 'value' => $m[1]];
                }
            }
            if (!empty($times)) {
                $this->workflowStep = 'select_time';
                $this->workflowOptions = $times;
                return;
            }
        }

        // Check for confirmation prompt
        if (stripos($message, 'type yes to confirm') !== false || 
            stripos($message, 'yes to confirm') !== false) {
            $this->workflowStep = 'confirm';
            $this->workflowOptions = [
                ['label' => '✅ YES — Confirm', 'value' => 'YES'],
                ['label' => '❌ NO — Cancel', 'value' => 'NO'],
            ];
        }
    }

    /**
     * Log AI activity for analytics
     */
    protected function logActivity(string $prompt, string $response, ?array $usage): void
    {
        try {
            $teamId = tenant('id') ?? 3;
            $locationId = session('selectedLocation') ?? 80;

            $promptTokens = $usage['prompt_tokens'] ?? 0;
            $completionTokens = $usage['completion_tokens'] ?? 0;
            $totalTokens = $usage['total_tokens'] ?? 0;
            $creditsConsumed = ($totalTokens / 1000) * 0.001;

            AiActivityLog::create([
                'team_id' => $teamId,
                'location_id' => $locationId,
                'chatbot_name' => 'AiBookingChatbot_LaravelAI',
                'type' => 'booking_assistant',
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

    public function render()
    {
        return view('livewire.booking-chatbot');
    }
}
