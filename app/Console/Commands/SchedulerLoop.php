<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SchedulerLoop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler:loop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run schedule:run every minute in a loop (emulates cron)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //   $this->info('Starting scheduler loop...');
        // while (true) {
        //     // Artisan::call('schedule:run');
        //     Artisan::call('schedule:work');
        //     $this->info('Ran schedule:run at ' . now());
        //     sleep(60); // Wait 60 seconds
        // }
    }
}
