<?php

namespace App\Http\Requests\RuleCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreRuleCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100', 'unique:rule_categories,title'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:20'],
            'icon_color' => ['nullable', 'string', 'max:20'],
            'icon_bg_color' => ['nullable', 'string', 'max:20'],
        ];
    }
}
