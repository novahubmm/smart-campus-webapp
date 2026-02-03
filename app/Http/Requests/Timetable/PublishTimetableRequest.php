<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class PublishTimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permit; route/middleware handle access. Avoid blocking when permission is missing.
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
