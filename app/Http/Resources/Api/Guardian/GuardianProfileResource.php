<?php

namespace App\Http\Resources\Api\Guardian;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile' => $this->whenLoaded('guardianProfile', function () {
                return [
                    'id' => $this->guardianProfile->id,
                    'occupation' => $this->guardianProfile->occupation,
                    'address' => $this->guardianProfile->address,
                ];
            }),
            'students' => $this->whenLoaded('guardianProfile', function () {
                return $this->guardianProfile->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->user?->name ?? 'N/A',
                        'student_id' => $student->student_identifier ?? $student->student_id,
                        'grade' => $student->grade?->name ?? 'N/A',
                        'section' => $student->classModel?->section ?? 'N/A',
                        'profile_image' => $student->photo_path ? asset($student->photo_path) : null,
                        'relationship' => $student->pivot->relationship ?? null,
                        'is_primary' => $student->pivot->is_primary ?? false,
                    ];
                });
            }),
        ];
    }
}
