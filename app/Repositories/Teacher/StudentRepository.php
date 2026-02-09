<?php

namespace App\Repositories\Teacher;

use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository
{
    /**
     * Find a student by ID
     * 
     * @param string $studentId
     * @return StudentProfile
     */
    public function find(string $studentId): StudentProfile
    {
        return StudentProfile::findOrFail($studentId);
    }
    
    /**
     * Get all students in a class
     * 
     * @param string $classId
     * @return Collection
     */
    public function getByClassId(string $classId): Collection
    {
        return StudentProfile::where('class_id', $classId)
            ->with('user')
            ->get();
    }
}
