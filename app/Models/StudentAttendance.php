<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAttendance extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'student_attendance';

    protected $fillable = [
        'student_id',
        'period_id',
        'date',
        'status',
        'remark',
        'marked_by',
        'collect_time',
        'period_number'
    ];

    protected $casts = [
        'date' => 'date',
        'collect_time' => 'datetime:H:i',
    ];

    /**
     * Get the student profile this attendance record belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    /**
     * Get the period this attendance is for.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Get the user who marked this attendance.
     */
    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Alias for marker relationship.
     */
    public function markedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
