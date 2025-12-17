<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends Model
{
    protected $fillable = [
        'domain_id',
        'type',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'location_id',
        'created_at',
        'updated_at',
        // add other fillable fields as needed
    ];
    use HasFactory;

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
 
}
