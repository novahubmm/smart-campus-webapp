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

    public function getFullTimetable(StudentProfile $student, ?string $weekStartDate = null): array
    {
        $weekStart = $weekStartDate ? Carbon::parse($weekStartDate)->startOfWeek() : Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek()->subDays(1); // Saturday

        $schedule = [];
        $totalClasses = 0;
        
        foreach ($this->days as $day) {
            $daySchedule = $this->getDayTimetableFormatted($student, $day);
            $schedule[$day] = $daySchedule;
            $totalClasses += count($daySchedule);
        }

        // Get break times with IDs and duration
        $breakTimes = [
            [
                'id' => 'break-1',
                'type' => 'break',
                'name' => 'Morning Break',
                'name_mm' => 'နံနက်အနားယူချိန်',
                'start_time' => '09:45',
                'end_time' => '10:00',
                'duration_minutes' => 15,
            ],
            [
                'id' => 'lunch-1',
                'type' => 'lunch',
                'name' => 'Lunch Break',
                'name_mm' => 'နေ့လည်စာစားချိန်',
                'start_time' => '12:15',
                'end_time' => '13:15',
                'duration_minutes' => 60,
            ],
        ];

        return [
            'student_id' => $student->id,
            'student_name' => $student->user?->name ?? 'N/A',
            'grade' => $student->grade?->name ?? 'N/A',
            'section' => $student->classModel?->section ?? 'N/A',
            'week_start_date' => $weekStart->format('Y-m-d'),
            'week_end_date' => $weekEnd->format('Y-m-d'),
            'schedule' => $schedule,
            'break_times' => $breakTimes,
            'total_periods_per_day' => 8,
            'total_classes_this_week' => $totalClasses,
        ];
    }

    private function getDayTimetableFormatted(StudentProfile $student, string $day): array
    {
        if (!$student->class_id) {
            return [];
        }

        // Get the active timetable for the student's class
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('is_active', true)
            ->first();

        if (!$timetable) {
            return [];
        }

        // Get periods for the specific day
        $periods = $timetable->periods()
            ->where('day_of_week', $day)
            ->where('is_break', false)
            ->with(['subject', 'teacher.user', 'room'])
            ->orderBy('period_number')
            ->get();

        $formattedPeriods = [];

        foreach ($periods as $period) {
            $formattedPeriods[] = [
                'id' => $period->id,
                'period' => $period->period_number,
                'subject_id' => $period->subject?->id,
                'subject' => $period->subject?->name ?? 'N/A',
                'subject_mm' => $period->subject?->name ?? 'N/A',
                'teacher_id' => $period->teacher?->id,
                'teacher' => $period->teacher?->user?->name ?? 'N/A',
                'teacher_mm' => $period->teacher?->user?->name ?? 'N/A',
                'teacher_phone' => $period->teacher?->user?->phone ?? '',
                'teacher_email' => $period->teacher?->user?->email ?? '',
                'start_time' => $period->starts_at ? Carbon::parse($period->starts_at)->format('H:i') : '00:00',
                'end_time' => $period->ends_at ? Carbon::parse($period->ends_at)->format('H:i') : '00:00',
                'room' => $period->room?->name ?? 'Room TBA',
                'room_mm' => 'အခန်း ' . ($period->room?->name ?? 'TBA'),
                'status' => 'normal',
                'substitute_teacher' => null,
                'substitute_teacher_mm' => null,
                'swapped_with' => null,
                'original_time' => null,
                'note' => $period->notes ?? null,
                'note_mm' => $period->notes ?? null,
            ];
        }

        return $formattedPeriods;
    }

    private function getClassStatus(Timetable $timetable): string
    {
        // Check if class is cancelled, substituted, or swapped
        // For now, return 'normal' - can be extended based on your business logic
        if ($timetable->is_cancelled ?? false) {
            return 'cancelled';
        }
        
        if ($timetable->substitute_teacher_id ?? false) {
            return 'substitute';
        }
        
        if ($timetable->swapped_with_id ?? false) {
            return 'swapped';
        }
        
        return 'normal';
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
                'class_id' => null,
                'grade_code' => 'N/A',
                'grade_name' => 'N/A',
                'grade' => 'N/A',
                'section' => 'N/A',
                'academic_year' => 'N/A',
                'building' => 'N/A',
                'room_number' => 'N/A',
                'location' => 'N/A',
                'student_count' => 0,
                'total_capacity' => 0,
                'class_teacher_id' => null,
                'class_teacher_name' => 'N/A',
                'class_teacher_name_mm' => 'N/A',
                'class_teacher_phone' => null,
                'class_teacher_email' => null,
                'class_teacher_photo' => null,
            ];
        }

        // Get academic year (batch)
        $academicYear = \App\Models\Batch::where('status', true)
            ->orderBy('start_date', 'desc')
            ->first();
        
        // Parse building and room from room field
        $building = 'N/A';
        $roomNumber = 'N/A';
        $location = $class->room ?? 'N/A';
        
        if ($class->room && str_contains($class->room, ',')) {
            $parts = explode(',', $class->room);
            $building = trim($parts[0] ?? 'N/A');
            $roomNumber = trim(str_replace('Room', '', $parts[1] ?? 'N/A'));
        } elseif ($class->room) {
            $roomNumber = $class->room;
        }

        return [
            'class_id' => $class->id,
            'grade_code' => ($class->grade?->name ?? '') . ($class->section ?? ''),
            'grade_name' => ($class->grade?->name ?? 'N/A') . ' - Section ' . ($class->section ?? 'N/A'),
            'grade' => $class->grade?->name ?? 'N/A',
            'section' => $class->section ?? 'N/A',
            'academic_year' => $academicYear ? $academicYear->name : date('Y') . '-' . (date('Y') + 1),
            'building' => $building,
            'room_number' => $roomNumber,
            'location' => $location,
            'student_count' => $class->students->count(),
            'total_capacity' => $class->capacity ?? 40,
            'class_teacher_id' => $class->teacher?->id,
            'class_teacher_name' => $class->teacher?->user?->name ?? 'N/A',
            'class_teacher_name_mm' => $class->teacher?->user?->name_mm ?? $class->teacher?->user?->name ?? 'N/A',
            'class_teacher_phone' => $class->teacher?->user?->phone,
            'class_teacher_email' => $class->teacher?->user?->email,
            'class_teacher_photo' => $class->teacher?->photo_path 
                ? asset($class->teacher->photo_path) 
                : null,
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
            ->with(['subject.subjectType', 'teacher.user'])
            ->get();

        $subjects = $gradeSubjects->map(function ($gs) {
            return [
                'id' => $gs->subject?->id,
                'name' => $gs->subject?->name ?? 'N/A',
                'category' => $gs->subject?->subjectType?->name ?? 'Core',
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
        $class = SchoolClass::with(['teacher.user'])->find($student->class_id);
        
        // Get all subjects for this grade
        $gradeSubjectIds = \App\Models\GradeSubject::where('grade_id', $student->grade_id)
            ->pluck('subject_id');

        // Get all teachers teaching these subjects (from subject_teacher pivot table)
        $subjectTeacherData = \DB::table('subject_teacher')
            ->whereIn('subject_id', $gradeSubjectIds)
            ->get();

        // Group subjects by teacher
        $teacherSubjectsMap = [];
        foreach ($subjectTeacherData as $st) {
            if (!isset($teacherSubjectsMap[$st->teacher_profile_id])) {
                $teacherSubjectsMap[$st->teacher_profile_id] = [];
            }
            $teacherSubjectsMap[$st->teacher_profile_id][] = $st->subject_id;
        }

        // Get class teacher
        $classTeacher = null;
        if ($class && $class->teacher) {
            $teacherSubjects = [];
            if (isset($teacherSubjectsMap[$class->teacher->id])) {
                $subjects = \App\Models\Subject::whereIn('id', $teacherSubjectsMap[$class->teacher->id])->get();
                $teacherSubjects = $subjects->pluck('name')->toArray();
            }

            $classTeacher = [
                'id' => $class->teacher->id,
                'name' => $class->teacher->user?->name ?? 'N/A',
                'name_mm' => $class->teacher->user?->name_mm ?? $class->teacher->user?->name ?? 'N/A',
                'role' => 'Class Teacher',
                'role_mm' => 'အတန်းဆရာ',
                'phone' => $class->teacher->user?->phone,
                'email' => $class->teacher->user?->email,
                'photo' => $class->teacher->photo_path 
                    ? asset($class->teacher->photo_path) 
                    : null,
                'subjects' => $teacherSubjects,
                'is_class_teacher' => true,
            ];
        }

        // Get subject teachers (excluding class teacher)
        $subjectTeachers = [];
        $classTeacherId = $class && $class->teacher ? $class->teacher->id : null;
        
        foreach ($teacherSubjectsMap as $teacherId => $subjectIds) {
            // Skip class teacher
            if ($teacherId === $classTeacherId) {
                continue;
            }

            $teacher = \App\Models\TeacherProfile::with('user')->find($teacherId);
            if (!$teacher) {
                continue;
            }

            $subjects = \App\Models\Subject::whereIn('id', $subjectIds)->get();
            $teacherSubjects = $subjects->pluck('name')->toArray();
            $primarySubject = $teacherSubjects[0] ?? 'Teacher';

            $subjectTeachers[] = [
                'id' => $teacher->id,
                'name' => $teacher->user?->name ?? 'N/A',
                'name_mm' => $teacher->user?->name_mm ?? $teacher->user?->name ?? 'N/A',
                'role' => $primarySubject . ' Teacher',
                'role_mm' => $primarySubject . ' ဆရာ',
                'phone' => $teacher->user?->phone,
                'email' => $teacher->user?->email,
                'photo' => $teacher->photo_path 
                    ? asset($teacher->photo_path) 
                    : null,
                'subjects' => $teacherSubjects,
                'is_class_teacher' => false,
            ];
        }

        $totalTeachers = ($classTeacher ? 1 : 0) + count($subjectTeachers);

        return [
            'class_teacher' => $classTeacher,
            'subject_teachers' => $subjectTeachers,
            'total_teachers' => $totalTeachers,
        ];
    }

    public function getClassStatistics(StudentProfile $student): array
    {
        $class = SchoolClass::with('students')->find($student->class_id);

        if (!$class) {
            return [
                'class_id' => null,
                'grade_code' => 'N/A',
                'total_students' => 0,
                'male_students' => 0,
                'female_students' => 0,
                'average_attendance' => 0,
                'average_performance' => 0,
                'top_performers' => [],
                'subject_performance' => [],
            ];
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
        $topPerformers = [];
        $subjectPerformance = [];

        if ($latestExam) {
            $examMarks = \App\Models\ExamMark::where('exam_id', $latestExam->id)
                ->whereIn('student_id', $class->students->pluck('id'))
                ->with('student.user')
                ->get();
            
            $totalMarks = $examMarks->sum('marks_obtained');
            $totalPossible = $examMarks->count() * ($latestExam->total_marks ?? 100);
            $classAverageMarks = $totalPossible > 0 ? round($totalMarks / $totalPossible * 100, 1) : 0;

            // Get top 3 performers
            $topPerformers = $examMarks->sortByDesc('marks_obtained')
                ->take(3)
                ->values()
                ->map(function ($mark, $index) use ($latestExam) {
                    $percentage = $latestExam->total_marks > 0 
                        ? round(($mark->marks_obtained / $latestExam->total_marks) * 100, 1)
                        : 0;
                    
                    return [
                        'student_id' => $mark->student_id,
                        'student_name' => $mark->student?->user?->name ?? 'N/A',
                        'average_score' => $percentage,
                        'rank' => $index + 1,
                    ];
                })
                ->toArray();

            // Get subject-wise performance
            $gradeSubjects = \App\Models\GradeSubject::where('grade_id', $student->grade_id)
                ->with('subject')
                ->get();

            foreach ($gradeSubjects as $gs) {
                if (!$gs->subject) continue;

                $subjectMarks = \App\Models\ExamMark::where('exam_id', $latestExam->id)
                    ->where('subject_id', $gs->subject->id)
                    ->whereIn('student_id', $class->students->pluck('id'))
                    ->get();

                if ($subjectMarks->isNotEmpty()) {
                    $subjectPerformance[] = [
                        'subject_name' => $gs->subject->name,
                        'class_average' => round($subjectMarks->avg('marks_obtained'), 1),
                        'highest_score' => round($subjectMarks->max('marks_obtained'), 1),
                        'lowest_score' => round($subjectMarks->min('marks_obtained'), 1),
                    ];
                }
            }
        }

        // Gender distribution
        $maleCount = $class->students->where('gender', 'male')->count();
        $femaleCount = $class->students->where('gender', 'female')->count();

        $gradeCode = ($class->grade?->name ?? '') . ($class->section ?? '');

        return [
            'class_id' => $class->id,
            'grade_code' => $gradeCode,
            'total_students' => $class->students->count(),
            'male_students' => $maleCount,
            'female_students' => $femaleCount,
            'average_attendance' => $classAttendanceRate,
            'average_performance' => $classAverageMarks,
            'top_performers' => $topPerformers,
            'subject_performance' => $subjectPerformance,
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
