<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    use HasFactory;
 
   /**
    * The table associated with the model.
    *
    * @var string
    */
   protected $table      = "plans";
   protected $primaryKey = 'id';
 
   /**
     * IS_ACTIVE|IN_ACTIVE
     */
    const IS_ACTIVE = 1;
    const IN_ACTIVE = 0;
   /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = [
        'uuid',
        'stripe_plan_id',
        'plan_name',
        'plan_price',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
   ];

   /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeActive($query) {
        $query->where('plans.status', static::IS_ACTIVE);
    }

}
