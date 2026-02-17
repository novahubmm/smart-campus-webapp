<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjectId = $this->route('id') ?? $this->route('subject');

        return [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'subject_type_id' => ['nullable', 'uuid', 'exists:subject_types,id'],
            'icon' => ['nullable', 'string', 'max:100'],
            'icon_color' => ['nullable', 'string', 'max:20'],
            'progress_color' => ['nullable', 'string', 'max:20'],
            'grade_ids' => ['required', 'array', 'min:1'],
            'grade_ids.*' => ['uuid', 'exists:grades,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $subjectId = $this->route('id') ?? $this->route('subject');
            
            $query = \App\Models\Subject::where('code', $this->code);
            
            if ($subjectId) {
                $query->where('id', '!=', $subjectId);
            }
            
            if ($query->exists()) {
                $validator->errors()->add('code', __('academic_management.duplicate_subject_error'));
            }
        });
    }
}
