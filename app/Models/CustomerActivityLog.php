<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'queue_id',
        'booking_id',
        'type',
        'customer_id',
        'note',
    ];

    /**
     * Get the customer that owns the activity log.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

     /**
     * Get the team that owns the activity log.
     */
    public function team()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the location that owns the activity log.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the queue that owns the activity log.
     */
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }
}