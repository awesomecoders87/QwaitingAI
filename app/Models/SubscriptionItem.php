<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Laravel\Cashier\SubscriptionItem as CashierSubscriptionItem;

class SubscriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'stripe_id',
        'stripe_product',
        'stripe_price',
        'quantity',
        'created_at',
        'updated_at',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function shrivraPackage()
    {
        return $this->belongsTo(ShrivraPackage::class);
    }

    public function smsPlan()
    {
        return $this->belongsTo(SmsPlan::class);
    }


}
