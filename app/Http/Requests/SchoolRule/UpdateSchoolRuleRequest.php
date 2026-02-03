<?php

namespace App\Http\Requests\SchoolRule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order' => ['nullable', 'integer', 'min:1'],
            'text' => ['required', 'string', 'max:500'],
            'severity' => ['required', 'string', 'in:low,medium,high'],
            'consequence' => ['nullable', 'string', 'max:500'],
        ];
    }
}
