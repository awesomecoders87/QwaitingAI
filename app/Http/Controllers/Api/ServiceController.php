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
     * Get time slots for a service/date
     * Handles category slot levels and staff-based time slots
     */
    public function timeSlots(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'team_id'              => 'required|integer',
            'location_id'          => 'required|integer',
            'appointment_date'     => 'required|date',
            'selected_category_id' => 'required|integer',
            'second_child_id'      => 'nullable|integer',
            'third_child_id'       => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $teamId = $request->team_id;
        $locationId = $request->location_id;
        $appointmentDate = Carbon::parse($request->appointment_date);
        $selectedCategoryId = $request->selected_category_id;
        $secondChildId = $request->second_child_id;
        $thirdChildId = $request->third_child_id;

        // Fetch site setting
        $siteSetting = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();

        if (!$siteSetting) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Site setting not found for this team and location'
            ], 404);
        }

        // Determine categoryId based on category_slot_level
        $categoryId = null;
        if ($siteSetting->category_slot_level == 1 && $selectedCategoryId) {
            $categoryId = $selectedCategoryId;
        } elseif ($siteSetting->category_slot_level == 2 && $secondChildId) {
            $categoryId = $secondChildId;
        } elseif ($siteSetting->category_slot_level == 3 && $thirdChildId) {
            $categoryId = $thirdChildId;
        } else {
            $categoryId = $selectedCategoryId;
        }

        // Determine estimatecategoryId based on category_level_est
        $estimatecategoryId = null;
        if ($siteSetting->category_level_est == "parent" && $selectedCategoryId) {
            $estimatecategoryId = $selectedCategoryId;
        } elseif ($siteSetting->category_level_est == "child" && $secondChildId) {
            $estimatecategoryId = $secondChildId;
        } elseif ($siteSetting->category_level_est == "automatic" && $thirdChildId) {
            $estimatecategoryId = $thirdChildId;
        } else {
            $estimatecategoryId = $selectedCategoryId;
        }

        $slots = [];
        $disabledDate = [];

        // Check time slots based on choose_time_slot setting
        if ($siteSetting->choose_time_slot != 'staff') {
            $slots = AccountSetting::checktimeslot($teamId, $locationId, $appointmentDate, $categoryId, $siteSetting);
        } else {
            // Remove null values from category array
            $selectedCategories = array_filter([
                $selectedCategoryId ?? null,
                $secondChildId ?? null,
                $thirdChildId ?? null
            ], fn($val) => !is_null($val));

            $staffIds = User::whereHas('categories', function ($query) use ($selectedCategories) {
                $query->whereIn('categories.id', $selectedCategories);
            })->pluck('id')->toArray();

            if (!empty($staffIds)) {
                $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $appointmentDate, $estimatecategoryId, $siteSetting, $staffIds);
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No staff found for the selected categories'
                ], 404);
            }
        }

        $disabledDate = $slots['disabled_date'] ?? [];

        return response()->json([
            'status'        => 'success',
            'slots'         => $slots['start_at'] ?? [],
            'disabled_date' => $disabledDate,
            'appointment_date' => $appointmentDate->toDateString(),
            'category_id'  => $categoryId,
            'estimate_category_id' => $estimatecategoryId
        ]);
    }

    /**
     * 2️⃣ Check if a date is available for a service
     * Flow: Check service availability → Check date availability → Check time availability
     */
    public function checkDate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_id'   => 'nullable|integer',
            'service_name' => 'nullable|string',
            'team_id'      => 'required|integer',
            'location_id'  => 'required|integer',
            'date'         => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        // At least one of service_id or service_name must be provided
        if (empty($request->service_id) && empty($request->service_name)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Either service_id or service_name is required'
            ], 400);
        }

        $teamId     = $request->team_id;
        $locationId = $request->location_id;
        $date       = Carbon::parse($request->date);
        $dateString = $date->toDateString();

        // Step 1: Check if service is available
        $serviceId = null;
        $service = null;
        
        if ($request->service_id) {
            // Check by service ID
            $services = Category::getFirstCategorybooking($teamId, $locationId);
            $service = $services->firstWhere('id', $request->service_id);
            
            if (!$service) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Service not found or not available',
                    'step'    => 'service_check'
                ], 404);
            }
            
            $serviceId = $service->id;
        } else {
            // Check by service name
            $services = Category::getFirstCategorybooking($teamId, $locationId);
            $queryName = strtolower($request->service_name);
            
            $service = $services->first(function ($s) use ($queryName) {
                return strtolower($s->name) === $queryName ||
                       strtolower($s->other_name ?? '') === $queryName;
            });
            
            if (!$service) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Service not found or not available',
                    'step'    => 'service_check',
                    'available_services' => $services->map(fn($s) => [
                        'id'   => $s->id,
                        'name' => $s->name
                    ])
                ], 404);
            }
            
            $serviceId = $service->id;
        }

        // Step 2: Check if date is available
        // Fetch site setting
        $siteSetting = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();

        if (!$siteSetting) {
            return response()->json([
                'status' => 'error',
                'message' => 'Site setting not found for this team and location',
                'step'   => 'date_check'
            ], 404);
        }

        // Step 3: Check available time slots
        if ($siteSetting->choose_time_slot != 'staff') {
            $slots = AccountSetting::checktimeslot($teamId, $locationId, $date, $serviceId, $siteSetting);
        } else {
            $staffIds = User::whereHas('categories', fn($q) => $q->where('categories.id', $serviceId))
                            ->pluck('id')->toArray();

            $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $date, $serviceId, $siteSetting, $staffIds);
        }

        // Check if date has available time slots
        if (empty($slots['start_at'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Date not available for this service - no time slots available',
                'step'    => 'time_check',
                'service_id' => $serviceId,
                'date'    => $dateString,
                'available_dates' => $slots['disabled_date'] ?? []
            ], 404);
        }

        // All checks passed - service, date, and time are available
        return response()->json([
            'status'  => 'success',
            'message' => 'Service, date, and time are available',
            'service' => [
                'id'   => $serviceId,
                'name' => $service->name ?? null
            ],
            'date'    => $dateString,
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
