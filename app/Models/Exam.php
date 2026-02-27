<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'exam_id',
        'name',
        'exam_type_id',
        'batch_id',
        'grade_id',
        'class_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($exam) {
            if (empty($exam->exam_id)) {
                $exam->exam_id = self::generateExamId();
            }
        });
    }

    /**
     * Generate a unique exam ID.
     */
    private static function generateExamId(): string
    {
        $year = now()->year;
        $prefix = "EX-{$year}-";
        
        // Get the last exam ID for this year
        $lastExam = self::where('exam_id', 'like', "{$prefix}%")
            ->orderBy('exam_id', 'desc')
            ->first();
        
        if ($lastExam && preg_match('/-(\d+)$/', $lastExam->exam_id, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the exam type.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * Get the batch this exam is for.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the grade this exam is for.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the class this exam is for.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the exam schedules.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class)->orderBy('exam_date')->orderBy('start_time');
    }

    /**
     * Alias for schedules() - for API consistency.
     */
    public function examSchedules(): HasMany
    {
        return $this->schedules();
    }

    /**
     * Get the exam marks.
     */
    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class);
    }

    /**
     * Scope to get active exams.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['upcoming', 'ongoing']);
    }

    /**
     * Scope to get upcoming exams.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    /**
     * Scope to get ongoing exams.
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    /**
     * Scope to get completed exams.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get exams by batch.
     */
    public function scopeForBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }
}
