<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'uuid', 'exists:grades,id'],
            'batch_id' => ['required', 'uuid', 'exists:batches,id'],
            'fee_type_id' => ['required', 'uuid', 'exists:fee_types,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', Rule::in(['monthly', 'quarterly', 'half-yearly', 'yearly', 'one-time'])],
            'applicable_from' => ['nullable', 'date'],
            'applicable_to' => ['nullable', 'date', 'after_or_equal:applicable_from'],
            'status' => ['sometimes', 'boolean'],
            'supports_payment_period' => ['sometimes', 'boolean'],
        ];
    }
}
