<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeedbackSetting;
use App\Models\QueueStorage;
use App\Models\Queue;
use App\Models\SiteDetail;
use Carbon\Carbon;
use App\Jobs\SendFeedbackSurveyJob;
use Illuminate\Support\Facades\Log;

class SendFeedbackSurvey extends Command
{
    protected $signature = 'feedback:send-automatic';
    protected $description = 'Send feedback survey automatically after X minutes of call closure';

    public function handle()
    {
        Log::info("📢 Starting feedback survey cron...");

        $feedbackSettings = FeedbackSetting::where([
            'feedback_system'         => 1,
            'enable_post_interaction' => 1,
            'trigger_method'          => 'auto'
        ])
        ->select('id', 'team_id', 'location_id', 'trigger_delay')
        ->get();

        if ($feedbackSettings->isEmpty()) {
            Log::info("No feedback settings found with auto trigger.");
            return;
        }

        foreach ($feedbackSettings as $setting) {
            $timezone = SiteDetail::where('location_id', $setting->location_id)
                ->value('select_timezone') ?? 'UTC';

            $delayMinutes = (int) ($setting->trigger_delay ?? 1);

            QueueStorage::where('status', Queue::STATUS_CLOSE)
                ->where('team_id', $setting->team_id)
                ->where('locations_id', $setting->location_id)
                ->whereDate('arrives_time', Carbon::now($timezone))
                ->whereNull('feedback_sent_at')
                ->where('closed_datetime', '<=', Carbon::now($timezone)->subMinutes($delayMinutes))
                ->select('id', 'json', 'name', 'phone', 'phone_code', 'locations_id', 'team_id', 'served_by', 'created_by', 'queue_id')
                ->chunk(50, function ($calls) {
                    foreach ($calls as $call) {
                        SendFeedbackSurveyJob::dispatch($call);

                        Log::info("Queued feedback survey job for queue ID {$call->id} (Team {$call->team_id}, Location {$call->locations_id})");
                    }
                });

            Log::info("Processed setting ID {$setting->id} (Team {$setting->team_id}, Location {$setting->location_id})");
        }

        Log::info("✅ Feedback survey cron finished.");
    }
}
