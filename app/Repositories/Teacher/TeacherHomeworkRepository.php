<?php

namespace App\Repositories\Teacher;

use App\Interfaces\Teacher\TeacherHomeworkRepositoryInterface;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;

class TeacherHomeworkRepository implements TeacherHomeworkRepositoryInterface
{
    public function getHomeworkList(User $teacher, ?string $classId, ?string $status): array
    {
        $teacherProfile = $teacher->teacherProfile;

        if (!$teacherProfile) {
            return ['homework' => [], 'summary' => ['total' => 0, 'active' => 0, 'completed' => 0]];
        }

        $query = Homework::where('teacher_id', $teacherProfile->id)
            ->with(['schoolClass.grade', 'schoolClass.students', 'submissions']);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $homeworks = $query->orderByDesc('assigned_date')->get();

        $homeworkData = $homeworks->map(function ($homework) {
            $class = $homework->schoolClass;
            $submittedCount = $homework->submissions()->whereNotNull('submitted_at')->count();
            $totalCount = $class?->students()->count() ?? 0;
            $progressPercentage = $totalCount > 0 ? (int) round(($submittedCount / $totalCount) * 100) : 0;

            // Get subject from teacher's periods for this class
            $subject = $this->getSubjectForClass($homework->teacher_id, $homework->class_id);

            return [
                'id' => $homework->id,
                'title' => $homework->title,
                'description' => $homework->description,
                'grade' => $class?->grade?->name . ($class?->name ? ' ' . $class->name : ''),
                'class_id' => $homework->class_id,
                'subject' => $subject,
                'due_date' => $homework->due_date->format('Y-m-d'),
                'assigned_date' => $homework->assigned_date->format('Y-m-d'),
                'status' => $homework->status,
                'submitted_count' => $submittedCount,
                'total_count' => $totalCount,
                'progress_percentage' => $progressPercentage,
                'icon' => '📚',
            ];
        });

        $activeCount = $homeworks->where('status', 'active')->count();
        $completedCount = $homeworks->where('status', 'completed')->count();

        return [
            'homework' => $homeworkData->values()->toArray(),
            'summary' => [
                'total' => $homeworks->count(),
                'active' => $activeCount,
                'completed' => $completedCount,
            ],
        ];
    }

    public function getHomeworkDetail(User $teacher, string $homeworkId): ?array
    {
        $teacherProfile = $teacher->teacherProfile;

        if (!$teacherProfile) {
            return null;
        }

        $homework = Homework::where('id', $homeworkId)
            ->where('teacher_id', $teacherProfile->id)
            ->with(['schoolClass.grade', 'schoolClass.students.user', 'submissions'])
            ->first();

        if (!$homework) {
            return null;
        }

        $class = $homework->schoolClass;
        $submissions = $homework->submissions->keyBy('student_id');
        $submittedCount = $submissions->whereNotNull('submitted_at')->count();
        $totalCount = $class?->students()->count() ?? 0;
        $progressPercentage = $totalCount > 0 ? (int) round(($submittedCount / $totalCount) * 100) : 0;

        $subject = $this->getSubjectForClass($homework->teacher_id, $homework->class_id);

        $students = $class?->students->map(function ($student) use ($submissions) {
            $submission = $submissions->get($student->id);
            return [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_number' => $student->student_identifier ?? '',
                'has_submitted' => $submission?->submitted_at !== null,
                'submitted_at' => $submission?->submitted_at?->toISOString(),
                'avatar' => avatar_url($student->photo_path, 'student'),
            ];
        })->sortBy('roll_number')->values();

        return [
            'homework' => [
                'id' => $homework->id,
                'title' => $homework->title,
                'description' => $homework->description,
                'grade' => $class?->grade?->name . ($class?->name ? ' ' . $class->name : ''),
                'class_id' => $homework->class_id,
                'subject' => $subject,
                'due_date' => $homework->due_date->format('Y-m-d'),
                'assigned_date' => $homework->assigned_date->format('Y-m-d'),
                'status' => $homework->status,
                'submitted_count' => $submittedCount,
                'total_count' => $totalCount,
                'progress_percentage' => $progressPercentage,
                'icon' => '📚',
            ],
            'students' => $students->toArray(),
        ];
    }

    public function createHomework(User $teacher, array $data): array
    {
        $teacherProfile = $teacher->teacherProfile;

        if (!$teacherProfile) {
            throw new \Exception('Teacher profile not found');
        }

        // Verify teacher has access to this class
        $hasAccess = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('class_id', $data['class_id'])->where('is_active', true))
            ->exists();

        if (!$hasAccess) {
            throw new \Exception('Access denied to this class');
        }

        $homework = Homework::create([
            'class_id' => $data['class_id'],
            'teacher_id' => $teacherProfile->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => Carbon::parse($data['due_date']),
            'assigned_date' => now(),
            'status' => 'active',
        ]);

        $homework->load(['schoolClass.grade', 'schoolClass.students']);

        $class = $homework->schoolClass;
        $totalCount = $class?->students()->count() ?? 0;
        $subject = $this->getSubjectForClass($teacherProfile->id, $homework->class_id);

        return [
            'homework' => [
                'id' => $homework->id,
                'title' => $homework->title,
                'description' => $homework->description,
                'grade' => $class?->grade?->name . ($class?->name ? ' ' . $class->name : ''),
                'class_id' => $homework->class_id,
                'subject' => $subject,
                'due_date' => $homework->due_date->format('Y-m-d'),
                'assigned_date' => $homework->assigned_date->format('Y-m-d'),
                'status' => $homework->status,
                'submitted_count' => 0,
                'total_count' => $totalCount,
                'progress_percentage' => 0,
                'icon' => '📚',
            ],
        ];
    }

    public function collectHomework(User $teacher, string $homeworkId, string $studentId): ?array
    {
        $teacherProfile = $teacher->teacherProfile;

        if (!$teacherProfile) {
            return null;
        }

        $homework = Homework::where('id', $homeworkId)
            ->where('teacher_id', $teacherProfile->id)
            ->with(['schoolClass.students'])
            ->first();

        if (!$homework) {
            return null;
        }

        // Verify student belongs to this class
        $student = $homework->schoolClass?->students()->where('student_profiles.id', $studentId)->with('user')->first();

        if (!$student) {
            return null;
        }

        // Create or update submission
        $submission = HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $homeworkId,
                'student_id' => $studentId,
            ],
            [
                'submitted_at' => now(),
                'collected_by' => $teacher->id,
            ]
        );

        // Get updated stats
        $submittedCount = $homework->submissions()->whereNotNull('submitted_at')->count();
        $totalCount = $homework->schoolClass?->students()->count() ?? 0;
        $progressPercentage = $totalCount > 0 ? (int) round(($submittedCount / $totalCount) * 100) : 0;

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_number' => $student->student_identifier ?? '',
                'has_submitted' => true,
                'submitted_at' => $submission->submitted_at->toISOString(),
                'avatar' => avatar_url($student->photo_path, 'student'),
            ],
            'homework_stats' => [
                'submitted_count' => $submittedCount,
                'total_count' => $totalCount,
                'progress_percentage' => $progressPercentage,
            ],
        ];
    }

    private function getSubjectForClass(string $teacherId, string $classId): string
    {
        $period = Period::where('teacher_profile_id', $teacherId)
            ->whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->with('subject')
            ->first();

        return $period?->subject?->name ?? 'N/A';
    }
}
