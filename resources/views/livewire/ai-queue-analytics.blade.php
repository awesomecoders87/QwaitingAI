<div class="min-h-screen bg-gray-50/50 p-6 sm:p-8 font-sans text-slate-800">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <span class="text-3xl">🤖</span> AI Queue Analytics
            </h1>
            <p class="mt-2 text-slate-500">Intelligent insights powered by machine learning and predictive analytics</p>
        </div>
        <div class="flex items-center gap-3">
            @if(\Carbon\Carbon::parse($endDate)->isFuture())
             <button wire:click="generateOpenAIInsight" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-wait">
                <svg wire:loading.remove wire:target="generateOpenAIInsight" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <svg wire:loading wire:target="generateOpenAIInsight" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Generate Prediction</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Floating Chat Widget -->
    <div class="fixed bottom-6 right-6 flex flex-col items-end gap-4 font-sans text-slate-800" style="z-index: 2147483647 !important;">
        
        <!-- Chat Window -->
        <div 
            x-show="$wire.isChatOpen" 
            x-on:chat-message-sent.window="$wire.processChat()"
            style="display: none; max-height: calc(100vh - 120px);"
            x-transition:enter="transition cubic-bezier(0.4, 0, 0.2, 1) duration-300"
            x-transition:enter-start="opacity-0 translate-y-10 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition cubic-bezier(0.4, 0, 0.2, 1) duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-10 scale-95"
            class="w-[350px] sm:w-[380px] bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col h-[800px] mb-2"
        >
            <!-- Header -->
            <div class="shrink-0 relative overflow-hidden" style="background: linear-gradient(to right, #0f172a, #1e293b); color: white; min-height: 140px; padding: 20px; display: flex; flex-direction: column; justify-content: center;">
                <!-- Decorative effects -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-3xl pointer-events-none -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl pointer-events-none -ml-10 -mb-10"></div>
                
                <div class="relative z-10">
                    <h3 class="font-bold text-xl mb-1 tracking-tight" style="color: #ffffff !important;">Hi there! 👋</h3>
                    <p class="text-sm leading-relaxed font-light" style="color: #cbd5e1 !important;">Start a chat. We're here to help you analyze your data.</p>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 p-5 overflow-y-auto bg-slate-50 space-y-4 scroll-smooth">
                <!-- Welcome Message -->
                @if(count($chatMessages) === 0)
                    <div class="flex justify-start">
                        <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm text-slate-600 text-[15px] max-w-[85%] border border-slate-100 leading-relaxed">
                            My name is Qwaiting AI. How can I assist you today with your queue analytics?
                        </div>
                    </div>
                @endif

                @foreach($chatMessages as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="
                            px-4 py-3 rounded-2xl text-[15px] max-w-[85%] shadow-sm leading-relaxed
                            {{ $msg['role'] === 'user' 
                                ? 'bg-rose-500 text-white rounded-br-none' 
                                : 'bg-white text-slate-600 border border-slate-100 rounded-tl-none' 
                            }}
                        ">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                @endforeach

                <!-- Loading Bubble -->
                @if($isChatProcessing)
                <div class="flex justify-start">
                     <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 flex items-center gap-2">
                        <div class="flex space-x-1.5">
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.15s"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Input Area -->
            <div class="p-4 bg-white border-t border-slate-100 shrink-0">
                <form wire:submit.prevent="sendMessage">
                    <div class="relative w-full">
                        <input 
                            type="text" 
                            wire:model="chatInput" 
                            placeholder="Ask a question..." 
                            class="block w-full rounded-2xl border-slate-200 pl-4 pr-12 text-[15px] focus:border-rose-500 focus:ring-rose-500 py-3 bg-slate-50 border ring-1 ring-slate-200 transition-shadow"
                            style="padding-right: 48px;"
                        >
                        <button 
                            type="submit" 
                            class="flex items-center justify-center p-2 bg-rose-500 text-white hover:bg-rose-600 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-rose-200 group h-9 w-9"
                            style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); z-index: 10;"
                            wire:loading.attr="disabled"
                        >
                            <svg class="w-5 h-5 transform group-hover:translate-x-0.5 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
                        </button>
                    </div>
                </form>
                <div class="text-center mt-3">
                     <span class="text-[11px] font-medium text-slate-400">Powered by <span class="font-bold text-rose-500">Qwaiting AI</span></span>
                </div>
            </div>
        </div>

        <!-- Toggle Button -->
        <button 
            wire:click="toggleChat" 
            class="w-14 h-14 rounded-full shadow-xl shadow-rose-500/20 flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-rose-200 {{ $isChatOpen ? 'bg-rose-500 rotate-180' : 'bg-rose-500' }}"
        >
            @if($isChatOpen)
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            @else
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            @endif
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
                    "
                >
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <input x-ref="picker" type="text" class="block w-full rounded-lg border-slate-200 bg-slate-50 pl-9 p-2.5 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer" />
                    </div>
                </div>
            </div>

            <!-- Queue Filter -->
            <div class="w-full sm:w-[48%] lg:w-auto lg:flex-1 lg:min-w-[200px] space-y-1.5">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Queue</label>
                 <div class="relative">
                    <select wire:model.live="selectedQueue" class="block w-full rounded-lg border-slate-200 bg-slate-50 p-2.5 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all">All Queues</option>
                        @foreach($this->queues as $queue)
                            <option value="{{ $queue->id }}">{{ $queue->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Agent Filter -->
            <div class="w-full sm:w-[48%] lg:w-auto lg:flex-1 lg:min-w-[200px] space-y-1.5">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Agent</label>
                 <div class="relative">
                    <select wire:model.live="selectedAgent" class="block w-full rounded-lg border-slate-200 bg-slate-50 p-2.5 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all">All Agents</option>
                        @foreach($this->agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
    </div>

    <!-- AI Insight Alert Box -->
    @if($openaiInsight)
    <div class="mb-8 rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-2 -mr-2 opacity-10">
            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
        </div>
        <div class="relative z-10">
            <h3 class="flex items-center text-lg font-bold text-indigo-900 mb-2">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                AI Analysis & Recommendation
            </h3>
            <div class="prose prose-sm text-indigo-800 max-w-none">
                {!! nl2br(e($openaiInsight)) !!}
            </div>
        </div>
    </div>
    @endif
    
    @if($openaiError)
    <div class="mb-8 rounded-xl bg-red-50 border border-red-100 p-4 flex items-center gap-3 text-red-700">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>{{ $openaiError }}</span>
    </div>
    @endif

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
        <!-- Metric Card: Incoming -->
            <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-100 text-blue-600 transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    </div>
                    <!-- <div class="flex items-center space-x-1 text-xs font-semibold {{ $incomingSessionsTrend >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        <span>{{ $incomingSessionsTrend >= 0 ? '↑' : '↓' }}{{ abs($incomingSessionsTrend) }}%</span>
                    </div> -->
                </div>
                <div class="text-4xl font-bold text-slate-800 mb-2">{{ $incomingSessions }}</div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Incoming Sessions
                </div>
            </div>

            <!-- Engaged Sessions Card -->
            <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 rounded-xl bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <!-- <div class="flex items-center space-x-1 text-xs font-semibold {{ $engagedSessionsTrend >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        <span>{{ $engagedSessionsTrend >= 0 ? '↑' : '↓' }}{{ abs($engagedSessionsTrend) }}%</span>
                    </div> -->
                </div>
                <div class="text-4xl font-bold text-slate-800 mb-2">{{ $engagedSessions }}</div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Engaged Sessions
                </div>
            </div>

        <!-- Metric Card: Avg Wait -->
        <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 rounded-xl bg-amber-600 text-white transition-transform group-hover:scale-110 duration-300">
                     <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                 <!-- <div class="flex items-center space-x-1 text-xs font-semibold {{ $waitTimeTrend <= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                     <span>{{ $waitTimeTrend <= 0 ? '↓' : '↑' }}{{ abs($waitTimeTrend) }}%</span>
                </div> -->
            </div>
            <div class="text-4xl font-bold text-slate-800 mb-2">{{ number_format($avgWaitTime, 1) }}</div>
            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Avg Wait (Sec)
            </div>
        </div>

        <!-- Metric Card: Avg Handle -->
        <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 rounded-xl bg-violet-600 text-white transition-transform group-hover:scale-110 duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <!-- <div class="flex items-center space-x-1 text-xs font-semibold {{ $handleTimeTrend <= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                     <span>{{ $handleTimeTrend <= 0 ? '↓' : '↑' }}{{ abs($handleTimeTrend) }}%</span>
                </div> -->
            </div>
             <div class="text-4xl font-bold text-slate-800 mb-2">{{ number_format($avgSessionHandleTime, 1) }}</div>
            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Avg Handle (Min)
            </div>
        </div>

        {{-- Metric Card: Transfer Rate - Commented out as per request --}}
        {{--
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between mb-4">
                <div class="p-2 rounded-lg bg-rose-600 text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>
                <div class="flex items-center space-x-1 text-xs font-semibold text-slate-400">
                     <span>—</span>
                </div>
            </div>
             <div class="text-3xl font-bold text-slate-800 mb-1">{{ number_format((float)$transferRate, 1) }}%</div>
            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Transfer Rate
            </div>
        </div>
        --}}

         <!-- Metric Card: Sentiment -->
        <div class="rounded-xl bg-white p-6 shadow-md border border-slate-200 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 rounded-xl bg-pink-600 text-white transition-transform group-hover:scale-110 duration-300">
                   <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
               <!-- <div class="flex items-center space-x-1 text-xs font-semibold {{ $sentimentTrend >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                     <span>{{ $sentimentTrend >= 0 ? '+' : '' }}{{ abs($sentimentTrend) }}%</span>
                </div> -->
            </div>
             <div class="text-4xl font-bold text-slate-800 mb-2">{{ number_format($avgSessionSentiment, 1) }}%</div>
            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                {!! $isShowingPrediction ? '<span class="text-purple-600 font-bold">Predicted</span>' : '' !!} Avg Sentiment
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="p-1 rounded bg-indigo-50 text-indigo-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></span>
                Incoming vs. Engaged Sessions
            </h3>
            <div class="h-80 w-full relative">
                 @if(count($sessionsChartData) > 0)
                    <canvas id="sessionsChart" wire:ignore></canvas>
                @else
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                         <svg class="w-16 h-16 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>No data available for the selected period</span>
                    </div>
                @endif
            </div>
        </div>

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
    </div>

    <!-- AI Insights Section -->
    <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-sm border border-slate-100">
        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-100">
            <div class="p-2 rounded-lg bg-indigo-600 text-white">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    AI-Powered Insights
                    @if($aiAnalysisTime)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 5a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0v-1H3a1 1 0 010-2h1v-1a1 1 0 011-1zm5-3a1 1 0 011 1v1h1a1 1 0 010 2h-1v1a1 1 0 01-2 0v-1h-1a1 1 0 010-2h1V3a1 1 0 011-1zm0 5a1 1 0 011 1v1h1a1 1 0 010 2h-1v1a1 1 0 01-2 0v-1h-1a1 1 0 010-2h1v-1a1 1 0 011-1zm4-3a1 1 0 011 1v1h1a1 1 0 010 2h-1v1a1 1 0 01-2 0v-1h-1a1 1 0 010-2h1V3a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                            Freshly Analyzed
                        </span>
                    @endif
                </h2>
                <p class="text-sm text-slate-500">Real-time predictive analysis of your queue performance</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" wire:key="ai-cards-{{ md5(json_encode($waitTimePredictions) . json_encode($staffingRecommendations) . json_encode($peakHoursForecast)) }}">
            <!-- Wait Time Predictions -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 hover:bg-white hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-lg bg-amber-100 text-amber-700">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
                <div class="space-y-3">
                    @forelse(array_slice($waitTimePredictions, 0, 5) as $prediction)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-slate-100 shadow-sm">
                            <span class="text-sm font-semibold text-slate-600">{{ $prediction['hour'] }}</span>
                            <span class="text-sm font-bold text-indigo-600">{{ $prediction['predicted_wait'] }} min</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-sm text-slate-400">Insufficient data</div>
                    @endforelse
                </div>
            </div>

            <!-- Staffing Recommendations -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 hover:bg-white hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-lg bg-emerald-100 text-emerald-700">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h4 class="font-bold text-slate-800">Staffing Recommendations</h4>
                </div>
                <div class="space-y-3">
                   @forelse(array_slice($staffingRecommendations, 0, 5) as $recommendation)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-slate-100 shadow-sm">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-600">{{ $recommendation['hour'] }}</span>
                                {{-- <span class="text-[10px] text-slate-400 font-medium">
                                    {{ $recommendation['ticket_volume'] }} visitors × {{ $recommendation['avg_service_time'] ?? 5 }} min = {{ $recommendation['total_workload'] ?? ($recommendation['ticket_volume'] * 5) }} min work
                                </span> --}}
                            </div>
                            <div class="flex items-center gap-2" title="{{ round(($recommendation['total_workload'] ?? (($recommendation['ticket_volume'] ?? 0) * ($recommendation['avg_service_time'] ?? 5))) / 60, 1) }} hours of work needed ÷ 1 hour per staff = {{ round(($recommendation['total_workload'] ?? (($recommendation['ticket_volume'] ?? 0) * ($recommendation['avg_service_time'] ?? 5))) / 60, 1) }} staff (rounded up)">
                                <span class="text-sm font-bold text-slate-700">{{ $recommendation['recommended_staff'] }} staff</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                    {{ $recommendation['priority'] === 'high' ? 'bg-red-100 text-red-700' : ($recommendation['priority'] === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                    {{ $recommendation['priority'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                         <div class="text-center py-4 text-sm text-slate-400">Insufficient data</div>
                    @endforelse
                </div>
            </div>

             <!-- Optimization Insights -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 hover:bg-white hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-lg bg-violet-100 text-violet-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <h4 class="font-bold text-slate-800">Optimization Tips</h4>
                </div>
                <div class="space-y-3">
                    <!-- Congestion -->
                    @if(count($peakHoursForecast) > 0)
                    <div class="p-3 rounded-lg bg-orange-50 border border-orange-100 text-sm">
                        <strong class="text-orange-800 block mb-1">Peak Hours Alert</strong>
                        <p class="text-orange-700">Expect high volume around <span class="font-bold">{{ $peakHoursForecast[0]['hour'] }}</span> today.</p>
                    </div>
                    @endif
                    
                    <!-- No-Show -->
                     <div class="p-3 rounded-lg bg-white border border-slate-100 text-sm shadow-sm flex items-center justify-between">
                        <span class="text-slate-600">No-Show Probability</span>
                        <span class="font-bold {{ $noShowProbability > 20 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($noShowProbability, 1) }}%</span>
                    </div>

                    <!-- Revenue -->
                    <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100 text-sm">
                        <strong class="text-emerald-800 block mb-1">Opportunity</strong>
                         <p class="text-emerald-700">Increase throughput by <span class="font-bold">{{ $incomingSessions - $engagedSessions }}</span> to boost efficiency.</p>
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
        document.addEventListener('livewire:initialized', () => {
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
                                font: { family: "'Inter', sans-serif", size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            padding: 12,
                            titleFont: { family: "'Inter', sans-serif", size: 13 },
                            bodyFont: { family: "'Inter', sans-serif", size: 12 },
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [4, 4], color: '#f1f5f9' },
                            ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#64748b' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#64748b' }
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
                            datasets: [
                                {
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
                                    label: 'Engaged',
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
                            datasets: [
                                {
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
