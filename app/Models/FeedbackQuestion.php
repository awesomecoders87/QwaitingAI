<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackQuestion extends Model
{

    use HasFactory;
    protected $table ='feedback_questions';
    protected $fillable =['questions','team_id','location_id','created_at','updated_at'];
    const STATUC_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    protected $casts = [
        'questions' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
 

    
}
