<div
    x-data="{
        open: $wire.entangle('isChatOpen'),
        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('booking-chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    }"
    x-init="$watch('open', () => scrollToBottom())"
    @booking-chat-updated.window="scrollToBottom()"
>
    <div class="fixed bottom-6 right-6 flex flex-col items-end gap-4" style="z-index: 2147483647 !important;">
        {{-- ── Chat Panel ──────────────────────────────────────────── --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            style="display:none; max-height: calc(100vh - 120px);"
            class="w-[360px] sm:w-[400px] flex flex-col rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800"
        >
        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3 shrink-0" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
            <div class="flex items-center justify-center w-9 h-9 rounded-full bg-white/20 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold text-sm leading-tight">Booking Assistant</p>
                <p class="text-white/70 text-xs">AI-powered · Booking Insights</p>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="block w-2 h-2 rounded-full bg-green-400"></span>
                <span class="text-white/70 text-xs">Online</span>
            </div>
        </div>

        {{-- Messages --}}
        <div
            id="booking-chat-messages"
            class="flex-1 overflow-y-auto px-4 py-3 space-y-3 bg-gray-50 dark:bg-gray-900"
            style="min-height: 260px; max-height: 380px;"
        >
            @foreach($messages as $msg)
                @if($msg['role'] === 'user')
                    <div class="flex justify-end gap-2 items-end">
                        <div class="max-w-[78%]">
                            <div class="rounded-2xl rounded-br-sm px-3.5 py-2 text-sm text-white shadow-sm leading-relaxed"
                                 style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                                {{ $msg['content'] }}
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 text-right">{{ $msg['time'] }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex justify-start gap-2 items-end">
                        <div class="flex-shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow">
                            <svg class="w-3.5 h-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div class="max-w-[78%]">
                            <div class="chatbot-response-content rounded-2xl rounded-bl-sm px-3.5 py-2 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-white/90 shadow-sm border border-gray-100 dark:border-gray-700 leading-relaxed">
                                {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">{{ $msg['time'] }}</p>
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Typing indicator --}}
            @if($isLoading)
                <div class="flex justify-start gap-2 items-end">
                    <div class="flex-shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow">
                        <svg class="w-3.5 h-3.5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                    <div class="rounded-2xl rounded-bl-sm px-4 py-3 bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-1.5">
                            <span class="blink-dot w-2 h-2 rounded-full bg-indigo-400" style="animation-delay:0ms"></span>
                            <span class="blink-dot w-2 h-2 rounded-full bg-violet-400" style="animation-delay:180ms"></span>
                            <span class="blink-dot w-2 h-2 rounded-full bg-indigo-400" style="animation-delay:360ms"></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Predefined Query Chips (shown until first user message) --}}
        @if(count(array_filter($messages, fn($m) => $m['role'] === 'user')) === 0)
        <div class="px-4 py-2 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 shrink-0">
            <p class="text-[10px] text-gray-400 mb-2 font-medium uppercase tracking-wide">Quick questions</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach([
                    'Total bookings this month',
                    'Cancelled bookings this week',
                    'Most popular service',
                    'Available slots today',
                    'Upcoming bookings',
                    'Booking trends last 30 days',
                ] as $chip)
                <button
                    wire:click="sendQuickQuery('{{ $chip }}')"
                    wire:loading.attr="disabled"
                    class="text-xs px-2.5 py-1 rounded-full border border-indigo-200 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:border-indigo-400 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >{{ $chip }}</button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Input --}}
        <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 shrink-0">
            <form wire:submit.prevent="sendMessage" class="flex items-center gap-2" autocomplete="off">
                <input
                    type="text"
                    wire:model="chatInput"
                    id="booking-chat-input"
                    placeholder="Ask about bookings…"
                    autocomplete="off"
                    :disabled="$wire.isLoading"
                    class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-violet-500 disabled:opacity-50 transition"
                />
                <button
                    type="submit"
                    :disabled="$wire.isLoading || !$wire.chatInput.trim()"
                    class="flex items-center justify-center w-10 h-10 rounded-xl text-white transition-all duration-200 hover:opacity-90 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed shrink-0"
                    style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
            <p class="text-center text-[10px] text-gray-400 mt-1.5">Powered by · Qwaiting AI</p>
        </div>
    </div>
        {{-- ── Floating Toggle Button ─────────────────────────────── --}}
        <div class="relative">
            <button
                @click="open = !open"
                class="flex items-center justify-center w-14 h-14 rounded-full shadow-2xl text-white transition-all duration-300 hover:scale-110 active:scale-95 focus:outline-none"
                style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);"
                title="Booking Assistant"
            >
                <span x-show="!open" x-transition.opacity>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z"/>
                    </svg>
                </span>
                <span x-show="open" x-transition.opacity>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
            </button>
            <!-- <span
                x-show="!open"
                x-transition.opacity
                class="absolute -top-1 -right-1 z-10 text-[10px] font-bold px-2 py-0.5 rounded-full text-white shadow pointer-events-none"
                style="background:#8b5cf6;"
            >AI</span> -->
        </div>
    </div>

    <style>
    @keyframes blink-dot {
        0%, 80%, 100% { opacity: 0.2; transform: scale(0.85); }
        40%            { opacity: 1;   transform: scale(1.15); }
    }
    .blink-dot { animation: blink-dot 1.2s ease-in-out infinite; }

    .chatbot-response-content p {
        margin-bottom: 0.5em;
    }
    .chatbot-response-content p:last-child {
        margin-bottom: 0;
    }
    .chatbot-response-content strong {
        font-weight: 600;
        color: inherit;
    }
    .chatbot-response-content ul {
        list-style-type: disc;
        padding-left: 1.25em;
        margin-bottom: 0.5em;
    }
    .chatbot-response-content li {
        margin-bottom: 0.25em;
    }
    </style>
</div>
