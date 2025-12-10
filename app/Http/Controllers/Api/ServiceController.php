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
     * API: Check service name
     * Accepts: Form data (application/x-www-form-urlencoded) or JSON
     * - If service exists: return success message
     * - If service not exists: return error message + service list
     */
    public function checkService(Request $request)
    {
        // Handle both form data and JSON requests
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

        // Get input values (works for both form data and JSON)
        $teamId     = $request->input('team_id', 3);
        $locationId = $request->input('location_id');

        $services = Category::getFirstCategorybooking($teamId, $locationId);

        $queryName = strtolower(trim($request->input('service_name')));

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
            ])->values()
        ], 404);
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
     * Extract booking details from natural language input
     * Extracts: service_name, date, time, name, phone, email
     */
    private function extractBookingDetails($inputText, $teamId, $locationId)
    {
        $result = [
            'service_name' => null,
            'date' => null,
            'time' => null,
            'name' => null,
            'phone' => null,
            'email' => null,
        ];

        $inputLower = strtolower($inputText);

        // Step 1: Get available services for matching
        $services = Category::getFirstCategorybooking($teamId, $locationId);
        
        // Step 2: Extract service name (try to match with available services)
        $bestMatch = null;
        $bestMatchScore = 0;
        
        foreach ($services as $service) {
            $serviceNameLower = strtolower($service->name);
            $otherNameLower = strtolower($service->other_name ?? '');
            
            // Check for exact match
            if (strpos($inputLower, $serviceNameLower) !== false || 
                (!empty($otherNameLower) && strpos($inputLower, $otherNameLower) !== false)) {
                $score = strlen($serviceNameLower);
                if ($score > $bestMatchScore) {
                    $bestMatch = $service->name;
                    $bestMatchScore = $score;
                }
            }
        }
        
        if ($bestMatch) {
            $result['service_name'] = $bestMatch;
        }

        // Step 3: Extract date
        // Patterns: "11 dec", "11 december", "11-12-2024", "2024-12-11", "dec 11", etc.
        $datePatterns = [
            '/(\d{1,2})\s+(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*/i',
            '/(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\s+(\d{1,2})/i',
            '/(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/',  // DD-MM-YYYY or MM-DD-YYYY
            '/(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/',  // YYYY-MM-DD or YYYY-DD-MM
            '/tomorrow/i',
            '/today/i',
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $inputText, $matches)) {
                try {
                    $dateString = $matches[0];
                    
                    // Handle "tomorrow"
                    if (stripos($dateString, 'tomorrow') !== false) {
                        $result['date'] = Carbon::now()->addDay()->format('Y-m-d');
                        break;
                    }
                    
                    // Handle "today"
                    if (stripos($dateString, 'today') !== false) {
                        $result['date'] = Carbon::now()->format('Y-m-d');
                        break;
                    }
                    
                    // Parse the date - return original string for parseDate to handle
                    $result['date'] = $dateString;
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // Step 4: Extract time
        // Patterns: "4pm", "4 pm", "4:00 PM", "16:00", "4:00pm", etc.
        $timePatterns = [
            '/(\d{1,2}):(\d{2})\s*(am|pm)/i',
            '/(\d{1,2})\s*(am|pm)/i',
            '/(\d{1,2}):(\d{2})/',
            '/(\d{1,2})\s+o\'?clock/i',
        ];

        foreach ($timePatterns as $pattern) {
            if (preg_match($pattern, $inputText, $matches)) {
                $result['time'] = trim($matches[0]);
                break;
            }
        }

        // Step 5: Extract name (look for patterns like "name is", "I am", "my name")
        if (preg_match('/(?:name is|I am|I\'m|my name is|call me)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i', $inputText, $matches)) {
            $result['name'] = trim($matches[1]);
        }

        // Step 6: Extract email
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $inputText, $matches)) {
            $result['email'] = trim($matches[0]);
        }

        // Step 7: Extract phone
        if (preg_match('/\b(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}\b/', $inputText, $matches)) {
            $result['phone'] = preg_replace('/[^0-9+]/', '', $matches[0]);
        }

        return $result;
    }

    /**
     * Parse date in various formats
     * Handles formats like "11 dec", "11 december", "11-12-2024", "2024-12-11", etc.
     * For "11-12-2024" format, tries both DD-MM-YYYY and MM-DD-YYYY
     */
    private function parseDate($dateString)
    {
        $dateString = trim($dateString);
        $currentYear = Carbon::now()->year;
        $today = Carbon::now()->startOfDay();
        
        // First, try to handle formats like "11 dec" or "11 december"
        $parts = explode(' ', strtolower($dateString));
        if (count($parts) >= 2 && is_numeric($parts[0])) {
            $day = (int)$parts[0];
            $monthName = trim($parts[1]);
            
            $monthMap = [
                'jan' => 1, 'january' => 1,
                'feb' => 2, 'february' => 2,
                'mar' => 3, 'march' => 3,
                'apr' => 4, 'april' => 4,
                'may' => 5,
                'jun' => 6, 'june' => 6,
                'jul' => 7, 'july' => 7,
                'aug' => 8, 'august' => 8,
                'sep' => 9, 'september' => 9,
                'oct' => 10, 'october' => 10,
                'nov' => 11, 'november' => 11,
                'dec' => 12, 'december' => 12,
            ];
            
            if (isset($monthMap[$monthName])) {
                try {
                    $date = Carbon::createFromDate($currentYear, $monthMap[$monthName], $day);
                    // If date is in the past, try next year
                    if ($date->lt($today)) {
                        $date = Carbon::createFromDate($currentYear + 1, $monthMap[$monthName], $day);
                    }
                    return $date;
                } catch (\Exception $e) {
                    // Invalid date (e.g., Feb 30), fall through to Carbon parsing
                }
            }
        }
        
        // Handle formats like "11-12-2024" or "11-12-2025" - try both DD-MM-YYYY and MM-DD-YYYY
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $dateString, $matches)) {
            $part1 = (int)$matches[1];
            $part2 = (int)$matches[2];
            $year = (int)$matches[3];
            
            // Try DD-MM-YYYY first (more common in international format)
            if ($part1 <= 31 && $part2 <= 12) {
                try {
                    $date = Carbon::createFromDate($year, $part2, $part1);
                    // If date is valid and not in the past, return it
                    if ($date->isValid() && $date->gte($today)) {
                        return $date;
                    }
                } catch (\Exception $e) {
                    // Invalid date, try MM-DD-YYYY
                }
            }
            
            // Try MM-DD-YYYY format
            if ($part2 <= 31 && $part1 <= 12) {
                try {
                    $date = Carbon::createFromDate($year, $part1, $part2);
                    // If date is valid and not in the past, return it
                    if ($date->isValid() && $date->gte($today)) {
                        return $date;
                    }
                } catch (\Exception $e) {
                    // Invalid date
                }
            }
            
            // If both formats failed but year is in the future, prefer DD-MM-YYYY
            if ($year > $currentYear) {
                try {
                    $date = Carbon::createFromDate($year, $part2, $part1);
                    if ($date->isValid()) {
                        return $date;
                    }
                } catch (\Exception $e) {
                    // Try MM-DD-YYYY as fallback
                    try {
                        $date = Carbon::createFromDate($year, $part1, $part2);
                        if ($date->isValid()) {
                            return $date;
                        }
                    } catch (\Exception $e2) {
                        // Both failed
                    }
                }
            }
        }
        
        // Try Carbon's flexible parsing for other formats
        try {
            $date = Carbon::parse($dateString);
            // If year is not specified or is in the past, use current year
            if ($date->year < 2000) {
                $date->year($currentYear);
            }
            // If date is in the past, try next year
            if ($date->lt($today)) {
                $date->year($currentYear + 1);
            }
            return $date;
        } catch (\Exception $e) {
            // If parsing fails, throw exception
            throw new \Exception("Unable to parse date: " . $dateString);
        }
    }

    /**
     * Normalize time format for comparison
     * Handles various time formats like "09:00 AM", "9:00 AM", "09:00", "4pm", "4 pm", "16:00", etc.
     * Returns standardized format: "09:00 AM" (uppercase)
     */
    private function normalizeTime($time)
    {
        // Remove extra spaces
        $time = trim($time);
        
        if (empty($time)) {
            return $time;
        }
        
        // Handle formats like "4pm", "4 pm", "4PM", "16:00"
        $timeLower = strtolower($time);
        if (preg_match('/^(\d{1,2})\s*(am|pm)$/', $timeLower, $matches)) {
            $hour = (int)$matches[1];
            $meridiem = strtoupper($matches[2]);
            
            // Convert to 24-hour format first
            if ($meridiem == 'PM' && $hour != 12) {
                $hour += 12;
            } elseif ($meridiem == 'AM' && $hour == 12) {
                $hour = 0;
            }
            
            // Format as "04:00 PM" or "12:00 PM"
            return Carbon::createFromTime($hour, 0, 0)->format('h:i A');
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
                    try {
                        // Try parsing formats like "4:00pm", "4:00 pm"
                        $parsed = Carbon::createFromFormat('g:i A', $time);
                        return strtoupper($parsed->format('h:i A'));
                    } catch (\Exception $e4) {
                        // If parsing fails, return uppercase version (might already be in correct format)
                        return strtoupper($time);
                    }
                }
            }
        }
    }

    /**
     * 2️⃣ Comprehensive API: Check service, date, time availability and book appointment
     * API: Accepts three explicit inputs: service_name, appointment_date, and time
     * Static values: team_id = 3, location_id = 80
     * Accepts: Form data (application/x-www-form-urlencoded) or JSON
     * Input fields:
     *   - service_name (required): Name of the service
     *   - appointment_date (required): Date in format "YYYY-MM-DD", "DD-MM-YYYY", "11 dec", etc.
     *   - time (required): Time in format "4pm", "4:00 PM", "16:00", etc.
     *   - name (optional): Customer name
     *   - phone (optional): Customer phone
     *   - email (optional): Customer email
     *   - phone_code (optional): Phone country code (default: 91)
     * Flow:
     * 1. Check service availability → if not available, return error + service list
     * 2. Check date availability → if not available, return error + available dates (next week)
     * 3. Check time availability → if not available, return error + other time slots for same day
     * 4. Book appointment if all checks pass
     */
    public function checkAndBook(Request $request)
    {
        // Static values
        $teamId = 3;
        $locationId = 80;

        // Handle both form data and JSON requests - accept three explicit inputs
        $validator = \Validator::make($request->all(), [
            'service_name'    => 'required|string',
            'appointment_date' => 'required|string',
            'time'            => 'required|string',
            'name'            => 'nullable|string',
            'phone'           => 'nullable|string',
            'email'           => 'nullable|email',
            'phone_code'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Get inputs
        $serviceName = trim($request->input('service_name'));
        $appointmentDateInput = trim($request->input('appointment_date'));
        $timeString = trim($request->input('time'));

        // Step 1: Check if service exists
        $services = Category::getFirstCategorybooking($teamId, $locationId);
        $queryName = strtolower($serviceName);

        $service = $services->first(function ($s) use ($queryName) {
            return strtolower($s->name) === $queryName ||
                   strtolower($s->other_name ?? '') === $queryName;
        });

        // Error Case 1: Service not available
        if (!$service) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Service not available',
                'services' => $services->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name
                ])->values()
            ], 404);
        }

        $serviceId = $service->id;

        // Step 2: Parse and check date availability
        try {
            // If date is already in YYYY-MM-DD format, use it directly
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointmentDateInput)) {
                $appointmentDate = Carbon::parse($appointmentDateInput);
            } else {
                // Parse the date string (handles formats like "11 dec", "11-12-2024", etc.)
                $appointmentDate = $this->parseDate($appointmentDateInput);
            }
            $dateString = $appointmentDate->toDateString();
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid date format. Please provide date in a valid format (e.g., "2024-12-11", "11-12-2024", "11 dec")'
            ], 400);
        }

        // Fetch site setting
        $siteSetting = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();

        if (!$siteSetting) {
            return response()->json([
                'status' => 'error',
                'message' => 'Site setting not found for this team and location'
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
                    'services' => $services->map(fn($s) => [
                        'id'   => $s->id,
                        'name' => $s->name
                    ])->values()
                ], 404);
            }

            $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $appointmentDate, $serviceId, $siteSetting, $staffIds);
        }

        $availableSlots = $slots['start_at'] ?? [];
        $disabledDates = $slots['disabled_date'] ?? [];

        // Error Case 2: Date not available - no time slots available
        if (empty($availableSlots)) {
            // Get available dates - check if requested date is in the past or beyond booking window
            $today = Carbon::now()->startOfDay();
            $requestedDateObj = Carbon::parse($dateString)->startOfDay();
            
            // Check if date is in the past
            if ($requestedDateObj->lt($today)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot book appointments for past dates',
                    'requested_date' => $dateString,
                    'available_dates' => $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting)
                ], 404);
            }
            
            // Check if date is beyond the advance booking window
            $advanceDays = $bookingSetting ? ($bookingSetting->allow_req_before ?? 30) : 30;
            // Ensure advanceDays is numeric to avoid Carbon TypeError
            $advanceDays = is_numeric($advanceDays) ? (int)$advanceDays : 30;
            $maxBookingDate = Carbon::now()->addDays($advanceDays)->startOfDay();
            
            if ($requestedDateObj->gt($maxBookingDate)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Date is beyond the maximum advance booking period (' . $advanceDays . ' days)',
                    'requested_date' => $dateString,
                    'max_booking_date' => $maxBookingDate->toDateString(),
                    'available_dates' => $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting)
                ], 404);
            }
            
            // Get available dates (extend search if requested date is beyond next week)
            $availableDates = $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting, $requestedDateObj);

            return response()->json([
                'status'  => 'error',
                'message' => 'Date not available for this service',
                'requested_date' => $dateString,
                'available_dates' => $availableDates
            ], 404);
        }

        // Step 3: Check time availability
        $requestedTime = trim($timeString);
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

        // Error Case 3: Time not available - show other time slots for same day
        if (!$timeExists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Time slot not available for this date',
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
                'name' => $request->input('name', ''),
                'phone' => $request->input('phone', ''),
                'phone_code' => $request->input('phone_code', '91'),
                'email' => $request->input('email', ''),
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
     * Get available dates for next week (or extend if requested date is beyond)
     */
    private function getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting, $requestedDate = null)
    {
        $availableDates = [];
        $startDate = Carbon::now()->addDay();
        
        // If requested date is provided and beyond next week, extend search range
        if ($requestedDate && $requestedDate->gt(Carbon::now()->addWeek())) {
            $endDate = $requestedDate->copy()->addWeek(); // Search up to requested date + 1 week
        } else {
            $endDate = Carbon::now()->addWeek();
        }
        
        // Limit to maximum advance booking days
        $advanceDays = $bookingSetting ? ($bookingSetting->allow_req_before ?? 30) : 30;
        // Ensure advanceDays is numeric to avoid Carbon TypeError
        $advanceDays = is_numeric($advanceDays) ? (int)$advanceDays : 30;
        $maxDate = Carbon::now()->addDays($advanceDays);
        if ($endDate->gt($maxDate)) {
            $endDate = $maxDate;
        }
        
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
            $slotPeriod = $bookingSetting ? ($bookingSetting->slot_period ?? null) : null;

            // If not available, try to get from service time
            if (!$slotPeriod && $service && $service->service_time) {
                $slotPeriod = $service->service_time;
            }

            // Default to 30 minutes if nothing is found
            if (!$slotPeriod) {
                $slotPeriod = 30;
            }

            // Ensure slotPeriod is numeric (cast to int/float) to avoid Carbon TypeError
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
