<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_category_id' => ['required', 'uuid', 'exists:event_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', Rule::in(['academic', 'sports', 'cultural', 'holiday', 'meeting', 'exam', 'other'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'banner_image' => ['nullable', 'string', 'max:255'],
            'target_roles' => ['nullable', 'array'],
            'target_grades_json' => ['nullable', 'string'],
            'target_teacher_grades_json' => ['nullable', 'string'],
            'target_guardian_grades_json' => ['nullable', 'string'],
            'target_departments_json' => ['nullable', 'string'],
            'schedules_json' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['upcoming', 'ongoing', 'completed', 'result'])],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $exists = \App\Models\Event::where('title', $this->title)
                ->where('start_date', $this->start_date)
                ->exists();

            if ($exists) {
                $validator->errors()->add('title', __('academic_management.duplicate_event_error'));
            }
        });
    }
}
