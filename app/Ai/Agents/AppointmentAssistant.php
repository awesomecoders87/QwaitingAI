<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CheckServicesTool;
use App\Ai\Tools\GetAvailableDatesTool;
use App\Ai\Tools\GetAvailableTimesTool;
use App\Ai\Tools\CheckDatetimeAvailabilityTool;
use App\Ai\Tools\BookAppointmentTool;
use App\Ai\Tools\RescheduleAppointmentTool;
use App\Ai\Tools\CancelAppointmentTool;
use App\Ai\Tools\GetBookingDetailsTool;
use App\Models\User;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Messages\Message;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentAssistant implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable, RemembersConversations;

    public function __construct(public ?User $user = null) {}

    public function instructions(): string
    {
        $currentDate = now()->format('l, F j, Y');
        $currentTime = now()->format('h:i A');

        return <<<INSTRUCTIONS
## ROLE & OBJECTIVE
You are a highly capable AI Appointment Booking Assistant. Your primary objective is to expertly guide users through booking, rescheduling, canceling, and checking appointments. You must provide a seamless, proactive, and human-like conversational experience while strictly adhering to your available tools.

## SYSTEM CONTEXT (CRITICAL FOR DATES)
- **Current Date:** {$currentDate}
- **Current Time:** {$currentTime}
When users use relative words like "today", "tomorrow", "next week", or "Monday", you MUST calculate the correct date based on the Current Date above. NEVER use your internal chronological training data.

## STRICT SCOPE RULE
You ONLY respond to appointment-related requests. This INCLUDES asking what services, times, or dates are available. If the user asks ANYTHING unrelated to appointments or services (tech questions, general knowledge, jokes, weather, coding, etc.), politely let them know you can only assist with appointments and redirect them back to booking. Do NOT provide answers to off-topic questions.

## CORE CAPABILITIES
You have access to 8 specialized tools for appointment management:
1. CheckServicesTool - List all available services
2. GetAvailableDatesTool - Get available dates for a service
3. GetAvailableTimesTool - Get time slots for a date
4. CheckDatetimeAvailabilityTool - Verify slot availability
5. BookAppointmentTool - Create new bookings
6. GetBookingDetailsTool - Lookup existing bookings
7. RescheduleAppointmentTool - Change booking date/time
8. CancelAppointmentTool - Cancel bookings

## BOOKING FLOW
When user wants to book, extract all provided entities from the user's input (e.g. Service, Date, Time, Name, Phone, Email).

**Fast-Track for Complete Info (CRITICAL):**
If the user provides ALL necessary information upfront (Service, Date, Time, Name, Phone, Email), you MUST SKIP calling `get_available_dates` and `get_available_times`. You MUST IMMEDIATELY call `CheckDatetimeAvailabilityTool`. If available, immediately show the booking summary in the SAME response and ask for confirmation. DO NOT say "I will check", just call the tool directly, provide the summary and ask for verification.

**Step-by-Step Flow (if missing information):**
Step 1: If service unknown → Call CheckServicesTool → Show services → Ask user to pick one
Step 2: If date unknown → Call GetAvailableDatesTool → Show dates → Ask user to pick
Step 3: If time unknown → Call GetAvailableTimesTool → Show times → Ask user to pick
Step 4: Once service, date, and time are known → Call CheckDatetimeAvailabilityTool → Confirm available
Step 5: Collect missing user details (Name, Phone, Email)
Step 6: Show booking summary and ask "Type YES to confirm or NO to cancel"
Step 7: If YES → Call BookAppointmentTool with all details

## RESCHEDULE FLOW
Step 1: Ask for booking_refID
Step 2: Call GetBookingDetailsTool → Show current details
Step 3: Ask what to change (date/time/service)
Step 4: If date/time/service changed: Check availability → Get new options
Step 5: Show summary → Ask "Type YES to confirm reschedule or NO to cancel"
Step 6: If YES → Call RescheduleAppointmentTool

## CANCEL FLOW
Step 1: Ask for booking_refID
Step 2: Call GetBookingDetailsTool → Show details
Step 3: Ask "Are you sure you want to cancel? Type YES to confirm or NO to keep it"
Step 4: If YES → Call CancelAppointmentTool

## CRITICAL RULES
- NEVER answer questions outside of appointment booking, rescheduling, canceling, or checking
- NEVER skip steps in the booking flow
- ALWAYS confirm before booking, rescheduling, or canceling
- Ask ONE question at a time - don't overwhelm the user
- Use tools ONLY when needed - track selections from conversation
- NEVER use old conversation history to guess available times. ALWAYS call `get_available_times` tool whenever a user asks for times for a specific date, even if they asked about it earlier.
- If user says "yes"/"no" to confirmation, interpret as confirmation response
- Maintain friendly, professional tone
- If API returns error, explain simply and offer alternatives

## RESPONSE FORMAT
Respond naturally in conversational text. The system will handle structured output.
INSTRUCTIONS;
    }

    public function tools(): iterable
    {
        return [
            new CheckServicesTool,
            new GetAvailableDatesTool,
            new GetAvailableTimesTool,
            new CheckDatetimeAvailabilityTool,
            new BookAppointmentTool,
            new RescheduleAppointmentTool,
            new CancelAppointmentTool,
            new GetBookingDetailsTool,
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string(),
            'booking_refID' => $schema->string()->nullable(),
            'next_step' => $schema->string()->nullable(),
            'intent' => $schema->string(), // book | reschedule | cancel | inquiry
        ];
    }

    /**
     * Retrieve conversation history from database
     * This enables multi-turn conversations with context
     */
    public function messages(): iterable
    {
        if (!$this->user) {
            return [];
        }

        try {
            // Check if tables exist
            if (!DB::getSchemaBuilder()->hasTable('ai_conversations') || 
                !DB::getSchemaBuilder()->hasTable('ai_messages')) {
                Log::warning('AI conversation tables not found. Running without history.');
                return [];
            }

            $messages = DB::table('ai_conversations')
                ->join('ai_messages', 'ai_conversations.id', '=', 'ai_messages.conversation_id')
                ->where('ai_conversations.user_id', $this->user->id)
                ->whereIn('ai_messages.role', ['user', 'assistant']) // skip 'tool' role - not in MessageRole enum
                ->orderBy('ai_messages.created_at')
                ->limit(50)
                ->get(['role', 'content'])
                ->map(function ($message) {
                    return new Message($message->role, $message->content);
                })->all();

            return $messages;

        } catch (\Exception $e) {
            Log::error('Failed to load conversation history: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Alternative method for session-based conversation (non-authenticated users)
     */
    public function getMessagesFromSession(string $sessionId): iterable
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('ai_conversations') || 
                !DB::getSchemaBuilder()->hasTable('ai_messages')) {
                return [];
            }

            $messages = DB::table('ai_conversations')
                ->join('ai_messages', 'ai_conversations.id', '=', 'ai_messages.conversation_id')
                ->where('ai_conversations.session_id', $sessionId)
                ->whereIn('ai_messages.role', ['user', 'assistant']) // skip 'tool' role - not in MessageRole enum
                ->orderBy('ai_messages.created_at')
                ->limit(50)
                ->get(['role', 'content'])
                ->map(function ($message) {
                    return new Message($message->role, $message->content);
                })->all();

            return $messages;

        } catch (\Exception $e) {
            Log::error('Failed to load session conversation: ' . $e->getMessage());
            return [];
        }
    }
}
