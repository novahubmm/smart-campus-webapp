<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuidPrimaryKey;

class Setting extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'school_name',
        'school_email',
        'school_phone',
        'school_address',
        'school_website',
        'school_about_us',
        'school_logo_path',
        'school_short_logo_path',
        'principal_name',
        'setup_completed_school_info',
        'setup_completed_event_and_announcements',
        'setup_completed_academic',
        'setup_completed_time_table_and_attendance',
        'setup_completed_finance',
        'announcement_notify_email',
        'announcement_notify_push',
        'announcement_notify_in_app',
        'payment_frequency',
        'late_fee_percentage',
        'late_fee_grace_period',
        'default_discount_percentage',
        'number_of_periods_per_day',
        'minute_per_period',
        'break_duration',
        'school_start_time',
        'school_end_time',
        'week_days',
        'timetable_time_format',
        'tuition_fee_by_grade',
        'maintenance_mode',
        'maintenance_message',
    ];

    protected $casts = [
        'setup_completed_school_info' => 'boolean',
        'setup_completed_event_and_announcements' => 'boolean',
        'setup_completed_academic' => 'boolean',
        'setup_completed_time_table_and_attendance' => 'boolean',
        'setup_completed_finance' => 'boolean',
        'announcement_notify_email' => 'boolean',
        'announcement_notify_push' => 'boolean',
        'announcement_notify_in_app' => 'boolean',
        'maintenance_mode' => 'boolean',
        'late_fee_percentage' => 'decimal:2',
        'default_discount_percentage' => 'decimal:2',
        'number_of_periods_per_day' => 'integer',
        'minute_per_period' => 'integer',
        'break_duration' => 'integer',
        'late_fee_grace_period' => 'integer',
        'week_days' => 'array',
        'tuition_fee_by_grade' => 'array',
    ];

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode(): bool
    {
        $setting = self::first();
        return $setting ? $setting->maintenance_mode : false;
    }

    /**
     * Get maintenance message
     */
    public static function getMaintenanceMessage(): string
    {
        $setting = self::first();
        return $setting && $setting->maintenance_message 
            ? $setting->maintenance_message 
            : 'The system is currently under maintenance. Please try again later.';
    }
}
