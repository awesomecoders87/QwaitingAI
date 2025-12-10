<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\AccountSetting;
use App\Models\User;
use App\Models\SiteDetail;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * 1️⃣ Check if a service exists
     */
    public function checkService(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_name' => 'required|string',
            //'team_id'      => 'nullable|integer',
            //'location_id'  => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $teamId     = $request->team_id ?? 3;
        $locationId = $request->location_id ?? null;

        $services = Category::getFirstCategorybooking($teamId, $locationId);

        $queryName = strtolower($request->service_name);

        $service = $services->first(function ($s) use ($queryName) {
            return strtolower($s->name) === $queryName ||
                   strtolower($s->other_name ?? '') === $queryName;
        });

        if ($service) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Service found',
                'service' => [
                    'id'   => $service->id,
                    'name' => $service->name
                ]
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Service not found',
            'services' => $services->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name
            ])
        ]);
    }

    /**
     * 2️⃣ Check if a date is available for a service
     */
	 
	public function checkDate(Request $request)
{
    $request->validate([
        'team_id' => 'required|integer',
        'location_id' => 'required|integer',
        'appointment_date' => 'required|date',
        'selected_category_id' => 'nullable|integer',
        'second_child_id' => 'nullable|integer',
        'third_child_id' => 'nullable|integer',
    ]);
	echo 11111111111111;exit;
    $teamId = $request->team_id;
    $locationId = $request->location_id;
    $appointment_date = $request->appointment_date;
    $selectedCategoryId = $request->selected_category_id;
    $secondChildId = $request->second_child_id;
    $thirdChildId = $request->third_child_id;

    $siteSetting = SiteDetail::where('team_id', $teamId)
        ->where('location_id', $locationId)
        ->first();

    // Determine category for slots
    if ($siteSetting->category_slot_level == 1 && $selectedCategoryId) {
        $categoryId = $selectedCategoryId;
    } elseif ($siteSetting->category_slot_level == 2 && $secondChildId) {
        $categoryId = $secondChildId;
    } elseif ($siteSetting->category_slot_level == 3 && $thirdChildId) {
        $categoryId = $thirdChildId;
    } else {
        $categoryId = $selectedCategoryId;
    }

    // Determine estimate category
    if ($siteSetting->category_level_est == "parent" && $selectedCategoryId) {
        $estimatecategoryId = $selectedCategoryId;
    } elseif ($siteSetting->category_level_est == "child" && $secondChildId) {
        $estimatecategoryId = $secondChildId;
    } elseif ($siteSetting->category_level_est == "automatic" && $thirdChildId) {
        $estimatecategoryId = $thirdChildId;
    } else {
        $estimatecategoryId = $selectedCategoryId;
    }

    // Check type
    if ($siteSetting->choose_time_slot != 'staff') {
        $slots = AccountSetting::checktimeslot($teamId, $locationId, $appointment_date, $categoryId, $siteSetting);
    } else {
        $selectedCategories = array_filter([
            $selectedCategoryId,
            $secondChildId,
            $thirdChildId
        ], fn($val) => !is_null($val));

        $staffIds = User::whereHas('categories', function ($query) use ($selectedCategories) {
            $query->whereIn('categories.id', $selectedCategories);
        })->pluck('id')->toArray();

        $slots = !empty($staffIds)
            ? AccountSetting::checkStafftimeslot($teamId, $locationId, $appointment_date, $estimatecategoryId, $siteSetting, $staffIds)
            : ['start_at' => [], 'disabled_date' => []];
    }

    return response()->json([
        'status' => 'success',
        'slots' => $slots['start_at'] ?? [],
        'disabled_dates' => $slots['disabled_date'] ?? [],
    ]);
} 
	 
    public function checkDate22(Request $request)
{
    $validator = \Validator::make($request->all(), [
        'service_id'   => 'required|integer',
       // 'team_id'      => 'required|integer',
       // 'location_id'  => 'required|integer',
        'date'         => 'required|date'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => $validator->errors()->first()
        ], 400);
    }

    $teamId     = $request->team_id ?? 3;
    $locationId = $request->location_id ?? 80;

    $serviceId  = $request->service_id;
    $date       = $request->date;
	$date = Carbon::parse($dateString)->toDateString();	
    // Fetch site setting
    $siteSetting = \App\Models\SiteDetail::where('team_id', $teamId)
        ->where('location_id', $locationId)
        ->first();

    if (!$siteSetting) {
        return response()->json([
            'status' => 'error',
            'message' => 'Site setting not found for this team and location'
        ], 404);
    }

    // Check available slots
    if ($siteSetting->choose_time_slot != 'staff') {
        $slots = AccountSetting::checktimeslot($teamId, $locationId, $date, $serviceId, $siteSetting);
    } else {
        $staffIds = User::whereHas('categories', fn($q) => $q->where('categories.id', $serviceId))
                        ->pluck('id')->toArray();

        $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $date, $serviceId, $siteSetting, $staffIds);
    }

    if (empty($slots['start_at'])) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Date not available for this service',
            'available_dates' => $slots['disabled_date'] ?? []
        ]);
    }

    return response()->json([
        'status'  => 'success',
        'message' => 'Date available',
        'available_times' => $slots['start_at']
    ]);
}

    /**
     * 3️⃣ Check if a specific time is available for a service & date
     */
   /*  public function checkTime(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_id'   => 'required|integer',
           // 'team_id'      => 'required|integer',
           // 'location_id'  => 'required|integer',
            'date'         => 'required|date',
            'time'         => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $teamId     = $request->team_id;
        $locationId = $request->location_id;
        $serviceId  = $request->service_id;
        $date       = $request->date;
        $time       = $request->time;

        // Check available slots
        if (site_setting('choose_time_slot') != 'staff') {
            $slots = AccountSetting::checktimeslot($teamId, $locationId, $date, $serviceId, site_setting());
        } else {
            $staffIds = User::whereHas('categories', fn($q) => $q->where('categories.id', $serviceId))
                            ->pluck('id')->toArray();

            $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $date, $serviceId, site_setting(), $staffIds);
        }

        if (empty($slots['start_at'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No time slots available for this service & date'
            ]);
        }

        $timeExists = in_array($time, $slots['start_at']);

        if (!$timeExists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Requested time not available',
                'available_times' => $slots['start_at']
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Service, date, and time available',
            'service_id' => $serviceId,
            'date'       => $date,
            'time'       => $time
        ]);
    } */
}
