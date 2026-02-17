<?php

namespace App\Http\Requests\StudentProfile;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionEnum::MANAGE_STUDENT_PROFILES->value) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50', 'unique:users,phone'],
            'nrc' => ['nullable', 'string', 'max:50', 'unique:users,nrc'],
            'password' => ['required_without:user_id', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],

            'student_identifier' => ['required', 'string', 'max:255', 'unique:student_profiles,student_identifier'],
            'starting_grade_at_school' => ['nullable', 'string', 'max:255'],
            'previous_grade' => ['required', 'string', 'max:255'],
            'previous_class' => ['required', 'string', 'max:255'],
            'guardian_teacher' => ['nullable', 'string', 'max:255'],
            'assistant_teacher' => ['nullable', 'string', 'max:255'],
            'date_of_joining' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'ethnicity' => ['nullable', 'string', 'max:100'],
            'religious' => ['nullable', 'string', 'max:100'],
            'dob' => ['required', 'date'],
            'previous_school_name' => ['required', 'string', 'max:255'],
            'previous_school_address' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'father_name' => ['required', 'string', 'max:255'],
            'father_nrc' => ['required', 'string', 'max:100'],
            'father_religious' => ['required', 'string', 'max:100'],
            'father_phone_no' => ['nullable', 'string', 'max:50'],
            'father_occupation' => ['required', 'string', 'max:255'],
            'father_address' => ['required', 'string', 'max:500'],
            'mother_name' => ['required', 'string', 'max:255'],
            'mother_nrc' => ['required', 'string', 'max:100'],
            'mother_religious' => ['required', 'string', 'max:100'],
            'mother_phone_no' => ['nullable', 'string', 'max:50'],
            'mother_occupation' => ['required', 'string', 'max:255'],
            'mother_address' => ['required', 'string', 'max:500'],
            'emergency_contact_phone_no' => ['nullable', 'string', 'max:50'],
            'in_school_relative_name' => ['nullable', 'string', 'max:255'],
            'in_school_relative_grade' => ['nullable', 'string', 'max:100'],
            'in_school_relative_relationship' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'medicine_allergy' => ['nullable', 'string', 'max:255'],
            'food_allergy' => ['nullable', 'string', 'max:255'],
            'medical_directory' => ['nullable', 'string', 'max:1000'],
            'photo_path' => ['nullable', 'string', 'max:500'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'grade_id' => ['nullable', 'exists:grades,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
