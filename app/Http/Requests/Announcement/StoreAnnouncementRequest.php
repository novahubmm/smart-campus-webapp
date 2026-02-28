<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'announcement_type_id' => ['nullable', 'uuid', 'exists:announcement_types,id'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'location' => ['nullable', 'string', 'max:255'],
            'target_roles' => ['nullable', 'array'],
            'target_roles.*' => ['string', Rule::in(['teacher', 'staff', 'guardian'])],
            'target_grades_json' => ['nullable', 'string'],
            'target_teacher_grades_json' => ['nullable', 'string'],
            'target_guardian_grades_json' => ['nullable', 'string'],
            'target_departments_json' => ['nullable', 'string'],
            'publish_mode' => ['nullable', 'string', Rule::in(['now', 'schedule'])],
            'publish_date' => ['nullable', 'date', 'required_if:publish_mode,schedule'],
            'publish_time' => ['nullable', 'date_format:H:i', 'required_if:publish_mode,schedule'],
            'is_published' => ['sometimes', 'boolean'],
            'attachment' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'publish_date.required_if' => 'Publish date is required when scheduling an announcement.',
            'publish_time.required_if' => 'Publish time is required when scheduling an announcement.',
        ];
    }
}
