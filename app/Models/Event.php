<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
class Event extends Model
{
    use HasFactory;

    protected $fillable = ['team_id','category_id','title','day','start','end','location_id'];
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public static function getEventsByDate($selectedDate, $team_id, $selectedCategoryId, $location = null)
    {
        $query = self::where([
            'team_id' => $team_id,
            'category_id' => $selectedCategoryId,
        ])
        ->whereDate('start', '<=', $selectedDate)
        ->whereDate('end', '>=', $selectedDate);

        if ($location !== null) {
            $query->where('location_id', $location);
        }

        return $query->get();
    }

    public static function formaDateTime($dateTime){
        $carbonInstance = Carbon::parse($dateTime);
        return $carbonInstance->format('H:i:s');
   }

}
