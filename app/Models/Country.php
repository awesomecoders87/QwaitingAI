<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
Use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'phonecode'
    ];
    public function states(): HasMany 
    {
        return $this->hasMany(State::class);
    }
    public function employees(): HasMany 
    {
        return $this->hasMany(Employee::class);
    }
    public function partners()
    {
        return $this->hasMany(User::class, 'country', 'id')->whereNotNull('created_by');
    }
    public function teams()
    {
        return $this->hasMany(User::class, 'country', 'id')->whereNotNull('team_user_id');
    }
}
