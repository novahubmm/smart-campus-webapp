<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage departments') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:36', Rule::unique('departments', 'code')->ignore($this->route('department')->id)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
