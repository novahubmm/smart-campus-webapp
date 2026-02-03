<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $batchId = $this->route('id') ?? $this->route('batch');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('batches', 'name')->ignore($batchId),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
