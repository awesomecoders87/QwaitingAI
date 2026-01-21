<?php

use App\Models\AccountSetting;
use App\Models\CustomSlot;
use App\Models\ServiceSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

if (!function_exists('formatDuration')) {
    function formatDuration($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        return $days > 0 ? "{$days}d {$formatted}" : $formatted;
    }
}

if (!function_exists('checkStaffAvailability')) {
    function checkStaffAvailability($staffId, $appointmentDate, $slotPeriod, $teamId, $locationId, $startTime, $endTime)
    {
        $availableSlots = [];
        $type = "staff";

        // Check for custom slots
        $customSlotQuery = CustomSlot::whereDate('selected_date', $appointmentDate)
            ->where('slots_type', $type)
            ->where('team_id', $teamId)
            ->where('location_id', $locationId);

        if ($type == "staff") {
            $customSlotQuery->where('user_id', $staffId);
        }

        $customSlot = $customSlotQuery->first();
        $dayOfWeek = Carbon::parse($appointmentDate)->format('l');

        // Use business hours from custom slots if available
        if (isset($customSlot)) {
            $businessHours_get = json_decode($customSlot->business_hours, true);
            $businessHours = $businessHours_get[0];
        } else {
            // Retrieve all account settings for the staff
            $staffAccount = AccountSetting::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->where('user_id', $staffId)
                ->where('slot_type', AccountSetting::STAFF_SLOT)
                ->first();

            if (!$staffAccount) {
                return false;
            }

            $decodedHours = json_decode($staffAccount->business_hours, true);
            $indexedBusinessHours = collect($decodedHours)->keyBy('day');

            if (!isset($indexedBusinessHours[$dayOfWeek])) {
                return false;
            }

            $businessHours = $indexedBusinessHours[$dayOfWeek];
        }

        if (isset($businessHours) && $businessHours['is_closed'] == ServiceSetting::SERVICE_OPEN) {
            $availableSlots = new Collection();
            $mainSlots = AccountSetting::generateSlots($businessHours['start_time'], $businessHours['end_time'], $slotPeriod);
            $availableSlots = $availableSlots->concat($mainSlots);

            if (!empty($businessHours['day_interval'])) {
                foreach ($businessHours['day_interval'] as $interval) {
                    $intervalSlots = AccountSetting::generateSlots($interval['start_time'], $interval['end_time'], $slotPeriod);
                    $availableSlots = $availableSlots->concat($intervalSlots);
                }
            }

            // Now check if the selected slot is fully within available slots
            $selectedStart = Carbon::parse($startTime)->format('H:i');
            $selectedEnd = Carbon::parse($endTime)->format('H:i');
            $slotRange = AccountSetting::generateSlots($selectedStart, $selectedEnd, $slotPeriod);

            foreach ($slotRange as $slot) {
                if (!$availableSlots->contains($slot)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
