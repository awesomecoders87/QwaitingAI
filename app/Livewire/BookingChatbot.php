<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\AiActivityLog;
use App\Ai\Agents\BookingAgent;
use Carbon\Carbon;
use Laravel\Reverb\Loggers\Log;

class BookingChatbot extends Component
{
    public $messages = [];
    public $userInput = '';
    public $isOpen = false;
    public $isAiTyping = false;
    
    // Quick Questions
    public $quickQuestions = [
        'What services do you offer?',
        'I want to book an appointment',
        'Cancel my booking',
        'Reschedule my booking',
        'Check availability for today'
    ];

    public function mount()
    {
        // Initial AI message
        $this->messages[] = [
            'role' => 'assistant',
            'content' => "Hi! I'm your booking assistant. I can help you book, reschedule, or cancel your appointments. How can I assist you today?",
            'time' => now()->format('h:i A')
        ];
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendQuickQuestion($question)
    {
        $this->userInput = $question;
        $this->sendMessage();
    }

    public function sendMessage()
    {
        if (trim($this->userInput) === '') return;

        $userText = $this->userInput;
        $this->messages[] = [
            'role' => 'user', 
            'content' => $userText,
            'time' => now()->format('h:i A')
        ];
        
        $this->userInput = '';
        $this->isAiTyping = true;
        
        // Dispatch event to instantly render the user's message to the UI, 
        // then the browser will immediately trigger processAgentTurn in the background
        $this->dispatch('process-ai-turn');
    }
    
    #[On('process-ai-turn')]
    public function processAgentTurn() 
    {
        $teamId = tenant('id') ?? 3;
        $locationId = session('selectedLocation') ?? 80;

        $agent = new BookingAgent($teamId, $locationId);
        
        // Strip times and metadata from messages to send to OpenAI
        $apiMessages = [];
        foreach ($this->messages as $msg) {
            if (empty($msg['is_hidden'])) {
                $apiMessages[] = [
                    'role' => $msg['role'], 
                    'content' => $msg['content']
                ];
            } else {
                $apiMessages[] = [
                    'role' => 'system', // Feed hidden API responses as system messages
                    'content' => $msg['content']
                ];
            }
        }
        
        $response = $agent->run($apiMessages);

        if ($response['type'] === 'gpt_response') {
            $data = $response['message'];
            
            \Log::info('--- AI WORKFLOW STEP ---');
            \Log::info('AI JSON Response:', $data);
            
            $reply = $data['reply'] ?? null;
            $action = $data['action'] ?? 'none';
            $apiParams = $data['data'] ?? [];
            
            // Show the conversational reply first
            if (!empty($reply)) {
                $this->messages[] = [
                    'role' => 'assistant', 
                    'content' => $reply,
                    'time' => now()->format('h:i A')
                ];
                $this->storeAiActivityLog($apiMessages[count($apiMessages)-1]['content'], json_encode($data), $teamId, $locationId, $response['usage']);
            }
            
            // Handle Action
            if ($action !== 'none') {
                \Log::info("Executing Action: {$action}", ['params' => $apiParams]);
                $apiResult = $this->executeAction($action, $apiParams);
                \Log::info("Action Result ({$action}):", ['result' => $apiResult]);
                
                // Add API result to history so the agent knows what happened (hidden from UI)
                $this->messages[] = [
                    'role' => 'system',
                    'content' => "System Event - API Response for '{$action}': \n" . json_encode($apiResult),
                    'time' => now()->format('h:i A'),
                    'is_hidden' => true
                ];
                
                // Re-run agent so it answers based on API result in a new request
                // this prevents timeouts and updates UI with the loading state again
                $this->dispatch('process-ai-turn');
                return; // Keep isAiTyping true since we are pinging again
            }
        } else {
            $this->messages[] = [
                'role' => 'assistant', 
                'content' => "I'm sorry, I'm having trouble connecting right now.",
                'time' => now()->format('h:i A')
            ];
        }
        
        // Only set false when the entire AI loop finishes and responds to the user
        $this->isAiTyping = false;
    }
    
    protected function executeAction($action, $params)
    {
        Log::info("Executing Action: {$action} | Params: " . json_encode($params));
        $uri = '';
        $method = 'POST';
    
        switch($action) {
            case 'check_service':
                $uri = '/api/check-service'; $method = 'GET';
                break;
            case 'get_dates':
                $uri = '/api/get-available-dates';
                break;
            case 'get_times':
                $uri = '/api/get-available-times';
                break;
            case 'check_availability':
                $uri = '/api/check-datetime-availability';
                break;
            case 'create_booking':
                $uri = '/api/check-and-book';
                break;
            case 'get_booking':
                $uri = '/api/get-booking-details';
                break;
            case 'reschedule_booking':
                $uri = '/api/edit-booking';
                break;
            case 'cancel_booking':
                $uri = '/api/cancel-booking';
                break;
            default:
                return null;
        }
    
        try {
            // Using the requested staging URL as the base domain instead of local url() helper
            $baseUrl = 'https://qwaiting-ai.thevistiq.com';
            $url = $baseUrl . $uri;
            
            if ($method === 'GET') {
                $response = \Illuminate\Support\Facades\Http::get($url, $params);
            } else {
                $response = \Illuminate\Support\Facades\Http::post($url, $params);
            }
            return $response->json();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Store Token usage into AiActivityLog
     */
    private function storeAiActivityLog($prompt, $response, $teamId, $locationId, $usage)
    {
        try {
            $promptTokens = $usage->promptTokens ?? 0;
            $completionTokens = $usage->completionTokens ?? 0;
            $totalTokens = $usage->totalTokens ?? 0;
            
            // Example credit consumption calculation
            $creditsConsumed = ($totalTokens / 1000) * 0.001; 
            
            AiActivityLog::create([
                'team_id' => $teamId,
                'location_id' => $locationId,
                'chatbot_name' => 'BookingAgent_Livewire',
                'type' => 'booking_assistant',
                'prompt' => $prompt,
                'response' => $response,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'credits_consumed' => $creditsConsumed,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log AI Activity: ' . $e->getMessage());
        }
    }

    public function handleVoiceInput($transcribedText)
    {
        $this->userInput = $transcribedText;
        $this->sendMessage();
    }

    public function render()
    {
        return view('livewire.booking-chatbot');
    }
}
