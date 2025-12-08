<?php

namespace App\Models;

use Stripe\Stripe;
use App\Models\Role;
use Stripe\Customer;
use App\Models\Subscription;
use Laravel\Cashier\Billable;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;
    // use Billable;
    use SoftDeletes;
 

    protected $fillable = ['name','slug','stripe_id','pm_type','pm_last_four','trial_ends_at','status','expired', 'created_by'];
    protected static function getModel(): string
    {
        return self::class;
    }
  protected $with = ['members'];
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
    public function pusherDetails(): HasMany{
        return $this->hasMany(PusherDetail::class);
    }
    // public function locations(): HasMany{
    //     return $this->hasMany(Location::class);
    // }
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

    public function getJudgeNameAttribute()
{
     $teamId = $this->id; // Replace with the actual ID of the team

    $admins = Team::where('id', $teamId)
        ->with(['members' => function ($query) {
            $query->role('admin'); // Filter users with the 'admin' role
        }])
        ->first();

    if ($admins) {
        $adminNames = $admins->members->value('name');
        // Use $adminNames as needed (it will contain names of admin users in the team)
        return $adminNames;
    } else {
        echo "";
    }
}
    public function getEmailAttribute()
{
     $teamId = $this->id; // Replace with the actual ID of the team

    $admins = Team::where('id', $teamId)
        ->with(['members' => function ($query) {
            $query->role('admin'); // Filter users with the 'admin' role
        }])
        ->first();

    if ($admins) {
        $adminNames = $admins->members->value('email');
        // Use $adminNames as needed (it will contain names of admin users in the team)
        return $adminNames;
    } else {
        echo "";
    }
}
    public function getCountryAttribute()
{
     $teamId = $this->id; // Replace with the actual ID of the team

    $admins = Team::where('id', $teamId)
        ->with(['members' => function ($query) {
            $query->role('admin'); // Filter users with the 'admin' role
        }])
        ->first();

    if ($admins) {
        $countryId = $admins->members->value('country');
        // Use $adminNames as needed (it will contain names of admin users in the team)
        $admincountry='';
        if(!empty($countryId)){

            $admincountry= Country::where('id',$countryId)->value('name');
        }
        return $admincountry;
    } else {
        echo "";
    }
}
    public static function getSubscriptionAttribute($team)
{
    //  $teamId = $id; // Replace with the actual ID of the team

    // $admins = Team::where('id', $teamId)
    //     ->with(['members' => function ($query) {
    //         $query->role('Admin'); // Filter users with the 'admin' role
    //     }])
    //     ->first();

    // if ($admins) {
    //     $subscription = Subscription::where('team_id',$teamId)->latest()->value('stripe_status');
    //     // Use $adminNames as needed (it will contain names of admin users in the team)
    //     return ucwords($subscription);
    // } else {
    //     echo "";
    // }
}
//     public static function getExpiredAtAttribute()
// {
//     Stripe::setApiKey(env('STRIPE_SECRET'));
//      $teamId = $this->id; // Replace with the actual ID of the team

//     $admins = Team::where('id', $teamId)
//         ->with(['members' => function ($query) {
//             $query->role('Admin'); // Filter users with the 'admin' role
//         }])
//         ->first();

//     if ($admins) {
//         // $subscription = Subscription::where('team_id',$teamId)->latest()->first();
    
//         if ($admins->subscription('default')) {
//             if($admins->subscription('default')->trial_ends_at){

//                 $expiryDate = $admins->subscription('default')->trial_ends_at->format('Y-m-d'); // Access the 'ends_at' attribute for the expiry date
//             }else{
//                 $expiryDate = '';
//             }
//             // $customer = Customer::retrieve('cus_QPCQSF2m78KR1b')->current_period_end;
//         //     if(isset($customer)){
//         //         $expiryDate =$customer;
//         //     }else{
//         //         $expiryDate = '';
//         //     }
//         //    // Access the 'ends_at' attribute for the expiry date
//         } else {
//             $expiryDate = '';
//         }
        
//         return $expiryDate;
   
// }
// }
    public static function memberDetail(){
        $teamId = tenant('id'); // Replace with the actual ID of the team

        $admins = Team::where('id', $teamId)
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

    public static function getSlug(){
        if (isset($_SERVER['REQUEST_URI'])) {
            $currentUrl = url($_SERVER['REQUEST_URI']);
            $parsedUrl = parse_url($currentUrl);
            $hostParts = explode('.', $parsedUrl['host']);
            return $hostParts[0];
        } else {
            return '';
        }
    }

    public static function viewTeam($teamID){
        return self::find($teamID);
    }

    public function rooms(): hasMany
    {
        return $this->hasMany(Room::class);
    }

}
