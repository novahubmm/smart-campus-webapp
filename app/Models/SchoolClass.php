<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'grade_id',
        'batch_id',
        'name',
        'teacher_id',
        'room_id',
        'class_leader_id',
    ];

    /**
     * Get the class leader (student).
     */
    public function classLeader(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'class_leader_id');
    }

    /**
     * Get the grade this class belongs to.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the batch this class belongs to.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the class teacher.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    /**
     * Get the room assigned to this class.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the students enrolled in this class.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(StudentProfile::class, 'student_class', 'class_id', 'student_id')
            ->using(StudentClass::class)
            ->withTimestamps();
    }

    /**
     * Get students directly assigned to this class via class_id.
     */
    public function enrolledStudents(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'class_id');
    }

    /**
     * Get the subjects taught in this class.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withTimestamps();
    }
}
