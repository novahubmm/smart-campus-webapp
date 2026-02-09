<?php

namespace App\Repositories\Teacher;

use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

class ClassRepository
{
    /**
     * Update male class leader
     * 
     * @param string $classId
     * @param string|null $studentId
     * @return void
     */
    public function updateMaleLeader(string $classId, ?string $studentId): void
    {
        DB::table('classes')
            ->where('id', $classId)
            ->update(['male_class_leader_id' => $studentId]);
    }
    
    /**
     * Update female class leader
     * 
     * @param string $classId
     * @param string|null $studentId
     * @return void
     */
    public function updateFemaleLeader(string $classId, ?string $studentId): void
    {
        DB::table('classes')
            ->where('id', $classId)
            ->update(['female_class_leader_id' => $studentId]);
    }
    
    /**
     * Get class with leader and teacher information
     * 
     * @param string $classId
     * @return SchoolClass
     */
    public function getClassWithLeaders(string $classId): SchoolClass
    {
        return SchoolClass::with(['teacher', 'maleLeader', 'femaleLeader'])
            ->findOrFail($classId);
    }
}
