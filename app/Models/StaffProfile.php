<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\User;

class StaffProfile extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'user_id',
        'employee_id',
        'position',
        'department_id',
        'hire_date',
        'basic_salary',
        'gender',
        'ethnicity',
        'religious',
        'nrc',
        'dob',
        'phone_no',
        'address',
        'qualification',
        'previous_experience_years',
        'green_card',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'emergency_contact',
        'marital_status',
        'partner_name',
        'partner_phone',
        'relative_name',
        'relative_relationship',
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
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
