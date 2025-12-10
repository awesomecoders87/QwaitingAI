<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.custom-layout')]
class BookingChatbot extends Component
{
    public $messages = [];
    public $currentMessage = '';
    public $isLoading = false;
    public $teamId = 3;
    public $locationId = 80;
    public $name = '';
    public $phone = '';
    public $email = '';
    public $showUserInfoForm = false;
    public $bookingConfirmed = false;
    public $bookingDetails = null;

    public function mount()
    {
        // Get team_id dynamically from tenant, fallback to 3
        $this->teamId = tenant('id') ?? 3;
        
        // Get location_id dynamically from session, fallback to 80
        $this->locationId = Session::get('selectedLocation', 80);
        
        // Ensure location_id is an integer
        $this->locationId = (int) $this->locationId ?: 80;
        
        // Add welcome message
        $this->addMessage('ai', "👋 Hello! I'm your booking assistant. I can help you book appointments.\n\nJust tell me what you need, for example:\n• 'I want to book dental service on December 11 at 4pm'\n• 'Book haircut for tomorrow morning'\n\nHow can I help you today?");
    }

    public function sendMessage()
    {
        if (empty(trim($this->currentMessage)) || $this->isLoading) {
            return;
        }

        $message = trim($this->currentMessage);
        $this->addMessage('user', $message);
        $this->currentMessage = '';
        $this->isLoading = true;

        // Call the chatbot API - try direct method call first, fallback to HTTP
        try {
            Log::info('Sending chatbot request', [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'message' => $message
            ]);
            
            // Try direct method call (more reliable for same-app calls)
            try {
                $controller = new ServiceController();
                $request = new Request([
                    'message' => $message,
                    'team_id' => $this->teamId,
                    'location_id' => $this->locationId,
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'email' => $this->email,
                    'phone_code' => '91'
                ]);
                
                $response = $controller->chatbotBook($request);
                $data = json_decode($response->getContent(), true);
                
            } catch (\Exception $directError) {
                // Fallback to HTTP call
                Log::warning('Direct call failed, trying HTTP', ['error' => $directError->getMessage()]);
                
                $apiUrl = '/api/chatbot-book';
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post(url($apiUrl), [
                        'message' => $message,
                        'team_id' => $this->teamId,
                        'location_id' => $this->locationId,
                        'name' => $this->name,
                        'phone' => $this->phone,
                        'email' => $this->email,
                        'phone_code' => '91'
                    ]);

                if (!$response->successful()) {
                    $statusCode = $response->status();
                    $errorData = $response->json();
                    $errorBody = $response->body();
                    
                    Log::error('Chatbot API Error Response', [
                        'status' => $statusCode,
                        'body' => $errorBody,
                        'json' => $errorData
                    ]);
                    
                    throw new \Exception($errorData['message'] ?? "API returned status {$statusCode}");
                }
                
                $data = $response->json();
            }

            $this->isLoading = false;

            // Handle response
            if (isset($data['status']) && $data['status'] === 'success') {
                // Check if booking was confirmed
                if (isset($data['booking'])) {
                    $this->bookingConfirmed = true;
                    $this->bookingDetails = $data['booking'];
                    $this->addMessage('ai', $data['chatbot_response'] ?? $data['message']);
                } else {
                    // Service found or date available - continue conversation
                    $this->addMessage('ai', $data['chatbot_response'] ?? $data['message']);
                }
            } else {
                // Error response
                $this->addMessage('ai', $data['chatbot_response'] ?? $data['message'] ?? 'Sorry, I encountered an error. Please try again.');
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->isLoading = false;
            Log::error('Chatbot API Connection Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addMessage('ai', "I'm having trouble connecting to the server. Please check your internet connection and try again.");
        } catch (\Exception $e) {
            $this->isLoading = false;
            Log::error('Chatbot API Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addMessage('ai', "I'm sorry, but I encountered an error. Please try again. Error: " . substr($e->getMessage(), 0, 100));
        }
    }

    public function addMessage($role, $content)
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->format('H:i')
        ];
        
        // Auto-scroll to bottom
        $this->dispatch('scroll-to-bottom');
    }

    public function startNewChat()
    {
        $this->messages = [];
        $this->bookingConfirmed = false;
        $this->bookingDetails = null;
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->showUserInfoForm = false;
        $this->mount();
    }

    public function render()
    {
        return view('livewire.booking-chatbot');
    }
}

