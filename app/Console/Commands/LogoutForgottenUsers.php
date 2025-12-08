<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SiteDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogoutForgottenUsers extends Command
{
    protected $signature = 'users:logout-forgotten';
    protected $description = 'Logout all users who forgot to logout at 11:59 PM in their timezone';

  public function handle()
{
    Log::info("🚀 Starting LogoutForgottenUsers cron...");

    // Fetch team timezone once
    $siteDetail = SiteDetail::where('team_id', 22)
        ->select('select_timezone')
        ->first();
    $timezone = $siteDetail?->select_timezone ?? 'UTC';

    // Current time in the team's timezone
    $now = Carbon::now($timezone);

    // Only run at 23:59 in that timezone
    if ($now->format('H:i') !== '23:59') {
        Log::info("Not 23:59 yet for timezone {$timezone}, exiting cron.");
        return;
    }

    // Chunk users to avoid memory issues
    User::where('team_id', 22)
        ->where('is_login', 1)
        ->select('id')
        ->chunkById(500, function ($users) use ($timezone) {

            $userIds = $users->pluck('id')->toArray();

            // Bulk update login status
            User::whereIn('id', $userIds)->update(['is_login' => 0]);

            // Bulk delete sessions
            DB::table('sessions')->whereIn('user_id', $userIds)->delete();

            // Bulk revoke tokens
            DB::table('personal_access_tokens')->whereIn('tokenable_id', $userIds)->delete();

            Log::info("✅ Logged out users: " . implode(',', $userIds) . " (TZ: {$timezone})");
        });

    Log::info("🎯 Logout cron finished.");
}
}
