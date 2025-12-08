<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\AutoCancelBookings;
use App\Console\Commands\SendBookingReminderEmails;
use App\Console\Commands\SendSubscriptionExpiryReminders;
use App\Console\Commands\SchedulerLoop;
use App\Console\Commands\SendWeeklyReportEmail;
use App\Console\Commands\SendFeedbackSurvey;
use App\Console\Commands\SendRandomFeedbackSurvey;
use App\Console\Commands\LogoutForgottenUsers;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command(AutoCancelBookings::class)->everyMinute();
        // $schedule->command(SendBookingReminderEmails::class)->everyMinute();
        $schedule->command(SendSubscriptionExpiryReminders::class)->daily();
        $schedule->command(SendWeeklyReportEmail::class)->weeklyOn(0, '23:00');
        // $schedule->command(SendFeedbackSurvey::class)->everyMinute();
        $schedule->command(SendRandomFeedbackSurvey::class)->dailyAt('22:00');
        $schedule->command(LogoutForgottenUsers::class)->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
