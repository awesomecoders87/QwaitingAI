<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlackSetting extends Model
{
    use HasFactory;

    protected $table = 'slack_settings';

    protected $fillable = [
        'team_id',
        'location_id',
        'slack_user_auth_token',
        'slack_user_bot_auth_token',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
