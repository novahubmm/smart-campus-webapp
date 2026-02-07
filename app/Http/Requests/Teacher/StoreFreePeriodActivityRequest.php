<?php

namespace App\Http\Requests\Teacher;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFreePeriodActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . now()->subDays(7)->format('Y-m-d')],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'activities' => ['required', 'array', 'min:1', 'max:5'],
            'activities.*.activity_type' => ['required', 'integer', 'exists:activity_types,id'],
            'activities.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Check duration (15 minutes to 4 hours)
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            if ($startTime && $endTime) {
                $start = Carbon::parse($startTime);
                $end = Carbon::parse($endTime);
                $durationMinutes = $start->diffInMinutes($end);

                if ($durationMinutes < 15) {
                    $validator->errors()->add('end_time', 'Duration must be at least 15 minutes');
                }

                if ($durationMinutes > 240) { // 4 hours
                    $validator->errors()->add('end_time', 'Duration must not exceed 4 hours');
                }

                // Check if within school hours (7:00 AM - 6:00 PM)
                $startHour = (int) $start->format('H');
                $endHour = (int) $end->format('H');
                
                if ($startHour < 7 || $endHour > 18) {
                    $validator->errors()->add('start_time', 'Time must be within school hours (7:00 AM - 6:00 PM)');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Date is required',
            'date.date' => 'Date must be a valid date',
            'date.before_or_equal' => 'Cannot record activities for future dates',
            'date.after_or_equal' => 'Cannot record activities older than 7 days',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in HH:MM format',
            'end_time.required' => 'End time is required',
            'end_time.date_format' => 'End time must be in HH:MM format',
            'end_time.after' => 'End time must be after start time',
            'activities.required' => 'At least one activity is required',
            'activities.min' => 'At least one activity is required',
            'activities.max' => 'Maximum 5 activities allowed per record',
            'activities.*.activity_type.required' => 'Activity type is required',
            'activities.*.activity_type.exists' => 'Invalid activity type',
            'activities.*.notes.max' => 'Notes must not exceed 500 characters',
        ];
    }
}
