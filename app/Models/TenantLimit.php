<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantLimit extends Model
{
    use HasFactory;

    protected $table = 'tenant_limits';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id',
        'is_ticket_limit_enabled',
        'ticket_limit',
        'is_booking_limit_enabled',
        'booking_limit',
        'staff_limit',
        'location_limit',
        'service_limit',
        'counter_limit',
        'status',
    ];

    /**
     * Casts for attributes
     */
    protected $casts = [
        'is_ticket_limit_enabled' => 'boolean',
        'is_booking_limit_enabled' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * A tenant limit belongs to a team.
     */
    public function team()
    {
        return $this->belongsTo(Tenant::class, 'team_id');
    }
}
