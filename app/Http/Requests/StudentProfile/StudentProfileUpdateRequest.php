<?php

namespace App\Http\Requests\StudentProfile;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionEnum::MANAGE_STUDENT_PROFILES->value) ?? false;
    }

    public function rules(): array
    {
        $profile = $this->route('student_profile');
        
        // Handle both model instance and string UUID
        if ($profile instanceof \App\Models\StudentProfile) {
            $profileId = $profile->id;
            $userId = $profile->user_id;
        } elseif (is_string($profile)) {
            $studentProfile = \App\Models\StudentProfile::find($profile);
            $profileId = $studentProfile?->id;
            $userId = $studentProfile?->user_id;
        } else {
            $profileId = null;
            $userId = null;
        }

        return [
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'nrc' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'nrc')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],

            'student_identifier' => [
                'required',
                'string',
                'max:255',
                Rule::unique('student_profiles', 'student_identifier')->ignore($profileId),
            ],
            'starting_grade_at_school' => ['nullable', 'string', 'max:255'],
            'current_grade' => ['nullable', 'string', 'max:255'],
            'current_class' => ['nullable', 'string', 'max:255'],
            'guardian_teacher' => ['nullable', 'string', 'max:255'],
            'assistant_teacher' => ['nullable', 'string', 'max:255'],
            'date_of_joining' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'ethnicity' => ['nullable', 'string', 'max:100'],
            'religious' => ['nullable', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'previous_school_name' => ['nullable', 'string', 'max:255'],
            'previous_school_address' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_nrc' => ['nullable', 'string', 'max:100'],
            'father_phone_no' => ['nullable', 'string', 'max:50'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_nrc' => ['nullable', 'string', 'max:100'],
            'mother_phone_no' => ['nullable', 'string', 'max:50'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
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
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'photo_path' => ['nullable', 'string', 'max:500'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'grade_id' => ['nullable', 'exists:grades,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
