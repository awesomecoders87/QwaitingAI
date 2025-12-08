<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutomationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'is_add_bookings_to_waitlist',
        'when_to_move_booking',
        'move_to_position',
        'is_close_waitlist_early',
        'is_assign_visit_on_alert',
        'is_waitlist_prioritization',
        'waitlist_prioritization'
    ];
}

?>