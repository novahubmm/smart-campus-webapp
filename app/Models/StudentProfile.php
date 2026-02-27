<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Batch;

class StudentProfile extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'user_id',
        'student_id',
        'student_identifier',
        'starting_grade_at_school',
        'guardian_teacher',
        'assistant_teacher',
        'date_of_joining',
        'gender',
        'ethnicity',
        'religious',
        'nrc',
        'dob',
        'previous_school_name',
        'previous_school_address',
        'address',
        'father_name',
        'father_nrc',
        'father_phone_no',
        'father_occupation',
        'mother_name',
        'mother_nrc',
        'mother_phone_no',
        'mother_occupation',
        'emergency_contact_phone_no',
        'in_school_relative_name',
        'in_school_relative_grade',
        'in_school_relative_relationship',
        'blood_type',
        'weight',
        'height',
        'medicine_allergy',
        'food_allergy',
        'medical_directory',
        'photo_path',
        'class_id',
        'status',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
        'dob' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(GuardianProfile::class, 'guardian_student')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Get the primary guardian (for backward compatibility with singular guardian() calls)
     */
    public function guardian()
    {
        return $this->belongsToMany(GuardianProfile::class, 'guardian_student')
            ->withPivot(['relationship', 'is_primary'])
            ->wherePivot('is_primary', true)
            ->withTimestamps();
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the batch through the grade relationship
     */
    public function batch(): HasOneThrough
    {
        return $this->hasOneThrough(
            Batch::class,
            Grade::class,
            'id',        // Foreign key on grades table
            'id',        // Foreign key on batches table
            'grade_id',  // Local key on student_profiles table
            'batch_id'   // Local key on grades table
        );
    }
    /**
     * Get the fee type assignments for this student
     */
    public function feeTypeAssignments()
    {
        return $this->hasMany(\App\Models\StudentFeeTypeAssignment::class, 'student_id');
    }

    /**
     * Get the exam marks for this student
     */
    public function examMarks()
    {
        return $this->hasMany(\App\Models\ExamMark::class, 'student_id');
    }



    /**
     * Check if student is male leader of their class.
     */
    public function isMaleLeader(): bool
    {
        return $this->classModel && $this->classModel->male_class_leader_id === $this->id;
    }

    /**
     * Check if student is female leader of their class.
     */
    public function isFemaleLeader(): bool
    {
        return $this->classModel && $this->classModel->female_class_leader_id === $this->id;
    }
    /**
     * Get formatted class name with grade level and section
     * e.g., "Kindergarten A" for Grade 0, "Grade 1 A" for Grade 1
     */
    public function getFormattedClassNameAttribute(): string
    {
        if (!$this->grade || !$this->classModel) {
            return '-';
        }

        $gradeLevel = $this->grade->level;
        $className = $this->classModel->name;

        // Extract section from class name (e.g., "A" from "A" or "Grade 1 A")
        $section = \App\Helpers\SectionHelper::extractSection($className);

        // If no section found and className is just a single letter, use it as the section
        if ($section === null && preg_match('/^[A-Za-z]$/', trim($className))) {
            $section = strtoupper(trim($className));
        }

        // Format the class name with localized grade
        return \App\Helpers\GradeHelper::formatClassName($gradeLevel, $section);
    }

}
