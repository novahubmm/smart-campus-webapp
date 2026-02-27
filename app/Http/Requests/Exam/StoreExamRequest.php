<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'exam_type_id' => ['required', 'uuid', 'exists:exam_types,id'],
            'batch_id' => ['nullable', 'uuid', 'exists:batches,id'],
            'grade_id' => ['required', 'uuid', 'exists:grades,id'],
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', 'string', 'in:upcoming,ongoing,completed,results'],

            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'schedules.*.class_id' => ['nullable', 'uuid', 'exists:classes,id'],
            'schedules.*.exam_date' => ['required', 'date'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'schedules.*.teacher_id' => ['nullable', 'uuid', 'exists:teacher_profiles,id'],
            'schedules.*.total_marks' => ['nullable', 'numeric', 'min:1'],
            'schedules.*.passing_marks' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation()
    {
        // Auto-generate unique exam_id
        $examId = $this->generateUniqueExamId();
        $this->merge(['exam_id' => $examId]);
    }

    private function generateUniqueExamId(): string
    {
        $prefix = 'EX';
        $year = date('Y');
        
        // Get the last exam ID for this year
        $lastExam = \App\Models\Exam::where('exam_id', 'like', "{$prefix}-{$year}-%")
            ->orderBy('exam_id', 'desc')
            ->first();
        
        if ($lastExam) {
            // Extract the number from the last exam_id
            $lastNumber = (int) substr($lastExam->exam_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        // Format: EX-2026-0001
        return sprintf('%s-%s-%04d', $prefix, $year, $newNumber);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for duplicate exam name
            $exists = \App\Models\Exam::where('name', $this->name)
                ->where('grade_id', $this->grade_id)
                ->where('class_id', $this->class_id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', __('academic_management.duplicate_exam_error'));
            }
            
            // Validate schedule dates are within exam start and end dates
            if ($this->has('schedules') && $this->has('start_date') && $this->has('end_date')) {
                $startDate = \Carbon\Carbon::parse($this->start_date)->startOfDay();
                $endDate = \Carbon\Carbon::parse($this->end_date)->endOfDay();
                
                foreach ($this->schedules as $index => $schedule) {
                    if (isset($schedule['exam_date'])) {
                        $examDate = \Carbon\Carbon::parse($schedule['exam_date']);
                        
                        if ($examDate->lt($startDate)) {
                            $validator->errors()->add(
                                "schedules.{$index}.exam_date",
                                __('The exam date for this subject must be on or after the exam start date.')
                            );
                        }
                        
                        if ($examDate->gt($endDate)) {
                            $validator->errors()->add(
                                "schedules.{$index}.exam_date",
                                __('The exam date for this subject must be on or before the exam end date.')
                            );
                        }
                    }
                }
            }
        });
    }
}
