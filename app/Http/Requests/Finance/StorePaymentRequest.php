<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'uuid', 'exists:student_profiles,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'receptionist_id' => ['nullable', 'string', 'max:50'],
            'receptionist_name' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.invoice_id' => ['nullable', 'uuid', 'exists:invoices,id'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0.01'],
        ];
    }
}
