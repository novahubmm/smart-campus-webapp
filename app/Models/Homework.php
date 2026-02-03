<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Homework extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'homework';

    protected $fillable = [
        'title',
        'description',
        'class_id',
        'subject_id',
        'teacher_id',
        'period_id',
        'assigned_date',
        'due_date',
        'priority',
        'status',
        'attachment',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function getSubmissionCountAttribute(): int
    {
        return $this->submissions()->whereIn('status', ['submitted', 'graded'])->count();
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->status === 'active';
    }
}
