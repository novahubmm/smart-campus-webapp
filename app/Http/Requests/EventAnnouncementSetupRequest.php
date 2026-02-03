<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventAnnouncementSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_categories' => ['nullable', 'array'],
            'event_categories.*' => ['string'],
            'custom_categories' => ['nullable', 'string'],
        ];
    }
}
