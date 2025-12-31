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
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\Location;
use App\Models\Customer;
use App\Models\CustomerActivityLog;
use App\Models\MessageDetail;
use App\Models\Counter;
use App\Models\SmsAPI;
use App\Models\SmtpDetails;
use App\Models\SalesforceSetting;
use App\Models\SalesforceConnection;
use App\Services\SalesforceService;
use App\Events\QueueCreated;
use App\Events\QueueNotification;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'service_name' => 'nullable|string',
            'team_id'      => 'nullable|integer',
            'location_id'  => 'nullable|integer',
            'type'         => 'nullable|string',
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
        
        // Filter services where service_time is not empty
        $services = $services->filter(function ($s) {
            return !empty($s->service_time);
        });

        // Filter by type if provided (e.g., 'appointment' or 'service')
        if ($request->has('type') && !empty($request->type)) {
            $requestedType = $request->type;
            $services = $services->filter(function ($s) use ($requestedType) {
                return isset($s->type) && $s->type === $requestedType;
            });
        }
        
        $queryName = trim($request->input('service_name', ''));
        
        // If service_name is provided, search for it
        if (!empty($queryName)) {
            $queryNameLower = strtolower($queryName);
            
            // First try exact match
            $service = $services->first(function ($s) use ($queryNameLower) {
                return strtolower($s->name) === $queryNameLower ||
                       strtolower($s->other_name ?? '') === $queryNameLower;
            });
            
            // If exact match not found, try partial search
            if (!$service) {
                $service = $services->first(function ($s) use ($queryNameLower) {
                    return strpos(strtolower($s->name), $queryNameLower) !== false ||
                           (!empty($s->other_name) && strpos(strtolower($s->other_name), $queryNameLower) !== false);
                });
            }
            
            if ($service) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Service found',
                    'service' => [
                        'id'   => $service->id,
                        'name' => $service->name,
                    ]
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Service not found',
                'services' => $services->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name,
                ])->values()
            ], 404);
        }
        
        // If no service_name provided, return all services
        return response()->json([
            'status'  => 'success',
            'message' => 'All services retrieved',
            'services' => $services->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name,
            ])->values()
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
     * API: Accepts flexible inputs - checks each step and asks for missing information
     * Static values: team_id = 3, location_id = 80
     * Accepts: Form data (application/x-www-form-urlencoded) or JSON
     * Input fields (all optional, checked step by step):
     *   - service_name: Name of the service
     *   - appointment_date: Date in format "YYYY-MM-DD", "DD-MM-YYYY", "11 dec", "tomorrow", etc.
     *   - time: Time in format "4pm", "4:00 PM", "16:00", etc.
     *   - name: Customer name
     *   - phone: Customer phone
     *   - email: Customer email
     *   - phone_code: Phone country code (default: 91)
     * Flow:
     * 1. Check if service_name provided → if not, ask for service
     * 2. Check if service exists → if not, return error + service list
     * 3. Check if appointment_date provided → if not, ask for date
     * 4. Check if date is valid → if not, ask for valid date
     * 5. Check if date is available → if not, return error + available dates
     * 6. Check if time provided → if not, ask for time
     * 7. Check if time is valid → if not, ask for valid time
     * 8. Check if time is available → if not, return error + available times
     * 9. Check for duplicate/overlapping bookings
     * 10. Book appointment if all checks pass
     */
    public function checkAndBook(Request $request)
    {
        // Static values
        $teamId = 3;
        $locationId = 80;

        // Get all services for reference
        $services = Category::getFirstCategorybooking($teamId, $locationId);

        // Step 1: Check if service_name is provided
        $serviceName = trim($request->input('service_name', ''));
        if (empty($serviceName)) {
            return response()->json([
                'status'  => 'error',
                'error_type' => 'missing_service',
                'message' => 'Which service would you like to book?',
                'services' => $services->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name
                ])->values()
            ], 400);
        }

        // Step 2: Check if service exists
        $queryName = strtolower($serviceName);
        $service = $services->first(function ($s) use ($queryName) {
            return strtolower($s->name) === $queryName ||
                   strtolower($s->other_name ?? '') === $queryName;
        });

        // Error Case 1: Service does NOT exist - return error with service list
        if (!$service) {
            return response()->json([
                'status'  => 'error',
                'error_type' => 'service_not_available',
                'message' => 'Service not available',
                'requested_service' => $serviceName,
                'services_list' => $services->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name
                ])->values()
            ], 404);
        }

        // Service exists - continue with checks
        $serviceId = $service->id;

        // Get date and time inputs
        $appointmentDateInput = trim($request->input('appointment_date', ''));
        $timeString = trim($request->input('time', ''));
        
        // If only service_name is provided (no date/time), return success message WITHOUT service list
        if (empty($appointmentDateInput) && empty($timeString)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Service found',
                'service' => [
                    'id'   => $service->id,
                    'name' => $service->name
                ]
            ], 200);
        }

        // If date & time + service_name are provided, proceed with full check
        // Step 3: Check if appointment_date is provided (required when time is provided)
        if (empty($appointmentDateInput)) {
            return response()->json([
                'status'  => 'error',
                'error_type' => 'missing_date',
                'message' => 'Please provide appointment date.',
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name
                ]
            ], 400);
        }

        // Step 4: Parse and validate date
        try {
            // Handle natural language dates
            $appointmentDateInputLower = strtolower($appointmentDateInput);
            
            // Handle "tomorrow"
            if ($appointmentDateInputLower === 'tomorrow') {
                $appointmentDate = Carbon::now()->addDay();
            }
            // Handle "day after tomorrow"
            elseif ($appointmentDateInputLower === 'day after tomorrow' || $appointmentDateInputLower === 'day after') {
                $appointmentDate = Carbon::now()->addDays(2);
            }
            // Handle "today"
            elseif ($appointmentDateInputLower === 'today') {
                $appointmentDate = Carbon::now();
            }
            // Handle other natural language dates (next Friday, coming Monday, etc.)
            elseif (preg_match('/next\s+(monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i', $appointmentDateInputLower, $matches)) {
                $dayName = ucfirst(strtolower($matches[1]));
                $appointmentDate = Carbon::now()->next($dayName);
            }
            elseif (preg_match('/coming\s+(monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i', $appointmentDateInputLower, $matches)) {
                $dayName = ucfirst(strtolower($matches[1]));
                $appointmentDate = Carbon::now()->next($dayName);
            }
            // Handle standard date formats
            elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointmentDateInput)) {
                $appointmentDate = Carbon::parse($appointmentDateInput);
            } else {
                // Parse the date string (handles formats like "11 dec", "11-12-2024", etc.)
                $appointmentDate = $this->parseDate($appointmentDateInput);
            }
            
            // Validate the parsed date is valid
            if (!$appointmentDate || !$appointmentDate->isValid()) {
                throw new \Exception('Invalid date');
            }
            
        $dateString = $appointmentDate->toDateString();
            $today = Carbon::now()->startOfDay();
            $requestedDateObj = $appointmentDate->startOfDay();
            
            // Error Case 3.3: Date invalid
            if (!$appointmentDate->isValid()) {
                return response()->json([
                    'status'  => 'error',
                    'error_type' => 'invalid_date',
                    'message' => 'Please pick a valid date. (e.g., "tomorrow", "2024-12-11", "11 dec", "next Friday")',
                    'requested_date' => $appointmentDateInput,
                    'service' => [
                        'id' => $service->id,
                        'name' => $service->name
                    ]
                ], 400);
            }
            
            // Note: Past date check will be done after fetching site settings to get available dates
        } catch (\Exception $e) {
            // Error Case 3.3: Date invalid
            return response()->json([
                'status'  => 'error',
                'error_type' => 'invalid_date',
                'message' => 'Please pick a valid date. (e.g., "tomorrow", "2024-12-11", "11 dec", "next Friday")',
                'requested_date' => $appointmentDateInput,
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name
                ]
            ], 400);
        }

        // Fetch site setting
        $siteSetting = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();

        if (!$siteSetting) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'booking_failed',
                'message' => 'System error while booking. Please try again.'
            ], 500);
        }

        // Get booking settings
        $bookingSetting = AccountSetting::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->first();
        
        // Check if date is in the past - return error message and available dates next week
        if ($requestedDateObj->lt($today)) {
            $availableDates = $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting);
            
            return response()->json([
                'status'  => 'error',
                'error_type' => 'date_not_available',
                'message' => 'Date not available for this service',
                'requested_date' => $dateString,
                'available_dates' => $availableDates,
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name
                ]
            ], 400);
        }

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

        // Error Case: Date not available or already booked - return error message and available dates next week
        if (empty($availableSlots)) {
            // Check if date is beyond the advance booking window
            $advanceDays = $bookingSetting ? ($bookingSetting->allow_req_before ?? 30) : 30;
            // Ensure advanceDays is numeric to avoid Carbon TypeError
            $advanceDays = is_numeric($advanceDays) ? (int)$advanceDays : 30;
            $maxBookingDate = Carbon::now()->addDays($advanceDays)->startOfDay();
            
            if ($requestedDateObj->gt($maxBookingDate)) {
            return response()->json([
                'status'  => 'error',
                    'error_type' => 'date_not_available',
                    'message' => 'Date not available for this service',
                'requested_date' => $dateString,
                    'available_dates' => $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting)
                ], 400);
            }
            
            // Get available dates (extend search if requested date is beyond next week)
            $availableDates = $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting, $requestedDateObj);

            // Error Case: Date not available or already booked - return error message and available dates next week
            return response()->json([
                'status'  => 'error',
                'error_type' => 'date_not_available',
                'message' => 'Date not available for this service',
                'requested_date' => $dateString,
                'available_dates' => $availableDates,
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name
                ]
            ], 404);
        }

        // Step 5: Check if time is provided (required when date is provided for booking)
        if (empty($timeString)) {
            return response()->json([
                'status'  => 'error',
                'error_type' => 'missing_time',
                'message' => 'Please provide appointment time.',
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name
                ],
                'date' => $dateString,
                'available_times' => $availableSlots
            ], 400);
        }

        // Step 6: Validate time format
        $requestedTime = trim($timeString);
        
        try {
        $normalizedRequestedTime = $this->normalizeTime($requestedTime);
            // Try to parse the normalized time to ensure it's valid
            $testTime = Carbon::createFromFormat('h:i A', $normalizedRequestedTime);
            if (!$testTime || !$testTime->isValid()) {
                throw new \Exception('Invalid time format');
            }
            
            // Error Case 4.3: Invalid time (e.g., 25:00 PM)
            $hour = (int)$testTime->format('H');
            if ($hour < 0 || $hour > 23) {
                throw new \Exception('Invalid hour');
            }
        } catch (\Exception $e) {
            // Error Case 4.3: Invalid time
            return response()->json([
                'status'  => 'error',
                'error_type' => 'invalid_time',
                'message' => 'Please provide a valid time. (e.g., "4pm", "4:00 PM", "16:00")',
                'requested_time' => $requestedTime,
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name
                ],
                'date' => $dateString
            ], 400);
        }
        
        $timeExists = false;
        $matchedSlot = null;
        $matchedStartTime = null;
        $matchedEndTime = null;

        foreach ($availableSlots as $slot) {
            // Handle slot format "09:00 AM-10:00 AM" by extracting start time
            $slotStartTime = $slot;
            $slotEndTime = null;
            if (strpos($slot, '-') !== false) {
                [$slotStartTime, $slotEndTime] = explode('-', $slot, 2);
                $slotStartTime = trim($slotStartTime);
                $slotEndTime = trim($slotEndTime);
            }

            $normalizedSlot = $this->normalizeTime($slotStartTime);
            if ($normalizedSlot === $normalizedRequestedTime) {
                $timeExists = true;
                $matchedSlot = $slot;
                $matchedStartTime = $normalizedSlot;
                $matchedEndTime = $slotEndTime ? $this->normalizeTime($slotEndTime) : null;
                break;
            }
        }

        // Error Case 3: If dates available but time not available - show error message and other time slots of same day
        if (!$timeExists) {
            return response()->json([
                'status'  => 'error',
                'error_type' => 'time_not_available',
                'message' => 'Time slot not available for this date',
                'requested_time' => $requestedTime,
                'date'    => $dateString,
                'available_times' => $availableSlots, // Other time slots of same day
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name
                ]
            ], 404);
        }

        // Step 7: Check if time is outside business hours (if needed)
        // This is handled by the available slots check above, but we can add explicit check here if needed

        // Step 8: Check for duplicate/overlapping bookings
            $startTime = $normalizedRequestedTime;
        
        try {
            $endTime = $this->calculateEndTime($startTime, $service, $bookingSetting, $siteSetting);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'error_type' => 'booking_failed',
                'message' => 'System error while booking. Please try again.',
                'details' => 'Failed to calculate end time: ' . $e->getMessage()
            ], 500);
        }
        
        // Check for duplicate booking (same phone/email at same date and time)
        $phone = $request->input('phone');
        $email = $request->input('email');
        
        if ($phone || $email) {
            try {
                $duplicateQuery = Booking::where('team_id', $teamId)
                    ->where('location_id', $locationId)
                    ->where('booking_date', $dateString)
                    ->where('start_time', $startTime)
                    ->where('status', '!=', Booking::STATUS_CANCELLED);
                
                if ($phone) {
                    $duplicateQuery->where('phone', $phone);
                }
                if ($email) {
                    $duplicateQuery->where('email', $email);
                }
                
                $duplicateBooking = $duplicateQuery->first();
                
                if ($duplicateBooking) {
                    // Error Case 5.3: Duplicate booking
                    return response()->json([
                        'status'  => 'error',
                        'error_type' => 'duplicate_booking',
                        'message' => 'You already have an appointment at this time.',
                        'existing_booking' => [
                            'id' => $duplicateBooking->id,
                            'date' => $duplicateBooking->booking_date,
                            'time' => $duplicateBooking->booking_time,
                            'status' => $duplicateBooking->status
                        ],
                        'service' => [
                            'id' => $service->id,
                            'name' => $service->name
                        ]
                    ], 409);
                }
            } catch (\Exception $e) {
                // If duplicate check fails, continue (don't block booking)
            }
        }
        
        // Check for overlapping time slots (any booking at same date and time)
        try {
            $overlappingBooking = Booking::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->where('booking_date', $dateString)
                ->where('start_time', $startTime)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->first();
            
            if ($overlappingBooking) {
                // Error Case 5.4: Overlapping time
                return response()->json([
                    'status'  => 'error',
                    'error_type' => 'overlapping_time',
                    'message' => 'This time slot is already booked. Please select another time.',
                    'requested_time' => $startTime . '-' . $endTime,
                    'date' => $dateString,
                    'alternative_times' => $availableSlots ?? [],
                    'service' => [
                        'id' => $service->id,
                        'name' => $service->name
                    ]
                ], 409);
            }
        } catch (\Exception $e) {
            // If overlapping check fails, continue (don't block booking)
        }

        // Step 9: All checks passed - Book the appointment
        try {
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

            // Success Case 5.1: Appointment success
            return response()->json([
                'status'  => 'success',
                'service_status' => 'available',
                'message' => 'Your appointment is confirmed!',
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
                ]
            ], 201);

        } catch (\Exception $e) {
            // Error Case 5.2: Appointment failed (internal error)
            return response()->json([
                'status'  => 'error',
                'error_type' => 'booking_failed',
                'message' => 'System error while booking. Please try again.',
                'details' => $e->getMessage()
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
            'team_id'      => 'nullable|integer',
            'location_id'  => 'nullable|integer',
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

        $teamId = 3;
        $locationId = 80;
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

    public function checkDateTimeAvailability(Request $request)
{
    $validator = \Validator::make($request->all(), [
        'service_id'   => 'nullable|integer',
        'service_name' => 'nullable|string',
        'team_id'      => 'nullable|integer',
        'location_id'  => 'nullable|integer',
        'date'         => 'required|date',
        'time'         => 'nullable|string' // <-- Add this
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

    $teamId = 3;
    $locationId = 80;
    $date = Carbon::parse($request->date);
    $dateString = $date->toDateString();

    // STEP 1: Validate Service
    $services = Category::getFirstCategorybooking($teamId, $locationId);

    if ($request->service_id) {
        $service = $services->firstWhere('id', $request->service_id);
    } else {
        $queryName = strtolower($request->service_name);
        $service = $services->first(function ($s) use ($queryName) {
            return strtolower($s->name) === $queryName ||
                   strtolower($s->other_name ?? '') === $queryName;
        });
    }

    if (!$service) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Service not found or not available',
            'available_services' => $services->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name
            ])
        ], 404);
    }

    $serviceId = $service->id;

    // STEP 2: Validate Date Availability
    $siteSetting = SiteDetail::where('team_id', $teamId)
        ->where('location_id', $locationId)
        ->first();

    if (!$siteSetting) {
        return response()->json([
            'status' => 'error',
            'message' => 'Site setting not found',
        ], 404);
    }

    // STEP 3: Generate Available Time Slots
    if ($siteSetting->choose_time_slot != 'staff') {
        $slots = AccountSetting::checktimeslot($teamId, $locationId, $date, $serviceId, $siteSetting);
    } else {
        $staffIds = User::whereHas('categories', fn($q) => $q->where('categories.id', $serviceId))
            ->pluck('id')->toArray();

        $slots = AccountSetting::checkStafftimeslot(
            $teamId, $locationId, $date, $serviceId, $siteSetting, $staffIds
        );
    }

    // Check if no slots at all
    if (empty($slots['start_at'])) {
        return response()->json([
            'status'  => 'error',
            'message' => 'No time slots available for this date',
            'date'    => $dateString,
            'service_id' => $serviceId,
            'available_dates' => $slots['disabled_date'] ?? []
        ], 404);
    }

    // STEP 4: Time Conflict Check (BOOKING VALIDATION)
if ($request->time) {

    // Normalize user input
    try {
        $requestedTime = trim($request->time);
        $parsedTime = Carbon::parse($requestedTime)->format('h:i A');
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid time format.',
        ], 400);
    }

    // Get booked start_times from DB
    $bookedSlots = \DB::table('bookings')
        ->where('team_id', $teamId)
        ->where('location_id', $locationId)
        ->where('category_id', $serviceId)
        ->where('booking_date', $date->toDateString())
        ->pluck('start_time')
        ->toArray();

    // Extract only start times from all generated slots
    $generatedStartTimes = array_map(function ($slot) {
        return trim(explode('-', $slot)[0]);  // "02:00 PM-02:30 PM" → "02:00 PM"
    }, $slots['start_at']);

    // Remove booked times from available list
    $availableStartTimes = array_values(array_filter($generatedStartTimes, function ($t) use ($bookedSlots) {
        return !in_array($t, $bookedSlots);
    }));

    // If user-selected time is not available → booked
    if (!in_array($parsedTime, $availableStartTimes)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'This time slot is already booked for the selected service.',
            'requested_time' => $parsedTime,
            'date'    => $dateString,
            'available_times' => $availableStartTimes
        ], 409);
    }
}


    // SUCCESS — Service, Date, and Requested Time are Available
    return response()->json([
        'status'  => 'success',
        'message' => 'Service, date, and time are available',
        'service' => [
            'id'   => $serviceId,
            'name' => $service->name
        ],
        'date'    => $dateString,
        'available_times' => $slots['start_at']
    ]);
}

