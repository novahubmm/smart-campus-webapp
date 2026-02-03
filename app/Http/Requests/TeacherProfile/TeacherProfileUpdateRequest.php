<?php

namespace App\Http\Requests\TeacherProfile;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionEnum::MANAGE_TEACHER_PROFILES->value) ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('teacher_profile')->user_id ?? null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'string', 'max:50', Rule::unique('users', 'phone')->ignore($userId)],
            'nrc' => ['required', 'string', 'max:50', Rule::unique('users', 'nrc')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],

            'employee_id' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'hire_date' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'gender' => ['nullable', 'string', 'max:50'],
            'ethnicity' => ['nullable', 'string', 'max:100'],
            'religious' => ['nullable', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone_no' => ['nullable', 'string', 'max:50'],
            'current_grades' => ['nullable', 'string', 'max:255'],
            'current_classes' => ['nullable', 'string', 'max:255'],
            'subjects_taught' => ['nullable', 'string', 'max:255'],
            'responsible_class' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'previous_experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
            'green_card' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:50'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'partner_phone' => ['nullable', 'string', 'max:50'],
            'in_school_relative_name' => ['nullable', 'string', 'max:255'],
            'in_school_relative_relationship' => ['nullable', 'string', 'max:100'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'medicine_allergy' => ['nullable', 'string', 'max:255'],
            'food_allergy' => ['nullable', 'string', 'max:255'],
            'medical_directory' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'role' => ['sometimes', Rule::in([RoleEnum::TEACHER->value])],
        ];
    }
}
