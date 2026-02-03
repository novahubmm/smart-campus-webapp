<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'name')->where(fn ($query) => $query->where('grade_id', $this->input('grade_id'))),
            ],
            'grade_id' => ['required', 'uuid', 'exists:grades,id'],
            'batch_id' => ['nullable', 'uuid', 'exists:batches,id'],
            'teacher_id' => ['nullable', 'uuid', 'exists:teacher_profiles,id'],
            'room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
        ];
    }
}
