<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = [
        'team_id', 'location_id','user_id','google_auth_enabled', 'verification_code', 'secretKey', 'okta_enabled', 'okta_client_id', 'okta_secret', 'okta_meta_url', 'okta_signout_url', 'office_enabled', 'office_client_id', 'office_secret', 'office_tenant_id','twillio_video_key','twillio_video_secret','twillio_video_accountSid'
    ];
}
