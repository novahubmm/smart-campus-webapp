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
        $class = SchoolClass::with(['grade', 'classTeacher.user', 'students'])
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
            'class_teacher' => $class->classTeacher ? [
                'id' => $class->classTeacher->id,
                'name' => $class->classTeacher->user?->name ?? 'N/A',
                'photo_url' => $class->classTeacher->photo_path 
                    ? asset($class->classTeacher->photo_path) 
                    : null,
                'phone' => $class->classTeacher->user?->phone,
                'email' => $class->classTeacher->user?->email,
            ] : null,
            'total_students' => $class->students->count(),
            'room' => $class->room ?? 'N/A',
            'subjects' => $subjects,
            'timetable' => $timetable,
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
