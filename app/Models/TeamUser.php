<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamUser extends Model
{
    use HasFactory;
    protected $table ='team_user';

    protected $fillable = [
        'user_id',
        'team_id',
        'created_at',
        'updated_at'
    ];


    public function users(){
        return $this->belongsTo(User::class);
    }
    public function teams(){
        return $this->belongsToMany(Team::class);
    }
    
}
