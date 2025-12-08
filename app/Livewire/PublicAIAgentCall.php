<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\VirtualQueue;
use App\Models\AISession;
use App\Models\VirtualQueueSetting;
use App\Models\SmsAPI;
use App\Models\Queue;
use App\Models\Category;
use App\Models\Level;
use App\Models\User;
use App\Models\Counter;
use App\Models\SiteDetail;
use App\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\OpenAIService;
use Livewire\Attributes\Layout;
use App\Models\Booking;
use App\Models\CustomSlot;
use App\Models\AccountSetting;
use Illuminate\Support\Facades\Log;


#[Layout('components.layouts.custom-layout')]
class PublicAIAgentCall extends Component
{
    public $virtualQueueId;
    public $virtualQueue;
    public $aiSession;
    public $settings;
    public $meetingLink;
    public $sessionId;
    public $status = 'connecting'; // connecting, connected, transferring, transferred, ended
    public $messages = [];
    public $currentMessage = '';
    public $showTransferButton = false;
    public $showNewChatButton = false;
    public $chatDisabled = false;
    public $transferReason = '';
    
    // Booking related properties
    public $bookingState = [
        'step' => 'initial',
        'service' => null,
        'date' => null,
        'time' => null,
        'name' => null,
        'phone' => null,
        'email' => null
    ];
    public $selectedService = null;
    public $selectedDate = null;
    public $selectedTime = null;
    public $availableSlots = [];
    protected $openai;
    protected $bookingContext = [];

    protected $listeners = ['messageReceived', 'aiResponseReceived', 'sessionEnded'];

    // public function boot()
    // {

    // }

    public function mount($virtualQueueId)
    {


        $this->openai = new OpenAIService();

        $decodedId = base64_decode($virtualQueueId);
        $this->virtualQueueId = $decodedId;
        $this->virtualQueue = VirtualQueue::findOrFail($decodedId);
        $this->settings = VirtualQueueSetting::getSettings($this->virtualQueue->team_id, $this->virtualQueue->location_id);
        // Generate session ID and meeting link
        $this->sessionId = Str::uuid()->toString();
        $this->meetingLink = $this->generateMeetingLink();

        // Create AI Session
        $this->aiSession = AISession::create([
            'team_id' => $this->virtualQueue->team_id,
            'virtual_queue_id' => $this->virtualQueue->id,
            'session_id' => $this->sessionId,
            'ai_model' => $this->settings->ai_model,
            'ai_voice' => $this->settings->ai_voice,
            'language' => $this->virtualQueue->selected_language,
            'avatar_url' => $this->settings->ai_avatar_url,
            'started_at' => now(),
        ]);

        // Update virtual queue
        $this->virtualQueue->update([
            'session_id' => $this->sessionId,
            'meeting_link' => $this->meetingLink,
            'status' => 'ai_connected',
            'connected_at' => now(),
        ]);

        // Send initial greeting
        $greeting = $this->settings->getDefaultGreeting($this->virtualQueue->selected_language);
        $this->addMessage('ai', $greeting);

        // Send meeting link via SMS/Email
        $this->sendMeetingLinkNotification();

        $this->status = 'connected';
    }

    protected function generateMeetingLink()
    {
        // Generate meeting link based on video provider
        switch ($this->settings->video_provider) {
            case 'twilio':
                return $this->generateTwilioLink();
            case 'agora':
                return $this->generateAgoraLink();
            case 'daily':
                return $this->generateDailyLink();
            default:
                return route('public.virtual-meeting', [
                    'room' => $this->sessionId,
                    'queueId' => $this->virtualQueue->id
                ]);
        }
    }

    protected function generateTwilioLink()
    {
        // Twilio Video room generation
        return route('public.virtual-meeting', [
            'room' => $this->sessionId,
            'queueId' => $this->virtualQueue->id
        ]);
    }

    protected function generateAgoraLink()
    {
        // Agora room generation
        return route('public.virtual-meeting', [
            'room' => $this->sessionId,
            'queueId' => $this->virtualQueue->id
        ]);
    }

    protected function generateDailyLink()
    {
        // Daily.co room generation
        return route('public.virtual-meeting', [
            'room' => $this->sessionId,
            'queueId' => $this->virtualQueue->id
        ]);
    }

    protected function sendMeetingLinkNotification()
    {
        $message = "Your virtual queue session is ready! Join here: {$this->meetingLink}";

        // Send SMS
        if ($this->settings->send_sms_notification && $this->virtualQueue->customer_phone) {
            SmsAPI::currentQueueSms(
                $this->virtualQueue->customer_phone,
                $message,
                $this->virtualQueue->team_id,
                'virtual_queue'
            );
        }

        // Send Email
        if ($this->settings->send_email_notification && $this->virtualQueue->customer_email) {
            // Email sending logic
        }

        // Send WhatsApp
        if ($this->settings->send_whatsapp_notification && $this->virtualQueue->customer_phone) {
            // WhatsApp sending logic
        }
    }

    public function sendMessage()
    {
        if (empty($this->currentMessage) || strlen($this->currentMessage) > 500) {
            return;
        } 

        if ($this->chatDisabled) {
            return;
        }

        // Ensure OpenAI service is initialized
        if (!$this->openai) {
            $this->openai = new OpenAIService();
        }

        // Add user message
        $this->addMessage('user', $this->currentMessage);

        // Store in AI session
        $this->aiSession->addMessage('user', $this->currentMessage);
        
        // Get real-time context data
        $contextData = $this->gatherContextData();
        
        // Add booking state to context if active
        if ($this->bookingState['step'] !== 'initial') {
            $contextData['booking_state'] = $this->bookingState;
        }
        
        // Build enhanced system prompt with real data
        $systemPrompt = $this->buildEnhancedSystemPrompt($contextData);
        
        // Get conversation history for context
        $conversationHistory = $this->buildConversationHistory();
        
        try {
            // Call OpenAI with full context
            $messages = array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $conversationHistory,
                [['role' => 'user', 'content' => $this->currentMessage]]
            );
            
            $aiResponse = $this->openai->generateResponse($messages);
            
            if (!$aiResponse) {
                Log::warning('OpenAI returned empty response', [
                    'message' => $this->currentMessage,
                    'user' => $this->virtualQueue->customer_name
                ]);
                $aiResponse = "I understand you'd like to help with that. Could you please provide more details? For appointments, I can help you book services. For queue information, I can check wait times and positions.";
            }
            
        } catch (\Throwable $e) {
            Log::error('OpenAI Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message' => $this->currentMessage
            ]);
            
