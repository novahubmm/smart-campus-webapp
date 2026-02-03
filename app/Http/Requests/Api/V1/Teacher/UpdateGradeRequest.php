<?php

namespace App\Http\Requests\Api\V1\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
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
            'score' => 'required|numeric|min:0|max:1000'
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
            'score.required' => 'Score is required',
            'score.numeric' => 'Score must be a number',
            'score.min' => 'Score cannot be negative',
            'score.max' => 'Score cannot exceed 1000'
        ];
    }
}