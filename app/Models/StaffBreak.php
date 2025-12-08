<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Auth;

class StaffBreak extends Model
{

    use HasFactory;
    protected $table="staff_daily_breaks";
    protected $fillable =['user_id','team_id','location_id','reason','comment','time_start','time_end','break_status','approved_by','status','breakreason_id','approved_at','created_at','updated_at'];
   

   const STATUS_YES = 1;
   const STATUS_NO = 0;


   const STATUS_PENDING = 0;
   const STATUS_APPROVED = 1;
   const STATUS_REJECTED = 2;
   const STATUS_AUTO_APPROVAL = 3;

   const LABEL_YES = 'Yes';
   const LABEL_NO ='No';

   public static function getReasons(){
    return[
        'Restroom'=>'Restroom',
        'Meal'=>'Meal',
        'Training'=>'Training',
        'Coaching'=>'Coaching',
        'Meeting'=>'Meeting',
        'Unplanned'=>'Unplanned',
    ];
   }

   public function team(): BelongsTo
   {
        return $this->belongsTo( Tenant::class );
    }
   public static function viewEmptyTimeEnd($userId){
       return self::where(['user_id'=>$userId])->whereIn('status',[self::STATUS_AUTO_APPROVAL,self::STATUS_APPROVED])->whereNull('time_end')->latest()->first();
   }
  
   public static function storeStaffBreak($data){
    return self::create($data);
   } 
   public function staff(){
    return $this->belongsTo(User::class,'user_id','id' );

   }
   }
