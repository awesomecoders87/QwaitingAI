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
            class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white rounded-full w-14 h-14 shadow-lg flex items-center justify-center transition-transform hover:scale-105 z-50">
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
    <div class="fixed bottom-24 right-4 sm:right-6 w-[90vw] sm:w-[400px] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden z-50 h-[580px] max-h-[85vh]">
       
        <!-- Header -->
        <div class="bg-indigo-500 text-white p-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="bg-indigo-400 p-2 rounded-full border border-indigo-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-tight">Booking Assistant</h3>
                    <p class="text-xs text-indigo-100">AI-powered · Booking Insights</p>
                </div>
            </div>
            <div class="flex items-center text-xs font-semibold">
                <div class="w-2 h-2 rounded-full bg-green-400 mr-1 animate-pulse"></div>
                Online
            </div>
        </div>


        <!-- Chat Area -->
        <div class="flex-1 bg-gray-50/50 p-4 pb-8 overflow-x-hidden overflow-y-auto scroll-smooth relative" id="chat-messages-container">
            @foreach($messages as $msg)
                @if($msg['role'] === 'assistant')
                    <!-- AI Message -->
                    <div class="mb-4">
                        <div class="chatbot-response-content bg-white border text-gray-800 p-3 rounded-2xl rounded-tl-sm shadow-sm inline-block max-w-[85%] text-sm leading-relaxed">
                            {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 ml-1">{{ $msg['time'] ?? '' }}</p>
                    </div>
                @elseif($msg['role'] === 'user')
                    <!-- User Message -->
                    <div class="mb-4 flex flex-col items-end">
                        <div class="bg-indigo-100 text-indigo-900 p-3 rounded-2xl rounded-tr-sm shadow-sm inline-block max-w-[85%] text-right">
                            {{ $msg['content'] }}
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 mr-1">{{ $msg['time'] ?? '' }}</p>
                    </div>
                @endif
            @endforeach
           
            @if($isAiTyping)
            <div class="mb-4 flex flex-col items-start">
                <div class="bg-white border text-gray-800 p-3 rounded-2xl rounded-tl-sm shadow-sm inline-flex items-center max-w-[85%] space-x-1">
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-violet-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
                </div>
            </div>
            @endif


            <!-- Workflow Option Cards (Services / Dates / Times) -->
            @if(!empty($workflowOptions) && !$isAiTyping)
            <div class="mt-4 mb-2 animate-fade-in-up" id="workflow-options-panel">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3 ml-1">
                    @if($workflowStep === 'select_service') 🏥 Select a Service
                    @elseif($workflowStep === 'select_date')  📅 Select a Date
                    @elseif($workflowStep === 'select_time')  🕐 Select a Time
                    @else 👆 Choose an Option
                    @endif
                    <span class="normal-case font-medium text-gray-300 ml-1 tracking-normal">· or type below</span>
                </p>
                <div class="flex flex-wrap gap-2.5">
                    @foreach($workflowOptions as $opt)
                        <button
                            wire:click="selectOption('{{ addslashes($opt['value']) }}')"
                            wire:loading.attr="disabled"
                            class="workflow-option-btn text-sm border border-indigo-100 text-indigo-700 bg-white hover:bg-indigo-600 hover:text-white hover:border-indigo-600 px-4 py-2 rounded-2xl transition-all duration-300 font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5"
                        >
                            {{ $opt['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif


            <!-- Quick Questions (Hide as soon as user types or sends a message causing the loading state) -->
            @if(count($messages) <= 1 && empty($userInput) && !$isAiTyping)
            <div class="mt-4 mb-2 animate-fade-in-up" wire:loading.remove wire:target="sendMessage">
                <p class="text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-wide ml-1">Quick Questions</p>
                <div class="flex flex-wrap gap-2.5">
                    @foreach($quickQuestions as $q)
                        <button wire:click="sendQuickQuestion('{{ addslashes($q) }}')" class="text-sm border border-indigo-100 text-indigo-600 bg-white hover:bg-indigo-50 px-4 py-2 rounded-2xl transition-all duration-300 shadow-sm hover:shadow-md hover:-translate-y-0.5 text-left">
                            {{ $q }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>


        <!-- Input Area -->
        <div class="p-3 bg-white border-t flex-shrink-0 flex flex-col">
            <div class="relative flex items-center w-full">
                <!-- Silence Detection JS handles Voice recording without holding -->
                <!-- <button type="button" id="voice-btn" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                    </svg>
                </button> -->
               
                <input wire:model.defer="userInput"
                       wire:keydown.enter="sendMessage"
                       type="text"
                       class="w-full flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 ml-2"
                       placeholder="Ask about bookings or select above…">
                       
                <button wire:click="sendMessage"
                        class="ml-2 bg-indigo-200 text-indigo-600 hover:bg-indigo-300 p-2 rounded-lg transition-colors shadow-sm disabled:opacity-50"
                        @if($isAiTyping) disabled @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mt-[-1px] ml-[-1px]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </button>
            </div>
            <div id="recording-status" class="hidden text-xs text-red-500 font-semibold mt-1 items-center ml-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse mr-1"></span> Recording... Listening for silence...
            </div>
        </div>
    </div>
    @endif


    <script>
        // Clickable options inside markdown lists — use document delegation
        document.addEventListener('click', (e) => {
            const li = e.target.closest('#chat-messages-container li');
            if(!li) return;
            let text = li.innerText.replace(/^[-*•]\s*/, '').trim();
            if(text) {
                // Walk UP from the li to find the BookingChatbot wire:id
                const wireEl = li.closest('[wire\\:id]');
                const wireId = wireEl?.getAttribute('wire:id');
                const component = wireId ? window.Livewire.find(wireId) : null;
                if(component) {
                    component.set('userInput', text);
                    component.call('sendMessage');
                }
            }
        });


        document.addEventListener('livewire:initialized', () => {
            // Tell Alpine to scroll explicitly after every livewire update completes
            Livewire.hook('message.processed', () => {
                window.dispatchEvent(new CustomEvent('booking-chat-updated'));
            });
            Livewire.on('process-ai-turn', () => {
                window.dispatchEvent(new CustomEvent('booking-chat-updated'));
            });


            // Voice Logic Fallback (No WebSockets)
            let mediaRecorder;
            let audioContext;
            let analyser;
            let silenceTimer;
            const SILENCE_THRESHOLD = 2500;
           
            const btn = document.getElementById('voice-btn');
            const statusBox = document.getElementById('recording-status');
           
            if(btn) {
                btn.addEventListener('click', async () => {
                    if(mediaRecorder && mediaRecorder.state === 'recording') {
                        mediaRecorder.stop();
                        return;
                    }


                    btn.classList.add('text-red-500', 'bg-red-50');
                    btn.classList.remove('text-indigo-500', 'hover:bg-indigo-50');
                    statusBox.classList.remove('hidden');
                    statusBox.classList.add('flex');
                   
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const source = audioContext.createMediaStreamSource(stream);
                        analyser = audioContext.createAnalyser();
                       
                        source.connect(analyser);
                        mediaRecorder = new MediaRecorder(stream);
                       
                        let audioChunks = [];
                        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
                       
                        mediaRecorder.onstop = async () => {
                            btn.classList.remove('text-red-500', 'bg-red-50');
                            btn.classList.add('text-indigo-500', 'hover:bg-indigo-50');
                            statusBox.classList.add('hidden');
                            statusBox.classList.remove('flex');
                           
                            $wire.set('userInput', 'This is a mock transcribed text since STT API endpoint is not provided.');
                            $wire.sendMessage();
                        };
                       
                        mediaRecorder.start();
                        checkForSilence();
                       
                    } catch(err) {
                        console.error('Mic access denied.', err);
                        btn.classList.remove('text-red-500', 'bg-red-50');
                        btn.classList.add('text-indigo-500', 'hover:bg-indigo-50');
                        statusBox.classList.add('hidden');
                        statusBox.classList.remove('flex');
                    }
                });
            }


            function checkForSilence() {
                if(!analyser || mediaRecorder.state !== 'recording') return;


                const dataArray = new Uint8Array(analyser.frequencyBinCount);
                analyser.getByteFrequencyData(dataArray);
               
                let volume = dataArray.reduce((sum, value) => sum + value, 0) / dataArray.length;
               
                if (volume < 10) {
                    if (!silenceTimer) {
                         silenceTimer = setTimeout(() => {
                             if(mediaRecorder.state === 'recording') mediaRecorder.stop();
                         }, SILENCE_THRESHOLD);
                    }
                } else {
                    clearTimeout(silenceTimer);
                    silenceTimer = null;
                }
               
                requestAnimationFrame(checkForSilence);
            }
        });
    </script>
    <style>
        /* Markdown rendering styles for the Chatbot */
        .chatbot-response-content p { margin-bottom: 0.5em; }
        .chatbot-response-content p:last-child { margin-bottom: 0; }
        .chatbot-response-content strong { font-weight: 600; color: #3730A3; }
        .chatbot-response-content ul {
            list-style-type: none;
            padding-left: 0;
            margin-top: 0.5em;
            margin-bottom: 0.5em;
        }
        .chatbot-response-content li {
            position: relative;
            padding-left: 1.5em;
            margin-bottom: 0.3em;
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 0.5rem;
            padding: 0.4em 0.8em;
            cursor: pointer;
            transition: all 0.2s;
        }
        .chatbot-response-content li:hover {
            background-color: #EEF2FF;
            border-color: #C7D2FE;
            color: #4F46E5;
        }
        .chatbot-response-content li::before {
            content: '•';
            position: absolute;
            left: 0.5em;
            color: #818CF8;
            font-weight: bold;
        }


        /* Workflow option chips */
        .workflow-option-btn:active { transform: translateY(0) scale(0.97); }


        /* Designer-Grade Custom Scrollbar */
        .custom-scrollbar, #chat-messages-container {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
        }
        .custom-scrollbar::-webkit-scrollbar,
        #chat-messages-container::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track,
        #chat-messages-container::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.01);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb,
        #chat-messages-container::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover,
        #chat-messages-container::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.7);
        }


        #recording-status {
            animation: fadeInOut 1.5s infinite;
        }
        @keyframes fadeInOut {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }


        /* Entry animation for dynamic options */
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