<?php

namespace App\Services;

use App\Models\Setting;

class FeatureService
{
    /**
     * Check if a feature is enabled
     */
    public function isEnabled(string $feature): bool
    {
        $setting = Setting::first();
        
        if (!$setting || !$setting->enabled_features) {
            // If no settings or no features configured, enable all by default
            return true;
        }

        return in_array($feature, $setting->enabled_features);
    }

    /**
     * Get all enabled features
     */
    public function getEnabledFeatures(): array
    {
        $setting = Setting::first();
        return $setting->enabled_features ?? [];
    }

    /**
     * Enable a feature
     */
    public function enable(string $feature): void
    {
        $setting = Setting::firstOrFail();
        $features = $setting->enabled_features ?? [];
        
        if (!in_array($feature, $features)) {
            $features[] = $feature;
            $setting->update(['enabled_features' => $features]);
        }
    }

    /**
     * Disable a feature
     */
    public function disable(string $feature): void
    {
        $setting = Setting::firstOrFail();
        $features = $setting->enabled_features ?? [];
        
        $features = array_filter($features, fn($f) => $f !== $feature);
        $setting->update(['enabled_features' => array_values($features)]);
    }

    /**
     * Set enabled features
     */
    public function setFeatures(array $features): void
    {
        $setting = Setting::firstOrFail();
        $setting->update(['enabled_features' => $features]);
    }

    /**
     * Get all available features
     */
    public function getAvailableFeatures(): array
    {
        return [
            'announcements' => 'Announcements',
            'attendance' => 'Attendance Management',
            'timetable' => 'Timetable',
            'exams' => 'Exams & Results',
            'homework' => 'Homework',
            'fees' => 'Fee Management',
            'payroll' => 'Payroll',
            'reports' => 'Reports',
            'events' => 'Events',
            'leave_requests' => 'Leave Requests',
            'daily_reports' => 'Daily Reports',
            'curriculum' => 'Curriculum',
            'rules' => 'School Rules',
        ];
    }
}
