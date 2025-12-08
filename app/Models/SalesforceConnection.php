<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesforceConnection extends Model
{
   use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'user_id',
        'salesforce_token',
        'salesforce_refresh_token',
        'salesforce_instance_url',
        'status',
    ];

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
        return $this->belongsTo(User::class);
    }
}
