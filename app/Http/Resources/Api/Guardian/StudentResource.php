<?php

namespace App\Http\Resources\Api\Guardian;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name ?? 'N/A',
            'student_id' => $this->student_identifier ?? $this->student_id,
            'grade' => $this->grade?->name ?? 'N/A',
            'section' => $this->classModel?->section ?? 'N/A',
            'roll_number' => $this->roll_number,
            'profile_image' => $this->photo_path ? asset($this->photo_path) : null,
            'date_of_birth' => $this->dob?->format('Y-m-d'),
            'blood_group' => $this->blood_type,
            'gender' => $this->gender,
            'class_id' => $this->class_id,
            'grade_id' => $this->grade_id,
        ];
    }
}
