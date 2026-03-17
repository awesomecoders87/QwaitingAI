<div
    x-data="{
        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('chat-messages-container');
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    }"
    @booking-chat-updated.window="scrollToBottom()"
    x-init="scrollToBottom()"
>
    <!-- Chat Widget Toggle Button -->
    <button wire:click="toggleChat"
            class="fixed bottom-6 right-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-full w-14 h-14 shadow-lg flex items-center justify-center transition-all hover:scale-110 hover:shadow-xl z-50">
        @if($isOpen)
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" />
            </svg>
        @endif
    </button>

    <!-- Chat Window -->
    @if($isOpen)
    <div class="fixed bottom-24 right-4 sm:right-6 w-[90vw] sm:w-[420px] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden z-50 h-[600px] max-h-[85vh] border border-gray-100">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 p-2 rounded-full border border-white/30 backdrop-blur-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-tight">AI Booking Assistant</h3>
                    <p class="text-xs text-indigo-100">Powered by Qwaiting AI</p>
                </div>
            </div>
            <div class="flex items-center text-xs font-semibold bg-white/10 px-2 py-1 rounded-full">
                <div class="w-2 h-2 rounded-full bg-green-400 mr-1.5 animate-pulse"></div>
                Online
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 bg-gray-50 p-4 overflow-x-hidden overflow-y-auto scroll-smooth relative" id="chat-messages-container">
            @foreach($messages as $msg)
                @if($msg['role'] === 'assistant')
                    <!-- AI Message -->
                    <div class="mb-4 flex items-start space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="chatbot-response-content bg-white border border-gray-100 text-gray-800 p-3 rounded-2xl rounded-tl-sm shadow-sm text-sm leading-relaxed">
                                {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 ml-1">{{ $msg['time'] ?? '' }}</p>
                        </div>
                    </div>
                @elseif($msg['role'] === 'user')
                    <!-- User Message -->
                    <div class="mb-4 flex flex-col items-end">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white p-3 rounded-2xl rounded-tr-sm shadow-sm inline-block max-w-[85%] text-right">
                            {{ $msg['content'] }}
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 mr-1">{{ $msg['time'] ?? '' }}</p>
                    </div>
                @endif
            @endforeach
            
            <!-- Typing Indicator -->
            @if($isAiTyping)
            <div class="mb-4 flex items-start space-x-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                </div>
                <div class="bg-white border border-gray-100 text-gray-800 p-3 rounded-2xl rounded-tl-sm shadow-sm inline-flex items-center space-x-1">
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
                </div>
            </div>
            @endif

            <!-- Workflow Option Cards -->
            @if(!empty($workflowOptions) && !$isAiTyping)
            <div class="mt-4 mb-2 animate-fade-in-up" id="workflow-options">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-3 ml-1 flex items-center">
                    @if($workflowStep === 'confirm')
                        <span class="mr-1">⚡</span> Confirm Action
                    @endif
                    <span class="normal-case font-medium text-gray-400 ml-1 tracking-normal">· or type below</span>
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($workflowOptions as $opt)
                        <button
                            wire:click="selectOption('{{ addslashes($opt['value']) }}')"
                            wire:loading.attr="disabled"
                            class="workflow-option-btn text-sm border border-indigo-200 text-indigo-700 bg-white hover:bg-gradient-to-r hover:from-indigo-500 hover:to-purple-500 hover:text-white hover:border-transparent px-4 py-2.5 rounded-xl transition-all duration-300 font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5"
                        >
                            {{ $opt['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Quick Questions -->
            @if(count($messages) <= 1 && empty($userInput) && !$isAiTyping)
            <div class="mt-4 mb-2 animate-fade-in-up" wire:loading.remove wire:target="sendMessage">
                <p class="text-[10px] font-bold text-gray-500 mb-2 uppercase tracking-wide ml-1">Quick Questions</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($quickQuestions as $q)
                        <button wire:click="sendQuickQuestion('{{ addslashes($q) }}')" 
                                class="text-sm border border-gray-200 text-gray-600 bg-white hover:border-indigo-300 hover:text-indigo-600 px-3 py-2 rounded-xl transition-all duration-300 shadow-sm hover:shadow text-left">
                            {{ $q }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white border-t border-gray-100 flex-shrink-0">
            <div class="relative flex items-center w-full">
                <input wire:model.defer="userInput"
                       wire:keydown.enter="sendMessage"
                       type="text"
                       class="w-full flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:bg-white transition-all"
                       placeholder="Type your message...">
                       
                <button wire:click="sendMessage"
                        class="ml-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white hover:from-indigo-600 hover:to-purple-600 p-3 rounded-xl transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        @if($isAiTyping) disabled @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </button>
            </div>
            <p class="text-[10px] text-gray-400 mt-2 text-center">
                Powered by Qwaiting AI
            </p>
        </div>
    </div>
    @endif

    <script>
        // Make markdown list items clickable
        document.addEventListener('click', (e) => {
            const li = e.target.closest('#chat-messages-container .chatbot-response-content li');
            if (!li || li.classList.contains('no-click')) return;
            
            // Re-verify and clean string including arrows
            let text = li.innerText.replace(/^[→\-*•]\s*/, '').trim();
            if (text) {
                const wireEl = li.closest('[wire\\:id]');
                const wireId = wireEl?.getAttribute('wire:id');
                const component = wireId ? window.Livewire.find(wireId) : null;
                if (component) {
                    component.call('sendMessage', text);
                }
            }
        });

        // Apply non-clickable styling to inputs and summaries
        function applyNoClickStyles() {
            const lis = document.querySelectorAll('#chat-messages-container .chatbot-response-content li:not(.no-click)');
            lis.forEach(li => {
                const text = li.innerText.trim();
                const lowerText = text.toLowerCase();
                
                // Identify questions, inputs and summary attributes
                const isNonClickable = 
                    text.includes('?') || 
                    lowerText.includes('your name') || 
                    lowerText.includes('your phone') ||
                    lowerText.includes('your email') ||
                    /^[^a-zA-Z]*(Service|Date|Time|Name|Phone|Email|Booking Ref|Reference ID|Status)\s*:/i.test(text);
                
                if (isNonClickable) {
                    li.classList.add('no-click');
                }
            });
        }

        // Auto-scroll and style hooks
        document.addEventListener('livewire:initialized', () => {
            applyNoClickStyles();
            Livewire.hook('message.processed', () => {
                window.dispatchEvent(new CustomEvent('booking-chat-updated'));
                applyNoClickStyles();
            });
        });

        window.addEventListener('booking-chat-updated', () => {
            applyNoClickStyles();
        });
    </script>

    <style>
        /* Markdown content styling */
        .chatbot-response-content p { margin-bottom: 0.5em; }
        .chatbot-response-content p:last-child { margin-bottom: 0; }
        .chatbot-response-content strong { font-weight: 600; color: #4F46E5; }
        .chatbot-response-content ul {
            list-style-type: none;
            padding-left: 0;
            margin-top: 0.5em;
            margin-bottom: 0.5em;
        }
        .chatbot-response-content li {
            position: relative;
            padding: 0.5em 0.75em;
            margin-bottom: 0.4em;
            background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
            border: 1px solid #E2E8F0;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .chatbot-response-content li:not(.no-click):hover {
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            border-color: #C7D2FE;
            color: #4F46E5;
            transform: translateX(4px);
        }
        .chatbot-response-content li.no-click {
            cursor: default;
        }
        .chatbot-response-content li::before {
            content: '→';
            margin-right: 0.5em;
            color: #818CF8;
            font-weight: bold;
        }
        .chatbot-response-content code {
            background: #F1F5F9;
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.9em;
            color: #4F46E5;
        }

        /* Workflow option buttons */
        .workflow-option-btn:active {
            transform: translateY(0) scale(0.97);
        }

        /* Custom scrollbar */
        #chat-messages-container {
            scrollbar-width: thin;
            scrollbar-color: rgba(99, 102, 241, 0.3) transparent;
        }
        #chat-messages-container::-webkit-scrollbar {
            width: 6px;
        }
        #chat-messages-container::-webkit-scrollbar-track {
            background: transparent;
        }
        #chat-messages-container::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.3);
            border-radius: 3px;
        }
        #chat-messages-container::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.5);
        }

        /* Entry animation */
        .animate-fade-in-up {
            animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(10px);
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</div>
