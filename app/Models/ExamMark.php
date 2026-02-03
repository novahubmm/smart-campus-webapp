<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\StudentProfile;

class ExamMark extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'exam_id',
        'student_id',
        'subject_id',
        'marks_obtained',
        'total_marks',
        'percentage',
        'grade',
        'remark',
        'entered_by',
        'is_absent'
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_absent' => 'boolean',
    ];

    /**
     * Get the exam this mark belongs to.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the student this mark belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    /**
     * Get the subject this mark is for.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the user who entered this mark.
     */
    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    /**
     * Calculate percentage automatically.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($examMark) {
            if ($examMark->total_marks > 0 && !$examMark->is_absent) {
                $examMark->percentage = ($examMark->marks_obtained / $examMark->total_marks) * 100;
            }
        });
    }
}
