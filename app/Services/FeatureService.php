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
        // System admin bypasses feature checks
        if (auth()->check() && auth()->user()->hasRole('system_admin')) {
            return true;
        }

        $setting = Setting::first();
        
        if (!$setting) {
            // If no settings exist, enable all by default
            return true;
        }

        // If enabled_features is null or not set, enable all by default
        if ($setting->enabled_features === null) {
            return true;
        }

        // If enabled_features is an array (even empty), check if feature is in it
        return in_array($feature, $setting->enabled_features);
    }

    /**
     * Get all enabled features
     */
    public function getEnabledFeatures(): array
    {
        $setting = Setting::first();
        
        // If no setting or enabled_features is null, return all features as enabled
        if (!$setting || $setting->enabled_features === null) {
            return array_keys($this->getAvailableFeatures());
        }
        
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
