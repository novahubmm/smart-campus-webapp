<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\User;

class TeacherProfile extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'user_id',
        'employee_id',
        'position',
        'department_id',
        'ethnicity',
        'religious',
        'hire_date',
        'basic_salary',
        'gender',
        'ethnicity',
        'religious',
        'nrc',
        'dob',
        'phone_no',
        'address',
        'current_grades',
        'current_classes',
        'subjects_taught',
        'responsible_class',
        'previous_school',
        'qualification',
        'previous_experience_years',
        'green_card',
        'father_name',
        'father_phone',
        'mother_name',
        'in_school_relative_name',
        'in_school_relative_relationship',
        'mother_phone',
        'emergency_contact',
        'marital_status',
        'partner_name',
        'partner_phone',
        'height',
        'weight',
        'blood_type',
        'medicine_allergy',
        'food_allergy',
        'medical_directory',
        'photo_path',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'dob' => 'date',
        'basic_salary' => 'decimal:2',
        'previous_experience_years' => 'integer',
        'subjects_taught' => 'array',
        'current_classes' => 'array',
        'current_grades' => 'array',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_profile_id', 'subject_id')->withTimestamps();
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id');
    }

    /**
     * Get formatted display name with employee ID
     * Format: Name [employee_id]
     */
    public function getDisplayNameAttribute()
    {
        $name = $this->user->name ?? 'Teacher';
        return "{$name} [{$this->employee_id}]";
    }

}
