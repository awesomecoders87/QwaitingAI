<?php


namespace App\Livewire;


use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\AiActivityLog;
use App\Ai\Agents\BookingAgent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class BookingChatbot extends Component
{
    public $messages = [];
    public $userInput = '';
    public $isOpen = false;
    public $isAiTyping = false;


    /**
     * Selectable options rendered as clickable cards in the UI.
     * Each item: ['label' => 'Display Text', 'value' => 'value to send']
     */
    public $workflowOptions = [];


    /**
     * Current workflow step to give context for which cards are shown.
     * Values: '' | 'select_service' | 'select_date' | 'select_time'
     */
    public $workflowStep = '';


    /**
     * Tracks booking context across chip selections so we don't need
     * to re-parse it from the conversation history.
     */
    public $bookingContext = [
        'service_name' => '',
        'date'         => '',
        'time'         => '',
        'name'         => '',
        'phone'        => '',
        'email'        => '',
    ];


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
            'role'    => 'assistant',
            'content' => "Hi! I'm your booking assistant. I can help you book, reschedule, or cancel your appointments. How can I assist you today?",
            'time'    => now()->format('h:i A')
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
     * Called when user clicks a workflow option card.
     * Routes DIRECTLY to the next API without calling OpenAI for service/date selections.
     * Only time selection (and beyond) goes through OpenAI.
     */
    public function selectOption($value)
    {
        $step = $this->workflowStep; // capture before clearing


        $this->workflowOptions = [];
        $this->workflowStep    = '';


        // Always add the selection as a user message
        $this->messages[] = [
            'role'    => 'user',
            'content' => $value,
            'time'    => now()->format('h:i A')
        ];
        $this->isAiTyping = true;


        switch ($step) {


            case 'select_service':
                // Service selected → directly fetch available dates (no OpenAI)
                $this->bookingContext['service_name'] = $value;
                $this->directAction('get_dates', ['service_name' => $value]);
                break;


            case 'select_date':
                // Date selected → directly fetch available times (no OpenAI)
                $this->bookingContext['date'] = $value;
                $this->directAction('get_times', [
                    'service_name' => $this->bookingContext['service_name'],
                    'date'         => $value,
                ]);
                break;


            case 'select_time':
                // Time selection → directly check availability (no OpenAI)
                $this->bookingContext['time'] = $value;
                $this->directCheckAvailability($value);
                break;


            case 'confirm_booking':
                // User clicked YES or NO on the confirmation chips
                if (strtoupper(trim($value)) === 'YES') {
                    $this->directCreateBooking();
                } else {
                    $this->messages[] = [
                        'role'    => 'assistant',
                        'content' => "No problem! Your booking has been cancelled. 😊 Is there anything else I can help you with?",
                        'time'    => now()->format('h:i A')
                    ];
                    $this->isAiTyping = false;
                    $this->bookingContext = ['service_name' => '', 'date' => '', 'time' => '', 'name' => '', 'phone' => '', 'email' => '', 'booking_refID' => ''];
                }
                break;


            case 'confirm_cancel':
                if (strtoupper(trim($value)) === 'YES' && !empty($this->bookingContext['booking_refID'])) {
                    $this->directCancelBooking();
                } else {
                    $this->messages[] = [
                        'role'    => 'assistant',
                        'content' => "Cancellation aborted. Your booking is safe! 😊 Is there anything else I can help you with?",
                        'time'    => now()->format('h:i A')
                    ];
                    $this->isAiTyping = false;
                    $this->bookingContext['booking_refID'] = '';
                }
                break;


                // Unknown step — fall back to AI
                $this->dispatch('process-ai-turn');
        }
       
        // Ensure scroll updates after any option click
        $this->dispatch('booking-chat-updated');
    }


    /**
     * Directly call check_availability after time chip selection.
     * If available → ask for name/phone/email in one message.
     * If not available → re-show time chips.
     */
    protected function directCheckAvailability(string $timeSlot): void
    {
        // Extract start time: "07:00 AM-08:00 AM" → "07:00 AM"
        $startTime = str_contains($timeSlot, '-')
            ? trim(explode('-', $timeSlot)[0])
            : $timeSlot;


        $params = [
            'service_name' => $this->bookingContext['service_name'],
            'date'         => $this->bookingContext['date'],
            'time'         => $startTime,
        ];


        Log::info('Direct check_availability: ' . json_encode($params));
        $apiResult = $this->executeAction('check_availability', $params);
        Log::info('check_availability result: ' . json_encode($apiResult));


        if ($apiResult === null) {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "I'm having trouble reaching the server. Please try again.",
                'time'    => now()->format('h:i A')
            ];
            $this->isAiTyping = false;
            return;
        }


        // Store hidden for AI context
        $this->messages[] = [
            'role'      => 'system',
            'content'   => "System Event - API Response for 'check_availability': \n" . json_encode($apiResult),
            'time'      => now()->format('h:i A'),
            'is_hidden' => true
        ];


        // Detect availability from response
        $message = strtolower(
            $apiResult['message']
            ?? $apiResult['result']['message']
            ?? $apiResult['status']
            ?? ''
        );
        $isAvailable = str_contains($message, 'available') && !str_contains($message, 'not available');


        if ($isAvailable) {
            $service = $this->bookingContext['service_name'];
            $date    = $this->bookingContext['date'];
            $this->bookingContext['time'] = $startTime;
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "✅ Great news! **{$service}** on **{$date}** at **{$startTime}** is available!\n\nKindly share your details to complete the booking:\n\n📋 **Name, Phone Number, Email**\n*(e.g. John Smith, 9876543210, john@email.com)*",
                'time'    => now()->format('h:i A')
            ];
            $this->isAiTyping = false;
        } else {
            // Not available — show other time slots
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "😔 I'm sorry, that time slot is not available. Not to worry — please choose another time below:",
                'time'    => now()->format('h:i A')
            ];
            // Re-fetch times to show chips again
            $this->directAction('get_times', [
                'service_name' => $this->bookingContext['service_name'],
                'date'         => $this->bookingContext['date'],
            ]);
        }
    }


    /**
     * Called after user types YES on the confirmation chips.
     * Directly calls create_booking API and shows refID.
     */
    protected function directCreateBooking(): void
    {
        $ctx     = $this->bookingContext;
        $params  = [
            'service_name'     => $ctx['service_name'],
            'appointment_date' => $ctx['date'],
            'time'             => $ctx['time'],
            'name'             => $ctx['name'],
            'phone'            => $ctx['phone'],
            'email'            => $ctx['email'],
        ];


        Log::info('Direct create_booking: ' . json_encode($params));
        $apiResult = $this->executeAction('create_booking', $params);
        Log::info('create_booking result: ' . json_encode($apiResult));


        if ($apiResult === null) {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "I'm sorry, I couldn't complete the booking right now. Please try again in a moment.",
                'time'    => now()->format('h:i A')
            ];
            $this->isAiTyping = false;
            return;
        }


        $status  = $apiResult['status']       ?? $apiResult['result']['status']       ?? '';
        $refID   = $apiResult['data']['refID'] ?? $apiResult['refID']                  ?? $apiResult['result']['refID'] ?? '';
        $msgText = $apiResult['message']       ?? $apiResult['result']['message']      ?? '';


        if ($status === 'success' && !empty($refID)) {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "🎉 **Booking Confirmed!** Hello **{$ctx['name']}**, your appointment has been successfully booked!\n\n"
                    . "📋 **Booking Summary**\n"
                    . "✅ **Service:** {$ctx['service_name']}\n"
                    . "📅 **Date:** {$ctx['date']}\n"
                    . "🕐 **Time:** {$ctx['time']}\n"
                    . "👤 **Name:** {$ctx['name']}\n\n"
                    . "🔖 **Reference ID:** `{$refID}`\n\n"
                    . "*Please save this Reference ID — you can use it to reschedule or cancel your booking anytime.*\n\n"
                    . "Is there anything else I can help you with? 😊",
                'time'    => now()->format('h:i A')
            ];
        } else {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "❌ **Booking could not be completed.** {$msgText} Would you like to try again?",
                'time'    => now()->format('h:i A')
            ];
        }


        $this->isAiTyping   = false;
        $this->bookingContext = ['service_name' => '', 'date' => '', 'time' => '', 'name' => '', 'phone' => '', 'email' => '', 'booking_refID' => ''];
    }


    /**
     * Directly calls cancel_booking API when user clicks YES on cancel confirm chips.
     */
    protected function directCancelBooking(): void
    {
        $refID = $this->bookingContext['booking_refID'] ?? '';
        if (empty($refID)) return;


        $apiResult = $this->executeAction('cancel_booking', ['booking_refID' => $refID]);


        if ($apiResult === null) {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "I'm sorry, I couldn't connect to the server right now. Please try again.",
                'time'    => now()->format('h:i A')
            ];
            $this->isAiTyping = false;
            return;
        }


        $status  = $apiResult['status']  ?? $apiResult['result']['status']  ?? '';
        $msgText = $apiResult['message'] ?? $apiResult['result']['message'] ?? 'Cancellation failed.';


        if ($status === 'success') {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "✅ **Booking Cancelled Successfully!**\n\nYour appointment (Ref ID: `{$refID}`) has been cancelled. If you need to book again in the future, just let me know! 😊",
                'time'    => now()->format('h:i A')
            ];
        } else {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "❌ **Cancellation could not be completed.** {$msgText}",
                'time'    => now()->format('h:i A')
            ];
        }


        $this->isAiTyping = false;
        $this->bookingContext['booking_refID'] = '';
    }


    /**
     * Execute an action directly (bypassing OpenAI), then auto-reply or dispatch AI.
     * Used for deterministic steps like service_selected → get_dates.
     */
    protected function directAction(string $action, array $params): void
    {
        Log::info("Direct action [{$action}] (no OpenAI): " . json_encode($params));


        $apiResult = $this->executeAction($action, $params);
        Log::info("Direct action result [{$action}]: " . json_encode(['result' => $apiResult]));


        // Guard: API call may return null on network failure
        if ($apiResult === null) {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "I'm having trouble reaching the server. Please try again in a moment.",
                'time'    => now()->format('h:i A')
            ];
            $this->isAiTyping = false;
            return;
        }


        $this->extractWorkflowOptions($action, $apiResult);


        // Store hidden API result so AI has context in the next user turn
        $this->messages[] = [
            'role'      => 'system',
            'content'   => "System Event - API Response for '{$action}': \n" . json_encode($apiResult),
            'time'      => now()->format('h:i A'),
            'is_hidden' => true
        ];


        $autoReply = $this->generateAutoReply($action, $apiResult);
        if ($autoReply !== null) {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => $autoReply,
                'time'    => now()->format('h:i A')
            ];
            $this->isAiTyping = false;
            $this->dispatch('booking-chat-updated');
            return;
        }


        // Non-auto-reply actions → let OpenAI interpret the result
        $this->dispatch('process-ai-turn');
    }


    public function sendMessage()
    {
        if (trim($this->userInput) === '') return;


        // Typing a message manually also clears any pending option cards
        $this->workflowOptions = [];
        $this->workflowStep    = '';


        $userText          = $this->userInput;
        $this->messages[]  = [
            'role'    => 'user',
            'content' => $userText,
            'time'    => now()->format('h:i A')
        ];


        $this->userInput    = '';
        $this->isAiTyping   = true;


        // Dispatch event to instantly render the user's message to the UI,
        // then the browser will immediately trigger processAgentTurn in the background
        $this->dispatch('process-ai-turn');
        $this->dispatch('booking-chat-updated');
    }


    #[On('process-ai-turn')]
    public function processAgentTurn()
    {
        $teamId     = tenant('id') ?? 3;
        $locationId = session('selectedLocation') ?? 80;


        $agent = new BookingAgent($teamId, $locationId);


        // ─────────────────────────────────────────────────────────────
        // Build trimmed context for OpenAI.
        // IMPORTANT: We do NOT re-send all hidden API result messages every turn.
        // The AI already processed & responded to older API results.
        // Only the MOST RECENT hidden message (latest API result) is kept.
        // Visible messages are capped at 20 to prevent context bloat + timeouts.
        // ─────────────────────────────────────────────────────────────
        $visibleMessages = [];
        $lastHiddenMsg   = null;


        foreach ($this->messages as $msg) {
            if (empty($msg['is_hidden'])) {
                $visibleMessages[] = [
                    'role'    => $msg['role'],
                    'content' => $msg['content']
                ];
            } else {
                // Keep overwriting — only the last hidden message survives
                $lastHiddenMsg = [
                    'role'    => 'system',
                    'content' => $msg['content']
                ];
            }
        }


        // Cap visible messages to last 20 (keeps conversation coherent without bloat)
        if (count($visibleMessages) > 20) {
            $visibleMessages = array_slice($visibleMessages, -20);
        }


        // Merge: last hidden API result first (gives AI the data it needs), then visible conversation
        $apiMessages = $lastHiddenMsg
            ? array_merge([$lastHiddenMsg], $visibleMessages)
            : $visibleMessages;


        $response = $agent->run($apiMessages);


        if ($response['type'] === 'gpt_response') {
            $data = $response['message'];


            Log::info('--- AI WORKFLOW STEP ---');
            Log::info('AI JSON Response: ' . json_encode($data));


            // ─────────────────────────────────────────────────────────────
            // GUARD 1: Schema validation
            // If AI returned raw API data instead of the chatbot schema,
            // inject a correction and retry silently (max 1 auto-correction).
            // ─────────────────────────────────────────────────────────────
            $hasValidSchema = isset($data['intent']) && isset($data['reply']) && isset($data['action']);
            if (!$hasValidSchema) {
                Log::warning('AI returned invalid schema (likely echoed API data). Auto-correcting...');
                Log::warning('Invalid payload: ' . json_encode($data));


                // Check we haven't already injected a correction (prevent infinite loop)
                $alreadyCorrected = collect($this->messages)
                    ->filter(fn($m) => !empty($m['is_hidden']) && str_contains($m['content'] ?? '', 'SCHEMA_CORRECTION'))
                    ->count() < 3; // allow up to 3 corrections before giving up


                if ($alreadyCorrected) {
                    $this->messages[] = [
                        'role'      => 'system',
                        'content'   => 'SCHEMA_CORRECTION: Your last response had an incorrect format. You MUST always respond with the exact JSON schema: {"intent":"...","reply":"...","action":"...","is_confirmation_required":false,"confirmation_type":"none","data":{...}}. Look at the conversation history and continue from where you left off.',
                        'time'      => now()->format('h:i A'),
                        'is_hidden' => true
                    ];
                    $this->dispatch('process-ai-turn');
                    return;
                }


                // Gave up correcting — show a graceful error
                $this->messages[]   = ['role' => 'assistant', 'content' => "I got confused for a moment. Could you please repeat your last selection?", 'time' => now()->format('h:i A')];
                $this->isAiTyping   = false;
                $this->dispatch('booking-chat-updated');
                return;
            }


            $reply     = $data['reply'] ?? null;
            $action    = $data['action'] ?? 'none';
            $apiParams = $data['data'] ?? [];


            // ─────────────────────────────────────────────────────────────
            // GUARD 2: Action deduplication
            // If the AI fires the same action we JUST received data for on the very last turn,
            // force it to 'none' so it presents the data instead of looping.
            // ─────────────────────────────────────────────────────────────
            if ($action !== 'none') {
                $lastMsg = end($this->messages);
                if ($lastMsg && !empty($lastMsg['is_hidden']) && str_contains($lastMsg['content'] ?? '', "API Response for '{$action}'")) {
                    Log::warning("AI repeated action '{$action}' immediately after receiving its data — forcing action to 'none'.");
                    $action = 'none';
                }
            }


            // ─────────────────────────────────────────────────────────────
            // GUARD 3: Strip duplicate list from reply when chips are showing
            // If workflow options are already populated (from a previous action),
            // the chips panel shows the options as interactive cards — so remove
            // any markdown bullet list from the AI reply to avoid duplication.
            // ─────────────────────────────────────────────────────────────
            if (!empty($this->workflowOptions) && !empty($reply)) {
                // Remove lines that are markdown list items: "- item" or "* item"
                $reply = preg_replace('/\n?\s*[-*]\s+.+/u', '', $reply);
                $reply = trim($reply);
                // If reply is now just a colon-ending phrase, clean it up
                $reply = rtrim($reply, ':') . ':' !== $reply ? $reply : rtrim($reply, ':');
            }


            // Show the conversational reply first
            if (!empty($reply)) {
                $this->messages[] = [
                    'role'    => 'assistant',
                    'content' => $reply,
                    'time'    => now()->format('h:i A')
                ];
                $this->storeAiActivityLog(
                    $apiMessages[count($apiMessages) - 1]['content'],
                    json_encode($data),
                    $teamId,
                    $locationId,
                    $response['usage']
                );


                // ─────────────────────────────────────────────────────────────
                // INTERCEPT: If AI signals confirmation required, save details
                // from AI's data field and show YES / NO chips directly.
                // This bypasses OpenAI for the confirm → create_booking step.
                // ─────────────────────────────────────────────────────────────
                if (!empty($data['is_confirmation_required'])) {
                    $d = $data['data'] ?? [];
                    if (!empty($d['name']))             $this->bookingContext['name']         = $d['name'];
                    if (!empty($d['phone']))            $this->bookingContext['phone']        = $d['phone'];
                    if (!empty($d['email']))            $this->bookingContext['email']        = $d['email'];
                    if (!empty($d['service_name']))     $this->bookingContext['service_name'] = $d['service_name'];
                    if (!empty($d['appointment_date'])) $this->bookingContext['date']         = $d['appointment_date'];
                    if (!empty($d['date']))             $this->bookingContext['date']         = $d['date'];
                    if (!empty($d['time']))             $this->bookingContext['time']         = $d['time'];


                    // Show YES / NO chips so the next chip click calls directCreateBooking()
                    $this->workflowStep    = 'confirm_booking';
                    $this->workflowOptions = [
                        ['label' => '✅ YES — Confirm Booking', 'value' => 'YES'],
                        ['label' => '❌ NO — Cancel',           'value' => 'NO'],
                    ];
                    $this->isAiTyping = false;
                    $this->dispatch('booking-chat-updated');
                    return;
                }
            }


            // Handle Action
            if ($action !== 'none') {
                Log::info("Executing Action: {$action} " . json_encode(['params' => $apiParams]));
                $apiResult = $this->executeAction($action, $apiParams);
                Log::info("Action Result ({$action}): " . json_encode(['result' => $apiResult]));


                // Extract selectable options (chips) from the API result
                $this->extractWorkflowOptions($action, $apiResult);


                // Store hidden API result for AI context in the NEXT user turn
                $this->messages[] = [
                    'role'      => 'system',
                    'content'   => "System Event - API Response for '{$action}': \n" . json_encode($apiResult),
                    'time'      => now()->format('h:i A'),
                    'is_hidden' => true
                ];


                // For simple data-display actions, auto-generate the assistant reply
                // WITHOUT calling OpenAI — the chips panel shows the options anyway.
                // Only complex results (availability, booking creation) need AI reasoning.
                $autoReply = $this->generateAutoReply($action, $apiResult);
                if ($autoReply !== null) {
                    Log::info("Auto-reply generated for '{$action}' (no OpenAI call needed).");
                    $this->messages[] = [
                        'role'    => 'assistant',
                        'content' => $autoReply,
                        'time'    => now()->format('h:i A')
                    ];
                    $this->isAiTyping = false;
                    $this->dispatch('booking-chat-updated');
                    return;
                }


                // For complex results → let OpenAI process and respond intelligently
                $this->dispatch('process-ai-turn');
                return;
            }
        } else {
            $this->messages[] = [
                'role'    => 'assistant',
                'content' => "I'm sorry, I'm having trouble connecting right now.",
                'time'    => now()->format('h:i A')
            ];
        }


        $this->isAiTyping = false;
        $this->dispatch('booking-chat-updated');
    }


    /**
     * Auto-generate a simple assistant reply for data-display actions.
     * Returns null for actions that need OpenAI to interpret the result.
     */
    protected function generateAutoReply(string $action, ?array $apiResult): ?string
    {
        if ($apiResult === null) return null;


        switch ($action) {
            case 'check_service':
                return 'Here are the available services:';


            case 'get_dates':
                $service = $apiResult['result']['service']['name'] ?? '';
                return $service
                    ? "Here are the available dates for **{$service}**:"
                    : 'Here are the available dates:';


            case 'get_times':
                $service = $apiResult['result']['service']['name'] ?? '';
                $date    = $apiResult['result']['date'] ?? '';
                $msg     = 'Here are the available times';
                if ($service) $msg .= " for **{$service}**";
                if ($date)    $msg .= " on **{$date}**";
                return $msg . ':';


            case 'get_booking':
                $status = $apiResult['status'] ?? $apiResult['result']['status'] ?? '';
                if ($status === 'success') {
                    $d = $apiResult['data'] ?? $apiResult['result']['data'] ?? [];
                    $service = $d['service_name'] ?? 'Service';
                    $date    = $d['booking_date'] ?? 'Unknown Date';
                    $time    = $d['booking_time'] ?? 'Unknown Time';
                   
                    return "Here are your booking details:\n"
                        . "✅ **Service:** {$service}\n"
                        . "📅 **Date:** {$date}\n"
                        . "🕐 **Time:** {$time}\n\n"
                        . "Are you sure you want to cancel this booking? Type **YES** to cancel or **NO** to keep it.";
                }
                return "I couldn't find a booking with that Reference ID. Please check and try again.";


            default:
                // check_availability, create_booking, reschedule_booking, cancel_booking
                // → OpenAI must interpret and respond
                return null;
        }
    }


    /**
     * Extract selectable workflow options from an API result and populate $workflowOptions.
     * This is what drives the clickable cards in the blade view.
     */
    protected function extractWorkflowOptions(string $action, ?array $apiResult): void
    {
        if ($apiResult === null) return; // API call returned nothing — skip safely


        $this->workflowOptions = [];
        $this->workflowStep    = '';


        switch ($action) {
            case 'get_booking':
                // For get_booking we want to auto-extract the refID into context
                $status = $apiResult['status'] ?? $apiResult['result']['status'] ?? '';
                if ($status === 'success') {
                    $d = $apiResult['data'] ?? $apiResult['result']['data'] ?? [];
                    if (!empty($d['refID'])) $this->bookingContext['booking_refID'] = $d['refID'];
                   
                    $this->workflowStep = 'confirm_cancel';
                    $this->workflowOptions = [
                        ['label' => '✅ YES — Cancel Booking', 'value' => 'YES'],
                        ['label' => '❌ NO — Keep Booking',    'value' => 'NO'],
                    ];
                }
                break;


            case 'check_service':
                $services = $apiResult['services'] ?? $apiResult['data']['services'] ?? [];
                if (empty($services) && isset($apiResult['status'])) {
                    // Some API wrappers nest differently
                    $services = $apiResult['services'] ?? [];
                }
                // Flatten nested result wrapper if present
                if (isset($apiResult['result']['services'])) {
                    $services = $apiResult['result']['services'];
                }
                if (!empty($services)) {
                    $this->workflowStep    = 'select_service';
                    $this->workflowOptions = array_map(fn($s) => [
                        'label' => $s['name'],
                        'value' => $s['name']
                    ], array_values($services));
                }
                break;


            case 'get_dates':
                // Dates may come as array of strings or objects. API returns 'available_dates' key.
                $dates = $apiResult['available_dates']
                    ?? $apiResult['dates']
                    ?? $apiResult['result']['available_dates']
                    ?? $apiResult['result']['dates']
                    ?? $apiResult['data']['dates']
                    ?? [];
                if (!empty($dates)) {
                    $this->workflowStep    = 'select_date';
                    $this->workflowOptions = array_map(function ($d) {
                        $raw   = is_array($d) ? ($d['date'] ?? $d['value'] ?? '') : $d;
                        $label = $raw;
                        // Try to format nicely: "2026-03-10" → "Mon, Mar 10"
                        try {
                            $label = \Carbon\Carbon::parse($raw)->format('D, M d Y');
                        } catch (\Throwable $e) {}
                        return ['label' => $label, 'value' => $raw];
                    }, array_values($dates));
                }
                break;


            case 'get_times':
                // API returns: {"result":{"available_times":[...]}}
                $times = $apiResult['result']['available_times']
                    ?? $apiResult['available_times']
                    ?? $apiResult['result']['times']
                    ?? $apiResult['times']
                    ?? $apiResult['data']['times']
                    ?? [];
                if (!empty($times)) {
                    $this->workflowStep    = 'select_time';
                    $this->workflowOptions = array_map(function ($t) {
                        $raw = is_array($t) ? ($t['time'] ?? $t['value'] ?? '') : $t;
                        return ['label' => $raw, 'value' => $raw];
                    }, array_values($times));
                }
                break;
        }
    }


    protected function executeAction($action, $params)
    {
        // Strip all empty/null values from params first
        $params = is_array($params)
            ? array_filter($params, fn($v) => $v !== '' && $v !== null && $v !== [])
            : [];


        $uri    = '';
        $method = 'POST';


        switch ($action) {


            case 'check_service':
                $uri    = '/api/check-service';
                $method = 'GET';
                $params = []; // No params needed
                break;


            case 'get_dates':
                $uri    = '/api/get-available-dates';
                // Only send: service_name
                $params = array_intersect_key($params, array_flip(['service_name']));
                break;


            case 'get_times':
                $uri    = '/api/get-available-times';
                // Only send: service_name, date
                // Fallback: use appointment_date if date is missing
                if (empty($params['date']) && !empty($params['appointment_date'])) {
                    $params['date'] = $params['appointment_date'];
                }
                $params = array_intersect_key($params, array_flip(['service_name', 'date']));
                break;


            case 'check_availability':
                $uri    = '/api/check-datetime-availability';
                $method = 'POST_QUERY'; // POST with params in query string (matches Postman exactly)
                // Extract start time only: "07:00 AM-08:00 AM" → "07:00 AM"
                if (!empty($params['time']) && str_contains($params['time'], '-')) {
                    $params['time'] = trim(explode('-', $params['time'])[0]);
                }
                $params = array_intersect_key($params, array_flip(['service_name', 'date', 'time']));
                break;


            case 'create_booking':
                $uri    = '/api/check-and-book';
                // Map 'date' → 'appointment_date' if needed
                if (!empty($params['date']) && empty($params['appointment_date'])) {
                    $params['appointment_date'] = $params['date'];
                }
                // Extract start time only: "07:00 AM-08:00 AM" → "07:00 AM"
                if (!empty($params['time']) && str_contains($params['time'], '-')) {
                    $params['time'] = trim(explode('-', $params['time'])[0]);
                }
                // Only send required booking fields
                $params = array_intersect_key($params, array_flip([
                    'service_name', 'appointment_date', 'time',
                    'name', 'phone', 'email', 'phone_code'
                ]));
                break;


            case 'get_booking':
                $uri    = '/api/get-booking-details';
                $params = array_intersect_key($params, array_flip(['booking_refID']));
                break;


            case 'reschedule_booking':
                $uri    = '/api/edit-booking';
                $params = array_intersect_key($params, array_flip([
                    'booking_refID', 'service_name', 'date', 'time'
                ]));
                break;


            case 'cancel_booking':
                $uri    = '/api/cancel-booking';
                $params = array_intersect_key($params, array_flip(['booking_refID']));
                break;


            default:
                return null;
        }


        Log::info("Calling API [{$method}] {$uri} with params: " . json_encode($params));


        try {
            $baseUrl = 'https://qwaiting-ai.thevistiq.com';
            $url     = $baseUrl . $uri;


            if ($method === 'GET') {
                $response = \Illuminate\Support\Facades\Http::timeout(30)->get($url, $params);
            } elseif ($method === 'POST_QUERY') {
                // POST but with params as URL query string (as shown in Postman collection)
                $queryUrl = $url . '?' . http_build_query($params);
                $response = \Illuminate\Support\Facades\Http::timeout(30)->post($queryUrl);
            } else {
                // Use asForm() to send as form-data matching the Postman collection
                $response = \Illuminate\Support\Facades\Http::asForm()->timeout(30)->post($url, $params);
            }
            return $response->json();
        } catch (\Exception $e) {
            Log::error("API call failed [{$action}]: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * Store Token usage into AiActivityLog
     */
    private function storeAiActivityLog($prompt, $response, $teamId, $locationId, $usage)
    {
        try {
            $promptTokens     = $usage->promptTokens ?? 0;
            $completionTokens = $usage->completionTokens ?? 0;
            $totalTokens      = $usage->totalTokens ?? 0;


            $creditsConsumed = ($totalTokens / 1000) * 0.001;


            AiActivityLog::create([
                'team_id'           => $teamId,
                'location_id'       => $locationId,
                'chatbot_name'      => 'BookingAgent_Livewire',
                'type'              => 'booking_assistant',
                'prompt'            => $prompt,
                'response'          => $response,
                'prompt_tokens'     => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens'      => $totalTokens,
                'credits_consumed'  => $creditsConsumed,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI Activity: ' . $e->getMessage());
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
