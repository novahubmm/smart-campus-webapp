<?php

namespace App\Services;

use App\Repositories\Teacher\ClassRepository;
use App\Repositories\Teacher\StudentRepository;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ClassLeaderService
{
    protected ClassRepository $classRepository;
    protected StudentRepository $studentRepository;
    
    public function __construct(
        ClassRepository $classRepository,
        StudentRepository $studentRepository
    ) {
        $this->classRepository = $classRepository;
        $this->studentRepository = $studentRepository;
    }
    
    /**
     * Assign male leader with validation
     * 
     * @param string $classId
     * @param string $studentId
     * @throws ValidationException if gender mismatch
     * @throws ModelNotFoundException if student not in class
     */
    public function assignMaleLeader(string $classId, string $studentId): void
    {
        $this->validateStudentInClass($classId, $studentId);
        $this->validateStudentGender($studentId, 'Male');
        
        $this->classRepository->updateMaleLeader($classId, $studentId);
    }
    
    /**
     * Assign female leader with validation
     * 
     * @param string $classId
     * @param string $studentId
     * @throws ValidationException if gender mismatch
     * @throws ModelNotFoundException if student not in class
     */
    public function assignFemaleLeader(string $classId, string $studentId): void
    {
        $this->validateStudentInClass($classId, $studentId);
        $this->validateStudentGender($studentId, 'Female');
        
        $this->classRepository->updateFemaleLeader($classId, $studentId);
    }
    
    /**
     * Validate student gender matches requirement
     * 
     * @param string $studentId
     * @param string $requiredGender ('Male' or 'Female')
     * @throws ValidationException if gender mismatch
     */
    protected function validateStudentGender(string $studentId, string $requiredGender): void
    {
        $student = $this->studentRepository->find($studentId);
        
        if (strcasecmp($student->gender, $requiredGender) !== 0) {
            throw ValidationException::withMessages([
                'student_id' => "Student must be {$requiredGender} to be assigned as {$requiredGender} leader"
            ])->errorBag('INVALID_GENDER');
        }
    }
    
    /**
     * Validate student exists in the class
     * 
     * @param string $classId
     * @param string $studentId
     * @throws ModelNotFoundException if student not in class
     */
    protected function validateStudentInClass(string $classId, string $studentId): void
    {
        $student = $this->studentRepository->find($studentId);
        
        if ($student->class_id !== $classId) {
            throw (new ModelNotFoundException('Student not found in this class'))
                ->setModel(StudentProfile::class);
        }
    }
    
    /**
     * Remove male leader
     * 
     * @param string $classId
     */
    public function removeMaleLeader(string $classId): void
    {
        $this->classRepository->updateMaleLeader($classId, null);
    }
    
    /**
     * Remove female leader
     * 
     * @param string $classId
     */
    public function removeFemaleLeader(string $classId): void
    {
        $this->classRepository->updateFemaleLeader($classId, null);
    }
    
    /**
     * Get class with leader information
     * 
     * @param string $classId
     * @return SchoolClass
     */
    public function getClassWithLeaders(string $classId): SchoolClass
    {
        return $this->classRepository->getClassWithLeaders($classId);
    }
    
    /**
     * Get students in a class
     * 
     * @param string $classId
     * @return Collection
     */
    public function getClassStudents(string $classId): Collection
    {
        return $this->studentRepository->getByClassId($classId);
    }
}
