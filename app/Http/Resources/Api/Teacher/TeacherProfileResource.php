<?php

namespace App\Http\Resources\Api\Teacher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $teacherProfile = $this->teacherProfile;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?? $teacherProfile?->phone_no,
            'avatar' => avatar_url($teacherProfile?->photo_path, 'teacher'),
            'teacher_id' => $teacherProfile?->employee_id,
            'department' => $teacherProfile?->department?->name,
            'subjects' => $this->getTeacherSubjects(),
            'classes' => $this->getTeacherClasses(),
        ];
    }

    private function getTeacherSubjects(): array
    {
        $teacherProfile = $this->teacherProfile;
        
        if (!$teacherProfile) {
            return [];
        }

        // First try to get from subjects relationship (subject_teacher pivot table)
        $subjects = $teacherProfile->subjects()->get();
        if ($subjects->isNotEmpty()) {
            return $subjects->map(fn($subject) => $subject->name)->unique()->values()->toArray();
        }

        // Also check periods for subjects this teacher teaches
        $periodSubjects = \App\Models\Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with('subject')
            ->get()
            ->pluck('subject.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        
        if (!empty($periodSubjects)) {
            return $periodSubjects;
        }

        // Fallback to subjects_taught JSON field
        if ($teacherProfile->subjects_taught) {
            $subjectData = is_array($teacherProfile->subjects_taught) 
                ? $teacherProfile->subjects_taught 
                : json_decode($teacherProfile->subjects_taught, true);
            
            if (is_array($subjectData)) {
                return $subjectData;
            }
        }

        return [];
    }

    private function getTeacherClasses(): array
    {
        $teacherProfile = $this->teacherProfile;
        
        if (!$teacherProfile) {
            return [];
        }

        // First check periods for classes this teacher teaches (most accurate)
        $periodClasses = \App\Models\Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with('timetable.schoolClass')
            ->get()
            ->pluck('timetable.schoolClass.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        
        if (!empty($periodClasses)) {
            return $periodClasses;
        }

        // Fallback to classes relationship (school_classes table - homeroom)
        $classes = $teacherProfile->classes()->with('grade')->get();
        if ($classes->isNotEmpty()) {
            return $classes->map(fn($class) => $class->name)->unique()->values()->toArray();
        }

        // Fallback to current_classes JSON field
        if ($teacherProfile->current_classes) {
            $classData = is_array($teacherProfile->current_classes) 
                ? $teacherProfile->current_classes 
                : json_decode($teacherProfile->current_classes, true);
            
            if (is_array($classData)) {
                return $classData;
            }
        }

        return [];
    }
}
