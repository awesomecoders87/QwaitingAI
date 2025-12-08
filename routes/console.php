<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\AutoCancelBookings;
use App\Console\Commands\SendBookingReminderEmails;
use App\Console\Commands\SendSubscriptionExpiryReminders;
use App\Console\Commands\SchedulerLoop;
use App\Console\Commands\SendWeeklyReportEmail;
use App\Console\Commands\SendFeedbackSurvey;
use App\Console\Commands\SendRandomFeedbackSurvey;
use App\Console\Commands\LogoutForgottenUsers;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());


})->purpose('Display an inspiring quote');

// Schedule::command(AutoCancelBookings::class)->everyMinute();
// Schedule::command(SendBookingReminderEmails::class)->everyMinute();
Schedule::command(SendSubscriptionExpiryReminders::class)->daily();
Schedule::command(SendWeeklyReportEmail::class)->weeklyOn(0, '23:00'); // 0 = Sunday, '23:00' = 11 PM
// Schedule::command(SendFeedbackSurvey::class)->everyMinute();
Schedule::command(SendRandomFeedbackSurvey::class)->dailyAt('22:00');
Schedule::command(LogoutForgottenUsers::class)->everyMinute();
