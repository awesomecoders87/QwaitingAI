<?php

namespace App\Observers;

use App\Models\Location;
use App\Models\User;
use App\Models\Category;
use App\Models\CustomSlot;
use App\Models\AccountSetting;
use Auth;
use DB;

class CategoryObserver
{
      /**
     * Handle the Location "creating" event.
     */
    public function created(Category $category)
    {
      $defaultBusinessHours = [
            ["day" => "Monday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Tuesday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Wednesday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Thursday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Friday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Saturday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []],
            ["day" => "Sunday", "is_closed" => "open", "start_time" => "12:00 AM", "end_time" => "11:59 PM", "day_interval" => []]
        ];

        $slottypes = ['category'];
        foreach($slottypes as $slot){
            foreach($category->category_locations as $location_id)
            AccountSetting::create([
                'team_id' => $category->team_id, // Assuming the location has a `team_id`
                'location_id' => $location_id,
                'category_id' => $category->id,
                'slot_type' => $slot, // Assuming the location has a `team_id`
                'business_hours' => json_encode($defaultBusinessHours),
                'created_by'=>Auth::id()
            ]);
        }

    }

      /**
     * Handle the Location "deleted" event.
     */
    public function deleting(Category $category)
    {
        // Delete related records before category is deleted
        DB::table('category_user')->where('category_id', $category->id)->delete();
        AccountSetting::where('category_id', $category->id)->delete();
        CustomSlot::where('category_id', $category->id)->delete();
    }

    // This event fires after the category has been deleted
    public function deleted(Category $category)
    {
        \Log::info("Category deleted: {$category->id}");
    }
    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category)
    {
        // Check if the 'location_ids' attribute was changed
        if ($category->isDirty('location_ids')) {
            // Get the original and current location IDs
            $originalLocationIds = $category->getOriginal('location_ids') ?? [];
            $currentLocationIds = $category->location_ids ?? [];

            // Determine which locations were added and which were removed
            $addedLocationIds = array_diff($currentLocationIds, $originalLocationIds);
            $removedLocationIds = array_diff($originalLocationIds, $currentLocationIds);

            // Handle added locations
            foreach ($addedLocationIds as $locationId) {
                AccountSetting::create([
                    'team_id' => $category->team_id,
                    'location_id' => $locationId,
                    'category_id' => $category->id,
                    'slot_type' => 'category',
                    'business_hours' => json_encode($this->defaultBusinessHours()),
                    'created_by' => Auth::id(),
                ]);
            }

            // Handle removed locations
            foreach ($removedLocationIds as $locationId) {
                AccountSetting::where('team_id', $category->team_id)
                    ->where('location_id', $locationId)
                    ->where('category_id', $category->id)
                    ->delete();
            }
        }
    }

    /**
     * Default business hours.
     */
    protected function defaultBusinessHours()
    {
        return [
            ["day" => "Monday", "is_closed" => "open", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Tuesday", "is_closed" => "open", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Wednesday", "is_closed" => "open", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Thursday", "is_closed" => "open", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Friday", "is_closed" => "open", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Saturday", "is_closed" => "open", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
            ["day" => "Sunday", "is_closed" => "closed", "start_time" => "09:00 AM", "end_time" => "06:00 PM", "day_interval" => []],
        ];
    }
    /**
     * Handle the Category "deleted" event.
     */
    // public function deleted(Category $category): void
    // {
    //     //
    // }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        //
    }
}
