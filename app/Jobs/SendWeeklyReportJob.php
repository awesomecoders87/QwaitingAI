<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\WeeklyReportMail;
use App\Models\User;

class SendWeeklyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user, $data;

    public function __construct(User $user, $data)
    {
        $this->user = $user;
        $this->data = $data;
    }

    public function handle()
    {
        try {
            Mail::to("aksh@stelleninfotech.in")->send(new WeeklyReportMail($this->data));
        } catch (\Exception $e) {
            \Log::error("Email send failed for {$this->user->email}: " . $e->getMessage());
        }
    }
}
