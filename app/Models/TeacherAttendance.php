<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $table = 'teacher_attendance';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'teacher_id',
        'date',
        'day_of_week',
        'check_in_time',
        'check_out_time',
        'check_in_timestamp',
        'check_out_timestamp',
        'working_hours_decimal',
        'status',
        'leave_type',
        'remarks',
        'location_lat',
        'location_lng',
        'device_info',
        'app_version',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_timestamp' => 'datetime',
        'check_out_timestamp' => 'datetime',
        'working_hours_decimal' => 'decimal:2',
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Generate unique ID for attendance
     */
    public static function generateId(string $date): string
    {
        $dateStr = str_replace('-', '', $date);
        $count = self::whereDate('date', $date)->count() + 1;
        return 'att_' . $dateStr . '_' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate working hours from check-in and check-out times
     */
    public static function calculateWorkingHours($checkInTime, $checkOutTime): float
    {
        if (!$checkInTime || !$checkOutTime) {
            return 0;
        }

        $checkIn = \Carbon\Carbon::parse($checkInTime);
        $checkOut = \Carbon\Carbon::parse($checkOutTime);
        
        return round($checkIn->diffInMinutes($checkOut) / 60, 2);
    }

    /**
     * Format working hours as HH:MM
     */
    public function getWorkingHoursAttribute(): ?string
    {
        if (!$this->working_hours_decimal) {
            return null;
        }

        $hours = floor($this->working_hours_decimal);
        $minutes = round(($this->working_hours_decimal - $hours) * 60);
        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * Get elapsed time since check-in (for currently checked-in status)
     */
    public function getElapsedTimeAttribute(): ?string
    {
        if (!$this->check_in_timestamp || $this->check_out_timestamp) {
            return null;
        }

        $now = now();
        $checkIn = \Carbon\Carbon::parse($this->check_in_timestamp);
        $minutes = $checkIn->diffInMinutes($now);
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%d:%02d', $hours, $mins);
    }
}
