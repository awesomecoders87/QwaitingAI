<div class="min-h-screen bg-gray-50/50 p-6 sm:p-8 font-sans text-slate-800"
    x-data="analyticsDashboard()"
    @analytics-data-updated.window="updateData($event.detail)">
    <style>
        #chat-toggle-btn {
            flex-shrink: 0;
        }
    </style>
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                AI Queue Analytics
            </h1>
            <p class="mt-2 text-slate-500">Intelligent insights powered by machine learning and predictive analytics</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Generate Prediction button - Commented Out --}}
            {{-- @if(\Carbon\Carbon::parse($endDate)->isFuture())
             <button wire:click="generateOpenAIInsight" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-wait">
                <svg wire:loading.remove wire:target="generateOpenAIInsight" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <svg wire:loading wire:target="generateOpenAIInsight" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Generate Prediction</span>
            </button>
            @endif --}}
        </div>
    </div>

    <!-- Floating Chat Widget -->
    <div class="fixed bottom-6 right-6 flex flex-col items-end gap-4 font-sans text-slate-800"
        style="z-index: 2147483647 !important;"
        x-data="{
             open: false,
             input: '',
             processing: false,
             messages: [],
             scrollToBottom() {
                 this.$nextTick(() => {
                     const el = this.$refs.msgContainer;
                     if (el) el.scrollTop = el.scrollHeight;
                 });
             },
             send() {
                 const msg = this.input.trim();
                 if (!msg || this.processing) return;
                 this.input = '';
                 this.processing = true;
                 this.messages.push({ role: 'user', content: msg });
                 this.scrollToBottom();
                 
                 // Call Livewire component method
                 @this.sendMessage(msg);
             }
         }">

        <!-- Chat Window -->
        <div
            x-show="open"
            style="display: none; max-height: calc(100vh - 120px);"
            x-transition:enter="transition cubic-bezier(0.4, 0, 0.2, 1) duration-300"
            x-transition:enter-start="opacity-0 translate-y-10 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition cubic-bezier(0.4, 0, 0.2, 1) duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-10 scale-95"
            class="w-[350px] sm:w-[380px] bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col h-[800px] mb-2"
            @chat-ai-response.window="
                console.log('✅ chat-ai-response event received:', $event.detail);
                processing = false;
                const payload = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
                if (payload && payload.content) {
                    messages.push({ role: 'assistant', content: payload.content });
                    scrollToBottom();
                }
            "
            @ai-set-date-range.window="
                console.log('✅ ai-set-date-range event received:', $event.detail);
                const payload = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
                if (payload && payload.start && payload.end) {
                    @this.setDateRange(payload.start, payload.end);
                }
            ">
            <!-- Header -->
            <div class="shrink-0 relative overflow-hidden" style="background: linear-gradient(to right, #0f172a, #1e293b); color: white; min-height: 140px; padding: 20px; display: flex; flex-direction: column; justify-content: center;">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-3xl pointer-events-none -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl pointer-events-none -ml-10 -mb-10"></div>
                <div class="relative z-10">
                    <h3 class="font-bold text-xl mb-1 tracking-tight" style="color: #ffffff !important;">Hi there! 👋</h3>
                    <p class="text-sm leading-relaxed font-light" style="color: #cbd5e1 !important;">Start a chat. We're here to help you analyze your data.</p>
                </div>
            </div>

            <!-- Messages Area -->
            <div x-ref="msgContainer" class="flex-1 p-5 overflow-y-auto bg-slate-50 space-y-4 scroll-smooth">

                <!-- Welcome message shown until first message -->
                <div x-show="messages.length === 0" class="flex justify-start">
                    <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm text-slate-600 text-[15px] max-w-[85%] border border-slate-100 leading-relaxed">
                        My name is Qwaiting AI. How can I assist you today with your queue analytics?
                    </div>
                </div>

                <!-- All messages rendered by Alpine -->
                <template x-for="(msg, i) in messages" :key="i">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user'
                                ? 'px-4 py-3 rounded-2xl rounded-br-none text-[15px] max-w-[85%] shadow-sm leading-relaxed bg-rose-500 text-white'
                                : 'px-4 py-3 rounded-2xl rounded-tl-none text-[15px] max-w-[85%] shadow-sm leading-relaxed bg-white text-slate-600 border border-slate-100'"
                            x-text="msg.content">
                        </div>
                    </div>
                </template>

                <!-- Loading Bubble -->
                <div x-show="processing" class="flex justify-start">
                    <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 flex items-center gap-2">
                        <div class="flex space-x-1.5">
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.15s"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-4 bg-white border-t border-slate-100 shrink-0">
                <div class="relative w-full">
                    <input
                        type="text"
                        x-model="input"
                        @keydown.enter.prevent="send()"
                        placeholder="Ask a question..."
                        class="block w-full rounded-2xl border-slate-200 pl-4 pr-12 text-[15px] focus:border-rose-500 focus:ring-rose-500 py-3 bg-slate-50 border ring-1 ring-slate-200 transition-shadow"
                        style="padding-right: 48px;"
                        :disabled="processing">
                    <button
                        @click.prevent="send()"
                        :disabled="processing"
                        class="flex items-center justify-center p-2 bg-rose-500 text-white hover:bg-rose-600 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-rose-200 h-9 w-9"
                        style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                        </svg>
                    </button>
                </div>
                <div class="text-center mt-3">
                    <span class="text-[11px] font-medium text-slate-400">Powered by <span class="font-bold text-rose-500">Qwaiting AI</span></span>
                </div>
            </div>
        </div>

        <!-- Toggle Button -->
        <button
            id="chat-toggle-btn"
            @click="open = !open"
            class="aibot w-14 h-14 rounded-full shadow-xl shadow-rose-500/20 flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-rose-200 bg-rose-500">
            <svg x-show="!open" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
            <svg x-show="open" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
    </div>

    <!-- Filters -->
    <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-5">
        <div class="flex flex-wrap gap-5">
            <!-- Date Range Picker -->
            <div class="w-full sm:w-[48%] lg:w-auto lg:flex-1 lg:min-w-[240px]">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Date Range</label>
                <div
                    wire:ignore
                    x-data="{
                        picker: null,
                        initPicker() {
                            this.picker = $(this.$refs.picker).daterangepicker({
                                startDate: moment('{{ $startDate }}'),
                                endDate: moment('{{ $endDate }}'),
                                ranges: {
                                   'Today': [moment(), moment()],
                                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                                },
                                locale: {
                                    format: 'DD-MM-YYYY'
                                }
                            }, (start, end) => {
                                @this.setDateRange(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
                            }).data('daterangepicker');
                        }
                    }"
                    x-init="initPicker"
                    @update-date-picker.window="
                        if (picker) {
                            picker.setStartDate(moment($event.detail[0].start));
                            picker.setEndDate(moment($event.detail[0].end));
                        }
                    ">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <input x-ref="picker" type="text" class="block w-full rounded-lg border-slate-200 bg-slate-50 pl-9 p-2.5 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer" />
                    </div>
                </div>
            </div>

            <!-- Queue Filter -->
            <div class="w-full sm:w-[48%] lg:w-auto lg:flex-1 lg:min-w-[200px] space-y-1.5">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Queue</label>
                <div class="relative flex items-center gap-2">
                    <select 
                        x-on:change="@this.set('selectedQueue', $event.target.value)"
                        class="block w-full rounded-lg border-slate-200 bg-slate-50 p-2.5 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" {{ $selectedQueue == 'all' ? 'selected' : '' }}>All Queues</option>
                        @foreach($this->queues as $queue)
                        <option value="{{ $queue->id }}" {{ $selectedQueue == $queue->id ? 'selected' : '' }}>{{ $queue->name }}</option>
                        @endforeach
                    </select>
                    <button wire:click="loadAnalytics" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors border border-slate-200" title="Refresh Dashboard">
                        <svg wire:loading.class="animate-spin" wire:target="loadAnalytics" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.001 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="relative">
        <!-- Global Loading Overlay -->
        <div wire:loading wire:target="loadAnalytics, setDateRange, selectedQueue, startDate, endDate" class="absolute inset-0 z-10 bg-white/50 backdrop-blur-[1px] flex items-center justify-center rounded-xl transition-all duration-300">
            <div class="flex flex-col items-center gap-2">
                <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Updating Data...</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8" wire:key="metrics-grid-{{ $lastUpdate }}">
            <!-- Metric Card: Incoming -->
            <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-100 text-blue-600 transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-slate-800 mb-2" x-text="incoming">
                    {{ $incomingSessions }}
                </div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Incoming Tickets
                </div>
            </div>

            <!-- Engaged Sessions Card -->
            <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 rounded-xl bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-slate-800 mb-2" x-text="engaged">
                    {{ $engagedSessions }}
                </div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Served Tickets
                </div>
            </div>

            <!-- Metric Card: Avg Wait -->
            <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 rounded-xl bg-amber-600 text-white transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-slate-800 mb-2">
                    <span x-text="Number(waitTime).toFixed(1)">{{ number_format($avgWaitTime, 1) }}</span>
                </div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Avg Wait (Sec)
                </div>
            </div>

            <!-- Metric Card: Avg Handle -->
            <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 rounded-xl bg-violet-600 text-white transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-slate-800 mb-2">
                    <span x-text="Number(handleTime).toFixed(1)">{{ number_format($avgSessionHandleTime, 1) }}</span>
                </div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Avg Handle (Min)
                </div>
            </div>

            <!-- Metric Card: Sentiment -->
            <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 rounded-xl bg-pink-600 text-white transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-slate-800 mb-2">
                    <span x-text="Number(sentiment).toFixed(1) + '%'">{{ number_format($avgSessionSentiment, 1) }}%</span>
                </div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Average Rating
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 gap-6 mb-8">
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="p-1 rounded bg-indigo-50 text-indigo-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg></span>
                Incoming vs. Served Tickets
            </h3>
            <div class="h-80 w-full relative">
                @if(count($sessionsChartData) > 0)
                <canvas id="sessionsChart" wire:ignore></canvas>
                @else
                <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                    <svg class="w-16 h-16 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>No data available for the selected period</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Wait Time & Handle Time Chart - Commented Out --}}
        {{--
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-100">
             <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="p-1 rounded bg-amber-50 text-amber-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                Wait Time & Handle Time
            </h3>
            <div class="h-80 w-full relative">
                 @if(count($waitTimeChartData) > 0)
                    <canvas id="waitTimeChart" wire:ignore></canvas>
                @else
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                         <svg class="w-16 h-16 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>No data available for the selected period</span>
                    </div>
                @endif
            </div>
        </div>
        --}}
    </div>


    <!-- AI Insights Section -->
    <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-sm border border-slate-100" wire:key="ai-insights-{{ $lastUpdate }}">
        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-100">
            <div class="p-2 rounded-lg bg-indigo-600 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    AI-Powered Insights
                </h2>
                <p class="text-sm text-slate-500">Real-time predictive analysis of your queue performance</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" wire:key="ai-cards-{{ $lastUpdate }}">
            <!-- Wait Time Predictions -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 hover:bg-white hover:shadow-md transition-all flex flex-col">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-lg bg-amber-100 text-amber-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-800">
                        Wait Time Predictions
                        @if($isShowingPrediction)
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                            Predicted
                        </span>
                        @endif
                    </h4>
                </div>
                <div class="space-y-3 flex-1">
                    <template x-for="(prediction, index) in waitTimePredictions" :key="'wait-'+index">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-slate-100 shadow-sm">
                            <span class="text-sm font-semibold text-slate-600" x-text="prediction.hour"></span>
                            <span class="text-sm font-bold text-indigo-600" x-text="prediction.predicted_wait + ' min'"></span>
                        </div>
                    </template>
                    <div x-show="waitTimePredictions.length === 0" class="text-center py-4 text-sm text-slate-400">Insufficient data</div>
                </div>
            </div>

            <!-- Staffing Recommendations -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 hover:bg-white hover:shadow-md transition-all flex flex-col">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-lg bg-emerald-100 text-emerald-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-800">Staffing Recommendations</h4>
                </div>
                <div class="space-y-3 flex-1">
                    <template x-for="(recommendation, index) in staffingRecommendations" :key="'staff-'+index">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-slate-100 shadow-sm">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-600" x-text="recommendation.hour"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-700" x-text="recommendation.recommended_staff + ' staff'"></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                    :class="{
                                        'bg-red-100 text-red-700': recommendation.priority === 'high',
                                        'bg-amber-100 text-amber-700': recommendation.priority === 'medium',
                                        'bg-green-100 text-green-700': recommendation.priority === 'low'
                                    }"
                                    x-text="recommendation.priority">
                                </span>
                            </div>
                        </div>
                    </template>
                    <div x-show="staffingRecommendations.length === 0" class="text-center py-4 text-sm text-slate-400">Insufficient data</div>
                </div>
            </div>

            <!-- Optimization Insights -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 hover:bg-white hover:shadow-md transition-all flex flex-col">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-lg bg-violet-100 text-violet-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-800">Optimization Tips</h4>
                </div>
                <div class="space-y-3 flex-1">
                    <template x-if="peakHoursForecast.length > 0">
                        <div class="p-3 rounded-lg bg-orange-50 border border-orange-100 text-sm">
                            <strong class="text-orange-800 block mb-1">Peak Hours Alert</strong>
                            <p class="text-orange-700">Expect high volume around <span class="font-bold" x-text="peakHoursForecast[0].hour"></span> today.</p>
                        </div>
                    </template>
                    <div class="p-3 rounded-lg bg-white border border-slate-100 text-sm shadow-sm flex items-center justify-between">
                        <span class="text-slate-600">No-Show Probability</span>
                        <span class="font-bold" :class="noShowProbability > 20 ? 'text-red-600' : 'text-green-600'" x-text="Number(noShowProbability).toFixed(1) + '%'"></span>
                    </div>
                    <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100 text-sm">
                        <strong class="text-emerald-800 block mb-1">Opportunity</strong>
                        <p class="text-emerald-700">Increase throughput by <span class="font-bold" x-text="incoming - engaged"></span> to boost efficiency.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@push('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        console.log('🚀 Alpine init');
        Alpine.data('analyticsDashboard', () => ({
            incoming: @entangle('incomingSessions'),
            engaged: @entangle('engagedSessions'),
            waitTime: @entangle('avgWaitTime'),
            handleTime: @entangle('avgSessionHandleTime'),
            sentiment: @entangle('avgSessionSentiment'),
            waitTimePredictions: @json($waitTimePredictions),
            staffingRecommendations: @json($staffingRecommendations),
            noShowProbability: @entangle('noShowProbability'),
            peakHoursForecast: @json($peakHoursForecast),

            init() {
                console.log('📦 Alpine component initialized');
            },

            updateData(detail) {
                console.log('✅ analytics-data-updated event received:', detail);
                const data = Array.isArray(detail) ? detail[0] : detail;
                
                this.incoming = data.incoming;
                this.engaged = data.engaged;
                this.waitTime = data.waitTime;
                this.handleTime = data.handleTime;
                this.sentiment = data.sentiment;
                this.waitTimePredictions = data.waitTimePredictions || [];
                this.staffingRecommendations = data.staffingRecommendations || [];
                this.noShowProbability = data.noShowProbability || 0;
                this.peakHoursForecast = data.peakHoursForecast || [];
                
                console.log('📊 UI State updated with:', { 
                    incoming: this.incoming, 
                    predictions: this.waitTimePredictions.length 
                });
            }
        }));
    });

    document.addEventListener('livewire:initialized', () => {
        console.log('⚡ Livewire initialized');
        let sessionsChart = null;
        let waitTimeChart = null;

        function initCharts(newSessionsData = null, newWaitTimeData = null) {
            // Destroy existing charts if they exist
            if (sessionsChart) sessionsChart.destroy();
            if (waitTimeChart) waitTimeChart.destroy();

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                family: "'Inter', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        titleFont: {
                            family: "'Inter', sans-serif",
                            size: 13
                        },
                        bodyFont: {
                            family: "'Inter', sans-serif",
                            size: 12
                        },
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [4, 4],
                            color: '#f1f5f9'
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif",
                                size: 11
                            },
                            color: '#64748b'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif",
                                size: 11
                            },
                            color: '#64748b'
                        }
                    }
                }
            };

            // Incoming vs Engaged Chart
            const sessionsCtx = document.getElementById('sessionsChart');
            if (sessionsCtx) {
                const sessionsData = newSessionsData || @json($sessionsChartData);
                sessionsChart = new Chart(sessionsCtx, {
                    type: 'line',
                    data: {
                        labels: sessionsData.map(d => d.date),
                        datasets: [{
                                label: 'Incoming',
                                data: sessionsData.map(d => d.incoming),
                                borderColor: '#6366f1', // Indigo 500
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 7
                            },
                            {
                                label: 'Served',
                                data: sessionsData.map(d => d.engaged),
                                borderColor: '#10b981', // Emerald 500
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                tension: 0.4,
                                borderDash: [5, 5],
                                pointRadius: 4,
                                pointHoverRadius: 7
                            }
                        ]
                    },
                    options: commonOptions
                });
            }

            // Wait Time Chart
            const waitTimeCtx = document.getElementById('waitTimeChart');
            if (waitTimeCtx) {
                const waitTimeData = newWaitTimeData || @json($waitTimeChartData);
                waitTimeChart = new Chart(waitTimeCtx, {
                    type: 'bar',
                    data: {
                        labels: waitTimeData.map(d => d.date),
                        datasets: [{
                                label: 'Avg Wait (min)',
                                data: waitTimeData.map(d => d.wait_time),
                                backgroundColor: '#f59e0b', // Amber 500
                                borderRadius: 4,
                                barPercentage: 0.6
                            },
                            {
                                label: 'Avg Handle (min)',
                                data: waitTimeData.map(d => d.handle_time),
                                backgroundColor: '#8b5cf6', // Violet 500
                                borderRadius: 4,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: commonOptions
                });
            }
        }

        // Initialize on load
        initCharts();

        // Debugging Livewire lifecycle
        Livewire.hook('message.processed', (message, component) => {
            console.log('🔄 Livewire message processed', {
                incoming: @this.incomingSessions,
                lastUpdate: @this.lastUpdate
            });
        });

        // Re-initialize on Livewire updates with new data
        Livewire.on('chartsUpdated', (data) => {
            // In Livewire 3, 'data' is an array of objects if using named params, or the object itself?
            // Depending on version. Safer to check. 
            // Usually: params are passed as arguments.

            // Inspecting how dispatch works: $this->dispatch('name', param: val);
            // js: (data) => { data.param }

            // However, sometimes it comes as [data].
            // Let's assume data is the object based on LW3 docs.

            // If data is array (old LW or specific case), unwrap it.
            const payload = Array.isArray(data) ? data[0] : data;

            setTimeout(() => {
                initCharts(payload.sessionsData, payload.waitTimeData);
            }, 100);
        });
    });
</script>
@endpush
</div>