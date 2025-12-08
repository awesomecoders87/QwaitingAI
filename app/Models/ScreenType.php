<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreenType extends Model
{
    use HasFactory;
    protected $fillable =['team_id','screen_templates_id','counter_category_id','created_at','updated_at'];
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
