<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageSetting extends Model
{
    protected $fillable = [
        'team_id',
        'location_id',
        'enabled_language_settings',
        'available_languages',
        'default_language',
    ];

    protected $casts = [
        'available_languages' => 'array',
        'enabled_language_settings' => 'boolean',
    ];
}