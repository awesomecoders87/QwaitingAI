<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'floor',
        'building',
        'room_image',
        'capacity',
        'days_available',
        'equipment',
        'other_equipment',
        'room_amenities',
        'other_resources',
        'room_policies',
        'additional_notes',
        'special_instructions',
    ];

    public function team():BelongsTo{
        return $this->belongsTo(Team::class);
    }
}
