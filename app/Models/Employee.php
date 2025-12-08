<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'address',
        'zip_code',
        'street',
        'date_of_birth',
        'date_hired',
        'department_id',
        'city_id',
        'state_id',
        'country_id',
    ];

   
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
    
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
