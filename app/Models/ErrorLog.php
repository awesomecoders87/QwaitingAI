<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ErrorLog extends Model
{
    use HasFactory;

    protected $table = 'error_logs';

    protected $fillable = [
        'team_id',
        'location_id',
        'code',
        'message',
        'description',
        'resolution',
        'url',
    ];

    
    public function team()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    

  
}
