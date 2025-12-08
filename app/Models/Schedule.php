<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules';

    protected $fillable = [
        'team_id',
        'feedback_setting_id',
        'schedule_duration_type',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Accessor to get a combined time range.
     */
    // public function getScheduleDurationAttribute()
    // {
    //     return "{$this->start_time->format('H:i')}–{$this->end_time->format('H:i')}";
    // }


    public function feedbackSetting()
    {
        return $this->belongsTo(FeedbackSetting::class);
    }
}
