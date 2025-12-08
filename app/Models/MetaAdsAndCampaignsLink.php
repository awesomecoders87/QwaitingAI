<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MetaAdsAndCampaignsLink extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'location_id', 'type', 'source', 'medium', 'campaign', 'generated_link'];

}