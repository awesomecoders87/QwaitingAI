<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counter extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $dates = ['deleted_at'];
    protected $fillable =['name','team_id','show_checkbox','counter_locations','deleted_at','created_at','updated_at'];
    const STATUS_ACTIVE = 1;
    protected $casts = [
        'show_checkbox' => 'boolean',
    ];

        public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }


    public function setCounterLocationsAttribute($value)
    {
        $this->attributes['counter_locations'] = json_encode($value);
    }

    // Define an accessor to decode the JSON string when retrieving from the database
    public function getCounterLocationsAttribute($value)
    {
        return json_decode($value, true);
    }


    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
    public function queues(){
        return $this->hasMany(QueueStorage::class,'counter_id','id');
    }
    public function screenTemplate()
    {
        return $this->belongsToMany(ScreenTemplate::class);
    }

    public static function getCounter($teamId, $counterOption, $userAuth, $location = null)
    {
        $condition = ['team_id' => $teamId, 'show_checkbox' => self::STATUS_ACTIVE];

        if ($userAuth->hasRole(User::ROLE_ADMIN) &&  $userAuth->is_admin == 1) {
            $query = self::where($condition)->whereJsonContains('counter_locations', "$location");
            return $query->get();
        }else{
            if ($counterOption == self::STATUS_ACTIVE) {
                // return self::where($condition)->whereJsonContains('counter_locations', "$location")->get();

                return  self::where($condition)
                ->whereJsonContains('counter_locations', "$location") ->whereIn('id', array_filter(array_merge([$userAuth->counter_id], $userAuth->assign_counters ?? [])))
                ->get();


            }

            $query = self::where($condition)->whereJsonContains('counter_locations', "$location");

            return $query->where('id', $userAuth->counter_id)->get();
        }

        // if ($counterOption == self::STATUS_ACTIVE) {
        //     return self::where($condition)->get();
        // } else {
        //     return self::where(array_merge($condition, ['id' => $userAuth->counter_id]))->get();
        // }
    }

   public static function getAssignedCounter($teamId, $counterOption=true, $userAuth, $location = null,$loginCounter = false)
    {
        $condition = ['team_id' => $teamId, 'show_checkbox' => self::STATUS_ACTIVE];

        if (($userAuth->hasRole(User::ROLE_ADMIN) &&  $userAuth->is_admin == 1) || !$loginCounter) {
            $query = self::where($condition)->whereJsonContains('counter_locations', "$location");
            return $query->select('id','name')->get();
        }else {
        // When loginCounter = true → get all counters from users who are logged in
        if ($loginCounter) {
            $loginUsers = User::where('team_id', $teamId)
                ->where('is_login', 1)
                ->get(['counter_id', 'assign_counters']);

            $allCounters = [];

            foreach ($loginUsers as $u) {
                $assignedCounters = is_array($u->assign_counters)
                    ? $u->assign_counters
                    : json_decode($u->assign_counters ?? '[]', true);

                $allCounters = array_merge(
                    $allCounters,
                    [$u->counter_id],
                    $assignedCounters ?? []
                );
            }

            // Remove duplicates + normalize to integers
            $allCounters = array_values(array_unique(array_map('intval', $allCounters)));

            return self::where($condition)
                ->whereJsonContains('counter_locations', (string) $location)
                ->whereIn('id', $allCounters)
                ->select('id', 'name')
                ->get();
        }
     }
    }

    public static function counterName($counterId)
    {
        return self::where('id', $counterId)->value('name');
    }
	
	public function queueStorages()
    {
        return $this->hasMany(QueueStorage::class, 'counter_id', 'id');
    }
}