/**
     * Get available dates for a specific service
     * API: Returns available dates based on service name
     */
    public function getAvailableDates(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $teamId = 3;
        $locationId = 80;

        // Step 1: Find the service
        $services = Category::getFirstCategorybooking($teamId, $locationId);
        $queryName = strtolower($request->service_name);
        
        $service = $services->first(function ($s) use ($queryName) {
            return strtolower($s->name) === $queryName ||
                   strtolower($s->other_name ?? '') === $queryName;
        });

        if (!$service) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Service not found',
                'available_services' => $services->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name
                ])->values()
            ], 404);
        }

        $serviceId = $service->id;

        // Step 2: Get Site Settings
        $siteSetting = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();

        if (!$siteSetting) {
            return response()->json([
                'status' => 'error',
                'message' => 'Site setting not found'
            ], 500);
        }

        // Step 3: Get Booking Settings
        $bookingSetting = AccountSetting::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->first();

        // Step 4: Get Available Dates
        // We reuse the existing helper function. 
        // Note: It searches starting from "tomorrow" by default logic in that function if no date provided, 
        // but let's check its implementation again. 
        // It starts from Carbon::now()->addDay(). If we want today included, we might need a different approach 
        // or modifying the helper. But for now, we'll stick to the existing helper logic which seems standard for this app.
        // Actually, let's look at getAvailableDatesForNextWeek: $startDate = Carbon::now()->addDay();
        // If the user wants "today", the helper skips it.
        // However, the requirement is just "return available dates". We will use the existing helper to be consistent.
        
        $availableDates = $this->getAvailableDatesForNextWeek($teamId, $locationId, $serviceId, $siteSetting, $bookingSetting);

        return response()->json([
            'status' => 'success',
            'service' => [
                'id' => $service->id,
                'name' => $service->name
            ],
            'available_dates' => $availableDates
        ]);
    }

    /**
     * Get available time slots for a specific service and date
     * API: Returns available time slots
     */
    public function getAvailableTimes(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_name' => 'required|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $teamId = 3;
        $locationId = 80;
        $appointmentDate = Carbon::parse($request->date);
        $dateString = $appointmentDate->toDateString();

        // Step 1: Find the service
        $services = Category::getFirstCategorybooking($teamId, $locationId);
        $queryName = strtolower($request->service_name);
        
        $service = $services->first(function ($s) use ($queryName) {
            return strtolower($s->name) === $queryName ||
                   strtolower($s->other_name ?? '') === $queryName;
        });

        if (!$service) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Service not found',
                'available_services' => $services->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name
                ])->values()
            ], 404);
        }

        $serviceId = $service->id;

        // Step 2: Get Site Settings
        $siteSetting = SiteDetail::where('team_id', $teamId)
            ->where('location_id', $locationId)
            ->first();

        if (!$siteSetting) {
            return response()->json([
                'status' => 'error',
                'message' => 'Site setting not found'
            ], 500);
        }

        // Step 3: Get Available Slots
        $slots = [];
        if ($siteSetting->choose_time_slot != 'staff') {
            $slots = AccountSetting::checktimeslot($teamId, $locationId, $appointmentDate, $serviceId, $siteSetting);
        } else {
            $staffIds = User::whereHas('categories', fn($q) => $q->where('categories.id', $serviceId))
                            ->pluck('id')->toArray();

            if (!empty($staffIds)) {
                $slots = AccountSetting::checkStafftimeslot($teamId, $locationId, $appointmentDate, $serviceId, $siteSetting, $staffIds);
            }
        }

        $availableSlots = $slots['start_at'] ?? [];

        // Filter out past times if date is today
        if ($appointmentDate->isToday()) {
            $now = Carbon::now();
            $availableSlots = array_values(array_filter($availableSlots, function($slot) use ($now) {
                try {
                    // Extract start time "09:00 AM" or "09:00 AM-10:00 AM"
                    $parts = explode('-', $slot);
                    $startTime = trim($parts[0]);
                    $slotTime = Carbon::parse($startTime);
                    
                    // Allow slot if it's in the future (plus maybe a buffer, but standard is just future)
                    return $slotTime->gt($now);
                } catch (\Exception $e) {
                    return true;
                }
            }));
        }

        
        return response()->json([
            'status' => 'success',
            'service' => [
                'id' => $service->id,
                'name' => $service->name
            ],
            'date' => $dateString,
            'available_times' => $availableSlots
        ]);
    }

    public function ConvertBookToQueue(Request $request)
    {
        $booking_refID = $request->booking_refID;

        if (empty($booking_refID)) {
             return response('Id is required', 400)->header('Content-Type', 'text/plain');
        }

        $booking = Booking::where('refID', $booking_refID)
            ->whereDate('booking_date', date('Y-m-d'))
            ->where('is_convert', Booking::STATUS_NO)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->first();

        if (!$booking) {
             return response('Booking not found or already converted or not for today', 404)->header('Content-Type', 'text/plain');
        }
        
        $teamId = $booking->team_id;
        $locationId = $booking->location_id;
        $siteDetails = SiteDetail::where('team_id', $teamId)->where('location_id', $locationId)->first();

        // Check Ticket Limit
        $checkTicketLimit = SiteDetail::checkTicketLimit($teamId, $locationId, $siteDetails);
        if($checkTicketLimit)
        {
             return response('Ticket limit exceeded', 400)->header('Content-Type', 'text/plain');
        }

        // Check if already queue
        $isAsQueue = QueueStorage::isBookExist($booking->id);
        if ($isAsQueue) {
             return response('Already converted', 400)->header('Content-Type', 'text/plain');
        }

        DB::beginTransaction();
        try {
            // Acronym Logic
            $acronym = SiteDetail::DEFAULT_APPOINTMENT_A;
            $selectedCategoryId = $booking->category_id ?? null;
            $secondChildId = $booking->sub_category_id ?? null;
            $thirdChildId = $booking->child_category_id ?? null;
            
            $acronym_level = $siteDetails->acronym_level ?? 0;
            if((int)$acronym_level == 1 && !empty($selectedCategoryId)){
                $acronym = Category::viewAcronym($selectedCategoryId);
            }elseif((int)$acronym_level == 2 && !empty($secondChildId)){
                $acronym = Category::viewAcronym($secondChildId);
            }elseif((int)$acronym_level == 3 && !empty($thirdChildId)){
                $acronym = Category::viewAcronym($thirdChildId);
            }

            // Last Category Logic
            $lastcategory = $selectedCategoryId;
            if(!empty($thirdChildId)){
                $lastcategory = $thirdChildId;
            }elseif(!empty($secondChildId)){
                $lastcategory = $secondChildId;
            }

            // Token Generation
            if($siteDetails?->count_by_service){
                 $lastToken = Queue::getLastToken($teamId, null, $locationId);
            }else{
                 $lastToken = Queue::getLastToken($teamId, $acronym, $locationId, $lastcategory);
            }
            
            $token_digit = $siteDetails?->token_digit ?? 4;
            $isExistToken = true;
            $token_start = "";

            while ($isExistToken) {
                $newToken = QueueStorage::newGeneratedToken($lastToken, $siteDetails?->token_start, $token_digit);
                
                $isExistToken = Queue::checkToken($teamId, $acronym, $newToken, $locationId);
                if ($isExistToken) {
                    $lastToken = $newToken;
                } else {
                    $token_start = $newToken;
                    $isExistToken = false;
                }
            }
            
            $todayDateTime = Carbon::now();

            // Determine assigned staff based on setting
            $assigned_staff_id = null;
            if (($siteDetails->assigned_staff_id ?? 0) == 1 && is_numeric($booking->staff_id)) {
                $assigned_staff_id = (int)$booking->staff_id;
            } else {
                $assigned_staff_id = is_numeric($booking->staff_id) ? (int)$booking->staff_id : null;
            }

            $enablePriority = $siteDetails->priority_enable ?? false; 

            $nextPrioritySort = $this->getNextPrioritySortDirect($teamId, $locationId, $booking->category_id);
            if ($enablePriority && empty($assigned_staff_id)) {
                $assigned_staff_id = User::getNextAgent($booking->team_id, $booking->location_id);
            }

            $is_virtual_meeting = 0;
            if($booking->json){
                $decodedJson = json_decode($booking->json, true);
                if (isset($decodedJson['type']) && $decodedJson['type'] === 'Virtual') {
                    $is_virtual_meeting = 1;
                }
            }
            
            $phone_full = (!empty($booking->phone_code) && !empty($booking->phone)) ? $booking->phone_code . $booking->phone : null;

            $storeData = [
                'name' => $booking->name,
                'phone' => $booking->phone ?? '',
                'phone_code' => $booking->phone_code ?? '91',
                'category_id' => $booking->category_id ?? null,
                'sub_category_id' => $booking->sub_category_id ?? null,
                'child_category_id' => $booking->child_category_id ?? null,
                'team_id' => $teamId,
                'token' => $token_start,
                'token_with_acronym' => Queue::LABEL_NO,
                'json' => $booking->json,
                'arrives_time' => $todayDateTime,
                'datetime' => $todayDateTime,
                'start_acronym' => $acronym,
                'locations_id' => $booking->location_id,
                'booking_id' => $booking->id,
                'priority_sort' => $nextPrioritySort ?? 0,
                'served_by' =>  $assigned_staff_id,
                'assign_staff_id' =>  $assigned_staff_id,
                'campaign_id' => is_numeric($booking->campaign_id) ? (int)$booking->campaign_id : null,
                'full_phone_number' => $phone_full,
                'is_virtual_meeting' =>$is_virtual_meeting,
            ];

            $queueCreated = Queue::storeQueue([
                'team_id' => $teamId,
                'token' => $token_start,
                'token_with_acronym' => Queue::LABEL_NO,
                'locations_id' => $booking->location_id,
                'arrives_time' => $todayDateTime,
                'last_category' => $lastcategory,
            ]);

            if ($is_virtual_meeting) {
                $room = 'room_' . base64_encode($queueCreated->id);
                $queueId = base64_encode($queueCreated->id);
                $storeData['meeting_link'] = url("meeting/{$room}/{$queueId}");
            } else {
                $storeData['meeting_link'] = null;
            }

            $queueStorage = QueueStorage::storeQueue(array_merge($storeData, ['queue_id' => $queueCreated->id]));

            $booking->is_convert = Booking::STATUS_YES;
            $booking->status = Booking::STATUS_COMPLETED;
            $booking->convert_datetime = $todayDateTime;
            $booking->save();

            // Salesforce
             try {
                $salesforcessettings = SalesforceSetting::where('team_id',  $teamId)
                    ->where('location_id', $booking->location_id)
                    ->first();

                $clientId = $salesforcessettings->client_id ?? null;
                $clientSecret = $salesforcessettings->client_secret ?? null;
                $tokenUrl = 'https://login.salesforce.com/services/oauth2/token';

                $refreshToken = SalesforceConnection::where('team_id', $teamId)
                    ->where('location_id', $booking->location_id)
                    ->where('status', 1)
                    ->value('salesforce_refresh_token');

                if (!empty($clientId) && !empty($clientSecret) && !empty($refreshToken)) {
                     $datetimeUtc = new \DateTime($queueStorage->arrives_time);
                    $datetimeUtc->setTimezone(new \DateTimeZone('UTC'));
                    $Qwaiting_Sync_Date__c = $datetimeUtc->format('Y-m-d\TH:i:s\Z');
                    
                    $assignUserSfId = null;
                     if (($siteDetails->assigned_staff_id ?? 0) == 1 && is_numeric($booking->staff_id)) {
                        $assignUserSfId = User::where('id', (int)$booking->staff_id)->value('saleforce_user_id');
                    } elseif (!empty($assigned_staff_id)) {
                        $assignUserSfId = User::where('id', $assigned_staff_id)->value('saleforce_user_id');
                    }

                    $customFields = json_decode($booking->json, true) ?: [];
                    $customFields = array_change_key_case($customFields, CASE_LOWER);
                    
                     $salesForceData = [
                        'refresh_token' => $refreshToken,
                        'FirstName' => $queueStorage->name ?? 'Guest',
                        'Phone' => $queueStorage->phone ?? '',
                        'Email' => $booking->email ?? '',
                        'Qwaiting_Sync_Date__c' => $Qwaiting_Sync_Date__c,
                        'Token' => $queueStorage->token ?? '',
                        'Page' => 'BookQueue',
                        'Created' => $queueStorage->created_at ?? now(),
                        'queue_storage_id' => $queueStorage->id,
                        'AssignId' => $assignUserSfId ?: '005Hu00000SBZ8bIAH',
                    ];
                    
                    $sfService = new SalesforceService($clientId, $clientSecret, $tokenUrl);
                    $leadResponse = $sfService->createLead($salesForceData);
                    $queueStorage->salesforce_lead = json_encode($leadResponse);
                    $queueStorage->save();
                }
            } catch (\Throwable $e) {
                 Log::error('Salesforce Error: ' . $e->getMessage());
            }

            QueueNotification::dispatch($queueStorage);
            QueueCreated::dispatch($queueStorage);
            
            // Calculate Wait Time & Pending
            $categoryName = "";
            if (!empty($queueStorage->category_id))
                $categoryName =  Category::viewCategoryName($queueStorage->category_id);
            if (!empty($queueStorage->sub_category_id))
                $categoryName = Category::viewCategoryName($queueStorage->sub_category_id); 
             if (!empty($queueStorage->child_category_id))
                $categoryName = Category::viewCategoryName($queueStorage->child_category_id); 

            // Determining Pending Count
            $pendingCount = 0;
            $waitingTime = 0;
            
            $countCatID = $selectedCategoryId;
            $fieldCatName = 'category_id';

             // determineCategoryColumn logic
             if (!empty($thirdChildId)) {
                if ($siteDetails?->category_level_est == 'automatic') {
                    $fieldCatName = 'child_category_id';
                    $countCatID =  $thirdChildId;
                } elseif ($siteDetails?->category_level_est == 'child') {
                    $fieldCatName = 'sub_category_id';
                    $countCatID =  $secondChildId;
                }
            } else if (!empty($secondChildId)) {
                if ($siteDetails?->category_level_est == 'child') {
                    $fieldCatName = 'sub_category_id';
                    $countCatID =  $secondChildId;
                }
            }
            
            if($siteDetails->category_estimated_time == SiteDetail::STATUS_YES &&  $siteDetails?->count_by_service == 0 ){
                  $estimatedetail = QueueStorage::countPendingByCategory($teamId, $queueStorage->id, $countCatID, $fieldCatName, '', $locationId);
                  if($estimatedetail == false){
                    $pendingCount = QueueStorage::countPending($teamId, $queueStorage->id, $countCatID, $fieldCatName, '', $locationId);
                  }else{
                    $pendingCount =$estimatedetail['customers_before_me'] ?? 0;
                    $pendingwaiting =$estimatedetail['estimated_wait_time'] ?? 0;
                  }
            }else{
                $pendingCountget = (int)QueueStorage::countPending($teamId, $queueStorage->id, '', '', '', $locationId);
                $counterCount = Counter::where('team_id',$teamId)->whereJsonContains('counter_locations', "$locationId")->where('show_checkbox',1)->count();
                if((int)$pendingCountget > 0 && (int)$counterCount > 0){
                     $pendingCount = floor((int)$pendingCountget / (int)$counterCount);
                }
            }
            
            $estimate_time = $siteDetails->estimate_time ?? 0;
             if($siteDetails->category_estimated_time == SiteDetail::STATUS_YES){ 
                 $waitingTime =  $pendingwaiting ?? $estimate_time * $pendingCount;
             }else{  
                 $waitingTime =  $estimate_time * $pendingCount;
             }

             // Handle Customer Creation Logs
             if (empty($booking->created_by)) {
                 if (!empty($queueStorage->phone)) {
                    $existingCustomer = Customer::where('phone', $queueStorage->phone)
                        ->where('team_id', $teamId)
                        ->where('location_id', $booking->location_id)
                        ->first();
                    if (!$existingCustomer) {
                        $existingCustomer = Customer::create([
                            'team_id' => $teamId,
                            'location_id' => $booking->location_id,
                            'name' => $booking->name ?? null,
                            'phone' => $queueStorage->phone,
                            'json_data' => $booking->json ?? '', 
                        ]);
                    }
                    CustomerActivityLog::create([
                        'team_id' => $teamId,
                        'location_id' => $booking->location_id,
                        'queue_id' => $queueStorage->id,
                        'booking_id' => null, 
                        'type' => 'queue',
                        'customer_id' => $existingCustomer->id,
                        'note' => 'Customer joined the queue.',
                    ]);
                    $queueStorage->created_by = $existingCustomer->id;
                    $queueStorage->save();
                 }
             } else {
                 $queueStorage->created_by = $booking->created_by;
                 $queueStorage->save();
             }
             
             $queueStorage->waiting_time = $waitingTime;
             $queueStorage->queue_count = $pendingCount;
             $queueStorage->save();
             
             $this->sendNotification($teamId, $booking, $queueStorage, $queueCreated, $acronym, $locationId);

            DB::commit();

            // QR Code
            $baseencodeQueueId = base64_encode($queueCreated->id);
            $customUrl = url("/visits/{$baseencodeQueueId}");
            
            $formatted_response = "Queue No.: $token_start\n";
            $formatted_response .= "Arrived: " . $todayDateTime->format('d-m-Y H:i:s') . "\n";
            $formatted_response .= "$categoryName\n";
            $formatted_response .= "$pendingCount Queue before you\n";
            $formatted_response .= "Your estimated waiting time is $waitingTime\n";
            $formatted_response .= "$customUrl";

            return response($formatted_response, 200)->header('Content-Type', 'text/plain');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex);
             return response('Error: ' . $ex->getMessage(), 500)->header('Content-Type', 'text/plain');
        }
    }

    private function getNextPrioritySortDirect($teamId, $location, $categoryId)
    {
        $category = Category::find($categoryId);
        if(!$category) return 0;
        
         $categories = Category::where('team_id', $teamId)
        ->where(function ($query) {
            $query->whereNull('parent_id')
                  ->orWhere('parent_id', '');
        })
        ->whereJsonContains('category_locations', (string)$location)
        ->orderBy('sort')
        ->pluck('visitor_in_queue', 'id');
        
        $sequencePattern = $categories;

        $nextserial = 1;
        $filteredCategories = $sequencePattern->except($category->id);
        $sumVisitorInQueue = $filteredCategories->sum() + ($sequencePattern[$category->id] ?? 0);
        
        $queues = QueueStorage::where('team_id', $teamId)
            ->where('locations_id', $location)
            ->where('category_id', $category->id)
            ->whereNotNull('priority_sort')
            ->orderBy('priority_sort')
            ->whereDate('created_at', Carbon::today())
            ->pluck('priority_sort')
            ->toArray();

         if (!empty($queues)) {
            $maxValue = max($queues);
            if ($maxValue == 0) {
                $maxValue = $nextserial;
                $queues = [];
            }
        } else {
            $maxValue = $nextserial;
        }
        
         if (($sequencePattern[$category->id] ?? 0) == 1) {
            if (!empty($queues)) {
                return $nextserial = $maxValue + $sumVisitorInQueue;
            } else {
                $categoriesArray = $sequencePattern->toArray();
                $slicedArray = array_slice($categoriesArray, 0, array_search($category->id, array_keys($categoriesArray)));
                $sumBefore = array_sum($slicedArray);
                return $nextserial = $maxValue + $sumBefore;
            }
        } elseif (($sequencePattern[$category->id] ?? 0) > 1) {
              $countserial = 0;
            if (!empty($queues)) {
                for ($i = $maxValue; $i >= 1; $i--) {
                     $checkSort = QueueStorage::where('team_id', $teamId)
                        ->where('locations_id', $location)
                        ->where('category_id', $category->id)
                        ->whereNotNull('priority_sort')
                        ->whereDate('created_at', Carbon::today())
                        ->where('priority_sort', $i)
                        ->exists();
                    if ($checkSort) {
                        $countserial += 1;
                    } else {
                        break;
                    }
                }
                if ($countserial == $sequencePattern[$category->id]) {
                    return $nextserial = $maxValue + $sumVisitorInQueue - 1;
                } else {
                    return $nextserial = $maxValue + 1;
                }
            } else {
                $categoriesArray = $sequencePattern->toArray();
                $slicedArray = array_slice($categoriesArray, 0, array_search($category->id, array_keys($categoriesArray)));
                $sumBefore = array_sum($slicedArray);
                return $nextserial = $maxValue + $sumBefore;
            }
        }
        return $nextserial;
    }
    
     public function sendNotification($teamId, $booking, $queueStorage, $queueCreated, $acronym, $locationId)
     {
         $data = [
                'name' => $queueStorage->name ?? '',
                'phone' => $queueStorage->phone ?? '',
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueCreated->id,
                'arrives_time' => Carbon::parse($queueCreated->created_at)->format(AccountSetting::showDateTimeFormat()),
                'token' => $queueCreated->token,
                'token_with_acronym' => $queueCreated->start_acronym,
                'to_mail' => $booking->email ?? '',
                'locations_id' => $booking->location_id,
                'team_id' => $teamId
         ];
         
         $logData = [
                'team_id' => $teamId,
                'location_id' => $locationId,
                'user_id' => $queueStorage->served_by,
                'customer_id' => $queueStorage->created_by,
                'queue_id' => $queueStorage->queue_id,
                'queue_storage_id' => $queueStorage->id,
                'email' => $booking->email ?? '',
                'contact' => $queueStorage->phone,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'Ticket Generate',
            ];
            
         $data['location_id'] = $locationId;
         $type = 'ticket created';
         
          if (isset($data['to_mail']) && $data['to_mail'] != '') {
                $logData['channel'] = 'email';
                $logData['status'] = MessageDetail::SENT_STATUS;
                SmtpDetails::sendMail($data, $type, 'ticket-created', $teamId,$logData);
            }

            if (!empty($data['phone'])) {
                SmsAPI::sendSms( $teamId, $data,$type,$type,$logData);
            }
     }
}

