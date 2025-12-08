<?php

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
