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
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $batchId = $this->route('id') ?? $this->route('batch');
            
            $query = \App\Models\Batch::where('name', $this->name);
            
            if ($batchId) {
                $query->where('id', '!=', $batchId);
            }
            
            if ($query->exists()) {
                $validator->errors()->add('name', __('academic_management.duplicate_batch_error'));
            }
        });
    }
}
