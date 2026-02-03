<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('subjects', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'subject_type_id' => ['nullable', 'uuid', 'exists:subject_types,id'],
            'icon' => ['nullable', 'string', 'max:100'],
            'icon_color' => ['nullable', 'string', 'max:20'],
            'progress_color' => ['nullable', 'string', 'max:20'],
            'grade_ids' => ['required', 'array', 'min:1'],
            'grade_ids.*' => ['uuid', 'exists:grades,id'],
        ];
    }
}
