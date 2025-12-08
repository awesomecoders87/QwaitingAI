<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtpCode extends Model
{
   use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    // Relationship (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope to find valid OTPs
    public function scopeValid($query, $userId, $code)
    {
        return $query->where('user_id', $userId)
                     ->where('code', $code)
                     ->where('used', false)
                     ->where('expires_at', '>', now());
    }
}
