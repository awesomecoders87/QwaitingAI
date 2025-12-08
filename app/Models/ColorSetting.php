<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'page_layout',
        'categories_background_layout',
        'text_layout',
        'hover_background_layout',
        'hover_text_layout',
        'hover_button_layout',
        'buttons_layout',
        'theme_color',
        'font_color',
        'button_color',
        'mobile_header_background_color',
        'mobile_heading_text_color',
        'mobile_button_text_color',
        'mobile_page_layout',
        'mobile_button_color',
        'mobile_font_color',
        'mobile_category_button_color',
        'total_served_queue_color',
        'total_served_queue_text_color',
        'transfer_queue_color',
        'transfer_queue_text_color',
        'hold_queue_color',
        'hold_queue_text_color',
        'missed_queue_color',
        'missed_queue_text_color',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
