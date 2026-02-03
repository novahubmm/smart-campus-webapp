<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage departments') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:36', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
