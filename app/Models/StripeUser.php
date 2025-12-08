<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StripeUser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stripe_users';

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
    protected $fillable = [
        'uuid',
        'user_id',
        'stripe_customer_id',
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
    public function activeSubscription() {
        return $this->hasOne(UserSubscription::class, 'stripe_user_id', 'id')->where(['current_status'=>UserSubscription::IS_ACTIVE_SUBSCRIPTION,'payment_type'=>UserSubscription::IS_SUBSCRIPTION_PLAN]);
    }

}
