<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gradeId = $this->route('id');
        
        return [
            'batch_id' => ['required', 'uuid', 'exists:batches,id'],
            'level' => [
                'required', 
                'integer', 
                'min:0',
                function ($attribute, $value, $fail) use ($gradeId) {
                    $query = \App\Models\Grade::where('batch_id', $this->batch_id)
                        ->where('level', $value)
                        ->where('grade_category_id', $this->grade_category_id);
                    
                    if ($gradeId) {
                        $query->where('id', '!=', $gradeId);
                    }
                    
                    if ($query->exists()) {
                        $fail(__('academic_management.duplicate_grade_error'));
                    }
                }
            ],
            'grade_category_id' => ['required', 'uuid', 'exists:grade_categories,id'],
            'price_per_month' => ['nullable', 'numeric', 'min:0'],
            'subjects' => ['sometimes', 'array'],
            'subjects.*' => ['uuid', 'exists:subjects,id'],
            'classes' => ['sometimes', 'array'],
            'classes.*.name' => ['required_with:classes', 'string', 'max:255'],
        ];
    }
}
