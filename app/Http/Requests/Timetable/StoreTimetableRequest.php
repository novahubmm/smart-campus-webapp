<?php

namespace App\Http\Requests\Timetable;

use App\Rules\TeacherNotDoubleBooked;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permit; route/middleware handle access. Avoid blocking when permission is missing.
        return true;
    }

    public function rules(): array
    {
        // Support both single timetable payload and multiple timetables[] payload.
        if ($this->has('timetables')) {
            $rules = [
                'timetables' => ['required', 'array', 'min:1'],
                'timetables.*.batch_id' => ['required', 'uuid', 'exists:batches,id'],
                'timetables.*.grade_id' => ['required', 'uuid', 'exists:grades,id'],
                'timetables.*.class_id' => ['required', 'uuid', 'exists:classes,id'],
                'timetables.*.name' => ['nullable', 'string', 'max:255'],
                'timetables.*.effective_from' => ['nullable', 'date'],
                'timetables.*.effective_to' => ['nullable', 'date', 'after_or_equal:timetables.*.effective_from'],
                'timetables.*.minutes_per_period' => ['nullable', 'integer', 'min:1'],
                'timetables.*.break_duration' => ['nullable', 'integer', 'min:0'],
                'timetables.*.school_start_time' => ['nullable', 'date_format:H:i'],
                'timetables.*.school_end_time' => ['nullable', 'date_format:H:i'],
                'timetables.*.week_days' => ['nullable', 'array'],
                'timetables.*.week_days.*' => ['string'],
                'timetables.*.periods' => ['required', 'array', 'min:1'],
                'timetables.*.periods.*.day_of_week' => ['required', 'string'],
                'timetables.*.periods.*.period_number' => ['required', 'integer', 'min:1'],
                'timetables.*.periods.*.starts_at' => ['required', 'date_format:H:i'],
                'timetables.*.periods.*.ends_at' => ['required', 'date_format:H:i'],
                'timetables.*.periods.*.is_break' => ['nullable', 'boolean'],
                'timetables.*.periods.*.subject_id' => ['nullable', 'uuid', 'exists:subjects,id'],
                'timetables.*.periods.*.teacher_profile_id' => ['nullable', 'uuid', 'exists:teacher_profiles,id'],
                'timetables.*.periods.*.room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
                'timetables.*.periods.*.notes' => ['nullable', 'string'],
            ];

            // Add teacher double-booking validation for each period
            $timetables = $this->input('timetables', []);
            foreach ($timetables as $tIndex => $timetable) {
                $periods = $timetable['periods'] ?? [];
                foreach ($periods as $pIndex => $period) {
                    if (!empty($period['teacher_profile_id']) && !empty($period['is_break']) === false) {
                        $rules["timetables.{$tIndex}.periods.{$pIndex}.teacher_profile_id"][] = new TeacherNotDoubleBooked(
                            $period['day_of_week'] ?? '',
                            $period['starts_at'] ?? '',
                            $period['ends_at'] ?? ''
                        );
                    }
                }
            }

            return $rules;
        }

        $rules = [
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

        // Add teacher double-booking validation for each period
        $periods = $this->input('periods', []);
        foreach ($periods as $index => $period) {
            if (!empty($period['teacher_profile_id']) && !empty($period['is_break']) === false) {
                $rules["periods.{$index}.teacher_profile_id"][] = new TeacherNotDoubleBooked(
                    $period['day_of_week'] ?? '',
                    $period['starts_at'] ?? '',
                    $period['ends_at'] ?? ''
                );
            }
        }

        return $rules;
    }
}
