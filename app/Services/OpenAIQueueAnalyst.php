<?php

namespace App\Services;

use OpenAI;
use App\Livewire\AIQueueAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OpenAIQueueAnalyst
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));

        // Log::info("Key  ",[config('services.openai.api_key')]);
    }

    /**
     * Analyze queue performance using OpenAI
     */
    public function analyzePerformance($teamId, $locationId, $startDate, $endDate, $queueId = 'all', $agentId = 'all', $timezone = 'UTC')
    {
        // Log::info('===== ANALYZE PERFORMANCE CALLED =====', [
        //     'team_id' => $teamId,
        //     'location_id' => $locationId,
        //     'date_range' => "$startDate to $endDate",
        //     'queue_id' => $queueId,
        //     'agent_id' => $agentId,
        //     'caller' => debug_backtrace()[1]['function'] ?? 'unknown'
        // ]);

        // Check if the requested period is in the future
        $start = Carbon::parse($startDate);
        $isFuture = $start->isFuture();
        
        // 1. First, attempt to load analytics for the requested TARGET period
        $analytics = new AIQueueAnalytics();
        $analytics->teamId = $teamId;
        $analytics->location = $locationId;
        $analytics->startDate = $startDate;
        $analytics->endDate = $endDate;
        $analytics->selectedQueue = $queueId;
        $analytics->selectedAgent = $agentId;
        $analytics->timezone = $timezone;
        
        $analytics->loadAnalytics();
        
        // Check if we have data
        $hasData = $analytics->incomingSessions > 0;
        $useHistoricalContext = $isFuture || !$hasData;

        // Log::info('===== DATA CHECK COMPLETE =====', [
        //     'isFuture' => $isFuture,
        //     'hasData' => $hasData,
        //     'incoming_sessions' => $analytics->incomingSessions,
        //     'usingMCP' => $useHistoricalContext
        // ]);
        
        $context = '';
        $historicalSource = '';
        
        if ($useHistoricalContext) {
            // Use MCP tool to get historical context for predictions
            // Log::info('OpenAIQueueAnalyst: Using MCP tool for historical context');
            
            $mcpTool = new \App\Mcp\Tools\PredictQueuePerformanceTool();
            $mcpData = $mcpTool->getPredictionContext($teamId, $locationId, $startDate, $endDate, $queueId, $agentId, $timezone);
            
            if (isset($mcpData['error'])) {
                // Log::info('OpenAIQueueAnalyst: No historical data available', ['error' => $mcpData['error']]);
                
                // Use AI to generate a user-friendly explanation
                $userMessage = $this->generateNoDataMessage($startDate, $endDate, $queueId, $agentId, $teamId, $locationId);
                
                // Return a graceful response instead of throwing exception
                return [
                    'is_prediction' => false,
                    'no_data' => true,
                    'error_message' => 'No historical data available for this selection',
                    'user_message' => $userMessage,
                    'raw_data' => [
                        'period' => ['start' => $startDate, 'end' => $endDate],
                        'metrics' => [
                            'incoming_sessions' => 0,
                            'engaged_sessions' => 0,
                            'avg_wait_time_minutes' => 0,
                            'avg_handle_time_minutes' => 0,
                            'transfer_rate_percent' => 0,
                            'sentiment_score' => 0,
                        ],
                    ],
                    'ai_analysis' => null,
                ];
            }
            
            // Log::info('OpenAIQueueAnalyst: MCP context received', [
            //     'source' => $mcpData['historical_context']['source'],
            //     'sessions' => $mcpData['historical_context']['metrics']['incoming_sessions']
            // ]);
            
            // Build context from MCP data
            $historicalSource = $mcpData['historical_context']['source'];
            $historicalMetrics = $mcpData['historical_context']['metrics'];
            $historicalPeriod = $mcpData['historical_context']['period'];
            $historicalDays = $mcpData['historical_context']['period']['days'];
            $targetDays = $mcpData['target_period']['days'];
            
            $context = "PREDICTION REQUEST\n\n";
            
            $context .= "Target Period: {$startDate} to {$endDate} ({$targetDays} days)\n\n";
            
            $context .= "Historical Data ({$historicalSource}):\n";
            $context .= "Period: {$historicalPeriod['start']} to {$historicalPeriod['end']} ({$historicalDays} days)\n\n";
            
            $context .= "Metrics:\n";
            $context .= "- Incoming Sessions: {$historicalMetrics['incoming_sessions']}\n";
            $context .= "- Engaged Sessions: {$historicalMetrics['engaged_sessions']}\n";
            $context .= "- Engagement Rate: {$historicalMetrics['engagement_rate_percent']}%\n";
            $context .= "- Average Wait Time: {$historicalMetrics['avg_wait_time_minutes']} minutes\n";
            $context .= "- Average Handle Time: {$historicalMetrics['avg_handle_time_minutes']} minutes\n";
            $context .= "- Transfer Rate: {$historicalMetrics['transfer_rate_percent']}%\n";
            $context .= "- Customer Sentiment: {$historicalMetrics['avg_sentiment_score']}/100\n\n";
            
            if (isset($mcpData['historical_context']['trends'])) {
                $trends = $mcpData['historical_context']['trends'];
                $context .= "Trends (vs previous period):\n";
                $context .= "- Incoming Sessions: {$trends['incoming_sessions_percent']}%\n";
                $context .= "- Wait Time: {$trends['wait_time_percent']}%\n";
                $context .= "- Handle Time: {$trends['handle_time_percent']}%\n\n";
            }

            if (isset($mcpData['historical_context']['hourly_breakdown'])) {
                $context .= "Historical Hourly Patterns (CRITICAL for predictions):\n";
                foreach ($mcpData['historical_context']['hourly_breakdown'] as $hour) {
                    $context .= "- {$hour['hour']}: {$hour['volume']} sessions, Avg Wait: {$hour['avg_wait_minutes']} min\n";
                }
                $context .= "NOTE: You MUST reflect these specific hourly peaks in your 'wait_time_predictions' and 'staffing_recommendations'. Do not use generic business hours.\n\n";
            }
            
            $context .= "Task: Predict metrics for the target period using daily average calculation.\n\n";
            
            // Calculate daily averages from historical data
            $dailyIncoming = round($historicalMetrics['incoming_sessions'] / $historicalDays, 3);
            $dailyEngaged = round($historicalMetrics['engaged_sessions'] / $historicalDays, 3);
            
            // Calculate baseline predictions
            $predictedIncoming = round($dailyIncoming * $targetDays);
            $predictedEngaged = round($dailyEngaged * $targetDays);
            
            $context .= "CALCULATION METHOD:\n";
            $context .= "Step 1 - Calculate Daily Averages:\n";
            $context .= "  Incoming per day: {$historicalMetrics['incoming_sessions']} ÷ {$historicalDays} = {$dailyIncoming}\n";
            $context .= "  Engaged per day: {$historicalMetrics['engaged_sessions']} ÷ {$historicalDays} = {$dailyEngaged}\n\n";
            
            $context .= "Step 2 - Predict for {$targetDays} Days:\n";
            $context .= "  Incoming: {$dailyIncoming} × {$targetDays} = {$predictedIncoming}\n";
            $context .= "  Engaged: {$dailyEngaged} × {$targetDays} = {$predictedEngaged}\n\n";
            
            $context .= "Step 3 - Other Metrics:\n";
            $context .= "  Wait Time: Keep similar to historical ({$historicalMetrics['avg_wait_time_minutes']} min) with small variation\n";
            $context .= "  Handle Time: Keep stable ({$historicalMetrics['avg_handle_time_minutes']} min) with small variation\n";
            $context .= "  Transfer Rate: Keep stable ({$historicalMetrics['transfer_rate_percent']}%)\n";
            $context .= "  Sentiment: Keep stable ({$historicalMetrics['avg_sentiment_score']}/100) with small variation\n\n";
            
            $context .= "Use these calculations as your baseline and adjust slightly based on trends if needed.\n\n";
            
            $context .= "Return predictions in this JSON format:\n";
            $context .= $this->buildJsonStructurePrompt();
            
        } else {
            // Actual data exists for the selected period - analyze it normally
            $context = $this->buildContext($analytics, $startDate, $endDate);
            
            // Log::info('OpenAIQueueAnalyst: Using actual period data (not prediction)', [
            //     'period' => "{$startDate} to {$endDate}",
            //     'sessions' => $analytics->incomingSessions
            // ]);
        }
        
        
        // Log::info('OpenAIQueueAnalyst: Final context built', [
        //     'context_length' => strlen($context), 
        //     'is_prediction_mode' => $useHistoricalContext,
        //     'context_preview' => substr($context, 0, 300) . '...'
        // ]);

        // Ask OpenAI to analyze and return JSON
        // Log::info('OpenAIQueueAnalyst: Sending request to OpenAI');
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4-1106-preview', // Use a model that supports JSON mode reliably
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a queue analytics expert. Analyze the data and provide actionable insights in valid JSON format matching the requested structure.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Analyze this queue performance data and return the result in JSON:\n\n" . $context
                    ]
                ],
                'temperature' => 0, // Deterministic predictions (was 0.7)
                'response_format' => ['type' => 'json_object'],
            ]);
            
            // Log::info('===== OPENAI REQUEST COMPLETED =====', [
            //     'team_id' => $teamId,
            //     'location_id' => $locationId,
            //     'date_range' => "$startDate to $endDate",
            //     'is_prediction' => $useHistoricalContext,
            //     'model' => config('services.openai.model', 'gpt-4-1106-preview'),
            //     'temperature' => 0,
            //     'context_length' => strlen($context),
            //     'response_length' => strlen($response->choices[0]->message->content ?? ''),
            // ]);
        } catch (\Exception $e) {
            Log::error('OpenAIQueueAnalyst: OpenAI Error', ['message' => $e->getMessage()]);
            throw $e;
        }

        $aiContent = $response->choices[0]->message->content;
        $aiData = json_decode($aiContent, true);
        
        // Log the EXACT predictions AI generated
        // Log::info('===== FINAL AI PREDICTIONS =====', [
        //     'date_range' => "$startDate to $endDate",
        //     'wait_predictions' => $aiData['wait_time_predictions'] ?? [],
        //     'staffing_recs' => $aiData['staffing_recommendations'] ?? [],
        //     'predicted_metrics' => $aiData['predicted_metrics'] ?? [],
        //     'is_prediction' => $useHistoricalContext,
        // ]);
        
        return [
            'is_prediction' => $useHistoricalContext, // Flag to tell frontend if this is a prediction
            'raw_data' => [
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
                'metrics' => [
                    'incoming_sessions' => $analytics->incomingSessions,
                    'engaged_sessions' => $analytics->engagedSessions,
                    'avg_wait_time_minutes' => round($analytics->avgWaitTime / 60, 1),
                    'avg_handle_time_minutes' => $analytics->avgSessionHandleTime,
                    'transfer_rate_percent' => $analytics->transferRate,
                    'sentiment_score' => $analytics->avgSessionSentiment,
                ],
            ],
            'ai_analysis' => $aiContent,
        ];
    }

    /**
     * Build context string from analytics data
     */
    private function buildContext($analytics, $startDate, $endDate)
    {
        $engagementRate = $analytics->incomingSessions > 0 
            ? round(($analytics->engagedSessions / $analytics->incomingSessions) * 100, 1)
            : 0;

        $context = "Period: {$startDate} to {$endDate}\n";
        $context .= "Filters - Queue: {$analytics->selectedQueue}, Agent: {$analytics->selectedAgent}\n\n";
        
        $context .= "Performance Metrics:\n";
        $context .= "- Incoming Sessions: {$analytics->incomingSessions}\n";
        $context .= "- Engaged Sessions: {$analytics->engagedSessions}\n";
        $context .= "- Engagement Rate: {$engagementRate}%\n";
        $context .= "- Average Wait Time: " . round($analytics->avgWaitTime / 60, 1) . " minutes\n";
        $context .= "- Average Handle Time: {$analytics->avgSessionHandleTime} minutes\n";
        $context .= "- Transfer Rate: {$analytics->transferRate}%\n";
        $context .= "- Customer Sentiment: {$analytics->avgSessionSentiment}/100\n\n";

        $context .= "Trends (vs previous period):\n";
        $context .= "- Incoming Sessions: {$analytics->incomingSessionsTrend}%\n";
        $context .= "- Wait Time: {$analytics->waitTimeTrend}%\n";
        $context .= "- Handle Time: {$analytics->handleTimeTrend}%\n\n";

        // Add AI insights if available
        if (!empty($analytics->staffingRecommendations)) {
            $context .= "Staffing Recommendations based on history:\n";
            foreach ($analytics->staffingRecommendations as $rec) {
                $context .= "- Hour {$rec['hour']}: {$rec['recommended_staff']} staff (Priority: {$rec['priority']})\n";
            }
            $context .= "\n";
        }

        if (!empty($analytics->bottleneckDetection)) {
            $context .= "Detected Bottlenecks:\n";
            foreach ($analytics->bottleneckDetection as $bottleneck) {
                $avgWait = isset($bottleneck['avg_wait_minutes']) ? $bottleneck['avg_wait_minutes'] : 0;
                $impact = isset($bottleneck['impact']) ? $bottleneck['impact'] : 'unknown';
                $context .= "- {$bottleneck['service']}: {$avgWait} min avg (Impact: {$impact})\n";
            }
            $context .= "\n";
        }

        if (!empty($analytics->peakHoursForecast)) {
            $context .= "Peak Hours Forecast:\n";
            foreach ($analytics->peakHoursForecast as $peak) {
                $volume = isset($peak['expected_volume']) ? $peak['expected_volume'] : 0;
                $context .= "- {$peak['hour']}: {$volume} customers expected\n";
            }
            $context .= "\n";
        }

        // Initialize full 24-hour array to ensure all hours are represented (0-23)
        $fullDayStats = [];
        for ($h = 0; $h < 24; $h++) {
            $hourKey = sprintf('%02d:00', $h);
            $fullDayStats[$hourKey] = ['volume' => 0, 'avg_wait' => 0];
        }

        // Fetch actual data
        $hourlyData = \App\Models\QueueStorage::where('team_id', $analytics->teamId)
            ->where('locations_id', $analytics->location)
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(), 
                Carbon::parse($endDate)->endOfDay()
            ])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as volume, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait_seconds')
            ->groupBy('hour')
            ->get();

        // Merge actual data into full day stats
        foreach ($hourlyData as $row) {
            $hourKey = sprintf('%02d:00', $row->hour);
            $fullDayStats[$hourKey] = [
                'volume' => $row->volume,
                'avg_wait' => round(($row->avg_wait_seconds ?? 0) / 60, 1)
            ];
        }

        // Build context string with full 24-hour view
        $context .= "Historical Hourly Patterns (12 AM - 11 PM):\n";
        $peaks = [];
        
        foreach ($fullDayStats as $hour => $stats) {
            if ($stats['volume'] > 0) {
                $context .= "- {$hour}: {$stats['volume']} sessions, Avg Wait: {$stats['avg_wait']} min\n";
                $peaks[$hour] = $stats['volume'];
            }
            // We implicitly skip 0 volume hours in the text to save tokens, 
            // BUT we tell the AI about the full range conceptually.
        }
            
        if (!empty($peaks)) {
            // Sort to find actual top peaks to explicitly tell AI
            arsort($peaks);
            $topPeaks = array_slice(array_keys($peaks), 0, 3);
            $topPeaksStr = implode(', ', $topPeaks);

            $context .= "\nINSTRUCTION: The 'Hourly Visits' chart shows clear peaks at: {$topPeaksStr}.\n";
            $context .= "You MUST align your 'wait_time_predictions' and 'staffing_recommendations' with these chart peaks.\n";
        } else {
             $context .= "\nNOTE: No hourly data available for this period.\n";
        }

        $context .= "\nPlease analyze this data and provide a JSON response with the following structure:\n";
        $context .= $this->buildJsonStructurePrompt();

        return $context;
    }

    /**
     * Build the JSON structure prompt for AI response
     */
    private function buildJsonStructurePrompt()
    {
        $prompt = "{\n";
        $prompt .= "  \"predicted_metrics\": {\n";
        $prompt .= "    \"incoming_sessions\": number,\n";
        $prompt .= "    \"engaged_sessions\": number,\n";
        $prompt .= "    \"avg_wait_seconds\": number,\n";
        $prompt .= "    \"avg_handle_minutes\": number,\n";
        $prompt .= "    \"transfer_rate\": number,\n";
        $prompt .= "    \"avg_sentiment\": number\n";
        $prompt .= "  },\n";
        $prompt .= "  \"wait_time_predictions\": [{\"hour\": \"HH:00\", \"predicted_wait\": number_in_minutes, \"confidence\": number_0_to_100}],\n";
        $prompt .= "  \"staffing_recommendations\": [{\"hour\": \"HH:00\", \"recommended_staff\": number, \"priority\": \"low\"|\"medium\"|\"high\"}],\n";
        $prompt .= "  \"peak_hours_forecast\": [{\"hour\": \"HH:00 - HH:00\", \"expected_volume\": number, \"severity\": \"low\"|\"medium\"|\"high\"}],\n";
        $prompt .= "  \"no_show_probability\": number_0_to_100,\n";
        $prompt .= "  \"bottleneck_detection\": [{\"service\": \"string\", \"avg_wait_minutes\": number, \"volume\": number, \"impact\": \"low\"|\"medium\"|\"high\"}],\n";
        $prompt .= "  \"daily_forecast\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"date\": \"YYYY-MM-DD\",\n";
        $prompt .= "      \"incoming_sessions\": number,\n";
        $prompt .= "      \"engaged_sessions\": number,\n";
        $prompt .= "      \"avg_wait_seconds\": number,\n";
        $prompt .= "      \"avg_handle_minutes\": number\n";
        $prompt .= "    }\n";
        $prompt .= "  ], \n";
        $prompt .= "  \"optimization_tips\": {\n";
        $prompt .= "    \"peak_hours_alert\": {\"active\": boolean, \"message\": \"string\"},\n";
        $prompt .= "    \"opportunity\": {\"message\": \"string\", \"value\": number}\n";
        $prompt .= "  }\n";
        $prompt .= "}\n";
        
        return $prompt;
    }

    /**
     * Chat with AI about queue data
     */
    public function chat(array $messages, $teamId, $locationId)
    {
        // Get current data for context
        $analytics = new AIQueueAnalytics();
        $analytics->teamId = $teamId;
        $analytics->location = $locationId;
        $analytics->startDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $analytics->endDate = Carbon::now()->format('Y-m-d');
        $analytics->loadAnalytics();

        // Build system message with current data
        $systemMessage = [
            'role' => 'system',
            'content' => $this->buildSystemPrompt($analytics)
        ];

        // Combine with user messages
        $allMessages = array_merge([$systemMessage], $messages);

        $response = $this->client->chat()->create([
            'model' => 'gpt-4',
            'messages' => $allMessages,
            'temperature' => 0.7,
        ]);

        return $response->choices[0]->message->content;
    }

    /**
     * Build system prompt with current data
     */
    private function buildSystemPrompt($analytics)
    {
        $engagementRate = $analytics->incomingSessions > 0 
            ? round(($analytics->engagedSessions / $analytics->incomingSessions) * 100, 1)
            : 0;

        return "You are a queue management expert with access to real-time queue data for the past 7 days.

Current Performance:
- Engagement Rate: {$engagementRate}%
- Average Wait Time: " . round($analytics->avgWaitTime / 60, 1) . " minutes
- Customer Sentiment: {$analytics->avgSessionSentiment}/100

You can answer questions about:
- Queue performance and metrics
- Staffing optimization
- Wait time reduction strategies
- Customer experience improvements
- Bottleneck identification
- Resource allocation

Provide concise, actionable advice based on the data. Use specific numbers and recommendations.";
    }

    /**
     * Quick insight generation
     */
    public function generateQuickInsight($teamId, $locationId)
    {
        $analytics = new AIQueueAnalytics();
        $analytics->teamId = $teamId;
        $analytics->location = $locationId;
        $analytics->startDate = Carbon::now()->subDays(1)->format('Y-m-d');
        $analytics->endDate = Carbon::now()->format('Y-m-d');
        $analytics->loadAnalytics();

        $prompt = "Based on today's queue performance, provide a one-sentence insight:\n\n";
        $prompt .= "Sessions: {$analytics->incomingSessions}, ";
        $prompt .= "Wait Time: " . round($analytics->avgWaitTime / 60, 1) . " min, ";
        $prompt .= "Sentiment: {$analytics->avgSessionSentiment}/100";

        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo', // Faster, cheaper for quick insights
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.5,
            'max_tokens' => 100,
        ]);

        return $response->choices[0]->message->content;
    }

    /**
     * Parse natural language user query to extract filters and intent
     */
    public function parseUserQuery($query, $teamId, $locationId, $timezone = 'UTC', $queues = [], $agents = [], $locations = [], $currentStartDate = null, $currentEndDate = null, $currentQueueId = 'all', $currentAgentId = 'all')
    {
        $now = Carbon::now($timezone);
        $today = $now->format('Y-m-d');
        
        // Format lists for prompt
        $queueList = count($queues) > 0 
            ? implode(", ", array_map(fn($q) => "{$q['name']} (ID: {$q['id']})", $queues)) 
            : "None available";
            
        $agentList = count($agents) > 0 
            ? implode(", ", array_map(fn($a) => "{$a['name']} (ID: {$a['id']})", $agents)) 
            : "None available";

        $locationList = count($locations) > 0 
            ? implode(", ", array_map(fn($l) => "{$l['name']} (ID: {$l['id']})", $locations)) 
            : "None available";
        

            // Get current location name for context
        $currentLocationName = '';
        foreach ($locations as $loc) {
            if ($loc['id'] == $locationId) {
                $currentLocationName = $loc['name'];
                break;
            }
        }

        // Get names for current context
        $currentQueueName = 'All Queues';
        if ($currentQueueId !== 'all') {
            foreach ($queues as $q) {
                if ($q['id'] == $currentQueueId) {
                    $currentQueueName = $q['name'];
                    break;
                }
            }
        }

        $currentAgentName = 'All Agents';
        if ($currentAgentId !== 'all') {
            foreach ($agents as $a) {
                if ($a['id'] == $currentAgentId) {
                    $currentAgentName = $a['name'];
                    break;
                }
            }
        }

        // Log::info("Location name is : " . $currentLocationName);    
        $systemPrompt = "You are an Elite Queue Analytics AI. Your domain is strictly limited to Queue Management, Staffing, and Performance Analytics.

        Context:
        - Current Date: {$today}
        - Timezone: {$timezone}
        - Location: {$currentLocationName}
        
        Available Data Points:
        - Queues: {$queueList}
        - Agents: {$agentList}
        
        Mission:
        1. Analyze the user's input for relevancy. 
           - RELEVANT TOPICS: Queue metrics, waiting times, staffing, agents, dates, efficiency, predictions, bottlenecks.
           - IRRELEVANT TOPICS: General knowledge (e.g., 'What is Laravel', 'Who is President'), coding help, creative writing, or anything outside queue management.
        
        2. If Irrelevant:
           - Set intent to 'off_topic'.
           - Explanation: Politely refuse. state clearly that you are a specialized AI for Queue Analytics and cannot answer general questions.
        
        3. If Relevant:
           - Identify filters (date, queue, agent).
           - Set intent:
             - 'analysis': Viewing past/current data.
             - 'prediction': Asking for future dates (e.g. 'next week').
             - 'scenario': Asking 'What if' hypothetical questions (e.g. 'What if volume +20%').
             - 'question': Asking specific data questions (e.g. 'Why is wait time high?').
             - 'general_chat': Greetings/small talk.
           
           - Explanation: 
             - For 'analysis'/'prediction'/'general_chat': Confirm action naturally.
             - For 'question'/'scenario': Keep it extremely brief (e.g. 'Analyzing based on your scenario...') or empty, as a detailed answer will follow.
        
        CONTEXT RULES (CRITICAL):
        - The user is already looking at a dashboard with active filters:
            - Date Range: {$currentStartDate} to {$currentEndDate}
            - Current Queue ID: {$currentQueueId} (Name: {$currentQueueName})
            - Current Agent ID: {$currentAgentId} (Name: {$currentAgentName})
        - IF the user does not specify a NEW date/queue/agent, YOU MUST RETURN THE CURRENT VALUES from the context above.
        - IF the user says 'this week', 'selected date', 'current range', or just asks a question like 'what is the wait time?', assume they mean the CURRENT filters.
        - NEVER ask the user to specify dates or queues if they are already set in the context. USE THEM.
        
        Return JSON:
        {
          \"start_date\": \"YYYY-MM-DD\" | null,
          \"end_date\": \"YYYY-MM-DD\" | null,
          \"queue_id\": \"ID\" | \"all\" | null,
          \"agent_id\": \"ID\" | \"all\" | null,
          \"intent\": \"analysis\" | \"prediction\" | \"scenario\" | \"question\" | \"general_chat\" | \"off_topic\",
          \"explanation\": \"Your response here\"
        }";

        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $query]
                ],
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object']
            ]);

            $content = $response->choices[0]->message->content;
            return json_decode($content, true);
        } catch (\Exception $e) {
            Log::error('OpenAIQueueAnalyst: Parse Query Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Answer a specific question based on the provided context
     */
    public function answerSpecificQuestion($question, $contextData, $timezone)
    {
        $systemPrompt = "You are an Elite Queue Analytics AI. You have 20+ years of experience in data analysis.
        
        DOMAIN RESTRICTION:
        - You consist ONLY of the data provided below and knowledge about Queue Management.
        - You DO NOT know about general world facts, other software (like Laravel), history, or pop culture.
        - If the user asks a general question (e.g., 'What is the capital of France?', 'What is Laravel?'), you MUST politely decline and state that you only analyze queue performance data.

        Task:
        1. Analyze the user's question against the Context Data.
        2. If the context contains 'smart_prediction_context', USE IT. It contains advanced historical analysis and specific instructions for predictions. Follow its 'analysis_instructions' strictly.
        3. If the question is about the data or queue management, provide a high-level, professional insight.
        4. If the question is NOT related to the data/queues, reply: 'I am a specialized AI for Queue Analytics. I cannot answer general knowledge questions. Please ask me about your queue performance, staffing, or wait times.'
        
        Context Data:
        " . json_encode($contextData, JSON_PRETTY_PRINT);

        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4o', 
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $question]
                ],
                'temperature' => 0.3, // Lower temperature for more focused, restrictive behavior
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('OpenAIQueueAnalyst: Answer Question Error', ['message' => $e->getMessage()]);
            return "I'm having trouble analyzing that specific question right now. Please try again.";
        }
    }

    /**
     * Generate AI-powered user-friendly message when no data is available
     */
    private function generateNoDataMessage($startDate, $endDate, $queueId, $agentId, $teamId, $locationId)
    {
        // Get available queues and agents to help AI suggest alternatives
        $queues = \App\Models\Category::where('team_id', $teamId)
            ->whereJsonContains('category_locations', (string) $locationId)
            ->where('parent_id', 0)
            ->select('id', 'name')
            ->get();

        $agents = \App\Models\User::where('team_id', $teamId)
            ->whereJsonContains('locations', (string) $locationId)
            ->select('id', 'name')
            ->get();

        $queueName = $queueId !== 'all' 
            ? ($queues->firstWhere('id', $queueId)->name ?? "Queue ID: {$queueId}") 
            : 'All Queues';
        
        $agentName = $agentId !== 'all' 
            ? ($agents->firstWhere('id', $agentId)->name ?? "Agent ID: {$agentId}") 
            : 'All Agents';

        $availableQueues = $queues->pluck('name')->join(', ');
        $availableAgents = $agents->pluck('name')->join(', ');

        $prompt = "The user is trying to analyze queue data but there is no historical data available for their current selection.

Current Filters:
- Date Range: {$startDate} to {$endDate}
- Queue: {$queueName}
- Agent: {$agentName}

Available Options:
- Available Queues: {$availableQueues}
- Available Agents: {$availableAgents}

Task: Generate a friendly, helpful message (2-3 sentences max) that:
1. Explains that no data is available for this specific combination
2. Intelligently suggests what they should try instead (e.g., if they selected a specific agent, suggest trying 'All Agents' or a different date range)
3. Sounds warm and conversational, not robotic

Do NOT include technical jargon or mention 'filters' or 'selection'. Speak naturally as if you're a helpful assistant.";

        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a friendly queue analytics assistant helping users find the data they need.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 150,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('OpenAIQueueAnalyst: Error generating no-data message', ['message' => $e->getMessage()]);
            // Fallback to a generic message if AI fails
            return "I don't have any data for this combination of date range, queue, and agent. Try selecting a different date range or choosing 'All Queues' and 'All Agents' to see broader results.";
        }
    }
}
