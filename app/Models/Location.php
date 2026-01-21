<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\LocationResource;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class Location extends Model
{
    use HasFactory;
    protected $fillable = [
        'location_name',
        'team_id',
        'user_id',
        'address',
        'country',
        'state',
        'city',
        'zip',
        'longitude',
        'latitude',
        'ip_address',
        'branch_image',
        'location_image',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // public function team()
    // {
    //     return $this->belongsTo(Tenant::class);
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Use the 'saving' event to set ip_address and user_id
        // static::saving(function ($model) {
        //     if (empty($model->ip_address)) {
        //         $model->ip_address = LocationResource::getUserIpAddr();
        //     }

        // Optionally, you can set user_id if it's not already set
        //     if (empty($model->user_id)) {
        //         $model->user_id = Auth::id();
        //     }
        // });
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public static function locationName($id)
    {
        return  self::where('id', $id)->value('location_name');
    }

    public static function getLocations($teamId)
    {
        return  self::where('team_id', $teamId)->where('status', 1)->pluck('location_name', 'id');
    }


    public static function adminLocation($teamId = null, $locationId = null)
    {
        return self::where('team_id', $teamId)
            ->where('id', $locationId)
            ->value('address');
    }

    public static function setConfig($teamId, $location)
    {

        Queue::timezoneSet();

        $timezone = Session::get('timezone_set') ?? 'UTC';

        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        $details = SmtpDetails::where('team_id', $teamId)->where('location_id', $location)->first();
        if (!empty($details->hostname) && !empty($details->port) &&  !empty($details->username) && !empty($details->password) && !empty($details->from_email) &&  !empty($details->from_name)) {
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', trim($details->hostname));
            Config::set('mail.mailers.smtp.port', trim($details->port));
            Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
            Config::set('mail.mailers.smtp.username', trim($details->username));
            Config::set('mail.mailers.smtp.password', trim($details->password));
            Config::set('mail.from.address', trim($details->from_email));
            Config::set('mail.from.name', trim($details->from_name));
        }
    }

    public function groups()
    {
        return $this->belongsToMany(LocationsGrouping::class, 'location_group_location');
    }

    public function siteDetail()
    {
        return $this->hasOne(SiteDetail::class, 'location_id', 'id');
    }

    public function accountSettings()
    {
        return $this->hasMany(AccountSetting::class, 'location_id', 'id');
    }

    public function paymentSetting()
    {
        return $this->hasOne(PaymentSetting::class, 'location_id', 'id');
    }
}
