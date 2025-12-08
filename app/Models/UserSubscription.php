<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSubscription extends Model
{
    use HasFactory;
 
   /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_subscriptions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
   /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

     /**
     * IS_ACTIVE|IN_ACTIVE
     */
    const IS_ACTIVE_SUBSCRIPTION = 1;
    const IS_CANCEL_SUBSCRIPTION = 0;

    const IS_SUBSCRIPTION_PLAN = 1;
    const IS_ONETIME_PAYMENT   = 2;


   protected $fillable = [
        'uuid',
        'user_id',
        'plan_id',
        'pi_intent_id',
        'user_name',
        'email',
        'phone_number',
        'stripe_user_id',
        'start_date',
        'end_date',
        'current_status',
        'subscription_id',
        'customer_id',
        'plan_amount',
        'created_at',
        'updated_at',
        'deleted_at'
   ];

     /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted() {
          static::creating(function ($model) {
          $model->uuid = Str::uuid()->toString();
          });
     }

     /**
     * 
     * @return type 
     *  listing order By Desc
     */
    public function stripePlan() {
     return $this->hasOne(Plan::class, 'id', 'plan_id');
 }
}
