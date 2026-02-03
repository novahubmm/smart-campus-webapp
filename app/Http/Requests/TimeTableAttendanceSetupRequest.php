<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class TimeTableAttendanceSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number_of_periods_per_day' => ['required', 'integer', 'min:1', 'max:24'],
            'minute_per_period' => ['required', 'integer', 'min:1', 'max:600'],
            'break_duration' => ['nullable', 'integer', 'min:0', 'max:180'],
            'school_start_time' => ['required', 'date_format:H:i'],
            'school_end_time' => ['required', 'date_format:H:i'],
            'week_days' => ['required', 'array', 'min:1'],
            'week_days.*' => [Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            if (!$this->filled('school_start_time') || !$this->filled('school_end_time')) {
                return;
            }

            $start = Carbon::createFromFormat('H:i', $this->input('school_start_time'));
            $end = Carbon::createFromFormat('H:i', $this->input('school_end_time'));

            if ($start && $end && $end->lessThanOrEqualTo($start)) {
                $v->errors()->add('school_end_time', __('School end time must be after start time.'));
            }
        });
    }
}
