<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationsGrouping extends Model
{
     protected $fillable = ['team_id', 'name', 'description'];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_group_location');
    }
}
