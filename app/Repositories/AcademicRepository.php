<?php

namespace App\Repositories;

use App\Models\Room;
use App\Models\Batch;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Facility;
use App\Models\SchoolClass;
use Illuminate\Support\Arr;
use App\Models\TeacherProfile;
use App\DTOs\Academic\RoomData;
use App\DTOs\Academic\BatchData;
use App\DTOs\Academic\GradeData;
use App\DTOs\Academic\SubjectData;
use App\Interfaces\AcademicRepositoryInterface;
use App\Models\SubjectType;

class AcademicRepository implements AcademicRepositoryInterface
{
    public function findBatch(string $id): ?Batch
    {
        return Batch::find($id);
    }

    public function updateBatch(Batch $batch, BatchData $data): Batch
    {
        $batch->update($data->toArray());

        return $batch->fresh();
    }

    /**
     * Create a new batch
     */
    public function createBatch(BatchData $data): Batch
    {
        return Batch::create($data->toArray());
    }

    /**
     * Create a new grade
     */
    public function createGrade(GradeData $data): Grade
    {
        $grade = Grade::create([
            'level' => $data->level,
            'batch_id' => $data->batch_id,
            'grade_category_id' => $data->grade_category_id,
            'price_per_month' => $data->price_per_month,
        ]);
        // Create classes for this grade (if array of objects)
        foreach ($data->classes as $classData) {
            if (is_array($classData)) {
                SchoolClass::create([
                    'grade_id' => $grade->id,
                    'batch_id' => $grade->batch_id,
                    'name' => $classData['name'] ?? $classData,
                ]);
            } else {
                SchoolClass::create([
                    'grade_id' => $grade->id,
                    'batch_id' => $grade->batch_id,
                    'name' => $classData,
                ]);
            }
        }
        return $grade;
    }

    /**
     * Update an existing grade
     */
    public function updateGrade(Grade $grade, GradeData $data): Grade
    {
        $grade->update([
            'level' => $data->level,
            'batch_id' => $data->batch_id,
            'grade_category_id' => $data->grade_category_id,
            'price_per_month' => $data->price_per_month,
        ]);

        return $grade->fresh();
    }

    /**
     * Find grade by id
     */
    public function findGrade(string $id): ?Grade
    {
        return Grade::find($id);
    }

    /**
     * Create a new room
     */
    public function createRoom(RoomData $data): Room
    {
        return Room::create($data->toArray());
    }

    /**
     * Update room
     */
    public function updateRoom(Room $room, RoomData $data): Room
    {
        $room->update($data->toArray());

        return $room->fresh();
    }

    public function findRoom(string $id): ?Room
    {
        return Room::find($id);
    }

    /**
     * Create a new subject
     */
    public function createSubject(SubjectData $data): Subject
    {
        $subject = Subject::create($data->toArray());

        $subject->grades()->sync($data->gradeIds());

        return $subject->fresh('grades');
    }

    public function updateSubject(Subject $subject, SubjectData $data): Subject
    {
        $subject->update($data->toArray());

        $subject->grades()->sync($data->gradeIds());

        return $subject->fresh('grades');
    }

    public function findSubject(string $id): ?Subject
    {
        return Subject::find($id);
    }

    /**
     * Create a class
     */
    public function createClass(array $data): SchoolClass
    {
        return SchoolClass::create($data);
    }

    /**
     * Update a class
     */
    public function updateClass(SchoolClass $class, array $data): SchoolClass
    {
        $class->update($data);

        return $class->fresh(['grade', 'batch', 'room', 'teacher']);
    }

    /**
     * Find a class by id
     */
    public function findClass(string $id): ?SchoolClass
    {
        return SchoolClass::find($id);
    }

    /**
     * Delete a class
     */
    public function deleteClass(string $classId): bool
    {
        $class = SchoolClass::find($classId);

        return $class?->delete() ?? false;
    }

    /**
     * Get all rooms
     */
    public function getRooms(): array
    {
        return Room::all()->toArray();
    }

    /**
     * Get all subjects
     */
    public function getSubjects(): array
    {
        return Subject::all()->toArray();
    }

    /**
     * Get all batches
     */
    public function getBatches(): array
    {
        return Batch::all()->toArray();
    }

    /**
     * Get grades with details
     */
    public function getGradesWithDetails(): array
    {
        return Grade::with(['gradeCategory', 'batch'])->orderBy('level')->get()->toArray();
    }

    // Count methods for stat cards (total counts)
    public function getBatchesCount(): int
    {
        return Batch::count();
    }

    public function getGradesCount(): int
    {
        return Grade::count();
    }

