<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsReport extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'location_id','user_id','message', 'contact','channel', 'status','failed_reason','queue_storage_id','booking_id','type'];

    public function team()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

     public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
