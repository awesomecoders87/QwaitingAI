<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Category;

class AnalyzeBookingsTool implements Tool
{
    public function __construct(
        protected int $teamId,
        protected int $locationId
    ) {}

    public function description(): Stringable|string
    {
        return 'Analyzes booking data: gets total counts, breakdowns by status/service/date, trends, and fetches lists of bookings based on filters.';
    }

    public function schema(JsonSchema $schema): array
    {
        $validStatuses = implode(', ', array_keys(Booking::getStatus()));
        
        return [
            'action' => $schema->string()->description("Action to perform: 'count' (for numbers/grouping) or 'list' (for a list of booking objects). Defaults to 'count'."),
            'status' => $schema->string()->description("Exact status to filter by (Allowed: {$validStatuses}). Null if no status specified.")->nullable(),
            'is_checkin' => $schema->boolean()->description('True ONLY if user explicitly asks for check-in or checked-in bookings.')->nullable(),
            'date_from' => $schema->string()->description('Start date exactly in YYYY-MM-DD format')->nullable(),
            'date_to' => $schema->string()->description('End date exactly in YYYY-MM-DD format')->nullable(),
            'service_name' => $schema->string()->description('The exact or partial name of the service/category requested, if any.')->nullable(),
            'group_by' => $schema->string()->description("Field to group the results by. Allowed values: 'status', 'date', 'month', 'week', 'year', 'service'. Null if no grouping is requested.")->nullable()
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        // \Illuminate\Support\Facades\Log::info('[AnalyzeBookingsTool] Request received', ['args' => (array) $request]);
        try {
            $action = $request['action'] ?? 'count';
            $reqFilters = is_array($request['filters'] ?? null) ? $request['filters'] : [];
            $filters = [
                'status' => !empty($request['status']) ? [$request['status']] : (!empty($reqFilters['status']) ? [$reqFilters['status']] : null),
                'is_checkin' => $request['is_checkin'] ?? ($reqFilters['is_checkin'] ?? null),
                'date_from' => $request['date_from'] ?? ($reqFilters['date_from'] ?? null),
                'date_to' => $request['date_to'] ?? ($reqFilters['date_to'] ?? null),
                'service_name' => $request['service_name'] ?? ($reqFilters['service_name'] ?? null),
            ];
            $groupBy = $request['group_by'] ?? null;

            \Illuminate\Support\Facades\Log::info('[AnalyzeBookingsTool] Parsed filters', ['action' => $action, 'filters' => $filters, 'group_by' => $groupBy]);

            $query = Booking::where('team_id', $this->teamId)->where('location_id', $this->locationId);

            // 1. Resolve Service ID if name is provided
            if (!empty($filters['service_name'])) {
                $category = Category::where('team_id', $this->teamId)
                    ->where(function ($q) { $q->whereNull('parent_id')->orWhere('parent_id', ''); })
                    ->whereJsonContains('category_locations', (string) $this->locationId)
                    ->where('name', 'like', '%' . $filters['service_name'] . '%')
                    ->first();
                    
                if ($category) {
                    $query->where('category_id', $category->id);
                } else {
                    return json_encode(['error' => "Could not find a service matching '{$filters['service_name']}'."]);
                }
            }

            // 2. Apply Filters
            if (!empty($filters['status']) && is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            }
            if (!empty($filters['is_checkin'])) {
                $query->where('is_convert', Booking::STATUS_YES);
            }
            
            $from = $filters['date_from'] ?? null;
            $to = $filters['date_to'] ?? null;
            
            if ($from && $to) {
                $query->whereBetween('booking_date', [$from, $to]);
            } elseif ($from) {
                $query->where('booking_date', '>=', $from);
            } elseif ($to) {
                $query->where('booking_date', '<=', $to);
            } else {
                // Default window: this month
                $monthStart = Carbon::now()->startOfMonth()->toDateString();
                $monthEnd = Carbon::now()->endOfMonth()->toDateString();
                $query->whereBetween('booking_date', [$monthStart, $monthEnd]);
            }

            $result = [
                'action' => $action,
                'filters_applied' => $filters,
            ];

            // 3. Execution
            if ($action === 'list') {
                $records = (clone $query)->orderByDesc('booking_date')->limit(20)->get()
                    ->map(fn($b) => [
                        'id' => $b->id,
                        'name' => $b->name,
                        'service' => optional(Category::find($b->category_id))->name ?? 'Unknown',
                        'date' => $b->booking_date,
                        'start_time' => $b->start_time,
                        'status' => $b->status,
                    ])->toArray();

                $result['records'] = $records;
                $result['total'] = $query->count();
                if ($result['total'] > 20) {
                    $result['notice'] = 'Note: Only showing the 20 most recent records due to limit.';
                }
            } elseif ($groupBy === 'service') {
                $rows = (clone $query)->selectRaw('category_id, COUNT(*) as count')
                    ->groupBy('category_id')
                    ->orderByDesc('count')
                    ->get()
                    ->map(fn($r) => [
                        'service' => optional(Category::find($r->category_id))->name ?? 'Unknown',
                        'count' => $r->count
                    ])->values()->toArray();
                
                $result['grouped_data'] = $rows;
                $result['total'] = array_sum(array_column($rows, 'count'));
            } elseif ($groupBy === 'status') {
                $rows = (clone $query)->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
                $result['grouped_data'] = $rows;
                $result['total'] = array_sum($rows);
            } elseif (in_array($groupBy, ['date', 'month', 'week', 'year'])) {
                $formatString = '%Y-%m-%d';
                if ($groupBy === 'month') $formatString = '%Y-%m';
                elseif ($groupBy === 'year') $formatString = '%Y';

                if ($groupBy === 'week') {
                    $rows = (clone $query)->selectRaw('YEAR(booking_date) as yr, WEEK(booking_date, 1) as wk, status, COUNT(*) as count')
                        ->groupBy('yr', 'wk', 'status')
                        ->get()
                        ->map(function ($row) {
                            return (object)[
                                'period' => $row->yr . '-W' . str_pad($row->wk, 2, '0', STR_PAD_LEFT),
                                'status' => $row->status,
                                'count' => $row->count,
                            ];
                        });
                } else {
                    $rows = (clone $query)->selectRaw("DATE_FORMAT(booking_date, ?) as period, status, COUNT(*) as count", [$formatString])
                        ->groupBy('period', 'status')
                        ->get();
                }

                $grouped = [];
                $totalCount = 0;
                foreach ($rows as $row) {
                    $key = $row->period;
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = ['total' => 0, 'by_status' => []];
                    }
                    $grouped[$key]['total'] += $row->count;
                    $grouped[$key]['by_status'][$row->status] = ($grouped[$key]['by_status'][$row->status] ?? 0) + $row->count;
                    $totalCount += $row->count;
                }
                ksort($grouped);
                $result['grouped_data'] = $grouped;
                $result['total'] = $totalCount;
            } else {
                $result['total'] = $query->count();
                if (!isset($filters['status'])) {
                    $result['breakdown'] = (clone $query)->selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray();
                }
            }

            if (empty($from) && empty($to)) {
                 $result['notice'] = 'Note: Filtered to the current month by default as no date was specified.';
            }

            \Illuminate\Support\Facades\Log::info('[AnalyzeBookingsTool] Query executing successfully', ['total_found' => $result['total'] ?? 0]);
            
            return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[AnalyzeBookingsTool] Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return json_encode(['error' => 'An error occurred while analyzing bookings.']);
        }
    }
}
