<?php

namespace App\Http\Requests\StaffProfile;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionEnum::MANAGE_STAFF_PROFILES->value) ?? false;
    }

    public function rules(): array
    {
        $profile = $this->route('staff_profile');
        $userId = $profile?->user_id ?? null;

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
            'phone_no' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'ethnicity' => ['nullable', 'string', 'max:100'],
            'religious' => ['nullable', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'green_card' => ['nullable', 'string', 'max:255'],

            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:50'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],

            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'partner_phone' => ['nullable', 'string', 'max:50'],
            'relative_name' => ['nullable', 'string', 'max:255'],
            'relative_relationship' => ['nullable', 'string', 'max:100'],

            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'medicine_allergy' => ['nullable', 'string', 'max:255'],
            'food_allergy' => ['nullable', 'string', 'max:255'],
            'medical_directory' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
