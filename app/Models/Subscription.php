<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends Model
{
    use HasFactory;

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
 
}
