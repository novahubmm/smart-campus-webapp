<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'income_date' => ['required', 'date'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'grade_id' => ['nullable', 'exists:grades,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
