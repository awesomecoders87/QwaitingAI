<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackSetting extends Model
{

    use HasFactory;
    protected $table ='feedback_settings';
    protected $fillable =[ 
        'team_id',
        'location_id',
        'feedback_system',
        'form_after_closedcall',
        'enable_comment_box',
        'rating_comment_box_threshold',
        'feedback_alert_enable',
        'feedback_alert_email',
        'feedback_alert_threshold',
        'enable_post_interaction',
        'trigger_method',
        'random_percent',
        'trigger_delay',
        'enable_scheduling',
        'manual_trigger',
        'rating_style',
       ];

    const STATUC_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public static function viewFeedbackSetting($teamId, $location = null)
    {
        $query = self::where('team_id', $teamId);
    
        if (!is_null($location)) {
            $query->where('location_id', $location);
        }
    
        return $query->first();
    }

    
    /**
         * Get all of the comments for the FeedbackSetting
         *
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    
}
