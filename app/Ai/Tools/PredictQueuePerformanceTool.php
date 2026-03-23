<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Carbon\Carbon;
use App\Models\QueueStorage;

class PredictQueuePerformanceTool implements Tool
{
    public function __construct(
        protected int $teamId,
        protected int $locationId
    ) {}

    public function description(): Stringable|string
    {
        return <<<'MARKDOWN'
Predicts future queue performance based on historical data analysis. Use this when the user asks for predictions about the FUTURE (e.g., tomorrow, next month) or "what-if" scenarios (e.g., "predict metrics if incoming tickets increase by 20%").
MARKDOWN;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'target_start_date' => $schema->string()->description('Target prediction start date exactly in YYYY-MM-DD format'),
            'target_end_date' => $schema->string()->description('Target prediction end date exactly in YYYY-MM-DD format'),
            'scenario_multiplier' => $schema->number()->description('If user asks "what if tickets increase by 20%", set this to 1.2. Defaults to 1.0.')->nullable(),
            'staff_change' => $schema->integer()->description('If user asks "what if I add 2 staff", set this to 2. If "remove 1 staff", set to -1.')->nullable(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        try {
            $targetStart = Carbon::parse($request['target_start_date']);
            $targetEnd = Carbon::parse($request['target_end_date']);
            $multiplier = $request['scenario_multiplier'] ?? 1.0;
            $staffChange = $request['staff_change'] ?? 0;

            // Find best historical baseline
            $historicalData = $this->findHistoricalData($targetStart);

            if (!$historicalData) {
                return json_encode([
                    'error' => 'No historical data available for prediction.',
                ]);
            }

            // Simple simulation logic for AI reasoning
            $baselineTickets = $historicalData['metrics']['incoming_sessions'];
            $predictedTickets = round($baselineTickets * $multiplier);
            
            // Logic: Wait time is inversely proportional to staff and proportional to volume (simplified)
            // If staff increases, wait time decreases.
            $currentStaffEstimate = count($historicalData['hourly_breakdown']) > 0 ? max(1, round($baselineTickets / 50)) : 1; // Very rough estimate
            $newStaffEstimate = max(1, $currentStaffEstimate + $staffChange);
            
            $staffFactor = $currentStaffEstimate / $newStaffEstimate;
            $volumeFactor = $multiplier;
            
            $predictedWaitMin = round($historicalData['metrics']['avg_wait_minutes'] * $volumeFactor * $staffFactor, 1);

            return json_encode([
                'prediction_summary' => [
                    'target_period' => "{$targetStart->toDateString()} to {$targetEnd->toDateString()}",
                    'predicted_incoming_tickets' => $predictedTickets,
                    'predicted_avg_wait_minutes' => $predictedWaitMin,
                    'confidence_level' => $multiplier == 1.0 && $staffChange == 0 ? 'High (85%)' : 'Medium (65%)',
                ],
                'scenario_details' => [
                    'volume_multiplier' => $multiplier,
                    'staff_adjustment' => $staffChange,
                    'impact_analysis' => $staffChange > 0 ? "Adding staff significantly reduces wait time pressure." : ($staffChange < 0 ? "Reducing staff will likely cause wait time spikes." : "Based on volume trends only."),
                ],
                'historical_baseline' => $historicalData,
                'instructions' => 'Explain the reasoning: if volume increases by ' . (($multiplier-1)*100) . '%, and staff changes by ' . $staffChange . ', the wait time is adjusted accordingly. Note that wait times non-linearly spike when volume exceeds capacity.'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[PredictQueuePerformanceTool] Error: ' . $e->getMessage());
            return json_encode(['error' => 'Failed to resolve predictions: ' . $e->getMessage()]);
        }
    }

    private function findHistoricalData(Carbon $targetStartDate): ?array
    {
        $anchorDate = Carbon::now();

        // Try Last 30 days
        $data = $this->loadPeriodData($anchorDate->copy()->subDays(30), $anchorDate);
        if ($data['metrics']['incoming_sessions'] > 0) {
            $data['source'] = 'Last 30 Days Baseline';
            return $data;
        }

        // Try Last 90 days
        $data = $this->loadPeriodData($anchorDate->copy()->subDays(90), $anchorDate);
        if ($data['metrics']['incoming_sessions'] > 0) {
            $data['source'] = 'Last 90 Days Baseline';
            return $data;
        }

        return null;
    }

    private function loadPeriodData(Carbon $startDate, Carbon $endDate): array
    {
        $baseQuery = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->locationId)
            ->whereBetween('arrives_time', [$startDate->startOfDay(), $endDate->endOfDay()]);

        $incoming = $baseQuery->count();
        $engaged = (clone $baseQuery)->where('status', 'Close')->count();
        
        $avgWaitSeconds = (clone $baseQuery)
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait')
            ->value('avg_wait');

        $avgHandleSeconds = (clone $baseQuery)
            ->whereNotNull('closed_datetime')
            ->whereNotNull('called_datetime')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as avg_handle')
            ->value('avg_handle');

        return [
            'period_days' => $startDate->diffInDays($endDate) + 1,
            'metrics' => [
                'incoming_sessions' => $incoming,
                'engaged_sessions' => $engaged,
                'avg_wait_minutes' => round(($avgWaitSeconds ?: 0) / 60, 1),
                'avg_handle_minutes' => round(($avgHandleSeconds ?: 0) / 60, 1),
            ],
            'hourly_breakdown' => (clone $baseQuery)
                ->selectRaw('HOUR(arrives_time) as hour, COUNT(*) as volume')
                ->groupBy('hour')
                ->orderBy('volume', 'desc')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'hour' => sprintf('%02d:00', $item->hour),
                        'volume' => $item->volume,
                    ];
                })->toArray()
        ];
    }
}
