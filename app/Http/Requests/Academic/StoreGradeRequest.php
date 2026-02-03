<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'uuid', 'exists:batches,id'],
            'level' => ['required', 'integer', 'min:0'],
            'grade_category_id' => ['required', 'uuid', 'exists:grade_categories,id'],
            'price_per_month' => ['nullable', 'numeric', 'min:0'],
            'subjects' => ['sometimes', 'array'],
            'subjects.*' => ['uuid', 'exists:subjects,id'],
            'classes' => ['sometimes', 'array'],
            'classes.*.name' => ['required_with:classes', 'string', 'max:255'],
        ];
    }
}
