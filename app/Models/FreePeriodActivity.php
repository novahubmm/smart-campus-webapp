<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FreePeriodActivity extends Model
{
    use SoftDeletes;

    protected $table = 'free_period_activities';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'teacher_id',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'duration_minutes' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function activityItems(): HasMany
    {
        return $this->hasMany(FreePeriodActivityItem::class, 'activity_id');
    }

    /**
     * Calculate duration in minutes from start and end time
     */
    public static function calculateDuration(string $startTime, string $endTime): int
    {
        $start = \Carbon\Carbon::parse($startTime);
        $end = \Carbon\Carbon::parse($endTime);
        return $start->diffInMinutes($end);
    }

    /**
     * Generate unique ID for activity
     */
    public static function generateId(string $date): string
    {
        $dateStr = str_replace('-', '', $date);
        $count = self::whereDate('date', $date)->count() + 1;
        return 'fpa_' . $dateStr . '_' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Format working hours as HH:MM
     */
    public function getWorkingHoursAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }
}
