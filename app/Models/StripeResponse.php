<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeResponse extends Model
{
    protected $fillable = [
        'team_id',
        'location_id',
        'queue_id',
        'category_id',
        'booking_id',
        'payment_intent_id',
        'customer_email',
        'amount',
        'currency',
        'status',
        'full_response',
    ];

    protected $casts = [
        'full_response' => 'array',
    ];

    
    public function team()
    {
        return $this->belongsTo(Tenant::class,'team_id');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

    public function queue()
    {
        return $this->belongsTo(Queue::class, 'queue_id');
    }
    
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
