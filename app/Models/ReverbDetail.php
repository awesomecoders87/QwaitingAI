<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReverbDetail extends Model
{
    use HasFactory;
    protected $table = 'reverb_details';
    protected $fillable = [ 'team_id','location_id', 'created_by', 'key', 'secret', 'app_id', 'host', 'port', 'scheme', 'created_at', 'updated_at' ];

    public function team(): BelongsTo
    {
        return $this->belongsTo( Team::class );
    }

    public static function viewReverbDetails($teamId = null, $location = null) {
        if ($teamId == null) {
            $teamId = tenant('id');
        }
    
        $query = self::where('team_id', $teamId);
    
        if (!is_null($location)) {
            $query->where('location_id', $location);
        }
    
        return $query->first(['id', 'key', 'secret', 'app_id', 'host', 'port', 'scheme']);
    }

}

