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

class AIAgentCall extends Component
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
    public $transferReason = '';

    protected $listeners = ['messageReceived', 'aiResponseReceived', 'sessionEnded'];

    public function mount($virtualQueueId)
    {
        $this->virtualQueueId = base64_decode($virtualQueueId);
        $this->virtualQueue = VirtualQueue::findOrFail($this->virtualQueueId);
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
                return route('virtual-meeting', [
                    'room' => $this->sessionId,
                    'queueId' => $this->virtualQueue->id
                ]);
        }
    }

    protected function generateTwilioLink()
    {
        // Twilio Video room generation
        return route('virtual-meeting', [
            'room' => $this->sessionId,
            'queueId' => $this->virtualQueue->id
        ]);
    }

    protected function generateAgoraLink()
    {
        // Agora room generation
        return route('virtual-meeting', [
            'room' => $this->sessionId,
            'queueId' => $this->virtualQueue->id
        ]);
    }

    protected function generateDailyLink()
    {
        // Daily.co room generation
        return route('virtual-meeting', [
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
        if (empty($this->currentMessage)) {
            return;
        }

        // Add user message
        $this->addMessage('user', $this->currentMessage);

        // Store in AI session
        $this->aiSession->addMessage('user', $this->currentMessage);

        // Analyze sentiment
        $sentiment = $this->aiSession->updateSentiment($this->currentMessage);

        // Get AI response (this would call your AI service)
        $aiResponse = $this->getAIResponse($this->currentMessage);

        // Add AI response
        $this->addMessage('ai', $aiResponse);
        $this->aiSession->addMessage('assistant', $aiResponse);

        // Check if transfer is needed based on keywords or sentiment
        if ($this->shouldTransferToHuman($this->currentMessage, $sentiment)) {
            $this->showTransferButton = true;
        }

        $this->currentMessage = '';
    }

    protected function getAIResponse($userMessage)
    {
        $lowerMessage = strtolower($userMessage);
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        // Greetings
        if (str_contains($lowerMessage, 'hello') || str_contains($lowerMessage, 'hi') || str_contains($lowerMessage, 'hey')) {
            return "Hello! Welcome to our virtual queue system. I'm your AI assistant. How can I help you today?";
        }
        
        // Help requests
        if (str_contains($lowerMessage, 'help') || str_contains($lowerMessage, 'assist') || str_contains($lowerMessage, 'what can you do')) {
            return $this->getHelpMenu();
        }
        
        // Queue/Wait time information - REAL DATA
        if (str_contains($lowerMessage, 'queue') || str_contains($lowerMessage, 'wait') || str_contains($lowerMessage, 'time') || str_contains($lowerMessage, 'how long')) {
            return $this->getQueueInformation();
        }
        
        // Service/Category information - REAL DATA
        if (str_contains($lowerMessage, 'service') || str_contains($lowerMessage, 'category') || str_contains($lowerMessage, 'what do you offer') || str_contains($lowerMessage, 'available')) {
            return $this->getServiceInformation();
        }
        
        // Service time/duration - REAL DATA
        if (str_contains($lowerMessage, 'duration') || str_contains($lowerMessage, 'service time') || str_contains($lowerMessage, 'how long does')) {
            return $this->getServiceTimeInformation();
        }
        
        // Agent information - REAL DATA
        if (str_contains($lowerMessage, 'agent') && !str_contains($lowerMessage, 'transfer')) {
            return $this->getAgentInformation();
        }
        
        // Location/Hours information - REAL DATA
        if (str_contains($lowerMessage, 'location') || str_contains($lowerMessage, 'address') || str_contains($lowerMessage, 'hours') || str_contains($lowerMessage, 'open') || str_contains($lowerMessage, 'timing')) {
            return $this->getLocationInformation();
        }
        
        // Counter information - REAL DATA
        if (str_contains($lowerMessage, 'counter') || str_contains($lowerMessage, 'desk') || str_contains($lowerMessage, 'window')) {
            return $this->getCounterInformation();
        }
        
        // Today's statistics - REAL DATA
        if (str_contains($lowerMessage, 'today') || str_contains($lowerMessage, 'statistics') || str_contains($lowerMessage, 'stats') || str_contains($lowerMessage, 'how many')) {
            return $this->getTodayStatistics();
        }
        
        // Busiest time - REAL DATA
        if (str_contains($lowerMessage, 'busy') || str_contains($lowerMessage, 'busiest') || str_contains($lowerMessage, 'peak') || str_contains($lowerMessage, 'best time')) {
            return $this->getBusiestTimeInformation();
        }
        
        // My position - REAL DATA
        if (str_contains($lowerMessage, 'my position') || str_contains($lowerMessage, 'my place') || str_contains($lowerMessage, 'where am i')) {
            return $this->getMyPosition();
        }
        
        // Pricing information - REAL DATA
        if (str_contains($lowerMessage, 'price') || str_contains($lowerMessage, 'cost') || str_contains($lowerMessage, 'fee') || str_contains($lowerMessage, 'charge') || str_contains($lowerMessage, 'payment')) {
            return $this->getPricingInformation();
        }
        
        // Contact information - REAL DATA
        if (str_contains($lowerMessage, 'contact') || str_contains($lowerMessage, 'phone') || str_contains($lowerMessage, 'email') || str_contains($lowerMessage, 'reach')) {
            return $this->getContactInformation();
        }
        
        // Appointment/Booking
        if (str_contains($lowerMessage, 'appointment') || str_contains($lowerMessage, 'book') || str_contains($lowerMessage, 'schedule')) {
            return "I can help you schedule an appointment. What service are you looking to book, and what date/time works best for you?";
        }
        
        // Transfer to human
        if (str_contains($lowerMessage, 'transfer') || str_contains($lowerMessage, 'human') || str_contains($lowerMessage, 'person') || str_contains($lowerMessage, 'speak to someone')) {
            $this->showTransferButton = true;
            return "I understand you'd like to speak with a human agent. I'll prepare to transfer you. Please click the 'Transfer to Human Agent' button below.";
        }
        
        // Thank you
        if (str_contains($lowerMessage, 'thank') || str_contains($lowerMessage, 'thanks')) {
            return "You're welcome! Is there anything else I can help you with today?";
        }
        
        // Goodbye
        if (str_contains($lowerMessage, 'bye') || str_contains($lowerMessage, 'goodbye') || str_contains($lowerMessage, 'done')) {
            return "Thank you for using our virtual queue system! Have a great day. If you need anything else, feel free to ask.";
        }
        
        // Default contextual response
        return "I can help you with:\n• Current queue wait times\n• Available services\n• Service durations\n• Agent information\n\nWhat specific information would you like?";
    }
    
    protected function getQueueInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        // Get current queue count (waiting)
        $currentQueue = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNull('start_datetime')
            ->whereNull('called_datetime')
            ->whereNull('cancelled_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->count();
        
        // Get average service time from completed queues (difference between start and closed)
        $completedQueues = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('start_datetime')
            ->whereNotNull('closed_datetime')
            ->whereDate('arrives_time', '>=', now()->subDays(7)->toDateString())
            ->select(DB::raw('TIMESTAMPDIFF(MINUTE, start_datetime, closed_datetime) as duration'))
            ->get();
        
        $avgMinutes = 5; // Default
        if ($completedQueues->count() > 0) {
            $totalMinutes = $completedQueues->sum('duration');
            $avgMinutes = round($totalMinutes / $completedQueues->count());
        }
        
        // Calculate estimated wait
        $estimatedWait = $currentQueue * $avgMinutes;
        
        $response = "📊 **Current Queue Information:**\n\n";
        $response .= "• People in queue: {$currentQueue}\n";
        $response .= "• Average service time: {$avgMinutes} minutes\n";
        $response .= "• Estimated wait time: {$estimatedWait} minutes\n\n";
        
        if ($currentQueue == 0) {
            $response .= "Great news! There's no wait right now. You can be served immediately.";
        } elseif ($currentQueue <= 3) {
            $response .= "The queue is moving quickly. You should be served soon!";
        } else {
            $response .= "We appreciate your patience. The queue is being processed as quickly as possible.";
        }
        
        return $response;
    }
    
    protected function getServiceInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        // Get first level categories
        $firstLevel = Level::getFirstRecord();
        
        if (!$firstLevel) {
            return "I'm having trouble accessing service information right now. Please ask a human agent for details.";
        }
        
        $categories = Category::where('team_id', $teamId)
            ->where('level_id', $firstLevel->id)
            ->whereJsonContains('category_locations', (string)$locationId)
            ->get(['name', 'description', 'service_time', 'amount']);
        
        if ($categories->isEmpty()) {
            return "Currently, there are no services configured for this location. Please contact a human agent for assistance.";
        }
        
        $response = "🏢 **Available Services:**\n\n";
        
        foreach ($categories as $index => $category) {
            $response .= ($index + 1) . ". **{$category->name}**\n";
            
            if ($category->description) {
                $response .= "   Description: {$category->description}\n";
            }
            
            if ($category->service_time) {
                $response .= "   Duration: {$category->service_time} minutes\n";
            }
            
            if ($category->amount && $category->amount > 0) {
                $response .= "   Price: \${$category->amount}\n";
            }
            
            $response .= "\n";
        }
        
        $response .= "Would you like more details about any specific service?";
        
        return $response;
    }
    
    protected function getServiceTimeInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        $firstLevel = Level::getFirstRecord();
        
        if (!$firstLevel) {
            return "I'm having trouble accessing service time information right now.";
        }
        
        $categories = Category::where('team_id', $teamId)
            ->where('level_id', $firstLevel->id)
            ->whereJsonContains('category_locations', (string)$locationId)
            ->whereNotNull('service_time')
            ->get(['name', 'service_time']);
        
        if ($categories->isEmpty()) {
            return "Service time information is not available at the moment. Typically, most services take between 5-15 minutes.";
        }
        
        $response = "⏱️ **Service Duration Information:**\n\n";
        
        foreach ($categories as $category) {
            $response .= "• **{$category->name}**: {$category->service_time} minutes\n";
        }
        
        $response .= "\nThese are estimated times. Actual duration may vary based on your specific needs.";
        
        return $response;
    }
    
    protected function getAgentInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        // Get available agents for this location
        $availableAgents = User::where('team_id', $teamId)
            ->where('status', 'active')
            ->count();
        
        // Get busy agents (currently serving customers)
        $busyAgents = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('start_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->distinct('user_id')
            ->count('user_id');
        
        $freeAgents = max(0, $availableAgents - $busyAgents);
        
        $response = "👥 **Agent Information:**\n\n";
        $response .= "• Total agents available: {$availableAgents}\n";
        $response .= "• Currently serving: {$busyAgents}\n";
        $response .= "• Available now: {$freeAgents}\n\n";
        
        if ($freeAgents > 0) {
            $response .= "Good news! We have agents available to assist you right now.";
        } else {
            $response .= "All agents are currently assisting other customers. You'll be served as soon as an agent becomes available.";
        }
        
        return $response;
    }
    
    protected function getHelpMenu()
    {
        return "🤖 **I can help you with:**\n\n" .
               "📊 **Queue Information:**\n" .
               "• Current wait time\n" .
               "• Your position in queue\n" .
               "• Today's statistics\n\n" .
               "🏢 **Services:**\n" .
               "• Available services\n" .
               "• Service duration\n" .
               "• Pricing information\n\n" .
               "👥 **Staff & Location:**\n" .
               "• Agent availability\n" .
               "• Counter information\n" .
               "• Location & hours\n" .
               "• Contact details\n\n" .
               "📈 **Analytics:**\n" .
               "• Busiest times\n" .
               "• Peak hours\n\n" .
               "Just ask me anything! For example:\n" .
               "• 'What's my position?'\n" .
               "• 'Show me all services'\n" .
               "• 'What are your hours?'\n" .
               "• 'When is the best time to visit?'";
    }
    
    protected function getLocationInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        $location = Location::where('id', $locationId)->first();
        $siteDetail = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();
        
        if (!$location) {
            return "I'm having trouble accessing location information right now.";
        }
        
        $response = "📍 **Location Information:**\n\n";
        $response .= "**{$location->name}**\n\n";
        
        if ($location->address) {
            $response .= "📮 Address:\n{$location->address}\n\n";
        }
        
        if ($siteDetail) {
            if ($siteDetail->business_hours) {
                $response .= "🕐 Business Hours:\n{$siteDetail->business_hours}\n\n";
            }
            
            if ($siteDetail->phone) {
                $response .= "📞 Phone: {$siteDetail->phone}\n";
            }
            
            if ($siteDetail->email) {
                $response .= "📧 Email: {$siteDetail->email}\n";
            }
        }
        
        return $response ?: "Location details are being updated. Please ask a human agent for specific information.";
    }
    
    protected function getCounterInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        $counters = Counter::where('team_id', $teamId)
            ->whereJsonContains('counter_locations', (string)$locationId)
            ->get(['name', 'id']);
        
        if ($counters->isEmpty()) {
            return "Counter information is not available at the moment.";
        }
        
        // Get active counters (with ongoing service)
        $activeCounters = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('start_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->distinct('counter_id')
            ->pluck('counter_id');
        
        $response = "🪟 **Counter Information:**\n\n";
        $response .= "Total Counters: {$counters->count()}\n";
        $response .= "Active Now: {$activeCounters->count()}\n\n";
        
        $response .= "**Available Counters:**\n";
        foreach ($counters as $index => $counter) {
            $status = $activeCounters->contains($counter->id) ? '🔴 Busy' : '🟢 Available';
            $response .= ($index + 1) . ". {$counter->name} - {$status}\n";
        }
        
        return $response;
    }
    
    protected function getTodayStatistics()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        $today = now()->toDateString();
        
        // Total served today
        $served = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('closed_datetime')
            ->whereDate('arrives_time', $today)
            ->count();
        
        // Currently waiting
        $waiting = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNull('start_datetime')
            ->whereDate('arrives_time', $today)
            ->count();
        
        // Currently being served
        $serving = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('start_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', $today)
            ->count();
        
        // Cancelled
        $cancelled = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('cancelled_datetime')
            ->whereDate('arrives_time', $today)
            ->count();
        
        $total = $served + $waiting + $serving + $cancelled;
        
        $response = "📊 **Today's Statistics:**\n\n";
        $response .= "• Total customers: {$total}\n";
        $response .= "• Served: {$served}\n";
        $response .= "• Currently serving: {$serving}\n";
        $response .= "• Waiting: {$waiting}\n";
        $response .= "• Cancelled: {$cancelled}\n\n";
        
        if ($served > 0) {
            $efficiency = round(($served / $total) * 100);
            $response .= "Service efficiency: {$efficiency}%";
        }
        
        return $response;
    }
    
    protected function getBusiestTimeInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        // Get hourly distribution for last 7 days
        $hourlyData = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereDate('arrives_time', '>=', now()->subDays(7)->toDateString())
            ->select(DB::raw('HOUR(arrives_time) as hour, COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        if ($hourlyData->isEmpty()) {
            return "Not enough data to determine busiest times. Please check back later.";
        }
        
        $response = "📈 **Busiest Times (Last 7 Days):**\n\n";
        
        foreach ($hourlyData as $index => $data) {
            $hour = $data->hour;
            $nextHour = ($hour + 1) % 24;
            $timeRange = sprintf("%02d:00 - %02d:00", $hour, $nextHour);
            $response .= ($index + 1) . ". {$timeRange} ({$data->count} customers)\n";
        }
        
        $busiestHour = $hourlyData->first()->hour;
        $nextHour = ($busiestHour + 1) % 24;
        $response .= "\n💡 **Best time to visit:** Avoid {$busiestHour}:00 - {$nextHour}:00 for shorter wait times.";
        
        return $response;
    }
    
    protected function getMyPosition()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        // Get all waiting customers before this one
        $position = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNull('start_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->where('arrives_time', '<', $this->virtualQueue->created_at)
            ->count() + 1;
        
        $totalWaiting = DB::table('queues_storage')
            ->where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNull('start_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->count();
        
        $response = "📍 **Your Position:**\n\n";
        $response .= "You are number **{$position}** out of {$totalWaiting} waiting\n\n";
        $response .= "Ticket Number: **{$this->virtualQueue->ticket_number}**\n";
        
        if ($position == 1) {
            $response .= "\n🎉 You're next! Please be ready.";
        } elseif ($position <= 3) {
            $response .= "\n⏰ You'll be called soon!";
        } else {
            $peopleAhead = $position - 1;
            $response .= "\n⏳ {$peopleAhead} people ahead of you.";
        }
        
        return $response;
    }
    
    protected function getPricingInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        $firstLevel = Level::getFirstRecord();
        
        if (!$firstLevel) {
            return "Pricing information is not available at the moment.";
        }
        
        $categories = Category::where('team_id', $teamId)
            ->where('level_id', $firstLevel->id)
            ->whereJsonContains('category_locations', (string)$locationId)
            ->whereNotNull('amount')
            ->where('amount', '>', 0)
            ->get(['name', 'amount', 'description']);
        
        if ($categories->isEmpty()) {
            return "All our services are currently free of charge!";
        }
        
        $response = "💰 **Pricing Information:**\n\n";
        
        foreach ($categories as $index => $category) {
            $response .= ($index + 1) . ". **{$category->name}**\n";
            $response .= "   Price: \${$category->amount}\n";
            if ($category->description) {
                $response .= "   {$category->description}\n";
            }
            $response .= "\n";
        }
        
        $response .= "Note: Prices are subject to change. Please confirm with an agent.";
        
        return $response;
    }
    
    protected function getContactInformation()
    {
        $teamId = $this->virtualQueue->team_id;
        $locationId = $this->virtualQueue->location_id;
        
        $siteDetail = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();
        
        if (!$siteDetail) {
            return "Contact information is being updated. Please ask a human agent.";
        }
        
        $response = "📞 **Contact Information:**\n\n";
        
        if ($siteDetail->phone) {
            $response .= "📱 Phone: {$siteDetail->phone}\n";
        }
        
        if ($siteDetail->email) {
            $response .= "📧 Email: {$siteDetail->email}\n";
        }
        
        if ($siteDetail->website) {
            $response .= "🌐 Website: {$siteDetail->website}\n";
        }
        
        if ($siteDetail->social_media) {
            $response .= "\n📱 Social Media:\n{$siteDetail->social_media}\n";
        }
        
        $response .= "\nFeel free to reach out through any of these channels!";
        
        return $response ?: "Contact information is not available at the moment.";
    }

    protected function shouldTransferToHuman($message, $sentiment)
    {
        // Check for transfer keywords
        $transferKeywords = ['transfer', 'human', 'agent', 'person', 'speak to someone', 'representative'];
        $lowerMessage = strtolower($message);
        
        foreach ($transferKeywords as $keyword) {
            if (str_contains($lowerMessage, $keyword)) {
                return true;
            }
        }
        
        // Check sentiment
        if ($sentiment === 'negative') {
            return true;
        }
        
        // Check message count
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
        return redirect()->route('human-agent-waiting', ['virtualQueueId' => $this->virtualQueue->id]);
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
        return redirect()->route('queue', ['location_id' => $this->virtualQueue->location_id]);
    }

    protected function addMessage($role, $content)
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->format('H:i'),
        ];
    }

    public function render()
    {
        return view('livewire.ai-agent-call');
    }
}
