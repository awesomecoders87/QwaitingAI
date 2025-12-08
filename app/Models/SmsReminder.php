<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsReminder extends Model
{
    use HasFactory;
    protected $fillable =['template','term','period','status','subject','team_id','created_at','updated_at'];
    const TYPE_MINUTES ='minutes';
    const TYPE_HOURS='hours';
    const TYPE_DAYS='days';
    
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }


    public static function viewSms($teamid){
        return self::where(['team_id'=>$teamid])->first();
    }
}
