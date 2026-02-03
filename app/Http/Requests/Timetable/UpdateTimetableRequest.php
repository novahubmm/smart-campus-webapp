<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permit; route/middleware handle access. Avoid blocking when permission is missing.
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'uuid', 'exists:batches,id'],
            'grade_id' => ['required', 'uuid', 'exists:grades,id'],
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'minutes_per_period' => ['nullable', 'integer', 'min:1'],
            'break_duration' => ['nullable', 'integer', 'min:0'],
            'school_start_time' => ['nullable', 'date_format:H:i'],
            'school_end_time' => ['nullable', 'date_format:H:i'],
            'week_days' => ['nullable', 'array'],
            'week_days.*' => ['string'],
            'periods' => ['required', 'array', 'min:1'],
            'periods.*.day_of_week' => ['required', 'string'],
            'periods.*.period_number' => ['required', 'integer', 'min:1'],
            'periods.*.starts_at' => ['required', 'date_format:H:i'],
            'periods.*.ends_at' => ['required', 'date_format:H:i'],
            'periods.*.is_break' => ['nullable', 'boolean'],
            'periods.*.subject_id' => ['nullable', 'uuid', 'exists:subjects,id'],
            'periods.*.teacher_profile_id' => ['nullable', 'uuid', 'exists:teacher_profiles,id'],
            'periods.*.room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'periods.*.notes' => ['nullable', 'string'],
        ];
    }
}
