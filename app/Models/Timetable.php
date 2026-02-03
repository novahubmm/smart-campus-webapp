<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timetable extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'batch_id',
        'grade_id',
        'class_id',
        'name',
        'version_name',
        'is_active',
        'published_at',
        'effective_from',
        'effective_to',
        'minutes_per_period',
        'break_duration',
        'school_start_time',
        'school_end_time',
        'week_days',
        'number_of_periods_per_day',
        'custom_period_times',
        'use_custom_settings',
        'version',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'published_at' => 'datetime',
        'week_days' => 'array',
        'custom_period_times' => 'array',
        'minutes_per_period' => 'integer',
        'break_duration' => 'integer',
        'number_of_periods_per_day' => 'integer',
        'school_start_time' => 'datetime:H:i',
        'school_end_time' => 'datetime:H:i',
        'version' => 'integer',
        'is_active' => 'boolean',
        'use_custom_settings' => 'boolean',
    ];

    /**
     * Set the week_days attribute, converting full day names to short format
     */
    public function setWeekDaysAttribute($value): void
    {
        if (is_array($value)) {
            $shortDays = array_map(function ($day) {
                $day = strtolower($day);
                // Convert full day names to short format (monday -> mon)
                return strlen($day) > 3 ? substr($day, 0, 3) : $day;
            }, $value);
            $this->attributes['week_days'] = json_encode($shortDays);
        } else {
            $this->attributes['week_days'] = $value;
        }
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active timetable for a class
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get inactive timetables
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to get timetables for a specific class
     */
    public function scopeForClass($query, string $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Get display name for the version
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->version_name) {
            return $this->version_name;
        }
        return $this->name ?? "Version {$this->version}";
    }

    /**
     * Check if timetable can be edited (only inactive timetables can be edited)
     */
    public function getCanEditAttribute(): bool
    {
        return !$this->is_active;
    }

    /**
     * Check if timetable can be deleted (only inactive timetables can be deleted)
     */
    public function getCanDeleteAttribute(): bool
    {
        return !$this->is_active;
    }

    /**
     * Get the effective number of periods per day
     */
    public function getEffectivePeriodsPerDayAttribute(): int
    {
        if ($this->use_custom_settings && $this->number_of_periods_per_day) {
            return $this->number_of_periods_per_day;
        }
        
        // Fallback to global setting
        $setting = Setting::first();
        return $setting?->number_of_periods_per_day ?? 8;
    }

    /**
     * Get the effective minutes per period
     */
    public function getEffectiveMinutesPerPeriodAttribute(): int
    {
        if ($this->use_custom_settings && $this->minutes_per_period) {
            return $this->minutes_per_period;
        }
        
        // Fallback to global setting
        $setting = Setting::first();
        return $setting?->minute_per_period ?? 45;
    }

    /**
     * Get the effective break duration
     */
    public function getEffectiveBreakDurationAttribute(): int
    {
        if ($this->use_custom_settings && $this->break_duration !== null) {
            return $this->break_duration;
        }
        
        // Fallback to global setting
        $setting = Setting::first();
        return $setting?->break_duration ?? 15;
    }

    /**
     * Get the effective school start time
     */
    public function getEffectiveSchoolStartTimeAttribute(): string
    {
        if ($this->use_custom_settings && $this->school_start_time) {
            return $this->school_start_time->format('H:i');
        }
        
        // Fallback to global setting
        $setting = Setting::first();
        return $setting?->school_start_time ?? '08:00';
    }

    /**
     * Get the effective school end time
     */
    public function getEffectiveSchoolEndTimeAttribute(): string
    {
        if ($this->use_custom_settings && $this->school_end_time) {
            return $this->school_end_time->format('H:i');
        }
        
        // Fallback to global setting
        $setting = Setting::first();
        return $setting?->school_end_time ?? '15:00';
    }

    /**
     * Calculate period times based on settings
     */
    public function calculatePeriodTimes(): array
    {
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->effective_school_start_time);
        $periodsPerDay = $this->effective_periods_per_day;
        $minutesPerPeriod = $this->effective_minutes_per_period;
        $breakDuration = $this->effective_break_duration;
        
        $periods = [];
        $currentTime = $startTime->copy();
        
        for ($i = 1; $i <= $periodsPerDay; $i++) {
            $periodStart = $currentTime->copy();
            $periodEnd = $currentTime->copy()->addMinutes($minutesPerPeriod);
            
            $periods[$i] = [
                'period_number' => $i,
                'starts_at' => $periodStart->format('H:i'),
                'ends_at' => $periodEnd->format('H:i'),
                'duration' => $minutesPerPeriod
            ];
            
            // Move to next period (add period duration + break)
            $currentTime->addMinutes($minutesPerPeriod + $breakDuration);
        }
        
        return $periods;
    }

    /**
     * Get custom period time for a specific period
     */
    public function getCustomPeriodTime(int $periodNumber): ?array
    {
        if (!$this->use_custom_settings || !$this->custom_period_times) {
            return null;
        }
        
        return $this->custom_period_times[$periodNumber] ?? null;
    }

    /**
     * Set custom period time for a specific period
     */
    public function setCustomPeriodTime(int $periodNumber, string $startTime, string $endTime): void
    {
        $customTimes = $this->custom_period_times ?? [];
        $customTimes[$periodNumber] = [
            'starts_at' => $startTime,
            'ends_at' => $endTime
        ];
        $this->custom_period_times = $customTimes;
    }
}