    public function getClassesCount(): int
    {
        return SchoolClass::count();
    }

    public function getRoomsCount(): int
    {
        return Room::count();
    }

    public function getSubjectsCount(): int
    {
        return Subject::count();
    }

    // Paginated methods for tables
    public function getBatchesWithCounts()
    {
        return Batch::select('batches.*')
            ->withCount('classes')
            ->selectSub(function ($query) {
                $query->from('student_profiles')
                    ->selectRaw('count(*)')
                    ->join('grades', 'grades.id', '=', 'student_profiles.grade_id')
                    ->whereColumn('grades.batch_id', 'batches.id')
                    ->whereNull('student_profiles.deleted_at');
            }, 'students_count')
            ->orderBy('start_date', 'desc')
            ->paginate(10, ['*'], 'batches_page')
            ->withQueryString();
    }

    public function getGradesWithCounts()
    {
        return Grade::select('grades.*')
            ->withCount('classes')
            ->selectSub(function ($query) {
                $query->from('student_profiles')
                    ->selectRaw('count(*)')
                    ->whereColumn('student_profiles.grade_id', 'grades.id')
                    ->whereNull('student_profiles.deleted_at');
            }, 'students_count')
            ->with(['batch', 'gradeCategory'])
            ->orderBy('level')
            ->paginate(10, ['*'], 'grades_page')
            ->withQueryString();
    }

    public function getClasses()
    {
        return SchoolClass::with(['grade', 'room', 'teacher.user'])
            ->select('classes.*')
            ->selectSub(function ($query) {
                $query->from('student_profiles')
                    ->selectRaw('count(distinct student_profiles.id)')
                    ->leftJoin('student_class', function ($join) {
                        $join->on('student_class.student_id', '=', 'student_profiles.id')
                            ->whereColumn('student_class.class_id', 'classes.id');
                    })
                    ->where(function ($where) {
                        $where->whereColumn('student_profiles.class_id', 'classes.id')
                            ->orWhereNotNull('student_class.class_id');
                    });
            }, 'students_count')
            ->orderBy('name')
            ->paginate(10, ['*'], 'classes_page')
            ->withQueryString();
    }

    public function getRoomsWithCounts()
    {
        return Room::withCount('classes')->with('classes')->orderBy('name')->paginate(10, ['*'], 'rooms_page')->withQueryString();
    }

    public function getSubjectsWithCounts()
    {
        return Subject::with(['grades:id,level,batch_id', 'subjectType'])
            ->withCount(['teachers', 'grades'])
            ->orderByRaw('(SELECT MIN(level) FROM grades INNER JOIN grade_subject ON grades.id = grade_subject.grade_id WHERE grade_subject.subject_id = subjects.id)')
            ->paginate(10, ['*'], 'subjects_page')
            ->withQueryString();
    }

    /**
     * Delete a grade
     */
    public function deleteGrade(string $gradeId): bool
    {
        $grade = Grade::find($gradeId);
        if (!$grade) {
            return false;
        }
        return $grade->delete();
    }

    public function deleteBatch(string $batchId): bool
    {
        $batch = Batch::find($batchId);

        return $batch?->delete() ?? false;
    }

    /**
     * Delete a room
     */
    public function deleteRoom(string $roomId): bool
    {
        $room = Room::find($roomId);
        if (!$room) {
            return false;
        }
        return $room->delete();
    }

    /**
     * Delete a subject
     */
    public function deleteSubject(string $subjectId): bool
    {
        $subject = Subject::find($subjectId);
        if (!$subject) {
            return false;
        }
        return $subject->delete();
    }

    /**
     * Attach subject to grade
     */
    public function attachSubjectToGrade(string $gradeId, string $subjectId): void
    {
        $grade = Grade::find($gradeId);
        $grade?->subjects()->attach($subjectId);
    }

    /**
     * Detach subject from grade
     */
    public function detachSubjectFromGrade(string $gradeId, string $subjectId): void
    {
        $grade = Grade::find($gradeId);
        $grade?->subjects()->detach($subjectId);
    }

    /**
     * Get active batches
     */
    public function getActiveBatches()
    {
        return Batch::where('status', true)->orderBy('name')->get();
    }

    /**
     * Get grade categories
     */
    public function getGradeCategories()
    {
        return \App\Models\GradeCategory::get();
    }

    /**
     * Get all classes
     */
    public function getTeachers()
    {
        return TeacherProfile::with('user')->get();
    }

    public function getFacilities()
    {
        return Facility::get();
    }

    public function getSubjectTypes()
    {
        return SubjectType::get();
    }
}
