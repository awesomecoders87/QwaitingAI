<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Models\Category;
use App\Models\SiteDetail;
use App\Models\AccountSetting;
use App\Services\OpenAIService;

class BookingQueryTool extends Tool
{
    protected string $description = 'Answers any booking-related query: counts, breakdowns, trends, available dates, available time slots, cancellations, status summaries, service comparisons, upcoming bookings, and more.';

    // ─── MCP Interface ────────────────────────────────────────────

    public function handle(Request $request): Response
    {
        $teamId     = (int) $request->string('team_id');
        $locationId = (int) $request->string('location_id');
        $query      = $request->string('query');
        $result     = $this->query($query, $teamId, $locationId, []);
        return Response::text(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query'       => $schema->string()->description('Natural language booking question from the user.')->required(),
            'team_id'     => $schema->string()->description('Tenant/team ID.')->required(),
            'location_id' => $schema->string()->description('Location ID.')->required(),
        ];
    }

    // ─── Public entry-point (called from Livewire directly) ───────

    public function query(string $query, int $teamId, int $locationId, array $history = []): array
    {
        if (!$teamId || !$locationId) {
            return ['error' => 'Context not available (team/location not set). Please refresh the page.'];
        }

        try {
            $requestData = $this->generateDataRequest($query, $teamId, $locationId, $history);
            return $this->executeDynamicQuery($requestData, $teamId, $locationId);
        } catch (\Throwable $e) {
            Log::error('[BookingQueryTool] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['error' => 'Error processing query: ' . $e->getMessage()];
        }
    }

    // ─── Data Request Generation ──────────────────────────────────

    /**
     * Instructs AI to act as a SQL query builder
     */
    private function generateDataRequest(string $query, int $teamId, int $locationId, array $history = []): array
    {
        $services = $this->getAllServices($teamId, $locationId);
        $serviceList = json_encode($services, JSON_UNESCAPED_UNICODE);
        $today = Carbon::today()->toDateString();
        $thisMonthStart = Carbon::now()->startOfMonth()->toDateString();
        $thisMonthEnd = Carbon::now()->endOfMonth()->toDateString();

        $prompt = <<<PROMPT
You are an expert AI data analyst for a booking system. Today's date is {$today}. The current month started on {$thisMonthStart} and ends on {$thisMonthEnd}.
Convert the user's natural language query into a structured JSON data request.

Actions available:
- 'count': (default) returning total number of bookings or grouped stats.
- 'list': returning a detailed list of individual bookings (names, statuses, times, services). Use this when the user asks for booking statuses, lists, or details about the bookings.
- 'check_slots': checking specific time availability.
- 'check_dates': checking which days are free.

If action is 'check_slots' or 'check_dates', you can stop and just return:
{
  "action": "check_slots",
  "service_id": 12,
  "date": "2023-11-01" 
}

For standard data queries ('count' or 'list'), use this format:
{
  "action": "count",
  "filters": {
    "status": ["Completed", "Cancelled"], // array of exact statuses mentioned (Confirmed, Pending, Completed, Cancelled) or null
    "is_checkin": true, // true ONLY if the user specifically asks for "check-in" or checked-in bookings, else null
    "date_from": "YYYY-MM-DD", // start date if mentioned
    "date_to": "YYYY-MM-DD",   // end date if mentioned
    "service_id": 12 // Map the mentioned service to its exact ID from the list below, or null
  },
  "group_by": "<'status', 'date', 'service', or null>"
}

For example, "Compare cancelled vs pending bookings for standard clean last week"
filters: status: ["Cancelled", "Pending"], date_from/to: (last week's dates), group_by: 'status'

Available Services in system:
{$serviceList}

Return ONLY a valid JSON object. No markdown, no extra text.
PROMPT;

        try {
            $messages = $history;
            $messages[] = ['role' => 'user', 'content' => $query];
            
            $openai = new OpenAIService();
            $response = $openai->generateResponse($messages, $prompt);
            
            $response = preg_replace('/```json\s*/', '', $response);
            $response = preg_replace('/```\s*/', '', $response);
            
            $json = json_decode(trim($response), true);
            return is_array($json) ? $json : ['action' => 'overview'];
        } catch (\Throwable $e) {
            Log::error('[BookingQueryTool] Request generation failed: ' . $e->getMessage());
            return ['action' => 'overview'];
        }
    }

    // ─── Dynamic Query Execution ──────────────────────────────────

    private function executeDynamicQuery(array $requestData, int $teamId, int $locationId): array
    {
        $action = $requestData['action'] ?? 'overview';

        // Direct pass-throughs for complex business logic routines
        if ($action === 'check_slots') {
            $serviceId = $requestData['service_id'] ?? null;
            $date = $requestData['date'] ?? Carbon::today()->toDateString();
            if (!$serviceId) return ['error' => 'Please specify a service to check slots.'];
            
            $service = Category::find((int)$serviceId);
            $slots = $this->computeAvailableSlots($teamId, $locationId, $serviceId, $date);
            return [
                'action' => 'check_slots',
                'service' => optional($service)->name,
                'date' => $date,
                'available_slots' => $slots,
                'total' => count($slots)
            ];
        }

        if ($action === 'check_dates') {
            $serviceId = $requestData['service_id'] ?? null;
            if (!$serviceId) return ['error' => 'Please specify a service to check dates.'];
            
            $service = Category::find((int)$serviceId);
            $dates = $this->computeAvailableDates($teamId, $locationId, $serviceId);
            return [
                'action' => 'check_dates',
                'service' => optional($service)->name,
                'available_dates' => $dates,
                'total' => count($dates)
            ];
        }

        // Standard dynamic Eloquent query
        $query = Booking::where('team_id', $teamId)->where('location_id', $locationId);
        $filters = $requestData['filters'] ?? [];

        // Apply filters
        if (!empty($filters['status']) && is_array($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }
        if (!empty($filters['is_checkin'])) {
            $query->where('is_convert', Booking::STATUS_YES);
        }
        if (!empty($filters['service_id'])) {
            $query->where('category_id', $filters['service_id']);
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
            // Default window
            $monthStart = Carbon::now()->startOfMonth()->toDateString();
            $monthEnd = Carbon::now()->endOfMonth()->toDateString();
            $query->whereBetween('booking_date', [$monthStart, $monthEnd]);
            $filters['date_from'] = $monthStart;
            $filters['date_to'] = $monthEnd;
        }

        // Action execution
        $result = [
            'action' => $action,
            'filters_applied' => $filters,
        ];
        $groupBy = $requestData['group_by'] ?? null;

        if ($action === 'list') {
            $records = (clone $query)->orderByDesc('booking_date')->limit(20)->get()
                ->map(fn($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'service' => optional(Category::find($b->category_id))->name ?? 'Unknown',
                    'date' => $b->booking_date,
                    'start_time' => $b->start_time,
                    'end_time' => $b->end_time,
                    'status' => $b->status,
                ])->toArray();

            $result['records'] = $records;
            $result['total'] = $query->count();
            
            if ($result['total'] > 20) {
                $result['notice'] = 'Note: Only showing the 20 most recent records due to limit. Ask the user to refine their filters if they need specific ones.';
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
        } elseif ($groupBy === 'date') {
            $rows = (clone $query)->selectRaw('booking_date, COUNT(*) as count')
                ->groupBy('booking_date')
                ->orderBy('booking_date')
                ->pluck('count', 'booking_date')
                ->toArray();
            $result['grouped_data'] = $rows;
            $result['total'] = array_sum($rows);
        } else {
            // Overview or raw count
            $result['total'] = $query->count();

            // Provide a quick status breakdown if not grouped to give the chatbot better data richness
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

        return $result;
    }

    // ─── Slot / Date Computation ─────────────────────────────────

    private function computeAvailableDates(int $teamId, int $locationId, int $serviceId): array
    {
        $siteSetting    = SiteDetail::where('team_id', $teamId)->where('location_id', $locationId)->first();
        $bookingSetting = AccountSetting::where('team_id', $teamId)->where('location_id', $locationId)->where('slot_type', AccountSetting::BOOKING_SLOT)->first();

        if (!$siteSetting || !$bookingSetting) return [];

        $advanceDays    = is_numeric($bookingSetting->allow_req_before ?? 30) ? (int) $bookingSetting->allow_req_before : 30;
        $availableDates = [];
        $end            = Carbon::today()->addDays($advanceDays);

        for ($date = Carbon::today(); $date->lte($end); $date->addDay()) {
            if ($this->isServiceClosedOnDate($teamId, $locationId, $serviceId, $date->toDateString())) {
                continue;
            }
            $slots = AccountSetting::checktimeslot($teamId, $locationId, $date->copy(), $serviceId, $siteSetting);
            $avail = $slots['start_at'] ?? [];
            if ($date->isToday()) $avail = $this->filterPastSlots($avail, $siteSetting);
            if (!empty($avail)) $availableDates[] = $date->toDateString();
        }

        return $availableDates;
    }

    private function isServiceClosedOnDate(int $teamId, int $locationId, int $serviceId, string $date): bool
    {
        $carbonDate = Carbon::parse($date);
        $currentDay = $carbonDate->format('l');

        $customSlot = \App\Models\CustomSlot::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', 'category')
            ->where('category_id', $serviceId)
            ->whereDate('selected_date', $carbonDate->toDateString())
            ->first();

        $slotData = $customSlot;

        if (!$slotData) {
            $slotData = \App\Models\AccountSetting::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->where('slot_type', 'category')
                ->where('category_id', $serviceId)
                ->first();
        }

        if ($slotData && !empty($slotData->business_hours)) {
            $businessHours = json_decode($slotData->business_hours, true);
            $todayConfig = collect($businessHours)->firstWhere('day', $currentDay);
            
            if (!$todayConfig || $todayConfig['is_closed'] !== 'open') {
                return true; 
            }
        }

        return false;
    }

    private function computeAvailableSlots(int $teamId, int $locationId, int $serviceId, string $date): array
    {
        if ($this->isServiceClosedOnDate($teamId, $locationId, $serviceId, $date)) return [];

        $siteSetting = SiteDetail::where('team_id', $teamId)->where('location_id', $locationId)->first();
        if (!$siteSetting) return [];

        $slots = AccountSetting::checktimeslot($teamId, $locationId, Carbon::parse($date), $serviceId, $siteSetting);
        $avail = $slots['start_at'] ?? [];

        if (Carbon::parse($date)->isToday()) {
            $avail = $this->filterPastSlots($avail, $siteSetting);
        }

        return $avail;
    }

    private function filterPastSlots(array $slots, $siteSetting): array
    {
        $tz  = $siteSetting->select_timezone ?? 'UTC';
        $now = Carbon::now($tz);

        return array_values(array_filter($slots, function ($slot) use ($now, $tz) {
            try {
                $parts    = explode('-', $slot);
                $slotTime = Carbon::parse(trim($parts[0]), $tz);
                return $slotTime->gt($now);
            } catch (\Throwable) {
                return true;
            }
        }));
    }

    // ─── Date Parsing ────────────────────────────────────────────

    public function extractDateRange(string $query): array
    {
        $q = strtolower($query);

        foreach ([
            '/between\s+(.+?)\s+and\s+(.+?)(?:\s|$)/i',
            '/from\s+(.+?)\s+to\s+(.+?)(?:\s|$)/i',
            '/from\s+(.+?)\s+(?:through|till|until)\s+(.+?)(?:\s|$)/i',
        ] as $pattern) {
            if (preg_match($pattern, $q, $m)) {
                $f = $this->tryParseDate(trim($m[1]));
                $t = $this->tryParseDate(trim($m[2]));
                if ($f && $t) return [$f->toDateString(), $t->toDateString()];
            }
        }

        foreach (['january','february','march','april','may','june','july','august','september','october','november','december'] as $month) {
            if (str_contains($q, $month)) {
                preg_match('/\b(20\d{2})\b/', $query, $yr);
                $year = $yr[1] ?? now()->year;
                $dt   = Carbon::createFromFormat('F Y', ucfirst($month) . ' ' . $year);
                return [$dt->copy()->startOfMonth()->toDateString(), $dt->copy()->endOfMonth()->toDateString()];
            }
        }

        $relative = [
            'this month'  => [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()],
            'last month'  => [Carbon::now()->subMonth()->startOfMonth()->toDateString(), Carbon::now()->subMonth()->endOfMonth()->toDateString()],
            'this week'   => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
            'last week'   => [Carbon::now()->subWeek()->startOfWeek()->toDateString(), Carbon::now()->subWeek()->endOfWeek()->toDateString()],
            'last 7 days' => [Carbon::now()->subDays(6)->toDateString(), Carbon::now()->toDateString()],
            'last 30 days'=> [Carbon::now()->subDays(29)->toDateString(), Carbon::now()->toDateString()],
            'last 90 days'=> [Carbon::now()->subDays(89)->toDateString(), Carbon::now()->toDateString()],
            'today'       => [Carbon::today()->toDateString(), Carbon::today()->toDateString()],
            'yesterday'   => [Carbon::yesterday()->toDateString(), Carbon::yesterday()->toDateString()],
        ];
        foreach ($relative as $key => [$f, $t]) {
            if (str_contains($q, $key)) return [$f, $t];
        }

        return [null, null];
    }

    public function extractSingleDate(string $query): ?string
    {
        $q = strtolower($query);
        if (str_contains($q, 'today'))     return Carbon::today()->toDateString();
        if (str_contains($q, 'tomorrow'))  return Carbon::tomorrow()->toDateString();
        if (str_contains($q, 'yesterday')) return Carbon::yesterday()->toDateString();

        if (preg_match('/\b(\d{4}-\d{2}-\d{2})\b/', $query, $m)) return Carbon::parse($m[1])->toDateString();
        if (preg_match('/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})\b/', $query, $m)) return Carbon::parse($m[1])->toDateString();

        if (preg_match('/(?:on|for|at)\s+(.+?)(?:\s+for|\s+service|$)/i', $query, $m)) {
            $d = $this->tryParseDate(trim($m[1]));
            if ($d) return $d->toDateString();
        }

        return null;
    }

    private function tryParseDate(string $text): ?Carbon
    {
        $text = trim($text);
        if (empty($text)) return null;
        try {
            $dt = Carbon::parse($text);
            return $dt->year > 2000 ? $dt : null;
        } catch (\Throwable) {
            return null;
        }
    }

    // ─── Service Matching ────────────────────────────────────────

    public function matchService(string $query, int $teamId, int $locationId): ?Category
    {
        $services   = $this->getServiceModels($teamId, $locationId);
        $queryLower = strtolower($query);
        $matched    = null;
        $matchLen   = 0;

        foreach ($services as $service) {
            foreach ([$service->name, $service->other_name ?? ''] as $name) {
                $n = strtolower($name);
                if ($n && str_contains($queryLower, $n) && strlen($n) > $matchLen) {
                    $matched  = $service;
                    $matchLen = strlen($n);
                }
            }
        }

        return $matched;
    }

    private function getServiceModels(int $teamId, int $locationId)
    {
        return Category::where('team_id', $teamId)
            ->where(function ($q) { $q->whereNull('parent_id')->orWhere('parent_id', ''); })
            ->whereJsonContains('category_locations', (string) $locationId)
            ->get();
    }

    public function getAllServices(int $teamId, int $locationId): array
    {
        return $this->getServiceModels($teamId, $locationId)
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
            ->values()->toArray();
    }
}
