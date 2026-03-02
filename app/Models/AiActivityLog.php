<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiActivityLog extends Model
{
    protected $fillable = [
        'team_id',
        'location_id',
        'chatbot_name',
        'type',
        'prompt',
        'response',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'credits_consumed'
    ];

    public function team()
    {
        return $this->belongsTo(Tenant::class, 'team_id');
    }
}
