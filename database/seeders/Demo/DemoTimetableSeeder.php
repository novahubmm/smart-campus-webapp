<?php

namespace Database\Seeders\Demo;

use App\Models\Batch;
use App\Models\Period;
use App\Models\Timetable;

class DemoTimetableSeeder extends DemoBaseSeeder
{
    // Track teacher assignments by time slot: [teacher_id][day][time_slot] = class_id
    private array $teacherTimeSlotAssignments = [];

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
        $this->teacherTimeSlotAssignments = [];

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
                // Shuffle subjects for variety
                $shuffledSubjects = $subjectsArray;
                shuffle($shuffledSubjects);
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
                        // Find an available teacher for this time slot
                        $assignedTeacher = null;
                        $assignedSubject = null;
                        $attempts = 0;
                        $maxAttempts = count($shuffledSubjects) * 2;

                        while ($attempts < $maxAttempts) {
                            $subjectData = $shuffledSubjects[$subjectIndex % count($shuffledSubjects)];
                            $teacherId = $subjectData['teacher']->id ?? null;

                            // Check if teacher is available for this time slot
                            if (!$teacherId || $this->isTeacherAvailableForTimeSlot($teacherId, $day, $startTime, $endTime)) {
                                $assignedTeacher = $teacherId;
                                $assignedSubject = $subjectData['subject'];
                                break;
                            }

                            $subjectIndex++;
                            $attempts++;
                        }

                        // If no available teacher found, assign anyway (fallback)
                        if (!$assignedSubject) {
                            $subjectData = $shuffledSubjects[0];
                            $assignedTeacher = $subjectData['teacher']->id ?? null;
                            $assignedSubject = $subjectData['subject'];
                            $this->command->warn("  Warning: Teacher double-booking for {$class->name} on {$day} at {$startTime}");
                        }

                        $periodAttributes['subject_id'] = $assignedSubject->id;
                        $periodAttributes['teacher_profile_id'] = $assignedTeacher;

                        // Mark teacher as assigned to this time slot
                        if ($assignedTeacher) {
                            $this->markTeacherAssignedToTimeSlot($assignedTeacher, $day, $startTime, $endTime, $class->id);
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
     * Check if teacher is available for a specific time slot
     */
    private function isTeacherAvailableForTimeSlot(?string $teacherId, string $day, string $startTime, string $endTime): bool
    {
        if (!$teacherId) {
            return true;
        }

        // Check if teacher has any assignments on this day
        if (!isset($this->teacherTimeSlotAssignments[$teacherId][$day])) {
            return true;
        }

        // Check for time conflicts with existing assignments
        foreach ($this->teacherTimeSlotAssignments[$teacherId][$day] as $slot) {
            if ($this->timeOverlaps($startTime, $endTime, $slot['start'], $slot['end'])) {
                return false; // Teacher is busy during this time
            }
        }

        return true; // No conflicts found
    }

    /**
     * Mark teacher as assigned to a specific time slot
     */
    private function markTeacherAssignedToTimeSlot(string $teacherId, string $day, string $startTime, string $endTime, string $classId): void
    {
        if (!isset($this->teacherTimeSlotAssignments[$teacherId])) {
            $this->teacherTimeSlotAssignments[$teacherId] = [];
        }

        if (!isset($this->teacherTimeSlotAssignments[$teacherId][$day])) {
            $this->teacherTimeSlotAssignments[$teacherId][$day] = [];
        }

        $this->teacherTimeSlotAssignments[$teacherId][$day][] = [
            'start' => $startTime,
            'end' => $endTime,
            'class_id' => $classId,
        ];
    }

    /**
     * Check if two time ranges overlap
     */
    private function timeOverlaps(string $startA, string $endA, string $startB, string $endB): bool
    {
        // Periods overlap if one starts before the other ends
        // Consecutive periods (where endA == startB) are NOT overlapping
        if ($endA <= $startB || $endB <= $startA) {
            return false; // No overlap
        }
        return true; // Overlap detected
    }
}
