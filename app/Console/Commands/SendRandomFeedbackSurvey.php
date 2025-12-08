<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeedbackSetting;
use App\Models\QueueStorage;
use App\Models\Queue;
use App\Models\Schedule;
use App\Models\SiteDetail;
use Carbon\Carbon;
use App\Jobs\SendFeedbackSurveyJob;
use Illuminate\Support\Facades\Log;

class SendRandomFeedbackSurvey extends Command
{
    protected $signature = 'feedback:send-random';
    protected $description = 'Send feedback survey to a random percentage of users based on schedule';

    public function handle()
    {
        Log::info("📢 Starting random feedback survey cron...");

        $feedbackSettings = FeedbackSetting::where([
            'feedback_system' => 1,
            'enable_post_interaction' => 1,
            'trigger_method' => 'random'
        ])->select('id', 'team_id', 'location_id', 'random_percent')->get();

        if ($feedbackSettings->isEmpty()) {
            Log::info("No feedback settings found for random trigger.");
            return;
        }

        foreach ($feedbackSettings as $setting) {
            $timezone = SiteDetail::where('location_id', $setting->location_id)
                ->value('select_timezone') ?? 'UTC';

            $schedules = Schedule::where('feedback_setting_id', $setting->id)
            ->select('id','schedule_duration_type','start_time','end_time')
            ->get();

            if ($schedules->isEmpty()) {
                Log::info("No schedules found for setting ID {$setting->id}");
                continue;
            }

            foreach ($schedules as $schedule) {
                [$startDate, $endDate] = $this->getDateRange($schedule->schedule_duration_type, $timezone) ?? [null, null];
                if (!$startDate || !$endDate) continue;

                $startDateTime = Carbon::parse($startDate->toDateString() . ' ' . $schedule->start_time, $timezone);
                $endDateTime   = Carbon::parse($endDate->toDateString() . ' ' . $schedule->end_time, $timezone);

                QueueStorage::where('status', Queue::STATUS_CLOSE)
                    ->where('team_id', $setting->team_id)
                    ->where('locations_id', $setting->location_id)
                    ->whereBetween('arrives_time', [$startDateTime, $endDateTime])
                    ->whereNull('feedback_sent_at')
                    ->select('id', 'name', 'phone', 'phone_code', 'locations_id', 'team_id', 'queue_id')
                    ->chunk(50, function ($calls) use ($setting) {
                        $countToSend = ceil(($setting->random_percent / 100) * $calls->count());
                        $randomCalls = $calls->random(min($countToSend, $calls->count()));

                        foreach ($randomCalls as $call) {
                            SendFeedbackSurveyJob::dispatch($call);

                            Log::info("Queued random feedback survey job", [
                                'queue_id' => $call->id,
                                'team_id' => $call->team_id,
                                'location_id' => $call->locations_id,
                                'customer_name' => $call->name,
                                'customer_phone' => $call->phone
                            ]);
                        }
                    });
            }
        }

        Log::info("✅ Random feedback survey cron finished.");
    }

    private function getDateRange($type, $timezone = 'UTC')
    {
        return match ($type) {
            1 => [Carbon::today($timezone), Carbon::now($timezone)],
            2 => [Carbon::yesterday($timezone)->startOfDay(), Carbon::yesterday($timezone)->endOfDay()],
            3 => [Carbon::now($timezone)->subWeek()->startOfWeek(), Carbon::now($timezone)->subWeek()->endOfWeek()],
            4 => [Carbon::now($timezone)->subMonth()->startOfMonth(), Carbon::now($timezone)->subMonth()->endOfMonth()],
            default => null,
        };
    }
}
