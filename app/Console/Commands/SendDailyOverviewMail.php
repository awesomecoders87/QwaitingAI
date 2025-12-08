<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyOverviewMail;


class SendDailyOverviewMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:overview-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $teamId = 3;
        $email = 'manpreet@stelleninfotech.in';
        Mail::to($email)->send(new DailyOverviewMail($teamId));
    }
}
