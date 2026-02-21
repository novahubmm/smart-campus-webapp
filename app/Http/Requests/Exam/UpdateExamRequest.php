<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'exam_id' => ['required', 'string', 'max:255'],
            'exam_type_id' => ['required', 'uuid', 'exists:exam_types,id'],
            'batch_id' => ['nullable', 'uuid', 'exists:batches,id'],
            'grade_id' => ['required', 'uuid', 'exists:grades,id'],
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', 'string', 'in:upcoming,ongoing,completed'],

            'schedules' => ['array'],
            'schedules.*.id' => ['nullable', 'uuid', 'exists:exam_schedules,id'],
            'schedules.*.subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'schedules.*.class_id' => ['nullable', 'uuid', 'exists:classes,id'],
            'schedules.*.exam_date' => ['required', 'date'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i'],
            'schedules.*.room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'schedules.*.total_marks' => ['nullable', 'numeric', 'min:1'],
            'schedules.*.passing_marks' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $examId = $this->route('id') ?? $this->route('exam');
            
            $query = \App\Models\Exam::where('name', $this->name)
                ->where('grade_id', $this->grade_id)
                ->where('class_id', $this->class_id);
            
            if ($examId) {
                $query->where('id', '!=', $examId);
            }
            
            if ($query->exists()) {
                $validator->errors()->add('name', __('academic_management.duplicate_exam_error'));
            }
        });
    }
}
