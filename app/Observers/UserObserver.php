<?php

namespace App\Observers;

use App\Models\{User,TeamUser,AccountSetting,CustomSlot};
use Auth;
class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    // public function created(User $user): void
    // {
    //     if (Auth::check()) {
    //         $userAuth = Auth::user();
    //         if ($userAuth->hasRole(User::ROLE_ADMIN)) {
    //            $userTeam = new TeamUser();
    //            $userTeam->user_id =$user->id;
    //            $userTeam->team_id = $userAuth?->teams?->first()?->id;
    //            $userTeam->save();

    //            $user->team_id = null;
    //            $user->save();

    //         }
    //     }
    // }

    public function created(User $user)
    {
        //     if (Auth::check()) {
        //     $userAuth = Auth::user();
        //     if ($userAuth->hasRole(User::ROLE_ADMIN)) {
        //        $userTeam = new TeamUser();
        //        $userTeam->user_id =$user->id;
        //        $userTeam->team_id = $userAuth?->teams?->first()?->id;
        //        $userTeam->save();
        //        $user->team_id = null;
        //        $user->save();

        //     }
        // }
       $defaultBusinessHours = [
            ["day" => "Monday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Tuesday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Wednesday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Thursday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Friday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Saturday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Sunday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []]
        ];

        $slottypes = ['staff'];

        foreach($slottypes as $slot){
            if(!empty($user->locations)){
            foreach($user->locations as $location_id)
            AccountSetting::create([
                'team_id' => $user->team_id, // Assuming the location has a `team_id`
                'location_id' => $location_id,
                'user_id' => $user->id,
                'slot_type' => $slot, // Assuming the location has a `team_id`
                'business_hours' => json_encode($defaultBusinessHours),
                'created_by'=>Auth::id()
            ]);
        }
    }


    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        AccountSetting::where('user_id', $user->id)->where('slot_type',AccountSetting::STAFF_SLOT)->delete();
        CustomSlot::where('user_id', $user->id)->where('slots_type',AccountSetting::STAFF_SLOT)->delete();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
