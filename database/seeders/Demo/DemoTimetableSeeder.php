<?php

namespace Database\Seeders\Demo;

use App\Models\Batch;
use App\Models\Period;
use App\Models\Timetable;

class DemoTimetableSeeder extends DemoBaseSeeder
{
    // Track teacher assignments: teacher can only teach ONE class per day
    private array $teacherDayAssignments = [];

    public function run(Batch $batch, array $classes, array $subjects): array
    {
        $this->command->info('Creating Timetables (39)...');

        $periodTimes = [
            1 => ['08:00', '08:45', false],
            2 => ['08:45', '09:30', false],
            3 => ['09:30', '10:15', false],
            4 => ['10:15', '11:15', true],   // Morning Break
            5 => ['11:15', '12:00', false],
            6 => ['12:00', '12:45', false],
            7 => ['12:45', '13:30', false],
        ];

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        // Reset teacher assignments
        $this->teacherDayAssignments = [];

        // Store all periods for later use by homework/class record seeders
        $allPeriods = [];
        $createdCount = 0;

        foreach ($classes as $class) {
            $grade = $class->grade;

            // Get subjects for this grade using the pivot table
            $gradeSubjects = \App\Models\Subject::whereHas('grades', function($query) use ($grade) {
                $query->where('grades.id', $grade->id);
            })->with('teachers')->get();

            if ($gradeSubjects->isEmpty()) {
                $this->command->warn("  No subjects found for {$class->name} (Grade: {$grade->name})");
                $createdCount++;
                continue;
            }

            $timetable = Timetable::create([
                'class_id' => $class->id,
                'batch_id' => $batch->id,
                'grade_id' => $grade->id,
                'name' => "Timetable - {$class->name}",
                'is_active' => true,
                'published_at' => $this->getSchoolOpenDate(),
                'effective_from' => $this->getSchoolOpenDate(),
                'minutes_per_period' => 45,
                'break_duration' => 15,
                'school_start_time' => '08:00',
                'school_end_time' => '14:30',
                'week_days' => $days,
                'version' => 1,
            ]);

            // Convert to array format with subject and teacher
            $subjectsArray = $gradeSubjects->map(function($subject) {
                return [
                    'subject' => $subject,
                    'teacher' => $subject->teachers->first()
                ];
            })->toArray();

            foreach ($days as $day) {
                // Find available subjects for this day (teacher not assigned to another class)
                $availableSubjects = $this->getAvailableSubjectsForDay($subjectsArray, $day, $class->id);
                
                // If no subjects are available (all teachers busy), use all subjects anyway
                // Teachers can teach multiple classes per day if needed
                if (empty($availableSubjects)) {
                    $availableSubjects = $subjectsArray;
                }
                
                shuffle($availableSubjects);
                $subjectIndex = 0;

                foreach ($periodTimes as $periodNumber => $periodData) {
                    [$startTime, $endTime, $isBreak] = $periodData;

                    $periodAttributes = [
                        'timetable_id' => $timetable->id,
                        'day_of_week' => $day,
                        'period_number' => $periodNumber,
                        'starts_at' => $startTime,
                        'ends_at' => $endTime,
                        'is_break' => $isBreak,
                        'room_id' => $class->room_id,
                    ];

                    if ($isBreak) {
                        $periodAttributes['subject_id'] = null;
                        $periodAttributes['teacher_profile_id'] = null;
                    } else {
                        $subjectData = $availableSubjects[$subjectIndex % count($availableSubjects)];
                        $teacherId = $subjectData['teacher']->id ?? null;

                        $periodAttributes['subject_id'] = $subjectData['subject']->id;
                        $periodAttributes['teacher_profile_id'] = $teacherId;

                        // Mark teacher as assigned to this class for this day
                        if ($teacherId) {
                            $this->markTeacherAssignedToClass($teacherId, $day, $class->id);
                        }
                        
                        $subjectIndex++;
                    }

                    $period = Period::create($periodAttributes);

                    if (!$isBreak) {
                        $allPeriods[] = [
                            'period' => $period,
                            'class' => $class,
                            'day' => $day,
                        ];
                    }
                }
            }

            $createdCount++;
        }

        $this->command->info("  Created timetables for {$createdCount} classes.");
        return $allPeriods;
    }

    /**
     * Get subjects whose teachers are available for this day (not assigned to another class)
     */
    private function getAvailableSubjectsForDay(array $gradeSubjects, string $day, string $classId): array
    {
        $available = [];
        foreach ($gradeSubjects as $subjectData) {
            $teacherId = $subjectData['teacher']->id ?? null;
            if (!$teacherId || $this->isTeacherAvailableForClass($teacherId, $day, $classId)) {
                $available[] = $subjectData;
            }
        }
        return $available;
    }

    /**
     * Check if teacher is available for this class on this day
     * Teacher can only teach ONE class per day
     */
    private function isTeacherAvailableForClass(?string $teacherId, string $day, string $classId): bool
    {
        if (!$teacherId) {
            return true;
        }

        $key = "{$teacherId}_{$day}";
        
        // If teacher not assigned to any class this day, they're available
        if (!isset($this->teacherDayAssignments[$key])) {
            return true;
        }

        // If teacher is already assigned to THIS class, they're available
        return $this->teacherDayAssignments[$key] === $classId;
    }

    /**
     * Mark teacher as assigned to a specific class for a day
     */
    private function markTeacherAssignedToClass(string $teacherId, string $day, string $classId): void
    {
        $key = "{$teacherId}_{$day}";
        $this->teacherDayAssignments[$key] = $classId;
    }
}
