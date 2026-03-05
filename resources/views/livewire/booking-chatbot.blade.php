<div>
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
    <div class="fixed bottom-24 right-4 sm:right-6 w-[90vw] sm:w-96 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden z-50 border border-gray-100 h-[500px] max-h-[80vh]">
        
        <!-- Header -->
        <div class="bg-indigo-500 text-white p-4 flex items-center justify-between">
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
        <div class="flex-1 bg-gray-50 p-4 overflow-y-auto" id="chat-messages-container">
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
        </div>

        <!-- Quick Questions (Hide as soon as user types or sends a message causing the loading state) -->
        @if(count($messages) <= 1 && empty($userInput))
        <div class="bg-gray-50 px-4 pb-2" wire:loading.remove wire:target="sendMessage">
            <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Quick Questions</p>
            <div class="flex flex-wrap gap-2">
                @foreach($quickQuestions as $q)
                    <button wire:click="sendQuickQuestion('{{ $q }}')" class="text-xs border border-indigo-200 text-indigo-600 bg-white hover:bg-indigo-50 px-3 py-1.5 rounded-full transition-colors truncate max-w-full text-left">
                        {{ $q }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Input Area -->
        <div class="p-3 bg-white border-t flex flex-col">
            <div class="relative flex items-center w-full">
                <!-- Silence Detection JS handles Voice recording without holding -->
                <button type="button" id="voice-btn" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                    </svg>
                </button>
                
                <input wire:model.defer="userInput" 
                       wire:keydown.enter="sendMessage"
                       type="text" 
                       class="w-full flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 ml-2 disabled:opacity-50" 
                       placeholder="Ask about bookings..."
                       @if($isAiTyping) disabled @endif>
                       
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

    <!-- Silence Detection & Auto-Scroll JS -->
    <script>
        // Scroll helper — fetches container fresh each time (it may not exist on load)
        function chatScrollToBottom() {
            const c = document.getElementById('chat-messages-container');
            if(c) c.scrollTo({ top: c.scrollHeight, behavior: 'smooth' });
        }

        // Clickable options — use document delegation so it works even when chat is closed on load
        document.addEventListener('click', (e) => {
            const li = e.target.closest('#chat-messages-container li');
            if(!li) return;
            let text = li.innerText.replace(/^[-*•]\s*/, '').trim();
            if(text) {
                // Walk UP from the li to find the BookingChatbot wire:id (avoids picking the wrong component)
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
            // Scroll on load
            chatScrollToBottom();

            // Scroll on every Livewire update or message event
            Livewire.hook('message.processed', () => chatScrollToBottom());
            Livewire.on('process-ai-turn', () => setTimeout(chatScrollToBottom, 100));

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
                        // Manual stop
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
                            
                            // Audio stopping, transcribe via external endpoint if necessary 
                            // In real scenario, upload blob via POST /api/transcribe, then pass the transcribed text to Livewire
                            // Mocking the transcription result to demonstrate HTTP flow:
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
        .chatbot-response-content strong { font-weight: 600; color: #3730A3; } /* Indigo-800 */
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
            background-color: #F8FAFC; /* Slate-50 */
            border: 1px solid #E2E8F0; /* Slate-200 */
            border-radius: 0.5rem;
            padding: 0.4em 0.8em;
            cursor: pointer;
            transition: all 0.2s;
        }
        .chatbot-response-content li:hover {
            background-color: #EEF2FF; /* Indigo-50 */
            border-color: #C7D2FE; /* Indigo-200 */
            color: #4F46E5; /* Indigo-600 */
        }
        .chatbot-response-content li::before {
            content: '•';
            position: absolute;
            left: 0.5em;
            color: #818CF8; /* Indigo-400 */
            font-weight: bold;
        }
        #recording-status {
            animation: fadeInOut 1.5s infinite;
        }
        @keyframes fadeInOut {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</div>
