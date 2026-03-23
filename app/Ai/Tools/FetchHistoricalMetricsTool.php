<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Carbon\Carbon;
use App\Models\QueueStorage;
use App\Models\Category;

class FetchHistoricalMetricsTool implements Tool
{
    public function __construct(
        protected int $teamId,
        protected int $locationId
    ) {}

    public function description(): Stringable|string
    {
        return 'Fetches actual historical queue performance metrics from the database. Use this to generate analytical reports, compare date ranges, find peak hours, and get aggregate wait times.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'start_date' => $schema->string()->description('Start date exactly in YYYY-MM-DD format.'),
            'end_date' => $schema->string()->description('End date exactly in YYYY-MM-DD format.'),
            'queue_id' => $schema->integer()->description('The ID of the specific queue/category. Null for across all queues.')->nullable(),
            'daily_breakdown' => $schema->boolean()->description('Set to true if you need daily metrics (e.g., to find the top 5 busiest days). Defaults to false.')->nullable(),
            'include_service_breakdown' => $schema->boolean()->description('Set to true to see performance per queue/service for bottleneck detection.')->nullable(),
            'include_staff_performance' => $schema->boolean()->description('Set to true to analyze agent efficiency and staffing gaps.')->nullable(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        try {
            $startDate = Carbon::parse($request['start_date'])->startOfDay();
            $endDate = Carbon::parse($request['end_date'])->endOfDay();
            $queueId = $request['queue_id'] ?? null;
            $wantsDaily = $request['daily_breakdown'] ?? false;
            $wantsService = $request['include_service_breakdown'] ?? false;
            $wantsStaff = $request['include_staff_performance'] ?? false;

            $baseQuery = QueueStorage::where('team_id', $this->teamId)
                ->where('locations_id', $this->locationId)
                ->whereBetween('arrives_time', [$startDate, $endDate]);

            if ($queueId) {
                // Verify queue belongs to location
                $category = Category::where('team_id', $this->teamId)
                    ->whereJsonContains('category_locations', (string) $this->locationId)
                    ->find($queueId);
                
                if (!$category) {
                    return json_encode(['error' => 'Queue not found or not assigned to this location.']);
                }
                $baseQuery->where('category_id', $queueId);
            }

            $results = [
                'period' => "{$startDate->toDateString()} to {$endDate->toDateString()}",
                'summary_metrics' => $this->getSummaryMetrics(clone $baseQuery),
            ];

            if ($wantsDaily) {
                $results['daily_metrics'] = $this->getDailyMetrics(clone $baseQuery);
            }

            if ($wantsService) {
                $results['service_breakdown'] = $this->getServiceBreakdown(clone $baseQuery);
            }

            if ($wantsStaff) {
                $results['staff_performance'] = $this->getStaffPerformance(clone $baseQuery);
            }

            return json_encode($results);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[FetchHistoricalMetricsTool] Error: ' . $e->getMessage());
            return json_encode(['error' => 'Failed to resolve historical metrics: ' . $e->getMessage()]);
        }
    }

    private function getSummaryMetrics($query): array
    {
        $incoming = $query->count();
        $engaged = (clone $query)->where('status', 'Close')->count();
        $avgWait = (clone $query)->whereNotNull('called_datetime')->whereNotNull('arrives_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait')->value('avg_wait');
        $avgHandle = (clone $query)->whereNotNull('closed_datetime')->whereNotNull('called_datetime')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as avg_handle')->value('avg_handle');

        return [
            'incoming_tickets' => $incoming,
            'served_tickets' => $engaged,
            'engagement_rate' => $incoming > 0 ? round(($engaged / $incoming) * 100, 1) . '%' : '0%',
            'avg_wait_minutes' => round(($avgWait ?: 0) / 60, 1),
            'avg_handle_minutes' => round(($avgHandle ?: 0) / 60, 1),
            'status' => $incoming > 0 && $engaged == 0 ? 'CRITICAL: High traffic with zero tickets served.' : 'Normal',
        ];
    }

    private function getDailyMetrics($query): array
    {
        return $query->selectRaw('DATE(arrives_time) as date, COUNT(*) as count, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as wait')
            ->groupBy('date')->orderByDesc('count')->get()->map(fn($r) => [
                'date' => $r->date,
                'tickets' => $r->count,
                'wait_min' => round(($r->wait ?: 0) / 60, 1)
            ])->toArray();
    }

    private function getServiceBreakdown($query): array
    {
        return $query->selectRaw('category_id, COUNT(*) as count, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as wait')
            ->whereNotNull('category_id')->groupBy('category_id')->get()->map(function($r) {
                $cat = Category::find($r->category_id);
                return [
                    'service_name' => $cat->name ?? 'Unknown',
                    'tickets' => $r->count,
                    'avg_wait_min' => round(($r->wait ?: 0) / 60, 1)
                ];
            })->toArray();
    }

    private function getStaffPerformance($query): array
    {
        return $query->selectRaw('assign_staff_id, COUNT(*) as served, AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as handle')
            ->whereNotNull('assign_staff_id')->where('status', 'Close')->groupBy('assign_staff_id')->get()->map(function($r) {
                $user = \App\Models\User::find($r->assign_staff_id);
                return [
                    'agent_name' => $user->name ?? 'Unknown Agent',
                    'tickets_served' => $r->served,
                    'avg_handle_min' => round(($r->handle ?: 0) / 60, 1)
                ];
            })->toArray();
    }
}
