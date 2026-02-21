<?php

namespace App\Services;

use App\DTOs\Academic\BatchData;
use App\DTOs\Academic\GradeData;
use App\DTOs\Academic\RoomData;
use App\DTOs\Academic\SubjectData;
use App\Interfaces\AcademicRepositoryInterface;
use App\Models\Batch;
use App\Models\Grade;
use App\Models\Room;
use App\Models\Subject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicService
{

    public function __construct(
        private readonly AcademicRepositoryInterface $academicRepository
    ) {}
    /**
     * Store all academic setup data in one transaction
     * @param array $data
     * @return array
     */
    public function completeSetup(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $result = [];
            // Store batch
            $batch = [
                'name' => $data['batch_name'] ?? null,
                'start_date' => $data['batch_start_date'] ?? null,
                'end_date' => $data['batch_end_date'] ?? null,
                'status' => $data['batch_status'] ?? true,
            ];
            if ($batch['name']) {
                if (empty($batch['start_date']) || empty($batch['end_date'])) {
                    throw ValidationException::withMessages([
                        'start_date' => ['Batch start date is required.'],
                        'end_date' => ['Batch end date is required.'],
                    ]);
                }
                $result['batch'] = $this->setupBatch(BatchData::from($batch));
            }

            // Store grades/classes
            $result['grades'] = [];
            if (!empty($data['grade_level'])) {
                $gradeCount = count($data['grade_level']);
                for ($i = 0; $i < $gradeCount; $i++) {
                    $grade = [
                        'level' => $data['grade_level'][$i] ?? null,
                        'batch_id' => $result['batch']->id ?? null,
                        'grade_category_id' => $data['grade_category_id'][$i] ?? null,
                        'price_per_month' => $data['grade_price_per_month'][$i] ?? 0,
                        'subjects' => is_array($data['grade_subjects'][$i] ?? null) ? ($data['grade_subjects'][$i] ?? []) : (($data['grade_subjects'][$i] ?? '') !== '' ? [$data['grade_subjects'][$i]] : []),
                        'classes' => is_array($data['grade_classes'][$i] ?? null) ? ($data['grade_classes'][$i] ?? []) : (($data['grade_classes'][$i] ?? '') !== '' ? [$data['grade_classes'][$i]] : []),
                    ];
                    $result['grades'][] = $this->setupGrade(GradeData::from($grade));
                }
            }

            // Store rooms
            $result['rooms'] = [];
            if (!empty($data['room_name'])) {
                $roomCount = count($data['room_name']);
                for ($i = 0; $i < $roomCount; $i++) {
                    $room = [
                        'name' => $data['room_name'][$i] ?? null,
                        'building' => $data['room_building'][$i] ?? '',
                        'floor' => $data['room_floor'][$i] ?? null,
                        'capacity' => $data['room_capacity'][$i] ?? null,
                        'facilities' => is_array($data['room_facilities'][$i] ?? null) ? ($data['room_facilities'][$i] ?? []) : (($data['room_facilities'][$i] ?? '') !== '' ? [$data['room_facilities'][$i]] : []),
                    ];
                    $result['rooms'][] = $this->setupRoom(RoomData::from($room));
                }
            }

            // Store subjects
            $result['subjects'] = [];
            if (!empty($data['subject_name'])) {
                $subjectCount = count($data['subject_name']);
                for ($i = 0; $i < $subjectCount; $i++) {
                    $subject = [
                        'name' => $data['subject_name'][$i] ?? null,
                        'code' => $data['subject_code'][$i] ?? null,
                        'subject_type_id' => $data['subject_type_id'][$i] ?? null,
                    ];
                    $result['subjects'][] = $this->setupSubject(SubjectData::from($subject));
                }
            }

            return $result;
        });
    }


    /**
     * Setup batch
     */
    public function setupBatch(BatchData $data): Batch
    {
        // Check if batch already exists by name and dates
        if (Batch::where('name', $data->name)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Batch with this name already exists.'],
            ]);
        }
        return $this->academicRepository->createBatch($data);
    }

    /**
     * Setup grade with classes
     */
    public function setupGrade(GradeData $data): Grade
    {
        // Check if grade already exists for batch
        if (Grade::where('level', $data->level)
            ->where('batch_id', $data->batch_id)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'level' => ['Grade already exists for this batch.'],
            ]);
        }
        return $this->academicRepository->createGrade($data);
    }

    /**
     * Setup room
     */
    public function setupRoom(RoomData $data): Room
    {
        // Check if room already exists by name, building, and floor
        if (Room::where('name', $data->name)
            ->where('building', $data->building)
            ->where('floor', $data->floor)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'name' => ['Room already exists for this building and floor.'],
            ]);
        }
        return $this->academicRepository->createRoom($data);
    }

    /**
     * Setup subject
     */
    public function setupSubject(SubjectData $data): Subject
    {
        // Check if subject already exists by name and type
        if (Subject::where('code', $data->code)
            ->where('subject_type_id', $data->subject_type_id)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'code' => ['Subject Code already exists for this type.'],
            ]);
        }
        return $this->academicRepository->createSubject($data);
    }

    /**
     * Get grades with details
     */
    public function getGradesWithDetails(): array
    {
        return $this->academicRepository->getGradesWithDetails();
    }

    /**
     * Create batch with uniqueness guard
     */
    public function createBatch(BatchData $data): Batch
    {
        if (Batch::where('name', $data->name)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Batch name already exists.'],
            ]);
        }

        return $this->academicRepository->createBatch($data);
    }

    public function updateBatch(string $batchId, BatchData $data): Batch
    {
        $batch = $this->academicRepository->findBatch($batchId);

        if (!$batch) {
            throw new ModelNotFoundException('Batch not found');
        }

        if (Batch::where('name', $data->name)->where('id', '!=', $batchId)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Batch name already exists.'],
            ]);
        }

        return $this->academicRepository->updateBatch($batch, $data);
    }

    public function deleteBatch(string $batchId): bool
    {
        return $this->academicRepository->deleteBatch($batchId);
    }

    public function createGrade(GradeData $data): Grade
    {
        if (Grade::where('level', $data->level)
            ->where('batch_id', $data->batch_id)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'level' => ['Grade already exists for this batch.'],
            ]);
        }

        return DB::transaction(function () use ($data) {
            $grade = $this->academicRepository->createGrade($data);

            if (!empty($data->subjects)) {
                $grade->subjects()->sync($data->subjects);
            }

            return $grade;
        });
    }

    public function updateGrade(string $gradeId, GradeData $data): Grade
    {
        $grade = $this->academicRepository->findGrade($gradeId);

        if (!$grade) {
            throw new ModelNotFoundException('Grade not found');
        }

        if (Grade::where('level', $data->level)
            ->where('batch_id', $data->batch_id)
            ->where('id', '!=', $gradeId)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'level' => ['Grade already exists for this batch.'],
            ]);
        }

        return DB::transaction(function () use ($grade, $data) {
            $updated = $this->academicRepository->updateGrade($grade, $data);

            if (!empty($data->subjects)) {
                $updated->subjects()->sync($data->subjects);
            }

            return $updated;
        });
    }

    public function createClass(array $data)
    {
        return $this->academicRepository->createClass($data);
    }

    public function updateClass(string $classId, array $data)
    {
        $class = $this->academicRepository->getClasses()->firstWhere('id', $classId);

        if (!$class) {
            throw new ModelNotFoundException('Class not found');
        }

        return $this->academicRepository->updateClass($class, $data);
    }

    public function deleteClass(string $classId): bool
    {
        return $this->academicRepository->deleteClass($classId);
    }

    public function createRoom(RoomData $data): Room
    {
        if (Room::where('name', $data->name)
            ->where('building', $data->building)
            ->where('floor', $data->floor)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'name' => ['Room already exists for this building and floor.'],
            ]);
        }

        return $this->academicRepository->createRoom($data);
    }

    public function updateRoom(string $roomId, RoomData $data): Room
    {
        $room = $this->academicRepository->findRoom($roomId);

        if (!$room) {
            throw new ModelNotFoundException('Room not found');
        }

        if (Room::where('name', $data->name)
            ->where('building', $data->building)
            ->where('floor', $data->floor)
            ->where('id', '!=', $roomId)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'name' => ['Room already exists for this building and floor.'],
            ]);
        }

        return $this->academicRepository->updateRoom($room, $data);
    }

    public function createSubject(SubjectData $data): Subject
    {
        if (Subject::where('code', $data->code)->exists()) {
            throw ValidationException::withMessages([
                'code' => ['Subject code already exists.'],
            ]);
        }

        $subject = Subject::create($data->toArray());

        $subject->grades()->sync($data->gradeIds());

        return $subject->load('grades');
    }

    public function updateSubject(string $subjectId, SubjectData $data): Subject
    {
        $subject = $this->academicRepository->findSubject($subjectId);

        if (!$subject) {
            throw new ModelNotFoundException('Subject not found');
        }

        if (Subject::where('code', $data->code)->where('id', '!=', $subjectId)->exists()) {
            throw ValidationException::withMessages([
                'code' => ['Subject code already exists.'],
            ]);
        }

        $subject->update($data->toArray());

        $subject->grades()->sync($data->gradeIds());

        return $subject->load('grades');
    }

    /**
     * Get rooms
     */
    public function getRooms(): array
    {
        return $this->academicRepository->getRooms();
    }

    /**
     * Get subjects
     */
    public function getSubjects(): array
    {
        return $this->academicRepository->getSubjects();
    }

    /**
     * Delete grade
     */
    public function deleteGrade(string $gradeId): bool
    {
        return $this->academicRepository->deleteGrade($gradeId);
    }

    /**
     * Delete room
     */
    public function deleteRoom(string $roomId): bool
    {
        return $this->academicRepository->deleteRoom($roomId);
    }

    /**
     * Delete subject
     */
    public function deleteSubject(string $subjectId): bool
    {
        return $this->academicRepository->deleteSubject($subjectId);
    }

    /**
     * Attach subject to grade
     */
    public function attachSubjectToGrade(string $gradeId, string $subjectId): void
    {
        $this->academicRepository->attachSubjectToGrade($gradeId, $subjectId);
    }

    /**
     * Detach subject from grade
     */
    public function detachSubjectFromGrade(string $gradeId, string $subjectId): void
    {
        $this->academicRepository->detachSubjectFromGrade($gradeId, $subjectId);
    }
}
