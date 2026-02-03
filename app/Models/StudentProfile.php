<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Grade;
use App\Models\SchoolClass;

class StudentProfile extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'user_id',
        'student_id',
        'student_identifier',
        'starting_grade_at_school',
        'current_grade',
        'current_class',
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
        'grade_id',
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

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
