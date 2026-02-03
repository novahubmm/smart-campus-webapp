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
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'cheque', 'card', 'online', 'mobile_payment', 'kbz_pay', 'wave_pay', 'check', 'other'])],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'grade_id' => ['nullable', 'exists:grades,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
