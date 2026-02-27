<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamMarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'uuid', 'exists:exams,id'],
            'student_id' => [
                'required', 
                'uuid', 
                'exists:student_profiles,id',
                // Unique validation: same student cannot have marks for same exam and subject
                function ($attribute, $value, $fail) {
                    $examId = $this->input('exam_id');
                    $subjectId = $this->input('subject_id');
                    
                    if ($examId && $subjectId) {
                        $exists = \App\Models\ExamMark::where('exam_id', $examId)
                            ->where('student_id', $value)
                            ->where('subject_id', $subjectId)
                            ->exists();
                            
                        if ($exists) {
                            $fail('This student already has marks recorded for this subject in this exam.');
                        }
                    }
                }
            ],
            'subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'marks_obtained' => [
                'required_unless:is_absent,1', 
                'nullable', 
                'numeric', 
                'min:0',
                // Validate marks obtained cannot exceed total marks
                function ($attribute, $value, $fail) {
                    $totalMarks = $this->input('total_marks');
                    if ($value !== null && $totalMarks !== null && $value > $totalMarks) {
                        $fail("Marks obtained ({$value}) cannot exceed total marks ({$totalMarks}).");
                    }
                }
            ],
            'total_marks' => ['required', 'numeric', 'min:1'],
            'grade' => ['nullable', 'string', 'max:50'],
            'remark' => ['nullable', 'string', 'max:255'],
            'entered_by' => ['nullable', 'uuid', 'exists:users,id'],
            'is_absent' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'exam_id.required' => 'Exam is required.',
            'exam_id.exists' => 'Selected exam does not exist.',
            'student_id.required' => 'Student is required.',
            'student_id.exists' => 'Selected student does not exist.',
            'subject_id.required' => 'Subject is required.',
            'subject_id.exists' => 'Selected subject does not exist.',
            'marks_obtained.required_unless' => 'Marks obtained is required unless student is marked as absent.',
            'marks_obtained.numeric' => 'Marks obtained must be a number.',
            'marks_obtained.min' => 'Marks obtained cannot be negative.',
            'total_marks.required' => 'Total marks is required.',
            'total_marks.numeric' => 'Total marks must be a number.',
            'total_marks.min' => 'Total marks must be at least 1.',
        ];
    }
}
