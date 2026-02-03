<?php

namespace App\Interfaces;

use App\DTOs\Academic\BatchData;
use App\DTOs\Academic\GradeData;
use App\DTOs\Academic\RoomData;
use App\DTOs\Academic\SubjectData;
use App\Models\Batch;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Room;
use App\Models\Subject;

interface AcademicRepositoryInterface
{
    /**
     * Create a new batch
     */
    public function createBatch(BatchData $data): Batch;

    /**
     * Update an existing batch
     */
    public function updateBatch(Batch $batch, BatchData $data): Batch;

    /**
     * Find a batch by id
     */
    public function findBatch(string $id): ?Batch;

    /**
     * Create a new grade
     */
    public function createGrade(GradeData $data): Grade;

    /**
     * Update an existing grade
     */
    public function updateGrade(Grade $grade, GradeData $data): Grade;

    /**
     * Find a grade by id
     */
    public function findGrade(string $id): ?Grade;

    /**
     * Create a new room
     */
    public function createRoom(RoomData $data): Room;

    /**
     * Update an existing room
     */
    public function updateRoom(Room $room, RoomData $data): Room;

    /**
     * Find a room by id
     */
    public function findRoom(string $id): ?Room;

    /**
     * Create a new subject
     */
    public function createSubject(SubjectData $data): Subject;

    /**
     * Update an existing subject
     */
    public function updateSubject(Subject $subject, SubjectData $data): Subject;

    /**
     * Find a subject by id
     */
    public function findSubject(string $id): ?Subject;

    /**
     * Create a class
     */
    public function createClass(array $data): SchoolClass;

    /**
     * Update a class
     */
    public function updateClass(SchoolClass $class, array $data): SchoolClass;

    /**
     * Find a class by id
     */
    public function findClass(string $id): ?SchoolClass;

    /**
     * Delete a class
     */
    public function deleteClass(string $classId): bool;

    /**
     * Get all grades with classes and subjects
     */
    public function getGradesWithDetails(): array;

    public function getBatchesWithCounts();

    public function getGradesWithCounts();

    public function getClasses();

    public function getRoomsWithCounts();

    public function getSubjectsWithCounts();

    public function getActiveBatches();

    public function getGradeCategories();

    public function getTeachers();

    public function getFacilities();

    public function getSubjectTypes();

    /**
     * Get all rooms
     */
    public function getRooms(): array;

    /**
     * Get all subjects
     */
    public function getSubjects(): array;

    /**
     * Get all batches
     */
    public function getBatches(): array;

    /**
     * Delete a grade
     */
    public function deleteGrade(string $gradeId): bool;

    /**
     * Delete a room
     */
    public function deleteRoom(string $roomId): bool;

    /**
     * Delete a subject
     */
    public function deleteSubject(string $subjectId): bool;

    /**
     * Delete a batch
     */
    public function deleteBatch(string $batchId): bool;

    /**
     * Attach subject to grade
     */
    public function attachSubjectToGrade(string $gradeId, string $subjectId): void;

    /**
     * Detach subject from grade
     */
    public function detachSubjectFromGrade(string $gradeId, string $subjectId): void;
}
