<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Use an incrementing integer ID instead of UUID.
     */
    public function getIncrementing()
    {
        return true;
    }

    // /**
    //  * Define the primary key type as integer.
    //  */
    protected $keyType = 'int';

    public function domains()
    {
        return $this->hasMany(Domain::class, 'team_id');
    }

    protected static function getModel(): string
    {
        return self::class;
    }

//   protected $with = ['members'];
    public function members(): BelongsToMany{
        return $this->belongsToMany(User::class);
    }

    public function subcriptions(): BelongsToMany{
        return $this->belongsToMany(User::class);
    }
    // public function teamUser(): BelongsToMany{
    //     return $this->hasMany(TeamUser::class);
    // }
   
    public function counters(): HasMany{
        return $this->hasMany(Counter::class);
    }
 
    public function levels(): HasMany{
        return $this->hasMany(Level::class);
    }
    public function categories(): HasMany{
        return $this->hasMany(Level::class);
    }
    public function events(): HasMany{
        return $this->hasMany(Event::class);
    }
    
    public function users(): HasMany{
        return $this->hasMany(User::class);
    }

    public function roles(): HasMany{
        return $this->hasMany(Role::class);
    }
    
    public function permissions(): HasMany{
        return $this->hasMany(Permission::class);
    }
    public function rolepermissions(): HasMany{
        return $this->hasMany(RolePermission::class);
    }
    public function bookings(): HasMany{
        return $this->hasMany(Booking::class);
    }
    public function smsReminders(): HasMany{
        return $this->hasMany(SmsReminder::class);
    }
    public function screenTemplates(): HasMany{
        return $this->hasMany(ScreenTemplate::class);
    }

    public function screenType(): HasMany{
        return $this->hasMany(ScreenType::class);
    }
    public function formFields(): HasMany{
        return $this->hasMany(FormField::class);
    }

    public function termsConditions()
    {
        return $this->hasOne(TermsCondition::class);
    }
    // public function siteDetails(): HasMany{
    //     return $this->hasMany(SiteDetail::class);
    // }
    public function siteDetails(): HasOne{
        return $this->hasOne(SiteDetail::class);
    }
    public function smsApi(): HasMany{
        return $this->hasMany(SmsApi::class);
    }
    public function smtpDetails(): HasMany{
        return $this->hasMany(SmtpDetails::class);
    }
    public function reverbDetails(): HasMany{
        return $this->hasMany(ReverbDetail::class);
    }
    public function locations(): HasMany{
        return $this->hasMany(Location::class);
    }
    public function generateQrCode(): HasMany{
        return $this->hasMany(GenerateQrCode::class);
    }
    public function ActivityLogs(): HasMany{
        return $this->hasMany(ActivityLog::class);
    }
    public function  feedbackSetting(): HasMany{
        return $this->hasMany(FeedbackSetting::class);
    }
    public function  feedbackQuestion(): HasMany{
        return $this->hasMany(FeedbackQuestion::class);
    }
    public function  rating(): HasMany{
        return $this->hasMany(Rating::class);
    }
    public function  queueStorage(): HasMany{
        return $this->hasMany(QueueStorage::class);
    }
  
    public static function getTeamId($slug){
        return self::where('slug',$slug)->first('id')?->id;
    }
    public function  breakReasons(): HasMany{
        return $this->hasMany(BreakReason::class);
    }
    public function  staffBreak(): HasMany{
        return $this->hasMany(StaffBreak::class);
    }

    public static function memberDetail(){
        $teamId = tenant('id'); // Replace with the actual ID of the team

        $admins = Tenant::where('id', $teamId)
            ->with(['members' => function ($query) {
                $query->role('admin'); // Filter users with the 'admin' role
            }])
            ->first();

        if ($admins) {
            $adminNames = $admins->members->pluck('name')->toArray();
            // Use $adminNames as needed (it will contain names of admin users in the team)
            return implode(" ", $adminNames);
        } else {
            echo "";
        }
    }

}
