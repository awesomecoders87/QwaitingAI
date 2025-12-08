<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'title',
        'term_content',
    ];

    // Relationship with the Team model
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
