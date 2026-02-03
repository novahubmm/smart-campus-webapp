<?php

namespace App\Http\Requests\Api\V1\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class SubmitGradesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'subject_id' => 'required|string|exists:subjects,id',
            'grades' => 'required|array|min:1',
            'grades.*.student_id' => [
                'required',
                'string',
                'exists:student_profiles,id',
                // Unique validation for API: same student cannot have marks for same exam and subject
                function ($attribute, $value, $fail) {
                    $examId = $this->route('id'); // Get exam ID from route
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
            'grades.*.score' => 'required|numeric|min:0|max:1000'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subject_id.required' => 'Subject ID is required',
            'subject_id.exists' => 'Invalid subject ID',
            'grades.required' => 'Grades data is required',
            'grades.array' => 'Grades must be an array',
            'grades.min' => 'At least one grade entry is required',
            'grades.*.student_id.required' => 'Student ID is required for each grade entry',
            'grades.*.student_id.exists' => 'Invalid student ID',
            'grades.*.score.required' => 'Score is required for each grade entry',
            'grades.*.score.numeric' => 'Score must be a number',
            'grades.*.score.min' => 'Score cannot be negative',
            'grades.*.score.max' => 'Score cannot exceed 1000'
        ];
    }
}