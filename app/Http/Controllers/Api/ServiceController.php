<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\AccountSetting;
use App\Models\User;
use App\Models\SiteDetail;

class ServiceController extends Controller
{
    /**
     * 1️⃣ Check if a service exists
     */
    public function checkService(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_name' => 'required|string',
            'team_id'      => 'nullable|integer',
            'location_id'  => 'nullable|integer',
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
        $validator = \Validator::make($request->all(), [
            'service_id'   => 'required|integer',
            //'team_id'      => 'required|integer',
            //'location_id'  => 'required|integer',
            'date'         => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $teamId     = $request->team_id ?? 3;
        $locationId = $request->location_id ?? null;
        $serviceId  = $request->service_id;
        $date       = $request->date;

        // Check slot type (simplified, your logic may vary)
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
    public function checkTime(Request $request)
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
    }
}
