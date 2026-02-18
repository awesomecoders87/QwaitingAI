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
use App\Services\OpenAIQueueAnalyst;
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
    public $selectedAgent = 'all';
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
    public $openaiInsight = '';
    public $isGeneratingInsight = false;
    public $isShowingPrediction = false;
    public $openaiError = '';
    public $aiAnalysisTime = null; // Track when AI analysis occurred
    public $aiPredictionGenerated = false; // Guard against infinite loop

    // Chat Interface
    public $chatInput = '';
    public $chatMessages = [];
    public $isChatProcessing = false;
    public $isChatOpen = false;

    public function toggleChat()
    {
        $this->isChatOpen = !$this->isChatOpen;
    }
    
    // Chart data
    public $sessionsChartData = [];
    public $waitTimeChartData = [];
    public $handleTimeChartData = [];
    public $sessionsByQueueData = [];

    public function mount($location_id = null)
    {
        $this->teamId = tenant('id');
        
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

    public function updatedStartDate()
    {
        $this->logDebug('MANUAL_DATE_CHANGE', 'updatedStartDate triggered');
        $this->loadAnalytics();
    }

    public function updatedEndDate()
    {
        $this->logDebug('MANUAL_DATE_CHANGE', 'updatedEndDate triggered');
        $this->loadAnalytics();
    }

    public function updatedSelectedChannel()
    {
        $this->loadAnalytics();
    }

    public function updatedSelectedQueue()
    {
        $this->loadAnalytics();
    }

    public function updatedSelectedAgent()
    {
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

    public function setDateRange($start, $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->selectedDuration = 'custom';
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        $this->isShowingPrediction = false;
        $this->calculateMetrics();
        $this->generateAIInsights();
        $this->prepareChartData();
        
        // Log final results before displaying on dashboard
        $this->logFinalResults('AFTER_LOADANALYTICS');
    }

    private function calculateMetrics()
    {
        $startDate = Carbon::parse($this->startDate, $this->timezone)->startOfDay();
        $endDate = Carbon::parse($this->endDate, $this->timezone)->endOfDay();

        // Log::info('calculateMetrics: Start', ['startDate' => $startDate, 'endDate' => $endDate, 'teamId' => $this->teamId, 'location' => $this->location]);

        // Build base query
        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [$startDate, $endDate]);

        // Apply filters
        if ($this->selectedQueue !== 'all') {
            $query->where('category_id', $this->selectedQueue);
        }

        if ($this->selectedAgent !== 'all') {
            $query->where('assign_staff_id', $this->selectedAgent);
        }
        
        // Log::info('calculateMetrics: Query built', ['filters' => ['queue' => $this->selectedQueue, 'agent' => $this->selectedAgent]]);

        // Incoming Sessions (Total created tickets)
        $this->incomingSessions = $query->count();
        // Log::info('calculateMetrics: Incoming Sessions', ['count' => $this->incomingSessions]);

        // Engaged Sessions (Served tickets)
        $this->engagedSessions = (clone $query)
            ->whereIn('status', ['Close'])
            ->count();
        // Log::info('calculateMetrics: Engaged Sessions', ['count' => $this->engagedSessions]);

        // Average Wait Time (in seconds)
        $avgWaitSeconds = (clone $query)
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait')
            ->value('avg_wait');
        
        $this->avgWaitTime = round($avgWaitSeconds ?: 0, 1);
        // Log::info('calculateMetrics: Avg Wait Time', ['seconds' => $this->avgWaitTime]);

        // Average Session Handle Time (in minutes)
        $avgHandleSeconds = (clone $query)
            ->whereNotNull('called_datetime')
            ->whereNotNull('closed_datetime')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as avg_handle')
            ->value('avg_handle');
        
        $this->avgSessionHandleTime = round(($avgHandleSeconds ?: 0) / 60, 1);

        // Transfer Rate (percentage of transferred tickets)
        $transferredCount = (clone $query)
            ->whereNotNull('transfer_id')
            ->count();
        
        $this->transferRate = $this->incomingSessions > 0 
            ? round(($transferredCount / $this->incomingSessions) * 100, 1) 
            : 0;

        // Average Session Sentiment (based on ratings)
        $avgRating = (clone $query)
            ->whereNotNull('rating')
            ->avg('rating');
        
        // Convert rating to percentage (assuming 1-5 scale)
        $this->avgSessionSentiment = $avgRating 
            ? round(($avgRating / 5) * 100, 1) 
            : 0;

        // Calculate trends (compare with previous period)
        $this->calculateTrends($startDate, $endDate);
        
        // Log::info('calculateMetrics: Finished', [
        //     'handleTime' => $this->avgSessionHandleTime,
        //     'transferRate' => $this->transferRate,
        //     'sentiment' => $this->avgSessionSentiment
        // ]);
    }

    private function calculateTrends($startDate, $endDate)
    {
        $periodDays = $startDate->diffInDays($endDate);
        $prevStartDate = (clone $startDate)->subDays($periodDays);
        $prevEndDate = (clone $endDate)->subDays($periodDays);

        $query = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [$prevStartDate, $prevEndDate]);

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
    }

    private function predictWaitTimes()
    {
        // AI-powered wait time prediction - ALIGNED WITH DASHBOARD (arrives_time filter, datetime grouping)
        $hourlyData = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->selectRaw('HOUR(datetime) as hour, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait')
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
    }

    private function generateStaffingRecommendations()
    {
        // AI recommendation for staffing - ALIGNED WITH DASHBOARD
        $hourlyLoad = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->selectRaw('HOUR(datetime) as hour, COUNT(*) as ticket_count')
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
    }

    private function calculateNoShowProbability()
    {
        // Calculate no-show rate for selected range
        $totalBooked = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->count();
            
        $noShows = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->where('status', 'no-show')
            ->count();

        $this->noShowProbability = $totalBooked > 0 
            ? round(($noShows / $totalBooked) * 100, 1) 
            : 0;
    }

    private function forecastPeakHours()
    {
        // Forecast peak hours based on selected data - ALIGNED WITH DASHBOARD
        $peakHours = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->selectRaw('HOUR(datetime) as hour, COUNT(*) as count')
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
    }

    private function detectSLABreaches()
    {
        // Detect potential SLA breaches (e.g., wait time > 15 minutes)
        $slaThreshold = 15 * 60; // 15 minutes in seconds

        $breaches = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->whereRaw('TIMESTAMPDIFF(SECOND, arrives_time, called_datetime) > ?', [$slaThreshold])
            ->selectRaw('DATE(datetime) as date, COUNT(*) as breach_count')
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
    }

    private function detectBottlenecks()
    {
        // Detect bottlenecks by analyzing categories with longest wait times in selected period
        $bottlenecks = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->whereNotNull('category_id')
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
    }

    private function optimizeThroughput()
    {
        // Analyze throughput per counter/staff in selected period
        $throughput = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [
                Carbon::parse($this->startDate, $this->timezone)->startOfDay(), 
                Carbon::parse($this->endDate, $this->timezone)->endOfDay()
            ])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->whereNotNull('assign_staff_id')
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
    }

    /**
     * Generate OpenAI-powered insight
     */
    public function generateOpenAIInsight()
    {
        if (empty(config('services.openai.api_key'))) {
            Log::error('generateOpenAIInsight: API key missing');
            $this->openaiError = 'OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file.';
            return;
        }

        try {
            // Log::info('generateOpenAIInsight: Button Clicked. Starting AI analysis...');
            $this->isGeneratingInsight = true;
            $this->openaiError = '';
            
            $analyst = new OpenAIQueueAnalyst();

            $result = $analyst->analyzePerformance(
                $this->teamId,
                $this->location,
                $this->startDate,
                $this->endDate,
                $this->selectedQueue,
                $this->selectedAgent,
                $this->timezone
            );
            
            // Check if there's no data available
            if (isset($result['no_data']) && $result['no_data']) {
                // Log::info('generateOpenAIInsight: No data available for this selection');
                // Add message to chat instead of showing error banner
                $this->chatMessages[] = [
                    'role' => 'assistant',
                    'content' => $result['user_message'] ?? 'No historical data available for this selection.',
                    'time' => Carbon::now()->format('H:i')
                ];
                $this->isGeneratingInsight = false;
                return;
            }
            
            // Log::info('generateOpenAIInsight: Analysis result received', [
            //     'is_prediction' => $result['is_prediction'] ?? false,
            //     'start_date' => $this->startDate,
            //     'end_date' => $this->endDate,
            //     'trigger' => 'Manual Button or Chatbot'
            // ]);
            
            
            // Try to parse JSON response
            $aiData = json_decode($result['ai_analysis'], true);
            

            if (json_last_error() === JSON_ERROR_NONE && is_array($aiData)) {

                // Determine if this is a prediction or analysis of actual data
                $isPrediction = isset($result['is_prediction']) && $result['is_prediction'];
                $this->isShowingPrediction = $isPrediction;

                // CRITICAL FIX: Only override local predictions if this is a future prediction
                // For historical data analysis, keep the locally calculated predictions
                if ($isPrediction) {
                    // Future prediction mode - use AI-generated predictions
                    if (isset($aiData['wait_time_predictions'])) {
                        $this->waitTimePredictions = $aiData['wait_time_predictions'];
                    }
                    
                    if (isset($aiData['staffing_recommendations'])) {
                        $this->staffingRecommendations = $aiData['staffing_recommendations'];
                    }
                    
                    // Log::info('===== APPLIED AI PREDICTIONS TO UI =====', [
                    //     'date_range' => "{$this->startDate} to {$this->endDate}",
                    //     'wait_predictions' => $this->waitTimePredictions,
                    //     'staffing_recs' => $this->staffingRecommendations,
                    //     'trigger' => debug_backtrace()[1]['function'] ?? 'unknown',
                    // ]);
                } else {
                    // Historical data analysis - KEEP local calculations, don't override
                    // Log::info('Keeping local predictions for historical data analysis', [
                    //     'wait_predictions_count' => count($this->waitTimePredictions),
                    //     'staffing_count' => count($this->staffingRecommendations),
                    //     'ai_wait_count' => isset($aiData['wait_time_predictions']) ? count($aiData['wait_time_predictions']) : 0,
                    //     'ai_staffing_count' => isset($aiData['staffing_recommendations']) ? count($aiData['staffing_recommendations']) : 0
                    // ]);
                }
                
                // Always update these as they don't conflict with local calculations
                if (isset($aiData['peak_hours_forecast'])) {
                    $this->peakHoursForecast = $aiData['peak_hours_forecast'];
                }
                
                if (isset($aiData['no_show_probability'])) {
                    $this->noShowProbability = $aiData['no_show_probability'];
                }
                
                if (isset($aiData['bottleneck_detection'])) {
                    $this->bottleneckDetection = $aiData['bottleneck_detection'];
                }

                // Handle predicted metrics for top cards
                // Only overwrite top cards if this was a prediction (i.e. we have no actual data)
                if ($isPrediction && isset($aiData['predicted_metrics'])) {
                    $metrics = $aiData['predicted_metrics'];
                    
                    if (isset($metrics['incoming_sessions'])) $this->incomingSessions = $metrics['incoming_sessions'];
                    if (isset($metrics['engaged_sessions'])) $this->engagedSessions = $metrics['engaged_sessions'];
                    if (isset($metrics['avg_wait_seconds'])) $this->avgWaitTime = $metrics['avg_wait_seconds'];
                    if (isset($metrics['avg_handle_minutes'])) $this->avgSessionHandleTime = $metrics['avg_handle_minutes'];
                    if (isset($metrics['transfer_rate'])) $this->transferRate = $metrics['transfer_rate'];
                    if (isset($metrics['avg_sentiment'])) $this->avgSessionSentiment = $metrics['avg_sentiment'];
                    
                } elseif (!$isPrediction) {
                    // Log::info('generateOpenAIInsight: Keeping actual metrics (no prediction override needed)');
                }

                // Handle daily forecast for charts
                if (isset($aiData['daily_forecast']) && is_array($aiData['daily_forecast'])) {
                    
                    // Initialize/clear chart arrays for predictions
                    if ($isPrediction) {
                        $this->sessionsChartData = [];
                        $this->waitTimeChartData = [];
                    }
                    
                    foreach ($aiData['daily_forecast'] as $day) {
                        try {
                            $dateObj = Carbon::parse($day['date']);
                            $formattedDate = $dateObj->format('M d') . ' (Est.)'; // Add (Est.) to indicate prediction
                            
                            // Check if this date already exists in our data to avoid duplicates (crude check)
                            // Actually, just appending creates a clear "Actual -> Forecast" flow
                            
                            $this->sessionsChartData[] = [
                                'date' => $formattedDate,
                                'incoming' => $day['incoming_sessions'],
                                'engaged' => $day['engaged_sessions'],
                                'is_predicted' => true
                            ];

                            $this->waitTimeChartData[] = [
                                'date' => $formattedDate,
                                'wait_time' => round(($day['avg_wait_seconds'] ?? 0) / 60, 1),
                                'handle_time' => $day['avg_handle_minutes'] ?? 0,
                                'is_predicted' => true
                            ];
                        } catch (\Exception $e) {
                            Log::warning('Error parsing forecast date', ['date' => $day['date'] ?? 'unknown']);
                        }
                    }
                    
                    $this->dispatch('chartsUpdated', 
                        sessionsData: $this->sessionsChartData, 
                        waitTimeData: $this->waitTimeChartData
                    );
                }

                $this->aiAnalysisTime = Carbon::now();
                
                // Log final results after AI generation
                $this->logFinalResults('AFTER_AI_GENERATION');
                
                // Dispatch events to update UI
                $this->dispatch('chartsUpdated', 
                    sessionsData: $this->sessionsChartData, 
                    waitTimeData: $this->waitTimeChartData
                );
                $this->dispatch('$refresh'); // Force Livewire to re-render
                
                // Clear the text insight so it doesn't show the big box
                $this->openaiInsight = ''; 
            } else {
                Log::warning('generateOpenAIInsight: Invalid JSON', ['raw' => $result['ai_analysis']]);
                // Fallback if not valid JSON (should be rare with JSON mode)
                $this->openaiInsight = $result['ai_analysis'];
                $this->aiAnalysisTime = Carbon::now();
            }
            
        } catch (\Exception $e) {
            Log::error('generateOpenAIInsight: Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            // Add error message to chat instead of showing banner
            $this->chatMessages[] = [
                'role' => 'assistant',
                'content' => 'I encountered an error while analyzing your data: ' . $e->getMessage() . '. Please try again or contact support if the issue persists.',
                'time' => Carbon::now()->format('H:i')
            ];
        } finally {
            $this->isGeneratingInsight = false;
        }
    }

    private function prepareChartData()
    {
        $startDate = Carbon::parse($this->startDate, $this->timezone)->startOfDay();
        $endDate = Carbon::parse($this->endDate, $this->timezone)->endOfDay();

        // 1. Get raw queries grouped by date
        $sessionsData = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [$startDate, $endDate])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->selectRaw("DATE(arrives_time) as date, COUNT(*) as incoming, SUM(CASE WHEN status = 'Close' THEN 1 ELSE 0 END) as engaged")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date'); // Key by date for easy lookup

        $timeData = QueueStorage::where('team_id', $this->teamId)
            ->where('locations_id', $this->location)
            ->whereBetween('arrives_time', [$startDate, $endDate])
            ->when($this->selectedQueue !== 'all', function($q) {
                $q->where('category_id', $this->selectedQueue);
            })
            ->when($this->selectedAgent !== 'all', function($q) {
                $q->where('assign_staff_id', $this->selectedAgent);
            })
            ->whereNotNull('called_datetime')
            ->whereNotNull('arrives_time')
            ->selectRaw('DATE(arrives_time) as date, AVG(TIMESTAMPDIFF(SECOND, arrives_time, called_datetime)) as avg_wait, AVG(TIMESTAMPDIFF(SECOND, called_datetime, closed_datetime)) as avg_handle')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

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
        
        $this->dispatch('chartsUpdated', 
            sessionsData: $this->sessionsChartData, 
            waitTimeData: $this->waitTimeChartData
        );
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

    #[Computed]
    public function agents()
    {
        return User::where('team_id', $this->teamId)
            ->whereJsonContains('locations', (string) $this->location)
            ->select('id', 'name')
            ->get();
    }

    public function sendMessage()
    {
        // Log::info('sendMessage: Method called', ['input' => $this->chatInput]);
        
        if (empty($this->chatInput)) {
            // Log::info('sendMessage: Empty input, returning');
            return;
        }

        $this->isChatProcessing = true;
        $this->lastUserQuery = $this->chatInput;
        // Log::info('sendMessage: Storing query', ['query' => $this->lastUserQuery]);
        
        // Add user message to history immediately
        $this->chatMessages[] = [
            'role' => 'user',
            'content' => $this->chatInput,
            'time' => Carbon::now()->format('H:i')
        ];

        $this->chatInput = ''; // Clear input immediately for UI responsiveness
        
        // Log::info('sendMessage: Dispatching chat-message-sent event');
        // Trigger the actual processing - event listener will call processChat()
        $this->dispatch('chat-message-sent'); 
    }

    public function processChat()
    {
        if (empty($this->lastUserQuery)) {
            $this->isChatProcessing = false;
            return;
        }

        try {
            // $this->logDebug('CHATBOT_DATE_CHANGE', 'processChat triggered with query: ' . $this->lastUserQuery);
            
            // Prepared lists for AI context
            $queuesList = $this->queues()->map(fn($q) => ['id' => $q->id, 'name' => $q->name])->toArray();
            $agentsList = $this->agents()->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->toArray();
            $locationsList = $this->allLocations->map(fn($l) => ['id' => $l->id, 'name' => $l->location_name])->toArray();

            $analyst = new OpenAIQueueAnalyst();
            $params = $analyst->parseUserQuery(
                $this->lastUserQuery, 
                $this->teamId, 
                $this->location, 
                $this->timezone,
                $queuesList,
                $agentsList,
                $locationsList,
                $this->startDate, // Pass current state
                $this->endDate,
                $this->selectedQueue,
                $this->selectedAgent
            );
            
            if ($params) {
                // Handle greetings specially - don't update dashboard
                if (isset($params['intent']) && $params['intent'] === 'greeting') {
                     $this->chatMessages[] = [
                        'role' => 'assistant',
                        'content' => $params['explanation'],
                        'time' => Carbon::now()->format('H:i')
                    ];
                    $this->isChatProcessing = false;
                    return;
                }

                // Handle not_available intent - when user requests agent/queue that doesn't exist
                if (isset($params['intent']) && $params['intent'] === 'not_available') {
                    $this->chatMessages[] = [
                        'role' => 'assistant',
                        'content' => $params['explanation'],
                        'time' => Carbon::now()->format('H:i')
                    ];
                    $this->isChatProcessing = false;
                    return;
                }

                // Handle off-topic queries - refuse immediately using AI-generated explanation
                if (isset($params['intent']) && $params['intent'] === 'off_topic') {
                    $this->chatMessages[] = [
                        'role' => 'assistant',
                        'content' => $params['explanation'],
                        'time' => Carbon::now()->format('H:i')
                    ];
                    $this->isChatProcessing = false;
                    return;
                }

                // Handle location mentions - user tried to specify location in chat
                if (isset($params['location_mentioned']) && $params['location_mentioned']) {
                    $this->chatMessages[] = [
                        'role' => 'assistant',
                        'content' => $params['explanation'],
                        'time' => Carbon::now()->format('H:i')
                    ];
                    $this->isChatProcessing = false;
                    return;
                }


                // Update filters based on interpreted parameters
                $start = $params['start_date'] ?? null;
                $end = $params['end_date'] ?? null;
                $queue = $params['queue_id'] ?? null;
                $agent = $params['agent_id'] ?? null;

                if ($start && $end) {
                    // $this->logDebug('CHATBOT_DATE_CHANGE', "Setting dates - Start: $start, End: $end");
                    $this->startDate = $start;
                    $this->endDate = $end;
                    
                    // Update the visual date picker to reflect new dates
                    $this->dispatch('update-date-picker', [
                        'start' => $start,
                        'end' => $end
                    ]);
                }
                
                if ($queue) {
                    $this->selectedQueue = $queue; 
                }

                if ($agent) {
                    $this->selectedAgent = $agent;
                }
                
                // $this->logDebug('CHATBOT_DATE_CHANGE', 'About to call loadAnalytics()');
                $this->loadAnalytics();
                
                // Add system response
                $this->chatMessages[] = [
                    'role' => 'assistant',
                    'content' => $params['explanation'],
                    'time' => Carbon::now()->format('H:i')
                ];
                
                // If intent is prediction or analysis, trigger detailed insights
                if (isset($params['intent']) && in_array($params['intent'], ['prediction', 'analysis'])) {
                    // Log::info('[CHATBOT] Intent detected, calling generateOpenAIInsight', [
                    //     'intent' => $params['intent'],
                    //     'date_range' => "{$this->startDate} to {$this->endDate}"
                    // ]);
                    $this->generateOpenAIInsight();
                }

                // NEW: Handle specific questions or scenarios
                if (isset($params['intent']) && in_array($params['intent'], ['question', 'scenario'])) {
                   // Log::info('[CHATBOT] Question/Scenario detected', ['intent' => $params['intent']]);
                    
                    // 1. Ensure we have the latest data for the selected range (loadAnalytics already called above)
                    // 2. If it's a prediction question (future date), ensure we have predictions
                    if (Carbon::parse($this->endDate)->isFuture() && empty($this->waitTimePredictions)) {
                        $this->generateOpenAIInsight();
                    }

                    // 3. Gather context for the answer
                    $contextData = [
                        'period' => "{$this->startDate} to {$this->endDate}",
                        'metrics' => [
                            'incoming' => $this->incomingSessions,
                            'engaged' => $this->engagedSessions,
                            'avg_wait_minutes' => round($this->avgWaitTime / 60, 1),
                            'avg_handle_minutes' => $this->avgSessionHandleTime,
                            'sentiment' => $this->avgSessionSentiment
                        ],
                        'predictions' => [
                            'wait_time_hourly' => $this->waitTimePredictions,
                            'staffing_hourly' => $this->staffingRecommendations,
                            'peak_hours' => $this->peakHoursForecast,
                            'no_show_prob' => $this->noShowProbability
                        ],
                        'insights' => [
                            'bottlenecks' => $this->bottleneckDetection,
                            'top_issues' => $this->slaBreachAlerts
                        ]
                    ];

                    // 4. Get specific answer
                    $answer = $analyst->answerSpecificQuestion(
                        $this->lastUserQuery,
                        $contextData,
                        $this->timezone
                    );

                    // 5. Add to chat
                    $this->chatMessages[] = [
                        'role' => 'assistant',
                        'content' => $answer,
                        'time' => Carbon::now()->format('H:i')
                    ];
                }


            } else {
                 $this->chatMessages[] = [
                    'role' => 'assistant',
                    'content' => "I'm sorry, I couldn't understand that request. Please try asking about a specific time range (e.g., 'Show me last week's data').",
                    'time' => Carbon::now()->format('H:i')
                ];
            }

        } catch (\Exception $e) {
            Log::error('Chat Error', ['message' => $e->getMessage()]);
            $this->chatMessages[] = [
                'role' => 'assistant',
                'content' => "An error occurred while processing your request.",
                'time' => Carbon::now()->format('H:i')
            ];
        } finally {
            $this->isChatProcessing = false;
        }
    }

    /**
     * Log debug information with context
     */
    private function logDebug($source, $message)
    {
        Log::info("[$source] $message", [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'queue' => $this->selectedQueue,
            'agent' => $this->selectedAgent
        ]);
    }

    /**
     * Log final results to a dedicated file for debugging
     */
    private function logFinalResults($trigger)
    {
        $logData = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'trigger' => $trigger,
            'filters' => [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'selectedQueue' => $this->selectedQueue,
                'selectedAgent' => $this->selectedAgent,
                'location' => $this->location,
                'teamId' => $this->teamId,
            ],
            'metrics' => [
                'incomingSessions' => $this->incomingSessions,
                'engagedSessions' => $this->engagedSessions,
                'avgWaitTime' => $this->avgWaitTime,
                'avgSessionHandleTime' => $this->avgSessionHandleTime,
            ],
            'waitTimePredictions' => $this->waitTimePredictions,
            'staffingRecommendations' => $this->staffingRecommendations,
            'peakHoursForecast' => $this->peakHoursForecast,
            'isShowingPrediction' => $this->isShowingPrediction,
        ];

        // Create logs directory if it doesn't exist
        $logDir = storage_path('logs/ai_analytics');
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Create a dated log file
        $logFile = $logDir . '/analytics_' . date('Y-m-d') . '.log';
        
        // Append to log file with clear separators
        $separator = str_repeat('=', 80);
        $logContent = "\n{$separator}\n" . json_encode($logData, JSON_PRETTY_PRINT) . "\n{$separator}\n";
        
        file_put_contents($logFile, $logContent, FILE_APPEND);
        
        // Log::info("Final results logged to: $logFile", [
        //     'trigger' => $trigger,
        //     'wait_predictions_count' => count($this->waitTimePredictions),
        //     'staffing_recommendations_count' => count($this->staffingRecommendations)
        // ]);
    }

    public function render()
    {
        return view('livewire.ai-queue-analytics');
    }
}
