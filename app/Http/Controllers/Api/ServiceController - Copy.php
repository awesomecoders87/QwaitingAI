<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\AccountSetting;
use App\Models\User;
use App\Models\SiteDetail;
use App\Models\Booking;
use App\Models\ServiceSetting;
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
     * Optionally checks if a specific time is available
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
            'time'                 => 'nullable|string', // Optional time to check availability
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
        $availableSlots = $slots['start_at'] ?? [];

        // If time is provided, check if that specific time is available
        if ($request->has('time') && !empty($request->time)) {
            $requestedTime = trim($request->time);
            
            // Normalize time format for comparison (handle different formats like "09:00 AM", "09:00", etc.)
            $timeExists = false;
            $normalizedRequestedTime = $this->normalizeTime($requestedTime);
            
            foreach ($availableSlots as $slot) {
                // Handle slot format "09:00 AM-10:00 AM" by extracting start time
                $slotStartTime = $slot;
                if (strpos($slot, '-') !== false) {
                    [$slotStartTime, $slotEndTime] = explode('-', $slot, 2);
                    $slotStartTime = trim($slotStartTime);
                }
                
                $normalizedSlot = $this->normalizeTime($slotStartTime);
                if ($normalizedSlot === $normalizedRequestedTime) {
                    $timeExists = true;
                    break;
                }
            }

            if (!$timeExists) {
                return response()->json([
                    'status'        => 'error',
                    'message'       => 'Requested time is not available',
                    'requested_time' => $requestedTime,
                    'available_times' => $availableSlots,
                    'slots'         => $availableSlots,
                    'disabled_date' => $disabledDate,
                    'appointment_date' => $appointmentDate->toDateString(),
                    'category_id'  => $categoryId,
                    'estimate_category_id' => $estimatecategoryId
                ], 404);
            }

            // Time is available
            return response()->json([
                'status'        => 'success',
                'message'       => 'Time is available',
                'requested_time' => $requestedTime,
                'time_available' => true,
                'slots'         => $availableSlots,
                'disabled_date' => $disabledDate,
                'appointment_date' => $appointmentDate->toDateString(),
                'category_id'  => $categoryId,
                'estimate_category_id' => $estimatecategoryId
            ]);
        }

        // Return all available slots if no specific time is requested
        return response()->json([
            'status'        => 'success',
            'slots'         => $availableSlots,
            'disabled_date' => $disabledDate,
            'appointment_date' => $appointmentDate->toDateString(),
            'category_id'  => $categoryId,
            'estimate_category_id' => $estimatecategoryId
        ]);
    }

    /**
     * Normalize time format for comparison
     * Handles various time formats like "09:00 AM", "9:00 AM", "09:00", etc.
     * Returns standardized format: "09:00 AM" (uppercase)
     */
    private function normalizeTime($time)
    {
        // Remove extra spaces
        $time = trim($time);
        
        if (empty($time)) {
            return $time;
        }
        
        // Try to parse with Carbon to standardize format
        try {
            // Try parsing as time with AM/PM (case insensitive)
            $parsed = Carbon::createFromFormat('h:i A', $time);
            return strtoupper($parsed->format('h:i A'));
        } catch (\Exception $e) {
            try {
                // Try parsing as 24-hour format
                $parsed = Carbon::createFromFormat('H:i', $time);
                return strtoupper($parsed->format('h:i A'));
            } catch (\Exception $e2) {
                try {
                    // Try parsing with lowercase am/pm
                    $parsed = Carbon::createFromFormat('h:i a', $time);
                    return strtoupper($parsed->format('h:i A'));
                } catch (\Exception $e3) {
                    // If parsing fails, return uppercase version (might already be in correct format)
                    return strtoupper($time);
                }
            }
        }
    }

    /**
     * Comprehensive API: Check service, date, time availability and book appointment
     * Single endpoint that handles:
     * 1. Service name validation
     * 2. Date availability check
     * 3. Time availability check
     * 4. Appointment booking if all checks pass
     */
    public function checkAndBook(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_name'      => 'required|string',
            'team_id'          => 'required|integer',
            'location_id'      => 'required|integer',
            'date'             => 'nullable|date',
            'time'             => 'nullable|string',
            'name'             => 'nullable|string',
            'phone'            => 'nullable|string',
            'email'            => 'nullable|email',
            'phone_code'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $teamId = $request->team_id;
        $locationId = $request->location_id;
        $serviceName = trim($request->service_name);

        // Step 1: Check if service exists
        $services = Category::getFirstCategorybooking($teamId, $locationId);
        $queryName = strtolower($serviceName);

        $service = $services->first(function ($s) use ($queryName) {
            return strtolower($s->name) === $queryName ||
                   strtolower($s->other_name ?? '') === $queryName;
        });

        if (!$service) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Service not found',
                'step'    => 'service_check',
                'available_services' => $services->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name
                ])->values()
            ], 404);
        }

        $serviceId = $service->id;

        // If only service name is provided (without date/time), return success
        if (empty($request->date) && empty($request->time)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Service found',
                'service' => [
                    'id'   => $service->id,
                    'name' => $service->name
                ]
            ]);
        }

        // Step 2: Check date availability (if date is provided)
        if (empty($request->date)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Date is required for booking'
            ], 400);
        }

        $appointmentDate = Carbon::parse($request->date);
        $dateString = $appointmentDate->toDateString();

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

        // Get booking settings
        $bookingSetting = AccountSetting::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->first();

        // Check available slots for the date
        if ($siteSetting->choose_time_slot != 'staff') {
            $slots = AccountSetting::checktimeslot($teamId, $locationId, $appointmentDate, $serviceId, $siteSetting);
        } else {
            $staffIds = User::whereHas('categories', fn($q) => $q->where('categories.id', $serviceId))
                            ->pluck('id')->toArray();

            if (empty($staffIds)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No staff available for this service',
                    'step'    => 'date_check'
                ], 404);
            }

            $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $appointmentDate, $serviceId, $siteSetting, $staffIds);
        }

        $availableSlots = $slots['start_at'] ?? [];
        $disabledDates = $slots['disabled_date'] ?? [];

        // Check if date has available time slots
        if (empty($availableSlots)) {
            // Get available dates for next week
            $availableDates = $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting);

            return response()->json([
                'status'  => 'error',
                'message' => 'Date not available for this service - no time slots available',
                'step'    => 'date_check',
                'requested_date' => $dateString,
                'available_dates' => $availableDates
            ], 404);
        }

        // Step 3: Check time availability (if time is provided)
        if (empty($request->time)) {
            // Return available time slots for the date
            return response()->json([
                'status'  => 'success',
                'message' => 'Date is available',
                'service' => [
                    'id'   => $service->id,
                    'name' => $service->name
                ],
                'date'    => $dateString,
                'available_times' => $availableSlots
            ]);
        }

        $requestedTime = trim($request->time);
        $normalizedRequestedTime = $this->normalizeTime($requestedTime);
        $timeExists = false;
        $matchedSlot = null;

        foreach ($availableSlots as $slot) {
            // Handle slot format "09:00 AM-10:00 AM" by extracting start time
            $slotStartTime = $slot;
            if (strpos($slot, '-') !== false) {
                [$slotStartTime, $slotEndTime] = explode('-', $slot, 2);
                $slotStartTime = trim($slotStartTime);
            }

            $normalizedSlot = $this->normalizeTime($slotStartTime);
            if ($normalizedSlot === $normalizedRequestedTime) {
                $timeExists = true;
                $matchedSlot = $slot;
                break;
            }
        }

        if (!$timeExists) {
            // Return available time slots for the same day
            return response()->json([
                'status'  => 'error',
                'message' => 'Requested time is not available for this date',
                'step'    => 'time_check',
                'requested_time' => $requestedTime,
                'date'    => $dateString,
                'available_times' => $availableSlots
            ], 404);
        }

        // Step 4: All checks passed - Book the appointment
        try {
            // Calculate end time
            $startTime = $normalizedRequestedTime;
            $endTime = $this->calculateEndTime($startTime, $service, $bookingSetting, $siteSetting);

            // Create booking
            $booking = Booking::create([
                'team_id' => $teamId,
                'booking_date' => $dateString,
                'booking_time' => $startTime . '-' . $endTime,
                'name' => $request->name ?? '',
                'phone' => $request->phone ?? '',
                'phone_code' => $request->phone_code ?? '91',
                'email' => $request->email ?? '',
                'category_id' => $serviceId,
                'sub_category_id' => null,
                'child_category_id' => null,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'location_id' => $locationId,
                'status' => Booking::STATUS_CONFIRMED,
                'refID' => time() . rand(1000, 9999)
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Appointment booked successfully',
                'booking' => [
                    'id' => $booking->id,
                    'ref_id' => $booking->refID,
                    'service' => [
                        'id'   => $service->id,
                        'name' => $service->name
                    ],
                    'date' => $dateString,
                    'time' => $startTime . '-' . $endTime,
                    'status' => $booking->status
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to book appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available dates for next week
     */
    private function getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting)
    {
        $availableDates = [];
        $startDate = Carbon::now()->addDay();
        $endDate = Carbon::now()->addWeek();

        $advanceDays = $bookingSetting->allow_req_before ?? 30;
        $getAdvanceBookingDates = AccountSetting::datesGet($advanceDays);

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();

            // Check if date is in advance booking range
            if (!in_array($date->format('d-m-Y'), $getAdvanceBookingDates)) {
                continue;
            }

            // Check available slots for this date
            if ($siteSetting->choose_time_slot != 'staff') {
                $slots = AccountSetting::checktimeslot($teamId, $locationId, $date, $serviceId, $siteSetting);
            } else {
                $staffIds = User::whereHas('categories', fn($q) => $q->where('categories.id', $serviceId))
                                ->pluck('id')->toArray();

                if (!empty($staffIds)) {
                    $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $date, $serviceId, $siteSetting, $staffIds);
                } else {
                    continue;
                }
            }

            $availableSlots = $slots['start_at'] ?? [];
            if (!empty($availableSlots)) {
                $availableDates[] = $dateString;
            }
        }

        return $availableDates;
    }

    /**
     * Calculate end time from start time
     */
    private function calculateEndTime($startTime, $service, $bookingSetting, $siteSetting)
    {
        try {
            $start = Carbon::createFromFormat('h:i A', $startTime);

            // Try to get slot period from booking setting
            $slotPeriod = $bookingSetting->slot_period ?? null;

            // If not available, try to get from service time
            if (!$slotPeriod && $service->service_time) {
                $slotPeriod = $service->service_time;
            }

            // Default to 30 minutes if nothing is found
            if (!$slotPeriod) {
                $slotPeriod = 30;
            }

            $end = $start->copy()->addMinutes($slotPeriod);
            return $end->format('h:i A');
        } catch (\Exception $e) {
            // Fallback: add 30 minutes
            try {
                $start = Carbon::createFromFormat('h:i A', $startTime);
                return $start->addMinutes(30)->format('h:i A');
            } catch (\Exception $e2) {
                // If all fails, return a default end time
                return '10:00 AM';
            }
        }
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

    
}
