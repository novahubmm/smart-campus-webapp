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
                'max:255',
            ],
            'grade_id' => ['required', 'uuid', 'exists:grades,id'],
            'batch_id' => ['nullable', 'uuid', 'exists:batches,id'],
            'teacher_id' => ['nullable', 'uuid', 'exists:teacher_profiles,id'],
            'room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $classId = $this->route('id');
            
            $query = \App\Models\SchoolClass::where('grade_id', $this->grade_id)
                ->where('name', $this->name);
            
            if ($classId) {
                $query->where('id', '!=', $classId);
            }
            
            if ($query->exists()) {
                $validator->errors()->add('name', __('academic_management.duplicate_class_error'));
            }
        });
    }
}
