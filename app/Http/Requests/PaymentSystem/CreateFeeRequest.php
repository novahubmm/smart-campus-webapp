<?php

namespace App\Http\Requests\PaymentSystem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can create fee categories
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'name_mm' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'description_mm' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', Rule::in(['one_time', 'monthly'])],
            'fee_type' => ['required', Rule::in(['tuition', 'transportation', 'library', 'lab', 'sports', 'course_materials', 'other'])],
            'grade' => ['required', 'string'],
            'batch' => ['required', 'string'],
            'target_month' => ['required_if:frequency,one_time', 'nullable', 'integer', 'min:1', 'max:12'],
            'due_date' => ['required', 'date'],
            'supports_payment_period' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Fee name is required.',
            'amount.required' => 'Fee amount is required.',
            'amount.min' => 'Fee amount must be at least 0.',
            'frequency.required' => 'Fee frequency is required.',
            'frequency.in' => 'Fee frequency must be either "one_time" or "monthly".',
            'fee_type.required' => 'Fee type is required.',
            'fee_type.in' => 'Fee type must be one of: tuition, transportation, library, lab, sports, course_materials, or other.',
            'grade.required' => 'Grade is required.',
            'batch.required' => 'Batch is required.',
            'target_month.required_if' => 'Target month is required for one-time fees.',
            'target_month.min' => 'Target month must be between 1 and 12.',
            'target_month.max' => 'Target month must be between 1 and 12.',
            'due_date.required' => 'Due date is required.',
        ];
    }
}
