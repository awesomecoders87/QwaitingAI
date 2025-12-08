<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_availability',
    ];

    public function team():BelongsTo{
        return $this->belongsTo(Team::class);
    }
}
