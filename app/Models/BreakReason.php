<?php

namespace App\Models;

use App\Models\QueueStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Facades\Filament;

class BreakReason extends Model
{
    use HasFactory;
    const IS_APPROVED_NO = 0;
    const IS_APPROVED_YES = 1;
    protected $fillable = [ 'reason','is_approved','team_id','break_location','break_time','created_by','created_at', 'updated_at' ]; 
   
    public function team(): BelongsTo
  {
       return $this->belongsTo( Tenant::class );
   }
   
    public static function getReasons()
    {
        return  self::where('team_id',tenant('id'))->pluck('reason', 'id')->toArray();
    } 

     
    public function setBreakLocationAttribute($value)
    {
        $this->attributes['break_location'] = json_encode($value);
    }

    // Define an accessor to decode the JSON string when retrieving from the database
    public function getBreakLocationAttribute($value)
    {
        return json_decode($value, true);
    }

  
}

