<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsPlan extends Model
{
    // Scope for filtering by name
    public function scopeName($query, $name)
    {
        return $query->where('name', 'like', "%$name%");
    }
    // Scope for filtering by status
    public function scopeStatus($query, $status)
    {
        return $query->where('is_active', $status);
    }
    // Scope for filtering by popular
    public function scopePopular($query, $popular)
    {
        return $query->where('is_popular', $popular);
    }
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'credit_amount',
        'price',
        'currency_code',
        'stripe_plan_id',
        'is_popular',
        'is_active',
    ];

    // Relationship removed as we store code directly
    // public function currency()
    // {
    //     return $this->belongsTo(Currency::class, 'currency_id', 'ID');
    // }
}
