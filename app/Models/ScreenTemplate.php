<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ScreenTemplate extends Model
 {
    use HasFactory;

    protected $fillable = [ 'team_id', 'location_id','name', 'type', 'json', 'template', 'background_color', 'font_color', 'is_datetime_show', 'datetime_position', 'datetime_bg_color', 'datetime_font_color', 'waiting_queue_bg', 'missed_queue_bg', 'hold_queue_bg', 'is_powered_by', 'powered_image', 'is_logo', 'is_image', 'is_video', 'is_waiting', 'is_date_time','is_hold_queue','is_missed_queue', 'show_queue_number', 'font_size', 'missed_queue','hold_queue', 'counter_title', 'token_title', 'is_header_show', 'current_serving_fontcolor', 'json_data', 'template_class', 'is_disclaimer','created_at', 'updated_at', 'is_name_on_display_screen_show', 'is_skip_call_show', 'is_waiting_call_show', 'is_skip_closed_call_from_display_screen', 'display_screen_disclaimer', 'waiting_queue','disclaimer_title','display_screen_tune','is_default_screen','display_behavior','is_location','location_fontcolor','location_bg'];

    const TEMPLATE_KEY_IMAGES = 'images';
    const TEMPLATE_KEY_VIDEO = 'videos';
    const TEMPLATE_KEY_IMAGESVIDEO = 'videos_images';
    const POSITION_LEFT = 'left';
    const POSITION_RIGHT = 'right';
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const FONT_DEFAULT_COLOR = '#ffffff';
    const DATETIME_BG_COLOR = '#120202';

    protected $casts = [
        'json' => 'array',
        'json_data' => 'array',
        'is_datetime_show'=>'boolean',
        'is_powered_by'=>'boolean',
    ];

    public function team(): BelongsTo
   {
        return $this->belongsTo( Tenant::class );
    }
    public function location(): BelongsTo
   {
        return $this->belongsTo( Location::class );
    }

    public function getTemplateNameAttribute()
    {
        return static::getTemplates( $this->template );
    }

    public static function getTemplates( $key = null ) {
        $templates = [
            'videos' => 'Videos + Queue',
            'images' => 'Images Slider + Queue',
            'videos_images' => 'Videos + Images Slider + Queue',
            't7' => 'Default (top to bottom)',
            'locally' => 'Local Videos',
            'single_counter' => 'Single Counter',
        ];

        if ( $key !== null && array_key_exists( $key, $templates ) ) {
            return $templates[ $key ];
        }

        return $templates;
    }
    public static function getTemplatesClass( $key = null ) {
        $templates = [
            'videos' => 'video-with-queue',
            'images' => 'slider-with-queue',
            'videos_images' => 'slider-with-video-with-queue',
            't7' => '',
             'locally' => 'video-with-queue',
             'single_counter' => 'single-counter',
        ];

        if ( $key !== null && array_key_exists( $key, $templates ) ) {
            return $templates[ $key ];
        }

        return $templates;
    }

    public function categories(): BelongsToMany
 {
        return $this->belongsToMany( Category::class );
    }

    public function counters(): BelongsToMany
 {
        return $this->belongsToMany( Counter::class );
    }

    public static function showQueueNumber()
 {
        return [
            '2' => '2' . __( ' Queue' ),
            '4' => '4' . __( ' Queue' ),
            '6' => '6' . __( ' Queue' ),
            '8' => '8' . __( ' Queue' ),
            '10' => '10' . __( ' Queue' ),
            '12' => '12' . __( ' Queue' ),
            '14' => '14' . __( ' Queue' ),
            '16' => '16' . __( ' Queue' ),
            '18' => '18' . __( ' Queue' ),
            '20' => '20' . __( ' Queue' ),
        ];
    }

    public static function getPosition() {
        return [
            'left'=>__( 'Left' ),
            'right'=>__( 'Right' )
        ];
    }
    public static function viewDetails($teamID, $ID, $location = null) {
        $query = self::with('counters')
            ->where([
                'id' => $ID,
                'team_id' => $teamID
            ]);

        if (!is_null($location)) {
            $query->where('location_id', $location);
        }

        return $query->first();
    }
    public static function getFontSize() {
        return [
            '' => 'Select',
            '1' => '1' . __(' VH'),
            '2' => '2' . __(' VH'),
            '3' => '3' . __(' VH'),
            '4' => '4' . __(' VH'),
            '5' => '5' . __(' VH'),
            '6' => '6' . __(' VH'),
            '7' => '7' . __(' VH'),
            '8' => '8' . __(' VH'),
            '9' => '9' . __(' VH'),
            '10' => '10' . __(' VH'),
            '11' => '11' . __(' VH'),
            '12' => '12' . __(' VH'),
            '13' => '13' . __(' VH'),
            '14' => '14' . __(' VH'),
            '15' => '15' . __(' VH'),
            '16' => '16' . __(' VH'),
            '17' => '17' . __(' VH'),
            '18' => '18' . __(' VH'),
            '19' => '19' . __(' VH'),
            '20' => '20' . __(' VH'),
        ];
    }
}
