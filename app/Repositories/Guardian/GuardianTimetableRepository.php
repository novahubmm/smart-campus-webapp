<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianTimetableRepositoryInterface;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Timetable;
use Carbon\Carbon;

class GuardianTimetableRepository implements GuardianTimetableRepositoryInterface
{
    private array $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function getFullTimetable(StudentProfile $student): array
    {
        $timetable = [];

        foreach ($this->days as $day) {
            $timetable[$day] = $this->getDayTimetable($student, $day);
        }

        return $timetable;
    }

    public function getDayTimetable(StudentProfile $student, string $day): array
    {
        $timetables = Timetable::where('class_id', $student->class_id)
            ->where('day', $day)
            ->with(['subject', 'teacher.user', 'room'])
            ->orderBy('start_time')
            ->get();

        return $timetables->map(function ($timetable) {
            $isLive = $this->isClassLive($timetable);

            return [
                'id' => $timetable->id,
                'subject' => $timetable->subject?->name ?? 'N/A',
                'subject_icon' => $timetable->subject?->icon ?? 'book',
                'teacher' => $timetable->teacher?->user?->name ?? 'N/A',
                'start_time' => $timetable->start_time,
                'end_time' => $timetable->end_time,
                'room' => $timetable->room?->name ?? 'N/A',
                'type' => $timetable->type ?? 'regular',
                'is_live' => $isLive,
                'meeting_link' => $timetable->meeting_link,
            ];
        })->toArray();
    }

    public function getClassInfo(StudentProfile $student): array
    {
        $class = SchoolClass::with(['grade', 'teacher.user', 'students'])
            ->find($student->class_id);

        if (!$class) {
            return [
                'id' => null,
                'class_name' => 'N/A',
                'section' => 'N/A',
                'class_teacher' => null,
                'total_students' => 0,
                'room' => 'N/A',
                'subjects' => [],
                'timetable' => [],
            ];
        }

        // Get subjects for this grade
        $gradeSubjects = \App\Models\GradeSubject::where('grade_id', $student->grade_id)
            ->with(['subject', 'teacher.user'])
            ->get();

        $subjects = $gradeSubjects->map(function ($gs) {
            return [
                'id' => $gs->subject?->id,
                'name' => $gs->subject?->name ?? 'N/A',
                'teacher' => $gs->teacher?->user?->name ?? 'N/A',
                'icon' => $gs->subject?->icon ?? 'book',
            ];
        })->toArray();

        // Get timetable
        $timetable = $this->getFullTimetable($student);

        return [
            'id' => $class->id,
            'class_name' => $class->grade?->name ?? 'N/A',
            'section' => $class->section ?? 'N/A',
            'class_teacher' => $class->teacher ? [
                'id' => $class->teacher->id,
                'name' => $class->teacher->user?->name ?? 'N/A',
                'photo_url' => $class->teacher->photo_path 
                    ? asset($class->teacher->photo_path) 
                    : null,
                'phone' => $class->teacher->user?->phone,
                'email' => $class->teacher->user?->email,
            ] : null,
            'total_students' => $class->students->count(),
            'room' => $class->room ?? 'N/A',
            'subjects' => $subjects,
            'timetable' => $timetable,
        ];
    }

    public function getDetailedClassInfo(StudentProfile $student): array
    {
        $class = SchoolClass::with(['grade', 'teacher.user', 'students.user'])
            ->find($student->class_id);

        if (!$class) {
            return [];
        }

        // Get class statistics
        $stats = $this->getClassStatistics($student);

        // Get all teachers teaching this class
        $teachers = $this->getClassTeachers($student);

        // Get subjects
        $gradeSubjects = \App\Models\GradeSubject::where('grade_id', $student->grade_id)
            ->with(['subject', 'teacher.user'])
            ->get();

        $subjects = $gradeSubjects->map(function ($gs) {
            return [
                'id' => $gs->subject?->id,
                'name' => $gs->subject?->name ?? 'N/A',
                'teacher' => [
                    'id' => $gs->teacher?->id,
                    'name' => $gs->teacher?->user?->name ?? 'N/A',
                    'photo_url' => $gs->teacher?->photo_path 
                        ? asset($gs->teacher->photo_path) 
                        : null,
                ],
                'icon' => $gs->subject?->icon ?? 'book',
            ];
        })->toArray();

        return [
            'id' => $class->id,
            'class_name' => $class->grade?->name ?? 'N/A',
            'section' => $class->section ?? 'N/A',
            'full_name' => ($class->grade?->name ?? 'N/A') . ' - ' . ($class->section ?? 'N/A'),
            'class_teacher' => $class->teacher ? [
                'id' => $class->teacher->id,
                'name' => $class->teacher->user?->name ?? 'N/A',
                'photo_url' => $class->teacher->photo_path 
                    ? asset($class->teacher->photo_path) 
                    : null,
                'phone' => $class->teacher->user?->phone,
                'email' => $class->teacher->user?->email,
                'department' => $class->teacher->department?->name ?? 'N/A',
            ] : null,
            'room' => $class->room ?? 'N/A',
            'capacity' => $class->capacity ?? 40,
            'total_students' => $class->students->count(),
            'subjects' => $subjects,
            'teachers' => $teachers,
            'statistics' => $stats,
        ];
    }

