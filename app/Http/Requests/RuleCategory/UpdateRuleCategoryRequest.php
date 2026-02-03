<?php

namespace App\Http\Requests\RuleCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRuleCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('ruleCategory')?->id ?? $this->route('rule_category')?->id;

        return [
            'title' => ['required', 'string', 'max:100', Rule::unique('rule_categories', 'title')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:20'],
            'icon_color' => ['nullable', 'string', 'max:20'],
            'icon_bg_color' => ['nullable', 'string', 'max:20'],
        ];
    }
}
