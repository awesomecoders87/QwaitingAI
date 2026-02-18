<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use App\Livewire\AIQueueAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PredictQueuePerformanceTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Predicts future queue performance based on historical data analysis. This tool uses AI to:
        
        - Analyze historical performance trends (last 30/60/90 days or same period last year)
        - Generate intelligent predictions for future periods
        - Scale all metrics proportionally based on observed patterns
        - Account for volume growth, efficiency improvements, and seasonal variations
        
        The AI will analyze:
        - Incoming/Engaged sessions growth patterns
        - Wait time and handle time trends
        - Transfer rate stability
        - Customer sentiment evolution
        
        **Use when:** You need to predict future queue performance for capacity planning or forecasting.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $data = $this->getPredictionContext(
            $request->string('team_id'),
            $request->integer('location_id'),
            $request->string('target_start_date'),
            $request->string('target_end_date'),
            $request->string('queue_id', 'all'),
            $request->string('agent_id', 'all'),
            $request->string('timezone', 'UTC')
        );

        return Response::text(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get prediction context data directly (for internal use)
     */
    public function getPredictionContext($teamId, $locationId, $targetStartDate, $targetEndDate, $queueId = 'all', $agentId = 'all', $timezone = 'UTC'): array
    {
        $targetStart = Carbon::parse($targetStartDate);
        $targetEnd = Carbon::parse($targetEndDate);

        // Log::info('PredictQueuePerformanceTool: Executing Prediction Logic', [
        //     'team_id' => $teamId, 
        //     'location_id' => $locationId,
        //     'target_period' => "{$targetStart->format('Y-m-d')} to {$targetEnd->format('Y-m-d')}"
        // ]);

        // Implement cascading fallback to find historical data
        $historicalData = $this->findHistoricalData($teamId, $locationId, $targetStart, $queueId, $agentId, $timezone);

        if (!$historicalData) {
            return [
                'error' => 'No historical data available for prediction',
                'attempted_periods' => [
                    'last_30_days',
                    'last_60_days',
                    'last_90_days',
                    'same_month_last_year',
                ],
            ];
        }

        // Build response with historical context for AI to analyze
        $response = [
            'target_period' => [
                'start' => $targetStart->format('Y-m-d'),
                'end' => $targetEnd->format('Y-m-d'),
                'days' => $targetStart->diffInDays($targetEnd) + 1,
            ],
            'historical_context' => [
                'source' => $historicalData['source'],
                'period' => [
                    'start' => $historicalData['period_start'],
                    'end' => $historicalData['period_end'],
                    'days' => $historicalData['days'],
                ],
                'metrics' => $historicalData['metrics'],
                'trends' => $historicalData['trends'],
            ],
            'analysis_instructions' => [
                'task' => 'Predict ALL queue performance metrics for the target period based on historical context',
                'approach' => [
                    'Analyze the historical metrics and trends provided',
                    'Consider the time gap between historical period and target period',
                    'Apply intelligent growth patterns based on historical trends',
                    'Scale ALL metrics proportionally - do not copy values directly',
                    'Account for volume impact on wait times and resource utilization',
                ],
                'required_predictions' => [
                    'incoming_sessions' => 'Predict based on historical volume and trends',
                    'engaged_sessions' => 'Scale proportionally with incoming sessions, maintaining similar engagement rate',
                    'avg_wait_time_seconds' => 'Adjust based on predicted volume changes',
                    'avg_handle_time_minutes' => 'Consider efficiency improvements over time',
                    'transfer_rate_percent' => 'Keep relatively stable unless trends indicate changes',
                    'avg_sentiment_score' => 'Account for service quality changes and volume impact',
                ],
            ],
        ];

        // Log::info('PredictQueuePerformanceTool: Prediction context prepared', [
        //     'historical_source' => $historicalData['source'],
        //     'historical_sessions' => $historicalData['metrics']['incoming_sessions'],
        // ]);

        return $response;
    }

    /**
     * Find historical data with cascading fallback
     */
    private function findHistoricalData($teamId, $locationId, $targetStartDate, $queueId, $agentId, $timezone): ?array
    {
        // Smart Anchoring:
        // If target is in the future, use NOW as the latest data point for history.
        // If target is in the past, use the day BEFORE target started to simulate a real prediction from that time.
        $anchorDate = $targetStartDate->isFuture() 
            ? Carbon::now() 
            : $targetStartDate->copy()->subDay();

        // Ensure we don't try to look at future data even if logic says so (clamper)
        if ($anchorDate->isFuture()) {
            $anchorDate = Carbon::now();
        }

        // Try 1: Last 30 days from Anchor
        $data = $this->loadPeriodData(
            $teamId, 
            $locationId, 
            $anchorDate->copy()->subDays(30), 
            $anchorDate, 
            $queueId, 
            $agentId,
            $timezone
        );
        if ($data['metrics']['incoming_sessions'] > 0) {
            return array_merge($data, ['source' => 'Last 30 Days (from ' . $anchorDate->format('M Y') . ')']);
        }

        // Try 2: Last 60 days from Anchor
        $data = $this->loadPeriodData(
            $teamId, 
            $locationId, 
            $anchorDate->copy()->subDays(60), 
            $anchorDate, 
            $queueId, 
            $agentId,
            $timezone
        );
        if ($data['metrics']['incoming_sessions'] > 0) {
            return array_merge($data, ['source' => 'Last 60 Days (from ' . $anchorDate->format('M Y') . ')']);
        }

        // Try 3: Last 90 days from Anchor
        $data = $this->loadPeriodData(
            $teamId, 
            $locationId, 
            $anchorDate->copy()->subDays(90), 
            $anchorDate, 
            $queueId, 
            $agentId,
            $timezone
        );
        if ($data['metrics']['incoming_sessions'] > 0) {
            return array_merge($data, ['source' => 'Last 90 Days (from ' . $anchorDate->format('M Y') . ')']);
        }

        // Try 4: Same month last year
        $lastYearStart = $targetStartDate->copy()->subYear()->startOfMonth();
        $lastYearEnd = $targetStartDate->copy()->subYear()->endOfMonth();
        $data = $this->loadPeriodData($teamId, $locationId, $lastYearStart, $lastYearEnd, $queueId, $agentId, $timezone);
        if ($data['metrics']['incoming_sessions'] > 0) {
            return array_merge($data, ['source' => 'Same Month Last Year']);
        }

        return null;
    }

    /**
     * Load analytics data for a specific period
     */
    private function loadPeriodData($teamId, $locationId, $startDate, $endDate, $queueId, $agentId, $timezone): array
    {
        $analytics = new AIQueueAnalytics();
        $analytics->teamId = $teamId;
        $analytics->location = $locationId;
        $analytics->startDate = $startDate->format('Y-m-d');
        $analytics->endDate = $endDate->format('Y-m-d');
        $analytics->selectedQueue = $queueId;
        $analytics->selectedAgent = $agentId;
        $analytics->timezone = $timezone;
        
        $analytics->loadAnalytics();

        return [
            'period_start' => $analytics->startDate,
            'period_end' => $analytics->endDate,
            'days' => $startDate->diffInDays($endDate) + 1,
            'metrics' => [
                'incoming_sessions' => $analytics->incomingSessions,
                'engaged_sessions' => $analytics->engagedSessions,
                'engagement_rate_percent' => $analytics->incomingSessions > 0 
                    ? round(($analytics->engagedSessions / $analytics->incomingSessions) * 100, 1)
                    : 0,
                'avg_wait_time_seconds' => $analytics->avgWaitTime,
                'avg_wait_time_minutes' => round($analytics->avgWaitTime / 60, 1),
                'avg_handle_time_minutes' => $analytics->avgSessionHandleTime,
                'transfer_rate_percent' => $analytics->transferRate,
                'avg_sentiment_score' => $analytics->avgSessionSentiment,
            ],
            'trends' => [
                'incoming_sessions_percent' => $analytics->incomingSessionsTrend,
                'wait_time_percent' => $analytics->waitTimeTrend,
                'handle_time_percent' => $analytics->handleTimeTrend,
            ],
            'hourly_breakdown' => \App\Models\QueueStorage::where('team_id', $teamId)
                ->where('locations_id', $locationId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as volume, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait_seconds')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->map(function($item) {
                    return [
                        'hour' => sprintf('%02d:00', $item->hour),
                        'volume' => $item->volume,
                        'avg_wait_minutes' => round(($item->avg_wait_seconds ?? 0) / 60, 1)
                    ];
                })->toArray()
        ];
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'team_id' => $schema->string()
                ->description('The team/tenant ID to predict for')
                ->required(),
            'location_id' => $schema->integer()
                ->description('The location ID to predict for')
                ->required(),
            'target_start_date' => $schema->string()
                ->description('Target prediction start date in YYYY-MM-DD format')
                ->required(),
            'target_end_date' => $schema->string()
                ->description('Target prediction end date in YYYY-MM-DD format')
                ->required(),
            'queue_id' => $schema->string()
                ->description('Specific queue ID to analyze, or "all" for all queues')
                ->default('all'),
            'agent_id' => $schema->string()
                ->description('Specific agent ID to analyze, or "all" for all agents')
                ->default('all'),
            'timezone' => $schema->string()
                ->description('Timezone for the analysis (e.g., "America/New_York")')
                ->default('UTC'),
        ];
    }
}
