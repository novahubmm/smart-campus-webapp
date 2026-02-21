<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'size:1',
                'regex:/^[A-Z]$/',
            ],
            'grade_id' => ['required', 'uuid', 'exists:grades,id'],
            'batch_id' => ['nullable', 'uuid', 'exists:batches,id'],
            'teacher_id' => ['nullable', 'uuid', 'exists:teacher_profiles,id'],
            'room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => __('academic_management.Class name must be a single uppercase letter (A-Z)'),
            'name.size' => __('academic_management.Class name must be a single letter'),
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $classId = $this->route('id');
            
            // Check for duplicate: same grade + same name (excluding current class)
            $query = \App\Models\SchoolClass::where('name', $this->name)
                ->where('grade_id', $this->grade_id);
            
            // Exclude current class from check
            if ($classId) {
                $query->where('id', '!=', $classId);
            }
            
            if ($query->exists()) {
                $validator->errors()->add('name', __('academic_management.duplicate_class_error'));
            }
        });
    }
}
