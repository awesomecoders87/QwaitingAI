<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueFreeSlotCount extends Model
{
    use HasFactory;

    protected $table = 'queue_free_slot_count';

    protected $fillable = [
        'team_id',
        'location_id',
        'booking_date',
        'last_category',
        'sb_start_time',
        'sb_end_time',
        'count',
        'user_id',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'sb_start_time' => 'datetime:H:i:s',
        'sb_end_time' => 'datetime:H:i:s',
    ];

    public $timestamps = false;
}
