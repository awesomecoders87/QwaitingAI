<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllowedCountry extends Model
{
   use HasFactory;

    protected $fillable = ['team_id','location_id','country_id','name', 'iso_code', 'phone_code'];
}
