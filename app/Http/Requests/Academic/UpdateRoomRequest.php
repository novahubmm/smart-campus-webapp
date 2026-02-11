<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'building' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roomId = $this->route('id') ?? $this->route('room');
            
            $query = \App\Models\Room::where('name', $this->name)
                ->where('building', $this->building);
            
            if ($roomId) {
                $query->where('id', '!=', $roomId);
            }
            
            if ($query->exists()) {
                $validator->errors()->add('name', __('academic_management.duplicate_room_error'));
            }
        });
    }
}
