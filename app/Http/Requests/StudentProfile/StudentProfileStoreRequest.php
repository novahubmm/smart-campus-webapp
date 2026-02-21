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
        $userId = $this->input('user_id');
        
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:50', Rule::unique('users', 'phone')->ignore($userId)],
            'nrc' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nrc')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],

            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'student_identifier' => ['nullable', 'string', 'max:255', 'unique:student_profiles,student_identifier'],
            'starting_grade_at_school' => ['nullable', 'string', 'max:255'],
            'previous_grade' => ['nullable', 'string', 'max:255'],
            'previous_class' => ['nullable', 'string', 'max:255'],
            'guardian_teacher' => ['nullable', 'string', 'max:255'],
            'assistant_teacher' => ['nullable', 'string', 'max:255'],
            'date_of_joining' => ['nullable', 'date'],
            'gender' => ['required', 'string', 'max:50'],
            'ethnicity' => ['required', 'string', 'max:100'],
            'religious' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date'],
            'previous_school_name' => ['nullable', 'string', 'max:255'],
            'previous_school_address' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'father_name' => ['required', 'string', 'max:255'],
            'father_nrc' => ['required', 'string', 'max:100'],
            'father_religious' => ['nullable', 'string', 'max:100'],
            'father_phone_no' => ['required', 'string', 'max:50'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'father_address' => ['nullable', 'string', 'max:500'],
            'mother_name' => ['required', 'string', 'max:255'],
            'mother_nrc' => ['required', 'string', 'max:100'],
            'mother_religious' => ['nullable', 'string', 'max:100'],
            'mother_phone_no' => ['required', 'string', 'max:50'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
            'mother_address' => ['nullable', 'string', 'max:500'],
            'emergency_contact_phone_no' => ['required', 'string', 'max:50'],
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
            'grade_id' => ['required', 'exists:grades,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            
            // Guardian fields - conditional based on guardian type
            'existing_guardian_id' => ['nullable', 'exists:guardian_profiles,id'],
            'guardian_name' => ['required_without:existing_guardian_id', 'nullable', 'string', 'max:255'],
            'guardian_email' => ['required_without:existing_guardian_id', 'nullable', 'email', 'max:255', 'unique:users,email'],
            'guardian_phone' => ['required_without:existing_guardian_id', 'nullable', 'string', 'max:50'],
        ];
    }
}
