<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherFreePeriodActivity extends Model
{
    use HasUuidPrimaryKey, SoftDeletes;

    protected $fillable = [
        'teacher_profile_id',
        'activity_type_id',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'duration_minutes' => 'integer',
    ];

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(FreePeriodActivityType::class, 'activity_type_id');
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
}
