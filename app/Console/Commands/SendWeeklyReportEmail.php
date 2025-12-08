<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\Booking;
use App\Models\Queue;
use App\Models\Location;
use App\Models\Domain;
use App\Models\Customer;
use App\Models\QueueStorage;
use App\Models\MessageDetail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\WeeklyReportMail;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendWeeklyReportJob;
use Illuminate\Support\Facades\Log;

class SendWeeklyReportEmail extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:weekly-report';

    /**
     * The console command description.
     *
     * @var string
     */
      protected $description = 'Send weekly report to admin';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        Log::info("cron run weekly report to admin");

        $startDate = Carbon::now()->startOfWeek();   // Sunday
        $endDate   = Carbon::now()->endOfWeek();     // Saturday

        Location::where('status', 1)
            ->where('team_id', 13)
            ->chunkById(10, function ($locations) use ($startDate, $endDate) {
                foreach ($locations as $location) {
                    // Preload related data

                    $domain = Domain::where('team_id', $location->team_id)->value('domain');

                    // Get admin user for location
                    $user = User::whereNotNull('locations')
                        ->whereNotNull('email')
                        ->where('is_admin', 1)
                        ->whereRaw("JSON_VALID(locations)")
                        ->where(function ($q) use ($location) {
                            $q->whereJsonContains('locations', (string) $location->id)
                              ->orWhereJsonContains('locations', (int) $location->id);
                        })
                        ->select('id', 'email', 'name', 'team_id', 'locations')
                        ->first();

                    if (!$user) {
                        continue;
                    }
 if (!empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    // Base queue query
                    $queueBase = QueueStorage::where('locations_id', $location->id)
                        ->whereBetween('arrives_time', [$startDate, $endDate]);

                    $queues = $queueBase->get();

                    // Counts
                    $customers = Customer::where('location_id', $location->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count();

                    $totalBookings = Booking::where('location_id', $location->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count();

                    // Filtered queues (not Cancelled)
                    $filtered = $queues->where('status', '!=', 'Cancelled');

                    $totalVisits  = $filtered->count();
                    $servedVisits = $filtered->where('status', 'Close')->count();
                    $noShow       = $queues->where('is_missed', 1)->count();
                    $cancelled    = $queues->where('status', 'Cancelled')
                                           ->whereNotNull('cancelled_datetime')->count();
                    $waiting      = $filtered->where('status', 'Pending')->count();

                    // Served & Wait times
                    $servedSecs = $filtered->filter(fn($q) => $q->status === 'Close' && $q->called_datetime && $q->closed_datetime)
                                           ->map(fn($q) => $q->called_datetime->diffInSeconds($q->closed_datetime));

                    $waitingSecs = $filtered->filter(fn($q) => $q->arrives_time && $q->called_datetime)
                                            ->map(fn($q) => $q->arrives_time->diffInSeconds($q->called_datetime));

                    $avgServedTime  = $servedSecs->count() ? round($servedSecs->avg()) : 0;
                    $maxServedTime  = $servedSecs->count() ? $servedSecs->max() : 0;
                    $avgWaitingTime = $waitingSecs->count() ? round($waitingSecs->avg()) : 0;
                    $maxWaitingTime = $waitingSecs->count() ? $waitingSecs->max() : 0;
                    $minWaitingTime = $waitingSecs->count() ? $waitingSecs->min() : 0;
                    $serveRate      = $totalVisits > 0 ? round(($servedVisits / $totalVisits) * 100, 2) : 0;

                    // Top services aggregation
                    $categories = (clone $queueBase)
                        ->join('categories', 'queues_storage.category_id', '=', 'categories.id')
                        ->select([
                            'queues_storage.category_id',
                            DB::raw('COUNT(queues_storage.id) AS total_calls'),
                            DB::raw('SUM(CASE WHEN queues_storage.status = "Pending" AND queues_storage.closed_datetime IS NULL AND queues_storage.is_missed = 0 THEN 1 ELSE 0 END) AS pending_calls'),
                            DB::raw('(SUM(CASE WHEN queues_storage.status = "Pending" AND queues_storage.closed_datetime IS NULL AND queues_storage.is_missed = 0 THEN 1 ELSE 0 END) / COUNT(queues_storage.id)) * 100 AS pending_percentage'),
                            DB::raw('SUM(CASE WHEN queues_storage.status = "Cancelled" THEN 1 ELSE 0 END) AS cancel_calls'),
                            DB::raw('(SUM(CASE WHEN queues_storage.status = "Cancelled" THEN 1 ELSE 0 END) / COUNT(queues_storage.id)) * 100 AS cancel_percentage'),
                            DB::raw('SUM(CASE WHEN queues_storage.status = "Close" AND queues_storage.closed_datetime IS NOT NULL THEN 1 ELSE 0 END) AS served_calls'),
                            DB::raw('(SUM(CASE WHEN queues_storage.status = "Close" AND queues_storage.closed_datetime IS NOT NULL THEN 1 ELSE 0 END) / COUNT(queues_storage.id)) * 100 AS served_percentage'),
                            DB::raw('SUM(CASE WHEN queues_storage.is_missed = 1 THEN 1 ELSE 0 END) AS no_show'),
                            DB::raw('(SUM(CASE WHEN queues_storage.is_missed = 1 THEN 1 ELSE 0 END) / COUNT(queues_storage.id)) * 100 AS no_show_percentage'),
                            DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(queues_storage.closed_datetime, queues_storage.start_datetime)))) AS total_served_time'),
                            DB::raw('TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(queues_storage.closed_datetime, queues_storage.start_datetime)))), "%H:%i:%s") AS average_served_time'),
                            DB::raw('SEC_TO_TIME(MAX(TIME_TO_SEC(TIMEDIFF(queues_storage.closed_datetime, queues_storage.start_datetime)))) AS max_served_time'),
                            DB::raw('TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(queues_storage.called_datetime, queues_storage.arrives_time)))), "%H:%i:%s") AS average_wait_time'),
                            DB::raw('SEC_TO_TIME(MAX(TIME_TO_SEC(TIMEDIFF(queues_storage.called_datetime, queues_storage.arrives_time)))) AS max_waiting_time'),
                        ])
                        ->groupBy('queues_storage.category_id')
                        ->with('category:id,name,service_time')
                        ->get();

                        // Data for email
                                            $data = [
                                                'start'            => $startDate,
                                                'end'              => $endDate,
                                                'waitlisted'       => $totalVisits,
                                                'bookings'         => $totalBookings,
                                                'served'           => $servedVisits,
                                                'noShows'          => $noShow,
                                                'cancellations'    => $cancelled,
                                                'waiting'          => $waiting,
                                                'avgServedTime'    => $avgServedTime,
                                                'maxServedTime'    => $maxServedTime,
                                                'avgWaitingTime'   => $avgWaitingTime,
                                                'maxWaitingTime'   => $maxWaitingTime,
                                                'minWaitingTime'   => $minWaitingTime,
                                                'serveRate'        => $serveRate,
                                                'newCustomerCount' => $customers,
                                                'adminName'        => $user?->name ?? 'Admin',
                                                'locationName'     => $location->location_name,
                                                'categories'       => $categories,
                                                'domain'           => $domain,
                                                'team_id'          => $location->team_id,
                                                'location_id'      => $location->id,
                                            ];

                                            $logData = [
                                                'team_id'        => $location->team_id,
                                                'location_id'    => $location->id,
                                                'user_id'        => $user->id,
                                                'email'          => $user->email ?? '',
                                                'type'           => MessageDetail::AUTOMATIC_TYPE,
                                                'event_name'     => 'Weekly Report to Admin',
                                                'channel'        => 'email',
                                                'response_status'=> json_encode([
                                                    'waitlisted'       => $totalVisits,
                                                    'bookings'         => $totalBookings,
                                                    'served'           => $servedVisits,
                                                    'noShows'          => $noShow,
                                                    'cancellations'    => $cancelled,
                                                    'waiting'          => $waiting,
                                                    'newCustomerCount' => $customers,
                                                ]),
                                                'status'         => 'sent',
                                                'failed_reason'  => null,
                                            ];

                                            try {
                                                dispatch(new SendWeeklyReportJob($user, $data));
                                            } catch (\Exception $e) {
                                                $logData['status'] = 'failed';
                                                $logData['failed_reason'] = $e->getMessage();
                                            }

                                            MessageDetail::storeLog($logData);
                        }

                }
            });

        $this->info('Weekly reports sent successfully.');
    }


}