            // Check if it's an API key issue
            if (stripos($e->getMessage(), 'api key') !== false || stripos($e->getMessage(), 'unauthorized') !== false) {
                $aiResponse = "I'm experiencing configuration issues. Please contact support or try speaking with a human agent.";
                $this->showTransferButton = true;
            } else {
                $aiResponse = "I'm here to help! You can ask me about:\n- Queue wait times\n- Available services\n- Booking appointments\n- Location and hours\n\nWhat would you like to know?";
            }
        }

        // Analyze sentiment
        $sentiment = $this->aiSession->updateSentiment($this->currentMessage);

        // Update booking state based on user's message and AI response
        $stateChanged = $this->updateBookingStateFromConversation($this->currentMessage, $aiResponse);
        
        // If booking state changed, show follow-up with real DB data instead of AI response
        if ($stateChanged) {
            $followUpResponse = $this->generateBookingFollowUp();
            if ($followUpResponse) {
                // Use the follow-up response with real data instead of AI's response
                $this->addMessage('ai', $followUpResponse);
                $this->aiSession->addMessage('assistant', $followUpResponse);
            } else {
                // If no follow-up, use AI's response
                $this->addMessage('ai', $aiResponse);
                $this->aiSession->addMessage('assistant', $aiResponse);
            }
        } else {
            // No state change, use AI's response normally
            $this->addMessage('ai', $aiResponse);
            $this->aiSession->addMessage('assistant', $aiResponse);
        }

        // Check if transfer is needed based on sentiment or request
        if ($this->shouldTransferToHuman($this->currentMessage, $sentiment)) {
            $this->showTransferButton = true;
        }

        $this->currentMessage = '';
    }

    protected function gatherContextData()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        return [
            'queue' => $this->getQueueData($teamId, $locationId),
            'services' => $this->getServicesData($teamId, $locationId),
            'agents' => $this->getAgentData($teamId, $locationId),
            'location' => $this->getLocationData($locationId),
            'statistics' => $this->getStatisticsData($teamId, $locationId),
            'customer' => [
                'name' => $this->virtualQueue->customer_name,
                'ticket' => $this->virtualQueue->ticket_number,
                'language' => $this->virtualQueue->selected_language
            ]
        ];
    }
    
    protected function buildEnhancedSystemPrompt($contextData)
    {
        $prompt = "You are an intelligent AI assistant for a virtual queue and appointment booking system. ";
        $prompt .= "You have access to real-time data about the business and should use it to provide accurate, helpful responses.\n\n";
        
        $prompt .= "**Current Context:**\n";
        $prompt .= "- Customer: {$contextData['customer']['name']} (Ticket: {$contextData['customer']['ticket']})\n";
        $prompt .= "- Language: {$contextData['customer']['language']}\n\n";
        
        // Queue information
        $prompt .= "**Queue Status:**\n";
        $prompt .= "- People waiting: {$contextData['queue']['waiting']}\n";
        $prompt .= "- Currently being served: {$contextData['queue']['serving']}\n";
        $prompt .= "- Average service time: {$contextData['queue']['avg_time']} minutes\n";
        $prompt .= "- Estimated wait: {$contextData['queue']['estimated_wait']} minutes\n\n";
        
        // Services
        if (!empty($contextData['services'])) {
            $prompt .= "**Available Services:**\n";
            foreach ($contextData['services'] as $service) {
                $prompt .= "- {$service['name']}";
                if ($service['duration']) $prompt .= " ({$service['duration']} min)";
                if ($service['price']) $prompt .= " - \${$service['price']}";
                $prompt .= "\n";
            }
            $prompt .= "\n";
        }
        
        // Staff Status
        $prompt .= "**Staff Status:**\n";
        $prompt .= "- Currently serving customers: {$contextData['agents']['busy']}\n";
        if ($contextData['agents']['busy'] > 0) {
            $prompt .= "- Additional staff: Available as needed\n";
        } else {
            $prompt .= "- Staff availability: Ready to assist\n";
        }
        $prompt .= "\n";
        
        // Location
        if ($contextData['location']) {
            $prompt .= "**Location Information:**\n";
            $prompt .= "- Name: {$contextData['location']['name']}\n";
            if ($contextData['location']['address']) {
                $prompt .= "- Address: {$contextData['location']['address']}\n";
            }
            if ($contextData['location']['phone']) {
                $prompt .= "- Phone: {$contextData['location']['phone']}\n";
            }
            $prompt .= "\n";
        }
        
        // Statistics
        $prompt .= "**Today's Statistics:**\n";
        $prompt .= "- Total served: {$contextData['statistics']['served']}\n";
        $prompt .= "- Total customers: {$contextData['statistics']['total']}\n\n";
        
        // Add booking state if user is in booking flow
        if (isset($contextData['booking_state'])) {
            $prompt .= "**Current Booking Progress:**\n";
            $bookingState = $contextData['booking_state'];
            $prompt .= "- Step: {$bookingState['step']}\n";
            if ($bookingState['service']) {
                $prompt .= "- Selected Service ID: {$bookingState['service']}\n";
                
                // Add available dates for selected service
                $availableDates = $this->getAvailableDates($bookingState['service']);
                if (!empty($availableDates)) {
                    $prompt .= "- Available Dates (next 7 days):\n";
                    foreach (array_slice($availableDates, 0, 7) as $date) {
                        $prompt .= "  • " . Carbon::parse($date)->format('l, F j, Y') . " ({$date})\n";
                    }
                }
            }
            if ($bookingState['date']) {
                $prompt .= "- Selected Date: {$bookingState['date']}\n";
                
                // Add available time slots for selected date
                if ($bookingState['service']) {
                    $availableSlots = $this->getAvailableTimeSlots(Carbon::parse($bookingState['date']), $bookingState['service']);
                    if (!empty($availableSlots)) {
                        $prompt .= "- Available Time Slots:\n";
                        foreach ($availableSlots as $slot) {
                            $displayTime = Carbon::parse($slot)->format('g:i A');
                            $prompt .= "  • {$displayTime} ({$slot})\n";
                        }
                    }
                }
            }
            if ($bookingState['time']) {
                $prompt .= "- Selected Time: {$bookingState['time']}\n";
            }
            $prompt .= "\nNote: User is currently in booking process. Use the real available dates and times shown above.\n\n";
        }
        
        $prompt .= "**CRITICAL LANGUAGE RULE:**\n";
        $prompt .= "- Customer's preferred language: {$contextData['customer']['language']}\n";
        $prompt .= "- ALWAYS respond in the customer's preferred language UNLESS they explicitly switch languages\n";
        $prompt .= "- If customer writes in Tamil, respond in Tamil\n";
        $prompt .= "- If customer writes in Arabic, respond in Arabic\n";
        $prompt .= "- If customer writes in English, respond in English\n";
        $prompt .= "- Match the EXACT language of the customer's most recent message\n\n";
        
        $prompt .= "**Instructions:**\n";
        $prompt .= "1. Be conversational, friendly, and natural - like a helpful human assistant\n";
        $prompt .= "2. Use the real data above to answer questions accurately\n";
        $prompt .= "3. You can handle multiple topics in one conversation:\n";
        $prompt .= "   - Answer questions about queue wait times, services, location, hours\n";
        $prompt .= "   - Help with appointment booking when requested\n";
        $prompt .= "   - Provide general information and assistance\n";
        $prompt .= "4. For booking appointments:\n";
        $prompt .= "   - When user wants to book, ask them to choose a service from the available services list\n";
        $prompt .= "   - Once they select a service, show them the REAL available dates from the data above\n";
        $prompt .= "   - Once they select a date, show them the REAL available time slots from the data above\n";
        $prompt .= "   - Guide users naturally through: service selection → date → time → confirmation\n";
        $prompt .= "   - IMPORTANT: Always use the actual available dates and times provided in the context above\n";
        $prompt .= "   - If user asks questions during booking, answer them naturally\n";
        $prompt .= "   - If user says 'stop', 'cancel', 'wait', or changes topic, acknowledge and help with what they need\n";
        $prompt .= "   - Be flexible - users can go back, change their mind, or ask questions anytime\n";
        $prompt .= "   - To move to the next step, tell the user you're processing their selection (e.g., 'Let me check available dates for [service]...')\n";
        $prompt .= "5. If users seem frustrated or request human help, acknowledge and offer to transfer\n";
        $prompt .= "6. Keep responses concise but informative and friendly\n";
        $prompt .= "7. If asked about unrelated topics, politely redirect to what you can help with\n\n";
        
        $prompt .= "**Important:** You are a conversational AI, not a rigid form. Be natural, helpful, and adapt to the user's needs. ALWAYS maintain language consistency.\n";
        
        return $prompt;
    }
    
    protected function buildConversationHistory()
    {
        $history = [];
        // Get last 10 messages for context
        $recentMessages = array_slice($this->messages, -10);
        
        foreach ($recentMessages as $msg) {
            if ($msg['role'] === 'user') {
                $history[] = ['role' => 'user', 'content' => $msg['content']];
            } elseif ($msg['role'] === 'ai') {
                $history[] = ['role' => 'assistant', 'content' => $msg['content']];
            }
        }
        
        return $history;
    }
    
    protected function extractBookingDetailsFromAI($message)
    {
        // If user just expressed booking intent and no step is set, start the flow
        if ($this->bookingState['step'] === 'initial') {
            $this->bookingState['step'] = 'select_service';
            
            // Get available services
            $services = $this->getAvailableServicesList();
            
            if (empty($services)) {
                $noServicesMsg = "I apologize, but there are no services available for booking at this location. Please contact us directly for assistance.";
                $translatedMsg = $this->translateBookingMessage($noServicesMsg);
                $this->addMessage('ai', $translatedMsg);
                $this->aiSession->addMessage('assistant', $translatedMsg);
                return;
            }
            
            $serviceList = "Great! I can help you book an appointment. We offer the following services:\n\n";
            foreach ($services as $service) {
                $serviceList .= "• {$service['name']}";
                if (!empty($service['description'])) {
                    $serviceList .= " - {$service['description']}";
                }
                if (!empty($service['service_time'])) {
                    $serviceList .= " ({$service['service_time']} min)";
                }
                $serviceList .= "\n";
            }
            $serviceList .= "\nWhich service would you like to book?";
            
            $translatedList = $this->translateBookingMessage($serviceList);
            $this->addMessage('ai', $translatedList);
            $this->aiSession->addMessage('assistant', $translatedList);
            return;
        }
        
        // Process based on current booking step
        if ($this->bookingState['step'] !== 'initial') {
            $response = $this->handleBookingFlow($message);
            if ($response) {
                $this->addMessage('ai', $response);
                $this->aiSession->addMessage('assistant', $response);
            }
        }
    }
    
    protected function getQueueData($teamId, $locationId)
    {
        $waiting = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNull('start_datetime')
            ->whereNull('cancelled_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->count();
        
        $serving = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('start_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->count();
        
        $completedQueues = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('start_datetime')
            ->whereNotNull('closed_datetime')
            ->whereDate('arrives_time', '>=', now()->subDays(7)->toDateString())
            ->select(DB::raw('TIMESTAMPDIFF(MINUTE, start_datetime, closed_datetime) as duration'))
            ->get();
        
        $avgMinutes = 5;
        if ($completedQueues->count() > 0) {
            $totalMinutes = $completedQueues->sum('duration');
            $avgMinutes = round($totalMinutes / $completedQueues->count());
        }
        
        return [
            'waiting' => $waiting,
            'serving' => $serving,
            'avg_time' => $avgMinutes,
            'estimated_wait' => $waiting * $avgMinutes
        ];
    }
    
    protected function getServicesData($teamId, $locationId)
    {
        $firstLevel = Level::getFirstRecord();
        if (!$firstLevel) return [];
        
        $categories = Category::where('team_id', $teamId)
            ->where('level_id', $firstLevel->id)
            ->whereJsonContains('category_locations', (string)$locationId)
            ->get(['name', 'description', 'service_time', 'amount']);
        
        $services = [];
        foreach ($categories as $cat) {
            $services[] = [
                'name' => $cat->name,
                'description' => $cat->description,
                'duration' => $cat->service_time,
                'price' => $cat->amount
            ];
        }
        
        return $services;
    }
    
    protected function getAgentData($teamId, $locationId)
    {
        // Count currently serving agents based on active queues
        $busy = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('start_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->whereNotNull('served_by')
            ->distinct('served_by')
            ->count('served_by');
        
        // For public interface, we show serving agents without exposing internal staff counts
        return [
            'available' => $busy > 0 ? 'Yes' : 'Limited',
            'busy' => $busy,
            'total' => 'Multiple' // Generic response for public
        ];
    }
    
    protected function getLocationData($locationId)
    {
        $location = Location::find($locationId);
        if (!$location) return null;
        
        $siteDetail = SiteDetail::where('location_id', $locationId)->first();
        
        return [
            'name' => $location->name,
            'address' => $location->address ?? null,
            'phone' => $siteDetail->phone ?? null,
            'email' => $siteDetail->email ?? null,
            'hours' => $siteDetail->business_hours ?? null
        ];
    }
    
    protected function getStatisticsData($teamId, $locationId)
    {
        $today = now()->toDateString();
        
        $served = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('closed_datetime')
            ->whereDate('arrives_time', $today)
            ->count();
        
        $total = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereDate('arrives_time', $today)
            ->count();
        
        return [
            'served' => $served,
            'total' => $total
        ];
    }

    protected function shouldTransferToHuman($message, $sentiment)
    {
        $lowerMessage = strtolower($message);
        
        // Check for explicit transfer requests
        $transferKeywords = ['transfer', 'human', 'agent', 'person', 'speak to someone', 'representative', 'real person'];
        foreach ($transferKeywords as $keyword) {
            if (str_contains($lowerMessage, $keyword)) {
                return true;
            }
        }
        
        // Check sentiment
        if ($sentiment === 'negative') {
            return true;
        }
        
        // Check message count - suggest transfer after many exchanges
        if ($this->aiSession->message_count > $this->settings->max_ai_attempts * 2) {
            return true;
        }
        
        return false;
    }

    public function transferToHuman()
    {
        $this->status = 'transferring';
        
        $transferMessage = $this->settings->getTransferMessage($this->virtualQueue->selected_language);
        $this->addMessage('system', $transferMessage);
        
        // Update AI session
        $this->aiSession->escalate('User requested transfer or AI unable to resolve');
        
        // Update virtual queue
        $this->virtualQueue->transferToHuman('User requested transfer or AI unable to resolve');
        
        // Redirect to human agent waiting page
        return redirect()->route('public.human-agent-waiting', ['virtualQueueId' => $this->virtualQueue->id]);
    }

    public function endSession()
    {
        $this->status = 'ended';
        
        // Update AI session
        $this->aiSession->update([
            'ended_at' => now(),
            'query_resolved' => true,
        ]);
        
        // Update virtual queue
        $this->virtualQueue->markAsCompleted();
        
        session()->flash('success', 'Session ended successfully!');
        return redirect()->route('public.queue-selection', ['location_id' => $this->virtualQueue->location_id]);
    }
    
    /**
     * Start a new chat conversation
     */
    public function startNewChat()
    {
        // Reset all chat-related state
        $this->messages = [];
        $this->currentMessage = '';
        $this->showTransferButton = false;
        $this->showNewChatButton = false;
        $this->chatDisabled = false; // Re-enable chat
        
        // Reset booking state
        $this->bookingState = [
            'step' => 'initial',
            'service' => null,
            'date' => null,
            'time' => null,
            'name' => null,
            'phone' => null,
            'email' => null
        ];
        $this->selectedService = null;
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->availableSlots = [];
        
        // Send fresh greeting
        $greeting = $this->settings->getDefaultGreeting($this->virtualQueue->selected_language);
        $this->addMessage('ai', $greeting);
        
        // Log the new chat start
        Log::info('New chat started', [
            'session_id' => $this->sessionId,
            'customer' => $this->virtualQueue->customer_name
        ]);
    }

    protected function addMessage($role, $content)
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->format('H:i'),
        ];
    }

    protected function initializeBookingFlow()
    {
        $this->bookingState = [
            'step' => 'select_service',
            'service' => null,
            'date' => null,
            'time' => null,
            'name' => null,
            'phone' => null,
            'email' => null
        ];
    }

    protected function handleBookingFlow($message)
    {
        switch ($this->bookingState['step']) {
            case 'select_service':
                return $this->handleServiceSelection($message);
            case 'select_date':
                return $this->handleDateSelection($message);
            case 'select_time':
                return $this->handleTimeSelection($message);
            case 'confirm_booking':
                return $this->handleBookingConfirmation($message);
            default:
                return null;
        }
    }
    
    protected function handleBookingConfirmation($message)
    {
        $lowerMessage = strtolower(trim($message));
        
        // Check for cancellation
        if (in_array($lowerMessage, ['cancel', 'no', 'nope', 'start over', 'reset'])) {
            $this->bookingState = [
                'step' => 'initial',
                'service' => null,
                'date' => null,
                'time' => null,
                'name' => null,
                'phone' => null,
                'email' => null
            ];
            $this->selectedService = null;
            $this->selectedDate = null;
            $this->selectedTime = null;
            $this->availableSlots = [];
            
            return "No problem! Your booking has been cancelled. If you'd like to book an appointment, just let me know!";
        }
        
        // Check for confirmation
        if (in_array($lowerMessage, ['confirm', 'yes', 'yeah', 'yep', 'sure', 'ok', 'okay', 'book it', 'proceed'])) {
            return $this->createBooking();
        }
        
        return "Please type 'confirm' or 'yes' to book this appointment, or 'cancel' to start over.";
    }

    protected function handleServiceSelection($message)
    {
        // Find the service that matches the user's input
        $service = Category::where('team_id', $this->virtualQueue->team_id)
            ->where(function($query) use ($message) {
                $query->where('name', 'LIKE', "%{$message}%")
                      ->orWhere('description', 'LIKE', "%{$message}%");
            })
            ->whereJsonContains('category_locations', (string)$this->virtualQueue->location_id)
            ->first();

        if ($service) {
            $this->selectedService = $service;
            $this->bookingState['service'] = $service->id;
            $this->bookingState['step'] = 'select_date';
            
            // Get available dates
            $availableDates = $this->getAvailableDates($service->id);
            
            if (empty($availableDates)) {
                $this->bookingState['step'] = 'select_service';
                $this->selectedService = null;
                $this->bookingState['service'] = null;
                return "I apologize, but there are no available dates for {$service->name} at the moment. Would you like to select a different service?";
            }
            
            $dateList = "Great! You've selected **{$service->name}**.\n\nHere are the available dates:\n\n";
            foreach (array_slice($availableDates, 0, 7) as $date) {
                $dateList .= "• " . Carbon::parse($date)->format('l, F j, Y') . "\n";
            }
            if (count($availableDates) > 7) {
                $dateList .= "\n...and more dates available.\n";
            }
            $dateList .= "\nPlease tell me your preferred date (e.g., 'tomorrow', 'next Monday', or 'October 30').";
            
            return $dateList;
        }

        return "I couldn't find that service. Please choose from these available services:\n" . 
               $this->getAvailableServicesListString();
    }

    protected function handleDateSelection($message)
    {
        try {
            $date = Carbon::parse($message);
            
            // Check if date is in the past
            if ($date->isPast() && !$date->isToday()) {
                return "I'm sorry, but that date has already passed. Please choose a future date.";
            }
            
            if ($this->isDateAvailable($date, $this->bookingState['service'])) {
                $this->selectedDate = $date;
                $this->bookingState['date'] = $date->format('Y-m-d');
                $this->bookingState['step'] = 'select_time';
                
                // Get available time slots
                $this->availableSlots = $this->getAvailableTimeSlots($date, $this->bookingState['service']);
                
                if (empty($this->availableSlots)) {
                    $this->bookingState['step'] = 'select_date';
                    $this->selectedDate = null;
                    $this->bookingState['date'] = null;
                    return "I apologize, but there are no available time slots for {$date->format('F j, Y')}. Please select another date.";
                }
                
                $timeList = "Perfect! For **{$date->format('l, F j, Y')}**, we have these time slots available:\n\n";
                foreach ($this->availableSlots as $slot) {
                    // Display in both 12-hour and 24-hour format
                    $displayTime = Carbon::parse($slot)->format('g:i A');
                    $timeList .= "• {$displayTime} ({$slot})\n";
                }
                $timeList .= "\nWhich time works best for you? (You can say '9 am', 'morning 9', '2pm', '14:00', etc.)";
                
                return $timeList;
            }
            return "I'm sorry, that date isn't available. Please choose from the available dates I mentioned earlier, or suggest another date.";
        } catch (\Exception $e) {
            Log::warning('Date parsing error', ['message' => $message, 'error' => $e->getMessage()]);
            return "I didn't understand that date format. Please provide a date like:\n• 'tomorrow'\n• 'next Monday'\n• 'October 30'\n• '2025-10-30'";
        }
    }

    protected function handleTimeSelection($message)
    {
        $selectedTime = null;
        $normalizedMessage = strtolower(trim($message));
        
        // Extract hour from message (handles "9 am", "morning 9 am", "9:00", "9", etc.)
        $hour = null;
        $isPM = stripos($normalizedMessage, 'pm') !== false || stripos($normalizedMessage, 'afternoon') !== false || stripos($normalizedMessage, 'evening') !== false;
        $isAM = stripos($normalizedMessage, 'am') !== false || stripos($normalizedMessage, 'morning') !== false;
        
        // Try to extract hour number
        if (preg_match('/\b(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/i', $normalizedMessage, $matches)) {
            $hour = (int)$matches[1];
            $minutes = isset($matches[2]) ? $matches[2] : '00';
            
            // Convert to 24-hour format if PM
            if ($isPM && $hour < 12) {
                $hour += 12;
            } elseif ($isAM && $hour == 12) {
                $hour = 0;
            }
            
            // Format the time
            $formattedTime = sprintf('%02d:%s', $hour, $minutes);
            
            // Try to match with available slots
            foreach ($this->availableSlots as $slot) {
                if ($slot === $formattedTime || stripos($slot, $formattedTime) !== false) {
                    $selectedTime = $slot;
                    break;
                }
            }
        }
        
        // If still not found, try direct string matching
        if (!$selectedTime) {
            foreach ($this->availableSlots as $slot) {
                // Direct match or partial match
                if (stripos($slot, $normalizedMessage) !== false || stripos($normalizedMessage, $slot) !== false) {
                    $selectedTime = $slot;
                    break;
                }
                
                // Try matching just the hour part
                if (preg_match('/\b(\d{1,2})/', $normalizedMessage, $msgMatches) && 
                    preg_match('/^(\d{2})/', $slot, $slotMatches)) {
                    if ((int)$msgMatches[1] == (int)$slotMatches[1]) {
                        $selectedTime = $slot;
                        break;
                    }
                }
            }
        }

        if ($selectedTime) {
            // Check availability one more time
            $date = Carbon::parse($this->bookingState['date']);
            $isBooked = Booking::where('team_id', $this->virtualQueue->team_id)
                ->where('category_id', $this->bookingState['service'])
                ->where('booking_date', $this->bookingState['date'])
                ->where('booking_time', $selectedTime)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->exists();
            
            if ($isBooked) {
                return "I apologize, but that time slot was just booked by someone else. Please choose another time from the available slots.";
            }
            
            $this->selectedTime = $selectedTime;
            $this->bookingState['time'] = $selectedTime;
            $this->bookingState['step'] = 'confirm_booking';
            
            $service = Category::find($this->bookingState['service']);
            $dateFormatted = $date->format('l, F j, Y');
            
            // Convert 24-hour time to 12-hour for display
            $displayTime = Carbon::parse($selectedTime)->format('g:i A');
            
            // Get location name
            $location = Location::find($this->virtualQueue->location_id);
            $locationName = $location ? $location->name : 'Our Location';
            
            $summary = "✅ Perfect! Let me confirm your appointment details:\n\n";
            $summary .= "📌 **Service:** {$service->name}\n";
            $summary .= "📅 **Date:** {$dateFormatted}\n";
            $summary .= "⏰ **Time:** {$displayTime}\n";
            $summary .= "📍 **Location:** {$locationName}\n\n";
            $summary .= "Please confirm by typing 'confirm' or 'yes' to book this appointment, or type 'cancel' to start over.";
            
            return $summary;
        }

        $timesList = "I'm sorry, I couldn't find that time slot. Please choose from these available times:\n\n";
        foreach ($this->availableSlots as $slot) {
            // Display in 12-hour format
            $displayTime = Carbon::parse($slot)->format('g:i A');
            $timesList .= "• {$displayTime} ({$slot})\n";
        }
        $timesList .= "\nYou can say it like: '9 am', 'morning 9', '2pm', '14:00', etc.";
        return $timesList;
    }

    protected function handleContactDetails($message)
    {
        // Extract contact details from message
        if (empty($this->bookingState['name']) && preg_match('/\b[A-Za-z\s]{2,}\b/', $message, $matches)) {
            $this->bookingState['name'] = $matches[0];
        }
        if (empty($this->bookingState['phone']) && preg_match('/\b\d{10,}\b/', $message, $matches)) {
            $this->bookingState['phone'] = $matches[0];
        }
        if (empty($this->bookingState['email']) && preg_match('/\b[\w\.-]+@[\w\.-]+\.\w+\b/', $message, $matches)) {
            $this->bookingState['email'] = $matches[0];
        }

        // Check if we have all required details
        if ($this->bookingState['name'] && $this->bookingState['phone'] && $this->bookingState['email']) {
            // Create the booking
            return $this->createBooking();
        }

        // Ask for missing details
        $missing = [];
        if (!$this->bookingState['name']) $missing[] = 'name';
        if (!$this->bookingState['phone']) $missing[] = 'phone number';
        if (!$this->bookingState['email']) $missing[] = 'email address';

        return "I still need your " . implode(', ', $missing) . ". Please provide them to complete your booking.";
    }

    protected function createBooking()
    {
        try {
            // Pre-fill customer details from virtual queue
            $name = $this->virtualQueue->customer_name;
            $phone = $this->virtualQueue->customer_phone;
            $email = $this->virtualQueue->customer_email;
            
            // Prepare booking data
            $bookingData = [
                'team_id' => $this->virtualQueue->team_id,
                'booking_date' => $this->bookingState['date'],
                'booking_time' => $this->bookingState['time'],
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'category_id' => $this->bookingState['service'],
                'location_id' => $this->virtualQueue->location_id,
                'status' => Booking::STATUS_CONFIRMED,
                'booking_type' => 'AI Agent',
            ];
            
            Log::info('AI Agent creating booking', $bookingData);
            
            $booking = Booking::create($bookingData);
            
            Log::info('Booking created successfully by AI', [
                'booking_id' => $booking->id,
                'customer' => $name
            ]);

            // Reset booking state
            $this->bookingState = [
                'step' => 'initial',
                'service' => null,
                'date' => null,
                'time' => null,
                'name' => null,
                'phone' => null,
                'email' => null
            ];
            $this->selectedService = null;
            $this->selectedDate = null;
            $this->selectedTime = null;
            $this->availableSlots = [];
            
            $service = Category::find($bookingData['category_id']);
            $dateFormatted = Carbon::parse($bookingData['booking_date'])->format('l, F j, Y');
            $location = Location::find($this->virtualQueue->location_id);
            $locationName = $location ? $location->name : 'Our Location';
            
            // Show new chat button and disable chat after successful booking
            $this->showNewChatButton = true;
            $this->chatDisabled = true;

            return "✅ **Booking Confirmed!**\n\n" .
                   "🎉 Great news! Your appointment has been successfully booked.\n\n" .
                   "**Booking Details:**\n" .
                   "📌 Service: {$service->name}\n" .
                   "📅 Date: {$dateFormatted}\n" .
                   "⏰ Time: {$bookingData['booking_time']}\n" .
                   "🎯 Reference: #{$booking->id}\n" .
                   "📍 Location: {$locationName}\n\n" .
                   "You'll receive a confirmation email shortly.\n\n" .
                   "To book another appointment or ask questions, please click the 'Start New Chat' button below.";
                   
        } catch (\Exception $e) {
            Log::error('Booking creation error in AI flow', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'booking_data' => $this->bookingState
            ]);
            return "I apologize, but there was an error creating your booking: {$e->getMessage()}\n\nPlease try again or ask to speak with a human agent for assistance.";
        }
    }

    protected function getAvailableDates($serviceId)
    {
        $dates = [];
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        while ($startDate <= $endDate) {
            if ($this->isDateAvailable($startDate, $serviceId)) {
                $dates[] = $startDate->format('Y-m-d');
            }
            $startDate->addDay();
        }

        return $dates;
    }

    protected function isDateAvailable($date, $serviceId)
    {
        // Check if the date is not in the past
        if ($date < Carbon::today()) {
            return false;
        }

        // Check if there are any available slots for this date
        $slots = $this->getAvailableTimeSlots($date, $serviceId);
        return !empty($slots);
    }

    protected function getAvailableTimeSlots($date, $serviceId)
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        // Get site settings to determine slot type
        $siteDetail = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();
        
        // Get booking settings
        $bookingSetting = \App\Models\AccountSetting::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', \App\Models\AccountSetting::BOOKING_SLOT)
            ->first();
        
        if (!$bookingSetting) {
            // Fallback: Get location-level settings
            $bookingSetting = \App\Models\AccountSetting::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->where('slot_type', \App\Models\AccountSetting::LOCATION_SLOT)
                ->first();
        }
        
        if (!$bookingSetting) {
            // Final fallback: return empty array
            return [];
        }
        
        // Use the existing checktimeslot method which handles all the business logic
        $result = \App\Models\AccountSetting::checktimeslot(
            $teamId,
            $locationId,
            $date,
            $serviceId,
            $siteDetail
        );
        
        // Extract slots and format them for the AI booking flow
        $slots = [];
        if (isset($result['start_at']) && !empty($result['start_at'])) {
            foreach ($result['start_at'] as $slot) {
                // Slots are in format "09:00 AM-10:00 AM", extract start time
                if (strpos($slot, '-') !== false) {
                    [$startTime, $endTime] = explode('-', $slot);
                    // Convert to 24-hour format for consistency
                    try {
                        $time24h = Carbon::createFromFormat('h:i A', trim($startTime))->format('H:i');
                        $slots[] = $time24h;
                    } catch (\Exception $e) {
                        // If parsing fails, keep original format
                        $slots[] = trim($startTime);
                    }
                } else {
                    $slots[] = $slot;
                }
            }
        }
        
        return $slots;
    }

    /**
     * Translate booking flow messages to match user's language
     */
    protected function translateBookingMessage($englishMessage)
    {
        // Check if OpenAI service is available
        if (!$this->openai) {
            return $englishMessage;
        }
        
        // Get the user's language from recent messages
        $userLanguage = $this->detectUserLanguage();
        
        // If English, no translation needed
        if ($userLanguage === 'en' || $userLanguage === 'English' || empty($userLanguage)) {
            return $englishMessage;
        }
        
        try {
            $translationPrompt = "Translate the following message to {$userLanguage}. ";
            $translationPrompt .= "Keep the exact same formatting (bullet points •, emojis, line breaks, **bold text**, numbers). ";
            $translationPrompt .= "Only translate the text, don't add anything extra.\n\n";
            $translationPrompt .= "Message:\n{$englishMessage}";
            
            $messages = [
                ['role' => 'system', 'content' => $translationPrompt]
            ];
            
            $translated = $this->openai->generateResponse($messages);
            
            return $translated ?: $englishMessage;
            
        } catch (\Exception $e) {
            Log::warning('Booking message translation failed', ['error' => $e->getMessage()]);
            return $englishMessage;
        }
    }
    
    /**
     * Generate follow-up response with real DB data after booking state changes
     */
    protected function generateBookingFollowUp()
    {
        $userLanguage = $this->detectUserLanguage();
        
        // Service selected - show available dates
        if ($this->bookingState['step'] === 'select_date' && $this->bookingState['service']) {
            $service = Category::find($this->bookingState['service']);
            if (!$service) return null;
            
            $availableDates = $this->getAvailableDates($this->bookingState['service']);
            
            if (empty($availableDates)) {
                $msg = "I apologize, but there are no available dates for {$service->name} at the moment. Would you like to select a different service?";
                return $this->translateToLanguage($msg, $userLanguage);
            }
            
            $dateList = "Great! You've selected **{$service->name}**.\n\nHere are the available dates:\n\n";
            foreach (array_slice($availableDates, 0, 7) as $date) {
                $dateList .= "• " . Carbon::parse($date)->format('l, F j, Y') . "\n";
            }
            if (count($availableDates) > 7) {
                $dateList .= "\n...and more dates available.\n";
            }
            $dateList .= "\nPlease tell me your preferred date (e.g., 'tomorrow', 'next Monday', or 'October 30').";
            
            return $this->translateToLanguage($dateList, $userLanguage);
        }
        
        // Date selected - show available time slots
        if ($this->bookingState['step'] === 'select_time' && $this->bookingState['date']) {
            $date = Carbon::parse($this->bookingState['date']);
            $timeList = "Perfect! For **{$date->format('l, F j, Y')}**, we have these time slots available:\n\n";
            
            foreach ($this->availableSlots as $slot) {
                $displayTime = Carbon::parse($slot)->format('g:i A');
                $timeList .= "• {$displayTime} ({$slot})\n";
            }
            $timeList .= "\nWhich time works best for you? (You can say '9 am', 'morning 9', '2pm', '14:00', etc.)";
            
            return $this->translateToLanguage($timeList, $userLanguage);
        }
        
        // Time selected - show confirmation
        if ($this->bookingState['step'] === 'confirm_booking' && $this->bookingState['time']) {
            $service = Category::find($this->bookingState['service']);
            $date = Carbon::parse($this->bookingState['date']);
            $displayTime = Carbon::parse($this->bookingState['time'])->format('g:i A');
            $location = Location::find($this->virtualQueue->location_id);
            $locationName = $location ? $location->name : 'Our Location';
            
            $summary = "✅ Perfect! Let me confirm your appointment details:\n\n";
            $summary .= "📌 **Service:** {$service->name}\n";
            $summary .= "📅 **Date:** {$date->format('l, F j, Y')}\n";
            $summary .= "⏰ **Time:** {$displayTime}\n";
            $summary .= "📍 **Location:** {$locationName}\n\n";
            $summary .= "Please confirm by typing 'confirm' or 'yes' to book this appointment, or type 'cancel' to start over.";
            
            return $this->translateToLanguage($summary, $userLanguage);
        }
        
        return null;
    }
    
    /**
     * Translate text to specified language using OpenAI
     */
    protected function translateToLanguage($text, $targetLanguage)
    {
        if ($targetLanguage === 'en' || $targetLanguage === 'English' || empty($targetLanguage)) {
            return $text;
        }
        
        if (!$this->openai) {
            return $text;
        }
        
        try {
            $prompt = "Translate the following message to {$targetLanguage}. Keep the exact same formatting (bullet points •, emojis, line breaks, **bold text**). Only translate the text.\n\nMessage:\n{$text}";
            
            $messages = [['role' => 'system', 'content' => $prompt]];
            $translated = $this->openai->generateResponse($messages);
            
            return $translated ?: $text;
        } catch (\Exception $e) {
            Log::warning('Translation failed', ['error' => $e->getMessage()]);
            return $text;
        }
    }
    
    /**
     * Update booking state based on conversation flow
     * Returns true if state changed
     */
    protected function updateBookingStateFromConversation($userMessage, $aiResponse)
    {
        $lowerMessage = strtolower(trim($userMessage));
        $stateChanged = false;
        
        // Check if user wants to start booking
        if ($this->bookingState['step'] === 'initial') {
            if (stripos($aiResponse, 'available services') !== false || 
                stripos($aiResponse, 'following services') !== false ||
                stripos($aiResponse, 'choose') !== false && stripos($aiResponse, 'service') !== false) {
                $this->bookingState['step'] = 'select_service';
                $stateChanged = true;
            }
            return $stateChanged;
        }
        
        // Check if user selected a service
        if ($this->bookingState['step'] === 'select_service') {
            $services = $this->getAvailableServicesList();
            
            // First try simple string matching
            foreach ($services as $service) {
                if (stripos($lowerMessage, strtolower($service['name'])) !== false) {
                    $this->bookingState['service'] = $service['id'];
                    $this->bookingState['step'] = 'select_date';
                    $this->selectedService = $service['id'];
                    $stateChanged = true;
                    break;
                }
            }
            
            // If no match, use AI to detect which service user selected
            if (!$stateChanged && $this->openai) {
                $selectedService = $this->detectServiceSelection($userMessage, $services);
                if ($selectedService) {
                    $this->bookingState['service'] = $selectedService['id'];
                    $this->bookingState['step'] = 'select_date';
                    $this->selectedService = $selectedService['id'];
                    $stateChanged = true;
                }
            }
            
            return $stateChanged;
        }
        
        // Check if user selected a date
        if ($this->bookingState['step'] === 'select_date') {
            try {
                $date = Carbon::parse($userMessage);
                if ($this->isDateAvailable($date, $this->bookingState['service'])) {
                    $this->bookingState['date'] = $date->format('Y-m-d');
                    $this->bookingState['step'] = 'select_time';
                    $this->selectedDate = $date;
                    $this->availableSlots = $this->getAvailableTimeSlots($date, $this->bookingState['service']);
                    $stateChanged = true;
                }
            } catch (\Exception $e) {
                // Not a valid date, stay in same step
            }
            return $stateChanged;
        }
        
        // Check if user selected a time
        if ($this->bookingState['step'] === 'select_time') {
            // Try to extract time from message
            foreach ($this->availableSlots as $slot) {
                if (stripos($userMessage, $slot) !== false || 
                    stripos($userMessage, Carbon::parse($slot)->format('g:i')) !== false ||
                    stripos($userMessage, Carbon::parse($slot)->format('ga')) !== false) {
                    $this->bookingState['time'] = $slot;
                    $this->bookingState['step'] = 'confirm_booking';
                    $this->selectedTime = $slot;
                    $stateChanged = true;
                    break;
                }
            }
            
            // If no match, use AI to detect time selection
            if (!$stateChanged && $this->openai && !empty($this->availableSlots)) {
                $selectedTime = $this->detectTimeSelection($userMessage, $this->availableSlots);
                if ($selectedTime) {
                    $this->bookingState['time'] = $selectedTime;
                    $this->bookingState['step'] = 'confirm_booking';
                    $this->selectedTime = $selectedTime;
                    $stateChanged = true;
                }
            }
            
            return $stateChanged;
        }
        
        // Check for confirmation
        if ($this->bookingState['step'] === 'confirm_booking') {
            if (in_array($lowerMessage, ['confirm', 'yes', 'yeah', 'yep', 'sure', 'ok', 'okay'])) {
                $this->createBooking();
                $stateChanged = true;
            } else if ($this->openai) {
                // Use AI to detect confirmation in any language
                $isConfirmed = $this->detectConfirmation($userMessage);
                if ($isConfirmed) {
                    $this->createBooking();
                    $stateChanged = true;
                }
            }
        }
        
        return $stateChanged;
    }
    
    /**
     * Detect which service user selected (works in any language)
     */
    protected function detectServiceSelection($userMessage, $services)
    {
        if (!$this->openai || empty($services)) {
            return null;
        }
        
        try {
            $serviceList = "";
            foreach ($services as $service) {
                $serviceList .= "- ID: {$service['id']}, Name: {$service['name']}\n";
            }
            
            $prompt = "The user is selecting a service from this list:\n{$serviceList}\n";
            $prompt .= "User's message: \"{$userMessage}\"\n\n";
            $prompt .= "Which service did the user select? Respond with ONLY the service ID number, or 'none' if unclear.";
            
            $messages = [['role' => 'system', 'content' => $prompt]];
            $response = $this->openai->generateResponse($messages);
            
            $serviceId = trim($response);
            if (is_numeric($serviceId)) {
                foreach ($services as $service) {
                    if ($service['id'] == $serviceId) {
                        return $service;
                    }
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning('Service selection detection failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Detect which time slot user selected (works in any language)
     */
    protected function detectTimeSelection($userMessage, $availableSlots)
    {
        if (!$this->openai || empty($availableSlots)) {
            return null;
        }
        
        try {
            $slotList = "";
            foreach ($availableSlots as $slot) {
                $displayTime = Carbon::parse($slot)->format('g:i A');
                $slotList .= "- {$displayTime} ({$slot})\n";
            }
            
            $prompt = "The user is selecting a time slot from this list:\n{$slotList}\n";
            $prompt .= "User's message: \"{$userMessage}\"\n\n";
            $prompt .= "Which time slot did the user select? Respond with ONLY the exact time in 24-hour format (HH:MM:SS), or 'none' if unclear.";
            
            $messages = [['role' => 'system', 'content' => $prompt]];
            $response = $this->openai->generateResponse($messages);
            
            $selectedTime = trim($response);
            if (in_array($selectedTime, $availableSlots)) {
                return $selectedTime;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning('Time selection detection failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Detect if user is confirming the booking (works in any language)
     */
    protected function detectConfirmation($userMessage)
    {
        if (!$this->openai) {
            return false;
        }
        
        try {
            $prompt = "Is the user confirming or agreeing to proceed? User's message: \"{$userMessage}\"\n\n";
            $prompt .= "Respond with ONLY 'yes' or 'no'.";
            
            $messages = [['role' => 'system', 'content' => $prompt]];
            $response = $this->openai->generateResponse($messages);
            
            return strtolower(trim($response)) === 'yes';
        } catch (\Exception $e) {
            Log::warning('Confirmation detection failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Detect if user wants to book an appointment (works in any language)
     */
    protected function detectBookingIntent($message)
    {
        // Quick check for common English keywords first (optimization)
        if (stripos($message, 'book') !== false || 
            stripos($message, 'appointment') !== false ||
            stripos($message, 'schedule') !== false ||
            stripos($message, 'reserve') !== false) {
            return true;
        }
        
        // Check if OpenAI service is available
        if (!$this->openai) {
            return false;
        }
        
        // For other languages, use OpenAI to detect intent
        try {
            $intentPrompt = "Does this message express intent to book an appointment, schedule a service, or make a reservation? ";
            $intentPrompt .= "Respond with ONLY 'yes' or 'no'.\n\n";
            $intentPrompt .= "Message: \"{$message}\"";
            
            $messages = [
                ['role' => 'system', 'content' => $intentPrompt]
            ];
            
            $response = $this->openai->generateResponse($messages);
            
            return strtolower(trim($response)) === 'yes';
            
        } catch (\Exception $e) {
            Log::warning('Booking intent detection failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Detect user's language from conversation
     */
    protected function detectUserLanguage()
    {
        // Get recent user messages
        $userMessages = array_filter($this->messages, function($msg) {
            return $msg['role'] === 'user';
        });
        
        if (empty($userMessages)) {
            return $this->virtualQueue->selected_language ?? 'en';
        }
        
        // Get the last user message
        $lastUserMessage = end($userMessages);
        $userText = $lastUserMessage['content'] ?? '';
        
        if (empty($userText)) {
            return $this->virtualQueue->selected_language ?? 'en';
        }
        
        // Check if OpenAI service is available
        if (!$this->openai) {
            return $this->virtualQueue->selected_language ?? 'en';
        }
        
        // Use OpenAI to detect language
        try {
            $detectPrompt = "Detect the language of this text and respond with ONLY the language name in English (e.g., 'Arabic', 'Spanish', 'French', 'Hindi', 'English', etc.).\n\nText: \"{$userText}\"";
            
            $messages = [
                ['role' => 'system', 'content' => $detectPrompt]
            ];
            
            $detectedLanguage = $this->openai->generateResponse($messages);
            
            return trim($detectedLanguage) ?: 'English';
            
        } catch (\Exception $e) {
            Log::warning('Language detection failed', ['error' => $e->getMessage()]);
            return $this->virtualQueue->selected_language ?? 'en';
        }
    }

    public function render()
    {
        return view('livewire.public-ai-agent-call');
    }

        /**
         * Returns available services for booking (AI response helper)
         * You may customize this logic as needed for your booking flow.
         */
        public function getAvailableServicesResponse()
        {
            // Example: Fetch available services from Category or Service model
            // Replace with your actual logic
            $services = \App\Models\Category::where('team_id', $this->virtualQueue->team_id)
                ->whereJsonContains('category_locations', (string)$this->virtualQueue->location_id)
                ->pluck('name')->toArray();
            if (empty($services)) {
                return 'No services are currently available for booking.';
            }
            return 'Available services: ' . implode(', ', $services);
        }
        
        /**
         * Get available services as array
         */
        protected function getAvailableServicesList()
        {
            $firstLevel = Level::getFirstRecord();
            if (!$firstLevel) {
                return [];
            }
            
            return Category::where('team_id', $this->virtualQueue->team_id)
                ->where('level_id', $firstLevel->id)
                ->whereJsonContains('category_locations', (string)$this->virtualQueue->location_id)
                ->get(['id', 'name', 'description', 'service_time', 'amount'])
                ->toArray();
        }
        
        /**
         * Get available services as formatted string
         */
        protected function getAvailableServicesListString()
        {
            $services = $this->getAvailableServicesList();
            if (empty($services)) {
                return "No services are currently available.";
            }
            
            $serviceList = "";
            foreach ($services as $service) {
                $serviceList .= "• {$service['name']}";
                if (!empty($service['description'])) {
                    $serviceList .= " - {$service['description']}";
                }
                $serviceList .= "\n";
            }
            return $serviceList;
        }
    }
    