    public function getClassTeachers(StudentProfile $student): array
    {
        $gradeSubjects = \App\Models\GradeSubject::where('grade_id', $student->grade_id)
            ->with(['subject', 'teacher.user', 'teacher.department'])
            ->get();

        $teachers = $gradeSubjects->map(function ($gs) {
            if (!$gs->teacher) {
                return null;
            }

            return [
                'id' => $gs->teacher->id,
                'name' => $gs->teacher->user?->name ?? 'N/A',
                'photo_url' => $gs->teacher->photo_path 
                    ? asset($gs->teacher->photo_path) 
                    : null,
                'subjects' => [$gs->subject?->name ?? 'N/A'],
                'phone' => $gs->teacher->user?->phone,
                'email' => $gs->teacher->user?->email,
                'department' => $gs->teacher->department?->name ?? 'N/A',
            ];
        })->filter()->unique('id')->values()->toArray();

        return $teachers;
    }

    public function getClassStatistics(StudentProfile $student): array
    {
        $class = SchoolClass::with('students')->find($student->class_id);

        if (!$class) {
            return [];
        }

        // Get attendance statistics
        $yearStart = Carbon::now()->startOfYear();
        $attendanceRecords = \App\Models\StudentAttendance::whereIn('student_id', $class->students->pluck('id'))
            ->where('date', '>=', $yearStart)
            ->get();

        $totalDays = $attendanceRecords->groupBy('student_id')->map->count()->avg();
        $presentDays = $attendanceRecords->whereIn('status', ['present', 'late'])->groupBy('student_id')->map->count()->avg();
        $classAttendanceRate = $totalDays > 0 ? round($presentDays / $totalDays * 100, 1) : 0;

        // Get exam statistics
        $latestExam = \App\Models\Exam::whereHas('examSchedules', function ($q) use ($student) {
                $q->where('class_id', $student->class_id);
            })
            ->orderBy('start_date', 'desc')
            ->first();

        $classAverageMarks = 0;
        if ($latestExam) {
            $examMarks = \App\Models\ExamMark::where('exam_id', $latestExam->id)
                ->whereIn('student_id', $class->students->pluck('id'))
                ->get();
            
            $totalMarks = $examMarks->sum('marks_obtained');
            $totalPossible = $examMarks->count() * ($latestExam->total_marks ?? 100);
            $classAverageMarks = $totalPossible > 0 ? round($totalMarks / $totalPossible * 100, 1) : 0;
        }

        // Gender distribution
        $maleCount = $class->students->where('gender', 'male')->count();
        $femaleCount = $class->students->where('gender', 'female')->count();

        return [
            'total_students' => $class->students->count(),
            'male_students' => $maleCount,
            'female_students' => $femaleCount,
            'class_attendance_rate' => $classAttendanceRate,
            'class_average_performance' => $classAverageMarks,
            'total_subjects' => \App\Models\GradeSubject::where('grade_id', $student->grade_id)->count(),
        ];
    }

    private function isClassLive(Timetable $timetable): bool
    {
        $now = Carbon::now();
        $today = $now->format('l');

        if ($timetable->day !== $today) {
            return false;
        }

        $startTime = Carbon::parse($timetable->start_time);
        $endTime = Carbon::parse($timetable->end_time);
        $currentTime = Carbon::parse($now->format('H:i:s'));

        return $currentTime->between($startTime, $endTime);
    }
}
