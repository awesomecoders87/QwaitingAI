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
use App\Services\OpenAIService;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

            // Ensure slotPeriod is numeric (cast to int/float)
            $slotPeriod = is_numeric($slotPeriod) ? (float)$slotPeriod : 30;

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
     * Chatbot API: Parse natural language and book appointment
     * Comprehensive implementation handling all edge cases
     * Example: "I want to book appointment for dental service on 11 dec at 4pm"
     */
    public function chatbotBook(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'message'      => 'required|string',
                'team_id'      => 'nullable|integer',
                'location_id'  => 'nullable|integer',
                'name'         => 'nullable|string',
                'phone'        => 'nullable|string',
                'email'        => 'nullable|email',
                'phone_code'   => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'error_type' => 'validation_error',
                    'chatbot_response' => "I'm sorry, but I need more information. Please provide a valid message."
                ], 400);
            }

            $message = trim($request->message);
            // Get team_id and location_id from request, default to 3 and 80
            $teamId = $request->input('team_id', 3);
            $locationId = $request->input('location_id', 80);
            
            // Log for debugging
            \Log::info('Chatbot API Request', [
                'team_id' => $teamId,
                'location_id' => $locationId,
                'message' => $message
            ]);

            // Case 1.5: Check for confusing or invalid messages
            if ($this->isConfusingMessage($message)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Message is unclear',
                    'error_type' => 'unclear_message',
                    'chatbot_response' => "I didn't quite understand that. Could you please tell me:\n• Which service you'd like to book?\n• When you'd like the appointment? (date and time)\n\nFor example: 'Book dental cleaning tomorrow at 4 PM'"
                ], 400);
            }

            // Step 1: Extract service name, date, time, and user info from natural language using OpenAI
            $extracted = $this->extractBookingDetailsWithAI($message, $teamId, $locationId);
            
            // Store extracted user info for later use
            $extractedName = $extracted['name'] ?? null;
            $extractedEmail = $extracted['email'] ?? null;
            $extractedPhone = $extracted['phone'] ?? null;

            // Case 1.2: Missing service
            if (!$extracted['service']) {
                $services = Category::getFirstCategorybooking($teamId, $locationId);
                
                // Generate AI-powered response
                $chatbotResponse = $this->generateAIResponse(
                    "The user wants to book an appointment but didn't specify a service. Available services: " . $services->take(10)->map(fn($s) => $s->name)->implode(', '),
                    "Politely ask which service they'd like to book and list the available services in a friendly way."
                );
                
                if (!$chatbotResponse) {
                    $chatbotResponse = "Which service would you like to book? Here are our available services:\n" . 
                        $services->take(10)->map(fn($s) => "• {$s->name}")->implode("\n") . 
                        "\n\nPlease specify which service you'd like to book.";
                }
                
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Service not found',
                    'error_type' => 'service_not_found',
                    'step'    => 'service_check',
                    'service_status' => 'not_available',
                    'available_services' => $services->map(fn($s) => [
                        'id'   => $s->id,
                        'name' => $s->name
                    ])->values(),
                    'chatbot_response' => $chatbotResponse
                ], 404);
            }

            $service = $extracted['service'];
            $serviceId = $service->id;

            // Case 2.1: Service exists
            $serviceStatus = 'available';

            // Case 1.3: Missing date (only service mentioned)
            if (!$extracted['date'] && !$extracted['time']) {
                $chatbotResponse = $this->generateAIResponse(
                    "User wants to book: {$service->name}. They haven't provided date or time yet.",
                    "Politely confirm the service and ask for their preferred date and time. Give examples."
                );
                
                if (!$chatbotResponse) {
                    $chatbotResponse = "Great! I found the service: **{$service->name}**. Please tell me your preferred date and time.\n\nFor example:\n• 'Tomorrow at 4 PM'\n• 'December 15 at 2 PM'\n• 'Next Monday morning'";
                }
                
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Service found',
                    'service_status' => $serviceStatus,
                    'service' => [
                        'id'   => $service->id,
                        'name' => $service->name
                    ],
                    'chatbot_response' => $chatbotResponse
                ]);
            }

            // Case 1.3: Missing date (service + time but no date)
            if (!$extracted['date'] && $extracted['time']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Date is required',
                    'error_type' => 'missing_date',
                    'chatbot_response' => "I found the service: **{$service->name}** and time: **{$this->normalizeTime($extracted['time'])}**. However, I need to know which date you'd like to book.\n\nPlease provide a date like:\n• 'Tomorrow'\n• 'December 11'\n• 'Next Monday'\n• '11 Dec'"
                ], 400);
            }

            $appointmentDate = $extracted['date'];
            $dateString = $appointmentDate->toDateString();

            // Case 3.4: Date in the past
            if ($appointmentDate->isPast() && !$appointmentDate->isToday()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Date is in the past',
                    'error_type' => 'date_in_past',
                    'requested_date' => $dateString,
                    'chatbot_response' => "I'm sorry, but **{$appointmentDate->format('l, F j, Y')}** has already passed. Please choose a future date.\n\nFor example:\n• 'Tomorrow'\n• 'Next week'\n• 'December 15'"
                ], 400);
            }

            // Fetch site setting
            $siteSetting = SiteDetail::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->first();

            if (!$siteSetting) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Site setting not found',
                    'error_type' => 'site_setting_not_found',
                    'step'   => 'date_check',
                    'chatbot_response' => "I'm sorry, but I couldn't find the booking settings for this location. Please contact support."
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
                        'error_type' => 'no_staff_available',
                        'step'    => 'date_check',
                        'chatbot_response' => "I'm sorry, but there are no staff members available for **{$service->name}** at the moment. Please try again later or contact support."
                    ], 404);
                }

                $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $appointmentDate, $serviceId, $siteSetting, $staffIds);
            }

            $availableSlots = $slots['start_at'] ?? [];
            $disabledDates = $slots['disabled_date'] ?? [];

            // Case 3.2: Date valid but NOT available
            if (empty($availableSlots)) {
                $availableDates = $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting);

                $datesList = !empty($availableDates) 
                    ? "\n\nHere are the next available dates:\n" . collect($availableDates)->take(7)->map(fn($d) => "• " . Carbon::parse($d)->format('l, F j, Y'))->implode("\n")
                    : "\n\nUnfortunately, there are no available dates in the next week.";

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Date not available',
                    'error_type' => 'date_not_available',
                    'step'    => 'date_check',
                    'requested_date' => $dateString,
                    'available_dates' => $availableDates,
                    'chatbot_response' => "I'm sorry, but **{$appointmentDate->format('l, F j, Y')}** is not available for **{$service->name}**.{$datesList}\n\nPlease choose another date."
                ], 404);
            }

            // Case 1.4: Missing time (service + date but no time)
            if (!$extracted['time']) {
                $timeList = collect($availableSlots)->take(10)->map(function($slot) {
                    $slotStart = is_string($slot) && strpos($slot, '-') !== false 
                        ? trim(explode('-', $slot)[0]) 
                        : $slot;
                    return "• " . $this->normalizeTime($slotStart);
                })->implode("\n");

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Date is available',
                    'service' => [
                        'id'   => $service->id,
                        'name' => $service->name
                    ],
                    'date'    => $dateString,
                    'available_times' => $availableSlots,
                    'chatbot_response' => "Perfect! **{$appointmentDate->format('l, F j, Y')}** is available for **{$service->name}**. At what time should I schedule your appointment?\n\nHere are the available time slots:\n\n{$timeList}\n\nPlease choose one of these times or suggest another time."
                ]);
            }

            $requestedTime = $extracted['time'];
            
            // Case 4.3: Invalid time validation
            $timeValidation = $this->validateTime($requestedTime, $availableSlots, $siteSetting, $bookingSetting);
            if (!$timeValidation['valid']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $timeValidation['message'],
                    'error_type' => $timeValidation['error_type'],
                    'requested_time' => $requestedTime,
                    'chatbot_response' => $timeValidation['chatbot_response']
                ], 400);
            }

            $normalizedRequestedTime = $timeValidation['normalized_time'];
            $timeExists = false;
            $matchedSlot = null;

            foreach ($availableSlots as $slot) {
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

            // Case 4.2: Time NOT available
            if (!$timeExists) {
                $timeList = collect($availableSlots)->take(10)->map(function($slot) {
                    $slotStart = is_string($slot) && strpos($slot, '-') !== false 
                        ? trim(explode('-', $slot)[0]) 
                        : $slot;
                    return "• " . $this->normalizeTime($slotStart);
                })->implode("\n");

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Time not available',
                    'error_type' => 'time_not_available',
                    'step'    => 'time_check',
                    'requested_time' => $requestedTime,
                    'date'    => $dateString,
                    'alternative_times' => $availableSlots,
                    'available_times' => $availableSlots,
                    'chatbot_response' => "I'm sorry, but **{$this->normalizeTime($requestedTime)}** is not available on **{$appointmentDate->format('l, F j, Y')}** for **{$service->name}**.\n\nHere are the available time slots for that day:\n\n{$timeList}\n\nPlease choose one of these times."
                ], 404);
            }

            // Case 5.3 & 5.4: Check for duplicate/overlapping bookings
            $duplicateCheck = $this->checkDuplicateBooking($teamId, $locationId, $dateString, $normalizedRequestedTime, $request->phone);
            if ($duplicateCheck['exists']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Duplicate booking',
                    'error_type' => 'duplicate_booking',
                    'chatbot_response' => "You already have an appointment booked for **{$appointmentDate->format('l, F j, Y')}** at **{$normalizedRequestedTime}**.\n\nWould you like to:\n• Cancel the existing appointment?\n• Book a different time?"
                ], 409);
            }

            // Step 5: All checks passed - Book the appointment
            try {
                // Calculate end time
                $startTime = $normalizedRequestedTime;
                $endTime = $this->calculateEndTime($startTime, $service, $bookingSetting, $siteSetting);

                // Check for overlapping bookings
                $overlapCheck = $this->checkOverlappingBooking($teamId, $locationId, $dateString, $startTime, $endTime);
                if ($overlapCheck['overlaps']) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Time slot overlaps with existing booking',
                        'error_type' => 'overlapping_booking',
                        'chatbot_response' => "I'm sorry, but this time slot overlaps with another booking. Please choose a different time.\n\nAvailable times:\n" . collect($availableSlots)->take(5)->map(fn($s) => "• " . $this->normalizeTime(is_string($s) && strpos($s, '-') !== false ? trim(explode('-', $s)[0]) : $s))->implode("\n")
                    ], 409);
                }

                // Extract user info from request or previously extracted data
                $userName = $request->name ?? $extractedName ?? '';
                $userPhone = $request->phone ?? $extractedPhone ?? '';
                $userEmail = $request->email ?? $extractedEmail ?? '';
                
                // Create booking
                $booking = Booking::create([
                    'team_id' => $teamId,
                    'booking_date' => $dateString,
                    'booking_time' => $startTime . '-' . $endTime,
                    'name' => $userName,
                    'phone' => $userPhone,
                    'phone_code' => $request->phone_code ?? '91',
                    'email' => $userEmail,
                    'category_id' => $serviceId,
                    'sub_category_id' => null,
                    'child_category_id' => null,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location_id' => $locationId,
                    'status' => Booking::STATUS_CONFIRMED,
                    'refID' => time() . rand(1000, 9999)
                ]);

                // Case 5.1: Appointment success
                $successContext = "Appointment successfully booked. Service: {$service->name}, Date: {$appointmentDate->format('l, F j, Y')}, Time: {$startTime} - {$endTime}, Booking ID: {$booking->refID}";
                $successInstruction = "Generate a warm, friendly confirmation message celebrating the successful booking. Include all booking details and express excitement about seeing them.";
                
                $chatbotResponse = $this->generateAIResponse($successContext, $successInstruction);
                
                if (!$chatbotResponse) {
                    $chatbotResponse = "✅ **Your appointment is confirmed!**\n\n" .
                        "**Service:** {$service->name}\n" .
                        "**Date:** {$appointmentDate->format('l, F j, Y')}\n" .
                        "**Time:** {$startTime} - {$endTime}\n" .
                        "**Booking ID:** {$booking->refID}\n\n" .
                        "We'll send you a confirmation shortly. See you then!";
                }
                
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Appointment booked successfully',
                    'appointment_id' => $booking->id,
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
                    ],
                    'chatbot_response' => $chatbotResponse
                ], 201);

            } catch (\Exception $e) {
                // Case 5.2: Appointment failed (internal error)
                \Log::error('Booking failed: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'request' => $request->all()
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Booking failed',
                    'error_type' => 'booking_failed',
                    'chatbot_response' => "I'm sorry, but there was a system error while booking your appointment. Please try again or contact support if the problem persists."
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('ChatbotBook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to process request: ' . $e->getMessage(),
                'error_type' => 'processing_error',
                'chatbot_response' => "I'm sorry, but I encountered an error processing your request. Please try again or contact support."
            ], 500);
        }
    }

    /**
     * Check if message is confusing or invalid
     */
    private function isConfusingMessage($message)
    {
        $messageLower = strtolower(trim($message));
        
        // Very short or unclear messages
        if (strlen($messageLower) < 5) {
            return true;
        }

        // Common unclear responses
        $unclearPatterns = [
            '/^(yes|no|ok|okay|sure|maybe|thanks?|thank you)$/i',
            '/^(book|appointment|schedule)$/i',
            '/^(something|anything|whatever)$/i',
            '/^(make it fast|asap|quickly)$/i',
        ];

        foreach ($unclearPatterns as $pattern) {
            if (preg_match($pattern, $messageLower)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate time - check if it's valid and within business hours
     */
    private function validateTime($time, $availableSlots, $siteSetting, $bookingSetting)
    {
        try {
            $normalized = $this->normalizeTime($time);
            
            if (!$normalized) {
                return [
                    'valid' => false,
                    'error_type' => 'invalid_time',
                    'message' => 'Invalid time format',
                    'chatbot_response' => "I didn't understand the time you mentioned. Please provide a time like:\n• '4 PM'\n• '4:00 PM'\n• '16:00'\n• '4 o'clock'"
                ];
            }

            // Try to parse the time
            $parsedTime = Carbon::createFromFormat('h:i A', $normalized);
            $hour = $parsedTime->hour;
            $minute = $parsedTime->minute;

            // Case 4.3: Check for invalid time (e.g., 25:00, 99:99)
            if ($hour > 23 || $minute > 59) {
                return [
                    'valid' => false,
                    'error_type' => 'invalid_time',
                    'message' => 'Invalid time value',
                    'chatbot_response' => "The time you mentioned is not valid. Please provide a time between 12:00 AM and 11:59 PM.\n\nFor example: '9 AM', '2 PM', '4:30 PM'"
                ];
            }

            // Case 4.4: Check if time is outside business hours (if business hours are available)
            // This would require getting business hours from siteSetting or bookingSetting
            // For now, we'll just check if it's in available slots
            
            return [
                'valid' => true,
                'normalized_time' => $normalized
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error_type' => 'invalid_time',
                'message' => 'Time parsing failed',
                'chatbot_response' => "I couldn't understand the time format. Please provide a time like:\n• '4 PM'\n• '4:00 PM'\n• '16:00'"
            ];
        }
    }

    /**
     * Check for duplicate booking
     */
    private function checkDuplicateBooking($teamId, $locationId, $date, $time, $phone = null)
    {
        $query = Booking::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('booking_date', $date)
            ->where('start_time', $time)
            ->where('status', '!=', Booking::STATUS_CANCELLED);

        if ($phone) {
            $query->where('phone', $phone);
        }

        return [
            'exists' => $query->exists()
        ];
    }

    /**
     * Check for overlapping bookings
     */
    private function checkOverlappingBooking($teamId, $locationId, $date, $startTime, $endTime)
    {
        $start = Carbon::createFromFormat('h:i A', $startTime);
        $end = Carbon::createFromFormat('h:i A', $endTime);

        $overlapping = Booking::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('booking_date', $date)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->where(function($q) use ($start, $end) {
                $q->where(function($query) use ($start, $end) {
                    // Existing booking starts before and ends after our start time
                    $query->whereRaw("TIME(start_time) < ?", [$start->format('H:i:s')])
                          ->whereRaw("TIME(end_time) > ?", [$start->format('H:i:s')]);
                })->orWhere(function($query) use ($start, $end) {
                    // Existing booking starts before our end time and ends after
                    $query->whereRaw("TIME(start_time) < ?", [$end->format('H:i:s')])
                          ->whereRaw("TIME(end_time) > ?", [$end->format('H:i:s')]);
                })->orWhere(function($query) use ($start, $end) {
                    // Existing booking is completely within our time slot
                    $query->whereRaw("TIME(start_time) >= ?", [$start->format('H:i:s')])
                          ->whereRaw("TIME(end_time) <= ?", [$end->format('H:i:s')]);
                });
            })
            ->exists();

        return [
            'overlaps' => $overlapping
        ];
    }

    /**
     * Extract booking details using OpenAI with fallback to regex
     */
    private function extractBookingDetailsWithAI($message, $teamId, $locationId)
    {
        try {
            $openai = new OpenAIService();
            
            // Get available services for context
            $services = Category::getFirstCategorybooking($teamId, $locationId);
            $serviceList = $services->map(fn($s) => $s->name)->implode(', ');
            
            // Build system prompt for OpenAI
            $systemPrompt = "You are a booking assistant. Extract booking information from the user's message.\n\n";
            $systemPrompt .= "Available services: {$serviceList}\n\n";
            $systemPrompt .= "Extract and return ONLY a valid JSON object with these fields:\n";
            $systemPrompt .= "- service_name: The service name from the available services list (match as closely as possible)\n";
            $systemPrompt .= "- date: Date in YYYY-MM-DD format (e.g., 2024-12-11) or null if not found\n";
            $systemPrompt .= "- time: Time in 12-hour format with AM/PM (e.g., '04:00 PM') or null if not found\n";
            $systemPrompt .= "- name: Person's name if mentioned in the message, or null\n";
            $systemPrompt .= "- email: Email address if mentioned, or null\n";
            $systemPrompt .= "- phone: Phone number if mentioned, or null\n\n";
            $systemPrompt .= "Rules:\n";
            $systemPrompt .= "1. For dates: Convert natural language like 'tomorrow', 'next Monday', '11 dec' to YYYY-MM-DD format\n";
            $systemPrompt .= "2. For times: Convert '4pm', '4:00 pm', '16:00' to '04:00 PM' format\n";
            $systemPrompt .= "3. Match service names from the available services list\n";
            $systemPrompt .= "4. Extract name, email, phone if mentioned in the message\n";
            $systemPrompt .= "5. Return null for missing fields\n";
            $systemPrompt .= "6. Return ONLY valid JSON, no other text\n\n";
            $systemPrompt .= "Example response: {\"service_name\": \"Dental Cleaning\", \"date\": \"2024-12-11\", \"time\": \"04:00 PM\", \"name\": \"John Doe\", \"email\": \"john@example.com\", \"phone\": \"1234567890\"}";

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ];

            $response = $openai->generateResponse($messages);
            
            if ($response) {
                // Try to extract JSON from response
                $jsonMatch = [];
                if (preg_match('/\{[^}]+\}/', $response, $jsonMatch)) {
                    $extracted = json_decode($jsonMatch[0], true);
                    
                    if ($extracted && is_array($extracted)) {
                        $result = [
                            'service' => null,
                            'date' => null,
                            'time' => null
                        ];

                        // Match service
                        if (!empty($extracted['service_name'])) {
                            $serviceName = strtolower(trim($extracted['service_name']));
                            foreach ($services as $service) {
                                if (strtolower($service->name) === $serviceName || 
                                    strtolower($service->other_name ?? '') === $serviceName ||
                                    strpos(strtolower($service->name), $serviceName) !== false ||
                                    strpos($serviceName, strtolower($service->name)) !== false) {
                                    $result['service'] = $service;
                                    break;
                                }
                            }
                        }

                        // Parse date
                        if (!empty($extracted['date'])) {
                            try {
                                $dateString = trim($extracted['date']);
                                // Ensure date string is valid before parsing
                                if (is_string($dateString) && !empty($dateString)) {
                                    $date = Carbon::parse($dateString);
                                    if ($date && ($date->isFuture() || $date->isToday())) {
                                        $result['date'] = $date;
                                    }
                                }
                            } catch (\TypeError $e) {
                                \Log::warning('Date parsing TypeError', [
                                    'date' => $extracted['date'] ?? null,
                                    'error' => $e->getMessage()
                                ]);
                            } catch (\Exception $e) {
                                \Log::warning('Date parsing failed', [
                                    'date' => $extracted['date'] ?? null,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        // Parse time
                        if (!empty($extracted['time'])) {
                            $normalized = $this->normalizeTime($extracted['time']);
                            if ($normalized) {
                                $result['time'] = $normalized;
                            }
                        }

                        // Extract name, email, phone from OpenAI response
                        if (!empty($extracted['name'])) {
                            $result['name'] = trim($extracted['name']);
                        }
                        if (!empty($extracted['email'])) {
                            $result['email'] = trim($extracted['email']);
                        }
                        if (!empty($extracted['phone'])) {
                            $result['phone'] = trim($extracted['phone']);
                        }

                        // If we got at least one field from OpenAI, return it
                        if ($result['service'] || $result['date'] || $result['time']) {
                            \Log::info('OpenAI extraction successful', [
                                'message' => $message,
                                'extracted' => $result
                            ]);
                            return $result;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('OpenAI extraction failed, falling back to regex', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }

        // Fallback to regex-based extraction
        return $this->extractBookingDetails($message, $teamId, $locationId);
    }

    /**
     * Extract booking details from natural language
     * Enhanced with service synonyms and better date/time parsing
     * Fallback method when OpenAI is not available
     */
    private function extractBookingDetails($message, $teamId, $locationId)
    {
        $result = [
            'service' => null,
            'date' => null,
            'time' => null,
            'name' => null,
            'email' => null,
            'phone' => null
        ];

        $messageLower = strtolower($message);

        // Step 1: Extract service name (with synonyms support)
        $services = Category::getFirstCategorybooking($teamId, $locationId);
        
        // Service synonyms mapping
        $serviceSynonyms = [
            'dental' => ['teeth', 'tooth', 'dentist', 'dental cleaning', 'dental service'],
            'haircut' => ['hair', 'hair cut', 'haircut', 'trim', 'hair styling'],
            'consultation' => ['consult', 'advice', 'meeting', 'appointment'],
            'massage' => ['massage therapy', 'body massage', 'relaxation'],
        ];

        // Try to find service in the message
        foreach ($services as $service) {
            $serviceName = strtolower($service->name);
            $otherName = strtolower($service->other_name ?? '');
            
            // Direct match
            if (strpos($messageLower, $serviceName) !== false || 
                strpos($messageLower, $otherName) !== false) {
                $result['service'] = $service;
                break;
            }

            // Synonym match
            foreach ($serviceSynonyms as $key => $synonyms) {
                if (strpos($serviceName, $key) !== false) {
                    foreach ($synonyms as $synonym) {
                        if (strpos($messageLower, $synonym) !== false) {
                            $result['service'] = $service;
                            break 2;
                        }
                    }
                }
            }
        }

        // Step 2: Extract date (enhanced patterns)
        try {
            $datePatterns = [
                // Natural language dates
                '/tomorrow/i',
                '/day after tomorrow/i',
                '/next\s+(monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i',
                '/this\s+(monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i',
                '/coming\s+(monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i',
                '/this weekend/i',
                '/next week/i',
                // Date formats
                '/(\d{1,2})\s+(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\s+(\d{4})?/i',
                '/(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\s+(\d{1,2})(?:st|nd|rd|th)?\s*,?\s*(\d{4})?/i',
                '/(\d{1,2})\/(\d{1,2})\/(\d{2,4})/',
                '/(\d{4})-(\d{1,2})-(\d{1,2})/',
            ];

            foreach ($datePatterns as $pattern) {
                if (preg_match($pattern, $message, $matches)) {
                    try {
                        $dateString = $matches[0];
                        
                        // For "12 Dec" format, ensure we have a year
                        if (preg_match('/^(\d{1,2})\s+(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*$/i', $dateString, $dateParts)) {
                            $currentYear = Carbon::now()->year;
                            $dateString = $dateParts[1] . ' ' . $dateParts[2] . ' ' . $currentYear;
                        }
                        
                        $date = Carbon::parse($dateString);
                        // Only accept future dates or today
                        if ($date->isFuture() || $date->isToday()) {
                            $result['date'] = $date;
                            break;
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Date parsing failed', [
                            'pattern' => $pattern,
                            'match' => $matches[0] ?? null,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            }

            // If no pattern matched, try Carbon's natural language parsing
            if (!$result['date']) {
                try {
                    // Only try parsing if message looks like it might contain a date
                    if (preg_match('/\d+/', $message)) {
                        $date = Carbon::parse($message);
                        if ($date && ($date->isFuture() || $date->isToday())) {
                            $result['date'] = $date;
                        }
                    }
                } catch (\TypeError $e) {
                    \Log::warning('Carbon parse TypeError', [
                        'message' => $message,
                        'error' => $e->getMessage()
                    ]);
                } catch (\Exception $e) {
                    // Ignore parsing errors
                }
            }
        } catch (\Exception $e) {
            // Date extraction failed
        }

        // Step 3: Extract time (enhanced patterns)
        $timePatterns = [
            '/(\d{1,2}):(\d{2})\s*(am|pm)/i',
            '/(\d{1,2})\s*(am|pm)/i',
            '/(\d{1,2}):(\d{2})/',
            '/(\d{1,2})\s+o\'?clock/i',
            '/morning/i',
            '/afternoon/i',
            '/evening/i',
            '/noon/i',
            '/midnight/i',
        ];

        foreach ($timePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                try {
                    $timeStr = $matches[0];
                    
                    // Handle special cases
                    if (stripos($timeStr, 'morning') !== false) {
                        $timeStr = '9 AM';
                    } elseif (stripos($timeStr, 'afternoon') !== false) {
                        $timeStr = '2 PM';
                    } elseif (stripos($timeStr, 'evening') !== false) {
                        $timeStr = '6 PM';
                    } elseif (stripos($timeStr, 'noon') !== false) {
                        $timeStr = '12 PM';
                    } elseif (stripos($timeStr, 'midnight') !== false) {
                        $timeStr = '12 AM';
                    }
                    
                    $normalized = $this->normalizeTime($timeStr);
                    if ($normalized) {
                        $result['time'] = $normalized;
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $result;
    }

    /**
     * Generate AI-powered chatbot response using OpenAI
     */
    private function generateAIResponse($context, $instruction)
    {
        try {
            $openai = new OpenAIService();
            
            $systemPrompt = "You are a friendly and helpful booking assistant for an appointment booking system. ";
            $systemPrompt .= "Your responses should be:\n";
            $systemPrompt .= "- Conversational and friendly\n";
            $systemPrompt .= "- Clear and concise\n";
            $systemPrompt .= "- Helpful and informative\n";
            $systemPrompt .= "- Professional but warm\n";
            $systemPrompt .= "- Use emojis sparingly (only when appropriate)\n\n";
            $systemPrompt .= "Context: {$context}\n\n";
            $systemPrompt .= "Instruction: {$instruction}\n\n";
            $systemPrompt .= "Generate a natural, conversational response:";

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];

            $response = $openai->generateResponse($messages);
            
            if ($response) {
                return trim($response);
            }
        } catch (\Exception $e) {
            \Log::warning('AI response generation failed', [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}
