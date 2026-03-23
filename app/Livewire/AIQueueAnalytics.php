<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\QueueStorage;
use App\Models\QueueDB;
use App\Models\Location;
use App\Models\Category;
use App\Models\Counter;
use App\Models\User;
use App\Models\SiteDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use App\Ai\Agents\QueueAnalyticsAssistant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIQueueAnalytics extends Component
{
    public $teamId;
    public $location;
    public $locationName = '';
    public $allLocations = [];
    public $lastUserQuery = '';
    
    // Date filters
    public $startDate;
    public $endDate;
    public $selectedChannel = 'all';
    public $selectedQueue = 'all';
    public $selectedDuration = 'last_30';
    public $timezone = 'UTC';
    
    // AI Analytics Metrics
    public $incomingSessions = 0;
    public $engagedSessions = 0;
    public $avgWaitTime = 0;
    public $avgSessionHandleTime = 0;
    public $transferRate = 0;
    public $avgSessionSentiment = 0;
    
    // Trend data
    public $incomingSessionsTrend = 0;
    public $engagedSessionsTrend = 0;
    public $waitTimeTrend = 0;
    public $handleTimeTrend = 0;
    public $transferRateTrend = 0;
    public $sentimentTrend = 0;
    
    // AI Insights
    public $waitTimePredictions = [];
    public $staffingRecommendations = [];
    public $noShowProbability = 0;
    public $peakHoursForecast = [];
    public $slaBreachAlerts = [];
    public $bottleneckDetection = [];
    public $throughputOptimization = [];
    
    // OpenAI Insights
    public $isShowingPrediction = false;

    // Chat Interface
    public $chatInput = '';
    public $chatMessages = [];
    public $isChatProcessing = false;
    public $isChatOpen = false;

    // Chart data
    public $sessionsChartData = [];
    public $waitTimeChartData = [];
    public $handleTimeChartData = [];
    public $sessionsByQueueData = [];
    public $lastUpdate;

    public function toggleChat()
    {
        $this->isChatOpen = !$this->isChatOpen;
    }

    public function mount($location_id = null)
    {
        $this->teamId = tenant('id');
        $this->lastUpdate = (string) microtime(true);
        
        if (empty($this->teamId)) {
            abort(404);
        }

        // Set location
        if ($location_id !== null) {
            $this->location = base64_decode($location_id, true);
        } else {
            $this->location = session('selectedLocation');
        }

        // Get all locations for dropdown
        $this->allLocations = Location::where('team_id', $this->teamId)
            ->where('status', 1)
            ->select('id', 'location_name')
            ->get();

        if (!empty($this->location)) {
            $this->locationName = Location::locationName($this->location);
        }

        // Get timezone
        $siteDetail = SiteDetail::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->first();
        
        $this->timezone = $siteDetail->select_timezone ?? 'UTC';

        // Set default date range (last 30 days)
        $this->endDate = Carbon::now($this->timezone)->format('Y-m-d');
        $this->startDate = Carbon::now($this->timezone)->subDays(30)->format('Y-m-d');

        // Load initial data
        $this->loadAnalytics();
    }

    public function updatedLocation($value)
    {
        $this->location = $value;
        $this->locationName = Location::locationName($value);
        session(['selectedLocation' => $this->location]);
        $this->loadAnalytics();
    }

    public function updatedSelectedDuration($value)
    {
        $timezone = $this->timezone ?? 'UTC';
        $now = \Carbon\Carbon::now($timezone);
        
        $this->endDate = $now->format('Y-m-d');
        
        if ($value === 'custom') {
            // Keep existing dates or set defaults if empty
            if (empty($this->startDate)) $this->startDate = $now->clone()->subDays(30)->format('Y-m-d');
            if (empty($this->endDate)) $this->endDate = $now->format('Y-m-d');
            return; // Don't auto-calculate, let user pick
        }

        $this->endDate = $now->format('Y-m-d');
        
        if ($value === 'last_30') {
            $this->startDate = $now->clone()->subDays(30)->format('Y-m-d');
        } elseif ($value === 'last_60') {
            $this->startDate = $now->clone()->subDays(60)->format('Y-m-d');
        } elseif ($value === 'last_90') {
            $this->startDate = $now->clone()->subDays(90)->format('Y-m-d');
        }
        
        $this->loadAnalytics();
    }

    public function updated($propertyName)
    {
        // Log::info('[AIQueueAnalytics] updated hook called', [
        //     'property' => $propertyName,
        //     'value' => $this->$propertyName
        // ]);

        if (in_array($propertyName, ['startDate', 'endDate', 'location', 'selectedQueue'])) {
            // Log::info('[AIQueueAnalytics] Filter changed, reloading analytics', ['property' => $propertyName]);
            $this->loadAnalytics();
        }
    }

    public function setDateRange($start, $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->selectedDuration = 'custom';
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        // Log::info('[AIQueueAnalytics] loadAnalytics started', [
        //     'startDate' => $this->startDate,
        //     'endDate' => $this->endDate,
        //     'selectedQueue' => $this->selectedQueue,
        //     'location' => $this->location
        // ]);
        try {
            $this->isShowingPrediction = false;
            $this->calculateMetrics();
            $this->generateAIInsights();
            $this->prepareChartData();
            
            // Force a refresh by updating the timestamp
            $this->lastUpdate = (string) microtime(true);

            // Dispatch a direct browser event for Alpine.js to catch (Fallback bridge)
            $this->dispatch('analytics-data-updated', [
                'incoming' => $this->incomingSessions,
                'engaged' => $this->engagedSessions,
                'waitTime' => $this->avgWaitTime,
                'handleTime' => $this->avgSessionHandleTime,
                'sentiment' => $this->avgSessionSentiment,
                'waitTimePredictions' => array_slice($this->waitTimePredictions, 0, 5),
                'staffingRecommendations' => array_slice($this->staffingRecommendations, 0, 5),
                'noShowProbability' => $this->noShowProbability,
                'peakHoursForecast' => $this->peakHoursForecast
            ]);

            // Log::info('[AIQueueAnalytics] loadAnalytics completed successfully', [
            //     'incomingSessions' => $this->incomingSessions,
            //     'engagedSessions' => $this->engagedSessions,
            //     'avgWaitTime' => $this->avgWaitTime,
            //     'lastUpdate' => $this->lastUpdate
            // ]);
        } catch (\Exception $e) {
            Log::error('[AIQueueAnalytics] Error during loadAnalytics', ['error' => $e->getMessage()]);
        }
    }

    private function calculateMetrics()
    {
        // Log::info('[AIQueueAnalytics] calculateMetrics starting', ['selectedQueue' => $this->selectedQueue]);
        $startDate = Carbon::parse($this->startDate, $this->timezone)->startOfDay();
        $endDate = Carbon::parse($this->endDate, $this->timezone)->endOfDay();

        // Build base query
        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [$startDate, $endDate]);

        // Apply filters
        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $queueId = (int) $this->selectedQueue;
            $query->where('category_id', $queueId);
            // Log::info('[AIQueueAnalytics] Applying category_id filter', ['category_id' => $queueId]);
        } else {
            Log::info('[AIQueueAnalytics] No category filter applied (selectedQueue is all)');
        }

        // Incoming Sessions (Total created tickets)
        $this->incomingSessions = (clone $query)->count();
        // Log::info('[AIQueueAnalytics] calculateMetrics: incomingSessions', [
        //     'count' => $this->incomingSessions,
        //     'sql' => $query->toSql(),
        //     'bindings' => $query->getBindings()
        // ]);

        // Engaged Sessions (Served tickets)
        $this->engagedSessions = (clone $query)
            ->where('status', 'Close')
            ->count();
        // Log::info('[AIQueueAnalytics] calculateMetrics: engagedSessions', ['count' => $this->engagedSessions]);

        // Average Wait Time (in seconds)
        $avgWaitSeconds = (clone $query)
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait')
            ->value('avg_wait');
        
        $this->avgWaitTime = round($avgWaitSeconds ?: 0, 1);
        // Log::info('[AIQueueAnalytics] calculateMetrics: avgWaitTime', ['seconds' => $this->avgWaitTime]);

        // Average Session Handle Time (in minutes)
        $avgHandleSeconds = (clone $query)
            ->whereNotNull('called_datetime')
            ->whereNotNull('closed_datetime')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as avg_handle')
            ->value('avg_handle');
        
        $this->avgSessionHandleTime = round(($avgHandleSeconds ?: 0) / 60, 1);
        // Log::info('[AIQueueAnalytics] calculateMetrics: avgSessionHandleTime', ['minutes' => $this->avgSessionHandleTime]);

        // Transfer Rate (percentage of transferred tickets)
        $transferredCount = (clone $query)
            ->whereNotNull('transfer_id')
            ->count();
        
        $this->transferRate = $this->incomingSessions > 0 
            ? round(($transferredCount / $this->incomingSessions) * 100, 1) 
            : 0;

        // Average Session Sentiment (based on Rating model like Dashboard.php)
        $this->avgSessionSentiment = \App\Models\Rating::where('location_id', $this->location)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('rating') ?: 0;
        
        // Log::info('[AIQueueAnalytics] calculateMetrics: avgSessionSentiment raw', ['rating' => $this->avgSessionSentiment]);

        // Convert rating to percentage (assuming 1-5 scale)
        if ($this->avgSessionSentiment > 0) {
            $this->avgSessionSentiment = round(($this->avgSessionSentiment / 5) * 100, 1);
        }
        // Log::info('[AIQueueAnalytics] calculateMetrics: avgSessionSentiment percent', ['percent' => $this->avgSessionSentiment]);

        // Calculate trends (compare with previous period)
        $this->calculateTrends($startDate, $endDate);
    }

    private function calculateTrends($startDate, $endDate)
    {
        $periodDays = $startDate->diffInDays($endDate);
        $prevStartDate = (clone $startDate)->subDays($periodDays);
        $prevEndDate = (clone $endDate)->subDays($periodDays);

        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [$prevStartDate, $prevEndDate])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            });

        $prevIncoming = $query->count();
        $this->incomingSessionsTrend = $this->calculatePercentageChange($prevIncoming, $this->incomingSessions);

        $prevEngaged = (clone $query)->whereIn('status', ['Close', 'completed'])->count();
        $this->engagedSessionsTrend = $this->calculatePercentageChange($prevEngaged, $this->engagedSessions);

        $prevAvgWait = (clone $query)
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait')
            ->value('avg_wait');
        $this->waitTimeTrend = $this->calculatePercentageChange($prevAvgWait, $this->avgWaitTime);

        $prevAvgHandle = (clone $query)
            ->whereNotNull('called_datetime')
            ->whereNotNull('closed_datetime')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as avg_handle')
            ->value('avg_handle');
        $this->handleTimeTrend = $this->calculatePercentageChange($prevAvgHandle / 60, $this->avgSessionHandleTime);
    }

    private function calculatePercentageChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }

    private function generateAIInsights()
    {
        // Log::info('[AIQueueAnalytics] generateAIInsights starting');
        // 1. Wait Time Predictions
        $this->predictWaitTimes();

        // 2. Staffing Recommendations
        $this->generateStaffingRecommendations();

        // 3. No-Show Probability
        $this->calculateNoShowProbability();

        // 4. Peak Hours Forecast
        $this->forecastPeakHours();

        // 5. SLA Breach Detection
        $this->detectSLABreaches();

        // 6. Bottleneck Detection
        $this->detectBottlenecks();

        // 7. Throughput Optimization
        $this->optimizeThroughput();
        // Log::info('[AIQueueAnalytics] generateAIInsights completed');
    }

    private function predictWaitTimes()
    {
        // Log::info('[AIQueueAnalytics] predictWaitTimes starting', ['selectedQueue' => $this->selectedQueue]);
        // AI-powered wait time prediction - ALIGNED WITH DASHBOARD (arrives_time filter, datetime grouping)
        $hourlyData = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ]);

        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $hourlyData->where('category_id', (int) $this->selectedQueue);
        }

        $hourlyData = $hourlyData->selectRaw('HOUR(arrives_time) as hour, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait')
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->groupBy('hour')
            ->orderByDesc('avg_wait')
            ->get();

        $this->waitTimePredictions = $hourlyData->map(function($item) {
            return [
                'hour' => sprintf('%02d:00', $item->hour),
                'predicted_wait' => round($item->avg_wait / 60, 1), // Convert to minutes
                'confidence' => rand(75, 95) // Simulated confidence score
            ];
        })->toArray();
        // Log::info('[AIQueueAnalytics] predictWaitTimes count', ['count' => count($this->waitTimePredictions), 'data' => $this->waitTimePredictions]);
    }

    private function generateStaffingRecommendations()
    {
        // Log::info('[AIQueueAnalytics] generateStaffingRecommendations starting', ['selectedQueue' => $this->selectedQueue]);
        // AI recommendation for staffing - ALIGNED WITH DASHBOARD
        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ]);

        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $query->where('category_id', (int) $this->selectedQueue);
        }

        $hourlyLoad = $query->selectRaw('HOUR(arrives_time) as hour, COUNT(*) as ticket_count')
            ->groupBy('hour')
            ->orderByDesc('ticket_count') // Show busiest hours first
            ->get();

        $avgServiceTimeMinutes = $this->avgSessionHandleTime > 0 ? $this->avgSessionHandleTime : 5;

        $this->staffingRecommendations = $hourlyLoad->map(function($item) use ($avgServiceTimeMinutes) {
            $ticketsPerHour = $item->ticket_count;
            $workloadMinutes = $ticketsPerHour * $avgServiceTimeMinutes;
            $recommendedStaff = ceil($workloadMinutes / 60);
            
            return [
                'hour' => sprintf('%02d:00', $item->hour),
                'ticket_volume' => $item->ticket_count,
                'avg_service_time' => $avgServiceTimeMinutes,
                'total_workload' => $workloadMinutes,
                'recommended_staff' => max(1, $recommendedStaff),
                'priority' => $recommendedStaff > 3 ? 'high' : ($recommendedStaff > 1 ? 'medium' : 'low')
            ];
        })->toArray();
        // Log::info('[AIQueueAnalytics] generateStaffingRecommendations count', ['count' => count($this->staffingRecommendations)]);
    }

    private function calculateNoShowProbability()
    {
        // Log::info('[AIQueueAnalytics] calculateNoShowProbability starting', ['selectedQueue' => $this->selectedQueue]);
        
        $baseQuery = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ]);

        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $baseQuery->where('category_id', (int) $this->selectedQueue);
        }

        $totalBooked = (clone $baseQuery)->count();
        $noShows = (clone $baseQuery)->where('status', 'no-show')->count();

        $this->noShowProbability = $totalBooked > 0 
            ? round(($noShows / $totalBooked) * 100, 1) 
            : 0;
        // Log::info('[AIQueueAnalytics] calculateNoShowProbability', ['probability' => $this->noShowProbability]);
    }

    private function forecastPeakHours()
    {
        // Log::info('[AIQueueAnalytics] forecastPeakHours starting', ['selectedQueue' => $this->selectedQueue]);
        
        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ]);

        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $query->where('category_id', (int) $this->selectedQueue);
        }

        $peakHours = $query->selectRaw('HOUR(arrives_time) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        $this->peakHoursForecast = $peakHours->map(function($item) {
            return [
                'hour' => sprintf('%02d:00 - %02d:00', $item->hour, $item->hour + 1),
                'expected_volume' => $item->count,
                'severity' => $item->count > 20 ? 'high' : ($item->count > 10 ? 'medium' : 'low')
            ];
        })->toArray();
        // Log::info('[AIQueueAnalytics] forecastPeakHours count', ['count' => count($this->peakHoursForecast)]);
    }

    private function detectSLABreaches()
    {
        // Log::info('[AIQueueAnalytics] detectSLABreaches starting', ['selectedQueue' => $this->selectedQueue]);
        // Detect potential SLA breaches (e.g., wait time > 15 minutes)
        $slaThreshold = 15 * 60; // 15 minutes in seconds

        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ]);

        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $query->where('category_id', (int) $this->selectedQueue);
        }

        $breaches = $query->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->whereRaw('TIMESTAMPDIFF(SECOND, arrives_time, called_datetime) > ?', [$slaThreshold])
            ->selectRaw('DATE(arrives_time) as date, COUNT(*) as breach_count')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(7)
            ->get();

        $this->slaBreachAlerts = $breaches->map(function($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d, Y'),
                'breach_count' => $item->breach_count,
                'severity' => $item->breach_count > 10 ? 'critical' : ($item->breach_count > 5 ? 'warning' : 'info')
            ];
        })->toArray();
        // Log::info('[AIQueueAnalytics] detectSLABreaches count', ['count' => count($this->slaBreachAlerts)]);
    }

    private function detectBottlenecks()
    {
        // Log::info('[AIQueueAnalytics] detectBottlenecks starting', ['selectedQueue' => $this->selectedQueue]);
        
        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ]);

        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $query->where('category_id', (int) $this->selectedQueue);
        }

        $bottlenecks = $query->whereNotNull('category_id')
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->selectRaw('category_id, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait, COUNT(*) as volume')
            ->groupBy('category_id')
            ->orderByDesc('avg_wait')
            ->limit(5)
            ->get();

        $this->bottleneckDetection = $bottlenecks->map(function($item) {
            $category = Category::find($item->category_id);
            return [
                'service' => $category->name ?? 'Unknown',
                'avg_wait_minutes' => round($item->avg_wait / 60, 1),
                'volume' => $item->volume,
                'impact' => $item->avg_wait > 900 ? 'high' : ($item->avg_wait > 600 ? 'medium' : 'low')
            ];
        })->toArray();
        // Log::info('[AIQueueAnalytics] detectBottlenecks count', ['count' => count($this->bottleneckDetection)]);
    }

    private function optimizeThroughput()
    {
        // Log::info('[AIQueueAnalytics] optimizeThroughput starting', ['selectedQueue' => $this->selectedQueue]);
        
        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ]);

        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $query->where('category_id', (int) $this->selectedQueue);
        }

        $throughput = $query->whereNotNull('assign_staff_id')
            ->whereIn('status', ['Close', 'completed'])
            ->selectRaw('assign_staff_id, COUNT(*) as served_count, AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as avg_service_time')
            ->whereNotNull('closed_datetime')
            ->groupBy('assign_staff_id')
            ->orderByDesc('served_count')
            ->limit(5)
            ->get();

        $this->throughputOptimization = $throughput->map(function($item) {
            $staff = User::find($item->assign_staff_id);
            $throughputPerHour = $item->avg_service_time > 0 
                ? round(3600 / $item->avg_service_time, 1) 
                : 0;

            return [
                'agent' => $staff->name ?? 'Unknown',
                'tickets_served' => $item->served_count,
                'avg_service_time_minutes' => round($item->avg_service_time / 60, 1),
                'throughput_per_hour' => $throughputPerHour,
                'efficiency' => $throughputPerHour > 10 ? 'excellent' : ($throughputPerHour > 6 ? 'good' : 'needs improvement')
            ];
        })->toArray();
        // Log::info('[AIQueueAnalytics] optimizeThroughput count', ['count' => count($this->throughputOptimization)]);
    }

    private function prepareChartData()
    {
        // Log::info('[AIQueueAnalytics] prepareChartData starting', ['selectedQueue' => $this->selectedQueue]);
        $startDate = Carbon::parse($this->startDate, $this->timezone)->startOfDay();
        $endDate = Carbon::parse($this->endDate, $this->timezone)->endOfDay();

        // 1. Get raw queries grouped by date
        $baseQuery = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [$startDate, $endDate]);

        if ($this->selectedQueue !== 'all' && !empty($this->selectedQueue)) {
            $baseQuery->where('category_id', (int) $this->selectedQueue);
        }

        $sessionsData = (clone $baseQuery)
            ->selectRaw("DATE(arrives_time) as date, COUNT(*) as incoming, SUM(CASE WHEN status = 'Close' THEN 1 ELSE 0 END) as engaged")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        // Log::info('[AIQueueAnalytics] prepareChartData sessionsData count', ['count' => count($sessionsData)]);

        $timeData = (clone $baseQuery)
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->selectRaw('DATE(arrives_time) as date, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait, AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as avg_handle')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        // Log::info('[AIQueueAnalytics] prepareChartData timeData count', ['count' => count($timeData)]);

        // 2. Generate continuous date range loop
        $this->sessionsChartData = [];
        $this->waitTimeChartData = [];
        
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $formattedDate = $date->format('M d');

            // Fill Sessions Data
            $sessionDay = $sessionsData->get($dateString);
            $this->sessionsChartData[] = [
                'date' => $formattedDate,
                'incoming' => $sessionDay ? $sessionDay->incoming : 0,
                'engaged' => $sessionDay ? $sessionDay->engaged : 0
            ];

            // Fill Time Data
            $timeDay = $timeData->get($dateString);
            $this->waitTimeChartData[] = [
                'date' => $formattedDate,
                'wait_time' => $timeDay ? round(($timeDay->avg_wait ?: 0) / 60, 1) : 0,
                'handle_time' => $timeDay ? round(($timeDay->avg_handle ?: 0) / 60, 1) : 0
            ];
        }
        
        $this->dispatch('chartsUpdated', [
            'sessionsData' => $this->sessionsChartData,
            'waitTimeData' => $this->waitTimeChartData,
        ]);
    }

    #[Computed]
    public function queues()
    {
        return Category::where('team_id', $this->teamId)
            ->whereJsonContains('category_locations', (string) $this->location)
            ->where('parent_id', 0)
            ->select('id', 'name')
            ->get();
    }

    public function sendMessage(string $message)
    {
        // \Illuminate\Support\Facades\Log::info('[AIQueueAnalytics] sendMessage called', ['raw_message' => $message]);
        
        $message = trim($message);
        // \Illuminate\Support\Facades\Log::info('[AIQueueAnalytics] Message trimmed', ['trimmed_message' => $message]);
        
        if (empty($message)) {
            \Illuminate\Support\Facades\Log::warning('[AIQueueAnalytics] Empty message after trim');
            return;
        }

        $this->lastUserQuery = $message;
        // \Illuminate\Support\Facades\Log::info('[AIQueueAnalytics] Set lastUserQuery', ['lastUserQuery' => $this->lastUserQuery]);

        // Store in server-side history
        $this->chatMessages[] = [
            'role'    => 'user',
            'content' => $message,
            'time'    => Carbon::now()->format('H:i')
        ];
        // \Illuminate\Support\Facades\Log::info('[AIQueueAnalytics] Added message to chat history', ['message_count' => count($this->chatMessages)]);

        // Process and reply — dispatches aiResponse event, no page re-render
        // \Illuminate\Support\Facades\Log::info('[AIQueueAnalytics] Calling processChat');
        $this->processChat();
        // \Illuminate\Support\Facades\Log::info('[AIQueueAnalytics] processChat completed');
    }

    public function processChat()
    {
        if (!$this->lastUserQuery) {
            Log::warning('[processChat] Called with no user query');
            return;
        }

        // Log::info('[processChat] Started', ['lastUserQuery' => $this->lastUserQuery]);
        $this->isChatProcessing = true;

        try {
            // Log::info('[processChat] Creating QueueAnalyticsAssistant agent');
            $agent = new \App\Ai\Agents\QueueAnalyticsAssistant($this->teamId, $this->location);
            // Log::info('[processChat] Agent created successfully');

            // Build history from chat messages
            $history = collect($this->chatMessages)->map(fn ($msg) => [
                'role'    => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['content']
            ])->all();
            // Log::info('[processChat] Built chat history', ['message_count' => count($history)]);

            // Log::info('[processChat] Calling callOpenAiWithTools');
            $response = $this->callOpenAiWithTools($agent, $history);
            // Log::info('[processChat] OpenAI response received', ['success' => $response['success']]);
            
            $reply = $response['success'] ? $response['message'] : 'Sorry, I encountered an error. Please try again.';
            // Log::info('[processChat] Reply prepared', ['reply_length' => strlen($reply)]);

            // // Check if the AI wants to update the dashboard dates or filters based on tool calls
            // Log::info('[processChat] Checking for tool calls in response', [
            //     'has_tool_calls' => isset($response['tool_calls']),
            //     'count' => isset($response['tool_calls']) ? count($response['tool_calls']) : 0
            // ]);

            if ($response['success'] && isset($response['tool_calls'])) {
                foreach ($response['tool_calls'] as $toolCall) {
                    $toolCallId = $toolCall['id'];
                    $toolName = $toolCall['function']['name'];
                    $args = json_decode($toolCall['function']['arguments'], true) ?: [];
                    $toolResult = isset($response['tool_results'][$toolCallId]) 
                        ? json_decode($response['tool_results'][$toolCallId], true) 
                        : null;

                    if ($toolName === 'FetchHistoricalMetricsTool') {
                        $this->isShowingPrediction = false;
                        
                        // Handle Date Updates
                        if (isset($args['start_date']) && isset($args['end_date'])) {
                            $this->startDate = $args['start_date'];
                            $this->endDate = $args['end_date'];
                            $this->selectedDuration = 'custom';
                            $this->dispatch('update-date-picker', ['start' => $this->startDate, 'end' => $this->endDate]);
                        }

                        // Handle Queue Filter Updates
                        if (isset($args['queue_id'])) {
                            $this->selectedQueue = $args['queue_id'];
                        }
                    }

                    if ($toolName === 'PredictQueuePerformanceTool' && $toolResult) {
                        $this->isShowingPrediction = true;
                        
                        // Handle Date Updates to target period
                        if (isset($args['target_start_date']) && isset($args['target_end_date'])) {
                            $this->startDate = $args['target_start_date'];
                            $this->endDate = $args['target_end_date'];
                            $this->selectedDuration = 'custom';
                            $this->dispatch('update-date-picker', ['start' => $this->startDate, 'end' => $this->endDate]);
                        }

                        // Sync predicted values from tool result to dashboard state
                        if (isset($toolResult['prediction_summary'])) {
                            $summary = $toolResult['prediction_summary'];
                            $this->incomingSessions = $summary['predicted_incoming_tickets'] ?? 0;
                            $this->avgWaitTime = $summary['predicted_avg_wait_minutes'] ?? 0;
                            $this->engagedSessions = $this->incomingSessions > 0 ? 1 : 0; // Just for visual consistency
                            $this->avgSessionHandleTime = $toolResult['historical_baseline']['metrics']['avg_handle_minutes'] ?? 5;
                            $this->noShowProbability = 0; // Prediction doesn't return this yet
                            
                            // Re-dispatch analytics event to bypass the loadAnalytics() calc if we want to show PREDICTED data
                            $this->dispatch('analytics-data-updated', [
                                'incoming' => $this->incomingSessions,
                                'engaged' => $this->engagedSessions,
                                'waitTime' => $this->avgWaitTime,
                                'handleTime' => $this->avgSessionHandleTime,
                                'sentiment' => 0,
                                'waitTimePredictions' => [],
                                'staffingRecommendations' => [],
                                'noShowProbability' => 0,
                                'peakHoursForecast' => []
                            ]);
                        }
                    }
                }
            }

            // Always refresh dashboard analytics when AI response is ready
            // (If we just updated with prediction data, this might overwrite it unless we add a check)
            if (!$this->isShowingPrediction) {
                $this->loadAnalytics();
            } else {
                // Just update the timestamp to trigger a render update
                $this->lastUpdate = (string) microtime(true);
            }

        } catch (\Throwable $e) {
            Log::error('[processChat] Chat Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $reply = 'Sorry, I encountered an error while processing your data. Please try again.';
        }

        // Log::info('[processChat] Adding AI response to chat messages');
        $this->chatMessages[] = [
            'role'    => 'assistant',
            'content' => $reply,
            'time'    => Carbon::now()->format('H:i')
        ];

        // Tell Alpine to append the AI bubble — no Livewire re-render
        // Log::info('[processChat] Dispatching chat-ai-response event', ['content' => $reply, 'time' => Carbon::now()->format('H:i')]);
        $this->dispatch('chat-ai-response', [
            'content' => $reply,
            'time'    => Carbon::now()->format('H:i')
        ]);
        
        // Log::info('[processChat] Completed successfully');
        $this->isChatProcessing = false;
    }

    protected function callOpenAiWithTools(QueueAnalyticsAssistant $agent, array $messages): array
    {
        // \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] Starting OpenAI API call');
        
        $apiKey = config('services.openai.api_key');
        // \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] API key exists', ['has_key' => !empty($apiKey)]);
        
        if (empty($apiKey)) {
            \Illuminate\Support\Facades\Log::error('[callOpenAiWithTools] OpenAI API key not configured');
            throw new \Exception('OpenAI API key not configured');
        }

        $apiMessages = [
            ['role' => 'system', 'content' => $agent->instructions()]
        ];
        
        foreach ($messages as $msg) {
            $apiMessages[] = $msg;
        }
        // \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] Built API messages', ['message_count' => count($apiMessages)]);

        $toolsDefinition = $this->getToolDefinitions($agent);
        // \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] Got tool definitions', ['tool_count' => count($toolsDefinition)]);

        $payload = [
            'model' => 'gpt-4o',
            'messages' => $apiMessages,
            'tools' => $toolsDefinition,
            'tool_choice' => 'auto',
            'temperature' => 0.1,
        ];
        // \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] Sending request to OpenAI API');

        $response = Http::withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/chat/completions', $payload);
        // \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] API response status', ['successful' => $response->successful()]);

        if (!$response->successful()) {
            \Illuminate\Support\Facades\Log::error('[callOpenAiWithTools] API error', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'message' => 'API error. Failed to reach OpenAI.'];
        }

        $data = $response->json();
        // \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] Parsed JSON response');
        
        $message = $data['choices'][0]['message'] ?? null;

        if (!empty($message['tool_calls'])) {
            \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] Tool calls detected', ['count' => count($message['tool_calls'])]);
            $toolCalls = $message['tool_calls'];
            $result = $this->handleToolCalls($agent, $toolCalls, $apiMessages, $apiKey);
            
            // Pass the original tool calls and results back so processChat can act on them (e.g., sync dashboard dates)
            if ($result['success']) {
                $result['tool_calls'] = $toolCalls;
                // tool_results is already in $result
            }
            return $result;
        }

        // \Illuminate\Support\Facades\Log::info('[callOpenAiWithTools] No tool calls, returning direct message');
        return [
            'success' => true,
            'message' => $message['content'] ?? 'I cannot answer that right now.',
        ];
    }

    protected function handleToolCalls(QueueAnalyticsAssistant $agent, array $toolCalls, array $apiMessages, string $apiKey): array
    {
        $apiMessages[] = [
            'role' => 'assistant',
            'content' => null,
            'tool_calls' => $toolCalls,
        ];

        $results = [];

        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'];
            $arguments = json_decode($toolCall['function']['arguments'], true) ?: [];

            $result = "Tool not found.";
            
            foreach ($agent->tools() as $tool) {
                if (class_basename($tool) === $functionName) {
                    try {
                        $result = $tool->handle(new \Laravel\Ai\Tools\Request($arguments));
                        $results[$toolCall['id']] = $result; // Store raw result for processChat
                    } catch (\Exception $e) {
                        $result = "Error executing tool: " . $e->getMessage();
                    }
                    break;
                }
            }

            $apiMessages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'name' => $functionName,
                'content' => (string) $result,
            ];
        }

        $payload = [
            'model' => 'gpt-4o',
            'messages' => $apiMessages,
            'temperature' => 0.1,
        ];

        $response = Http::withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/chat/completions', $payload);

        if (!$response->successful()) {
            return ['success' => false, 'message' => 'API error on tool return.'];
        }

        $data = $response->json();
        return [
            'success' => true,
            'message' => $data['choices'][0]['message']['content'] ?? 'Done.',
            'tool_results' => $results // Return the raw tool results
        ];
    }

    protected function getToolDefinitions(QueueAnalyticsAssistant $agent): array
    {
        $defs = [];
        
        foreach ($agent->tools() as $tool) {
            $schemaParams = $tool->schema(new class implements \Illuminate\Contracts\JsonSchema\JsonSchema {
                public function string(): \Illuminate\JsonSchema\Types\StringType
                {
                    return \Illuminate\JsonSchema\JsonSchema::string();
                }

                public function integer(): \Illuminate\JsonSchema\Types\IntegerType
                {
                    return \Illuminate\JsonSchema\JsonSchema::integer();
                }

                public function number(): \Illuminate\JsonSchema\Types\NumberType
                {
                    return \Illuminate\JsonSchema\JsonSchema::number();
                }

                public function boolean(): \Illuminate\JsonSchema\Types\BooleanType
                {
                    return \Illuminate\JsonSchema\JsonSchema::boolean();
                }

                public function array(): \Illuminate\JsonSchema\Types\ArrayType
                {
                    return \Illuminate\JsonSchema\JsonSchema::array();
                }

                public function object($properties = []): \Illuminate\JsonSchema\Types\ObjectType
                {
                    return \Illuminate\JsonSchema\JsonSchema::object($properties);
                }
            });
            
            $properties = [];
            $required = [];

            foreach ($schemaParams as $key => $typeBuilder) {
                if ($typeBuilder instanceof \Illuminate\JsonSchema\Types\Type) {
                    $prop = $typeBuilder->toArray();
                    $properties[$key] = $prop;
                    if (!isset($prop['nullable']) || !$prop['nullable']) {
                        $required[] = $key;
                    }
                }
            }

            $defs[] = [
                'type' => 'function',
                'function' => [
                    'name' => class_basename($tool),
                    'description' => (string) $tool->description(),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => $properties,
                        'required' => $required,
                    ],
                ]
            ];
        }
        return $defs;
    }

    private function logDebug($source, $message)
    {
        Log::info("[$source] $message", [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'queue' => $this->selectedQueue
        ]);
    }

    public function render()
    {
        // One final force-update of the key before rendering
        $this->lastUpdate = (string) microtime(true);

        // Log::info('[AIQueueAnalytics] render() called', [
        //     'incomingSessions' => $this->incomingSessions,
        //     'engagedSessions' => $this->engagedSessions,
        //     'avgWaitTime' => $this->avgWaitTime,
        //     'avgSessionHandleTime' => $this->avgSessionHandleTime,
        //     'avgSessionSentiment' => $this->avgSessionSentiment,
        //     'startDate' => $this->startDate,
        //     'endDate' => $this->endDate,
        //     'lastUpdate' => $this->lastUpdate
        // ]);

        return view('livewire.ai-queue-analytics');
    }
}