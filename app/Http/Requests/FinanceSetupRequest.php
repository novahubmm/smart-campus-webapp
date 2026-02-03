<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_fee_grade_id' => ['nullable', 'array'],
            'grade_fee_grade_id.*' => ['nullable', 'exists:grades,id'],
            'grade_fee_amount' => ['nullable', 'array'],
            'grade_fee_amount.*' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'expense_categories' => ['nullable', 'array'],
            'expense_categories.*' => ['string'],
            'custom_expense_categories' => ['nullable', 'string'],
        ];
    }
}
