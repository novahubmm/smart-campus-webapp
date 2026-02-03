<?php

use App\Models\Setting;
use Carbon\Carbon;

if (!function_exists('format_time')) {
    /**
     * Format time based on global timetable time format setting (12h or 24h).
     *
     * @param string|Carbon|null $time The time to format (H:i or Carbon instance)
     * @return string|null Formatted time string
     */
    function format_time($time): ?string
    {
        if (!$time) {
            return null;
        }

        // Parse time if it's a string
        if (is_string($time)) {
            // Handle various time formats
            if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                $carbon = Carbon::createFromFormat('H:i', $time);
            } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                $carbon = Carbon::createFromFormat('H:i:s', $time);
            } else {
                $carbon = Carbon::parse($time);
            }
        } elseif ($time instanceof Carbon) {
            $carbon = $time;
        } else {
            return null;
        }

        // Get time format setting (cached for performance)
        static $timeFormat = null;
        if ($timeFormat === null) {
            $setting = Setting::first();
            $timeFormat = $setting?->timetable_time_format ?? '24h';
        }

        if ($timeFormat === '12h') {
            return $carbon->format('g:i A'); // e.g., "8:00 AM"
        }

        return $carbon->format('H:i'); // e.g., "08:00"
    }
}

if (!function_exists('get_time_format')) {
    /**
     * Get the current timetable time format setting.
     *
     * @return string '12h' or '24h'
     */
    function get_time_format(): string
    {
        static $timeFormat = null;
        if ($timeFormat === null) {
            $setting = Setting::first();
            $timeFormat = $setting?->timetable_time_format ?? '24h';
        }
        return $timeFormat;
    }
}

if (!function_exists('storage_url')) {
    /**
     * Generate a URL for a storage path that works with the current request's host.
     * This is useful for local network access where APP_URL might not match the actual host.
     *
     * @param string|null $path The storage path (relative to storage/app/public)
     * @param string|null $default Default path if $path is null/empty
     * @return string
     */
    function storage_url(?string $path, ?string $default = null): string
    {
        $storagePath = $path ?: $default;
        
        if (!$storagePath) {
            return '';
        }

        // Use the current request's scheme and host for local network compatibility
        $baseUrl = request()->getSchemeAndHttpHost();
        
        // Ensure path doesn't start with /
        $storagePath = ltrim($storagePath, '/');
        
        // If path doesn't start with 'storage/', add it
        if (!str_starts_with($storagePath, 'storage/')) {
            $storagePath = 'storage/' . $storagePath;
        }
        
        return $baseUrl . '/' . $storagePath;
    }
}

if (!function_exists('avatar_url')) {
    /**
     * Generate an avatar URL with a default fallback.
     *
     * @param string|null $photoPath The photo path from the model
     * @param string $type 'teacher', 'student', or 'staff'
     * @return string
     */
    function avatar_url(?string $photoPath, string $type = 'student'): string
    {
        $defaults = [
            'teacher' => 'default_profile.jpg',
            'student' => 'student_default_profile.jpg',
            'staff' => 'default_profile.jpg',
        ];
        
        $default = $defaults[$type] ?? 'default_profile.jpg';
        $baseUrl = request()->getSchemeAndHttpHost();

        if (! $photoPath) {
            return $baseUrl . '/images/' . $default;
        }

        // If path starts with 'images/', it's a public asset (demo data)
        // Otherwise, it's in storage
        if (str_starts_with($photoPath, 'images/')) {
            return $baseUrl . '/' . ltrim($photoPath, '/');
        }

        return storage_url($photoPath, $default);
    }
}
