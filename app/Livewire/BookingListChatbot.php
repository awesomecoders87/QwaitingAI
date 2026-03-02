<?php

namespace App\Livewire;

use Livewire\Component;
use App\Mcp\Tools\BookingQueryTool;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\On;
use App\Models\SiteDetail;

class BookingListChatbot extends Component
{
    public array  $messages   = [];
    public string $chatInput  = '';
    public bool   $isLoading  = false;
    public bool   $isChatOpen = false;

    // Must be public so Livewire persists between round-trips
    public int $teamId     = 0;
    public int $locationId = 0;

    // ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->teamId     = (int) tenant('id');
        $this->locationId = (int) Session::get('selectedLocation');

        $tz = SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->locationId)->value('select_timezone') ?? config('app.timezone', 'UTC');

        $this->messages[] = [
            'role'    => 'assistant',
            'content' => 'Hi! Ask me anything about your bookings — counts, trends, available slots, cancellations, and more.',
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

        // Log::info('[BookingListChatbot] User sent message: ' . $userInput);

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

        try {
            // Log::info('[BookingListChatbot] Processing AI request for: ' . $lastUserMsg);
            
            // Build history strictly before calling tool so it can be passed
            $historyContext = [];
            foreach (array_slice($this->messages, -6) as $msg) {
                if ($msg['content'] !== $lastUserMsg) {
                    $historyContext[] = ['role' => $msg['role'], 'content' => $msg['content']];
                }
            }
            
            $reply = $this->callBookingAI($lastUserMsg, $historyContext);
            // Log::info('[BookingListChatbot] AI response generated', ['reply' => $reply]);
        } catch (\Throwable $e) {
            Log::error('[BookingListChatbot] Error: ' . $e->getMessage(), [
                'teamId'     => $this->teamId,
                'locationId' => $this->locationId,
            ]);
            $reply = 'Sorry, I encountered an error. Please try again.';
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

    private function callBookingAI(string $userQuery, array $historyContext = []): string
    {
        $tool        = new BookingQueryTool();
        $toolResult  = $tool->query($userQuery, $this->teamId, $this->locationId, $historyContext);
        $servicesArr = $tool->getAllServices($this->teamId, $this->locationId);
        $servicesCount = count($servicesArr);
        $allServices = json_encode($servicesArr, JSON_UNESCAPED_UNICODE);

        $today       = Carbon::now()->format('D, d M Y H:i');
        $contextJson = json_encode($toolResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $systemPrompt = <<<PROMPT
You are an expert AI Booking Assistant embedded in the Booking List dashboard.
Today is {$today}.

Total available services: {$servicesCount}
Available services:
{$allServices}

## Your Role
Answer ANY booking question using ONLY the structured data below.
If the data is insufficient, explain what you can and guide the user.

## Response Style
- Concise, friendly, professional
- Never say "JSON", "API", "database"
- Use bullet lists for dates/slots
- Highlight key numbers
- If data has an error key, gracefully explain and suggest alternatives

## Structured Data:
```json
{$contextJson}
```
PROMPT;

        // Keep last 6 turns for context-awareness (excluding the current one)
        $history = [];
        foreach (array_slice($this->messages, -6) as $msg) {
            if ($msg['content'] !== $userQuery) {
                $history[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }
        
        $openai   = new OpenAIService();
        $finalHistory = $history;
        $finalHistory[] = ['role' => 'user', 'content' => $userQuery];
        
        $loggingContext = [
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'chatbot_name' => 'BookingListChatbot',
            'type' => 'booking_chatbot'
        ];
        
        $response = $openai->generateResponse($finalHistory, $systemPrompt, $loggingContext);

        return $response ?? "I'm having trouble connecting right now. Please try again shortly.";
    }

    // ─────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.booking-list-chatbot');
    }
}
