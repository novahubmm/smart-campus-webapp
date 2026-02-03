<?php

namespace App\Console\Commands;

use App\Models\Period;
use App\Models\Timetable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTimetableConflicts extends Command
{
    protected $signature = 'timetable:fix-conflicts';
    protected $description = 'Fix teacher conflicts in timetables (same teacher in multiple classes at same time)';

    private array $teacherSchedule = [];

    public function handle(): int
    {
        $this->info('Fixing timetable teacher conflicts...');

        // Get all active timetables
        $timetables = Timetable::where('is_active', true)
            ->with(['periods.subject.teachers', 'schoolClass.grade'])
            ->get();

        $this->info("Found {$timetables->count()} active timetables");

        // Reset teacher schedule tracker
        $this->teacherSchedule = [];

        // Group timetables by grade to process together
        $timetablesByGrade = $timetables->groupBy(fn($t) => $t->grade_id);

        $fixedCount = 0;
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        foreach ($timetablesByGrade as $gradeId => $gradeTimetables) {
            $this->info("Processing grade: {$gradeId}");

            // For each day and period, check for conflicts
            foreach ($days as $day) {
                for ($periodNumber = 1; $periodNumber <= 7; $periodNumber++) {
                    // Get all periods for this day/period across all classes in this grade
                    $periodsAtTime = Period::whereIn('timetable_id', $gradeTimetables->pluck('id'))
                        ->where('day_of_week', $day)
                        ->where('period_number', $periodNumber)
                        ->where('is_break', false)
                        ->whereNotNull('teacher_profile_id')
                        ->with('subject.teachers')
                        ->get();

                    // Group by teacher to find conflicts
                    $byTeacher = $periodsAtTime->groupBy('teacher_profile_id');

                    foreach ($byTeacher as $teacherId => $teacherPeriods) {
                        if ($teacherPeriods->count() > 1) {
                            // Conflict! Teacher is in multiple classes at same time
                            $this->warn("  Conflict: Teacher {$teacherId} in {$teacherPeriods->count()} classes on {$day} period {$periodNumber}");

                            // Keep the first one, remove teacher from others
                            $first = true;
                            foreach ($teacherPeriods as $period) {
                                if ($first) {
                                    $first = false;
                                    continue;
                                }

                                // Try to find an alternative teacher for this subject
                                $alternativeTeacher = $this->findAlternativeTeacher($period, $day, $periodNumber);

                                if ($alternativeTeacher) {
                                    $period->teacher_profile_id = $alternativeTeacher;
                                    $this->markTeacherBusy($alternativeTeacher, $day, $periodNumber);
                                    $this->info("    Assigned alternative teacher: {$alternativeTeacher}");
                                } else {
                                    $period->teacher_profile_id = null;
                                    $this->info("    Removed teacher (no alternative available)");
                                }

                                $period->save();
                                $fixedCount++;
                            }
                        } else {
                            // No conflict, mark teacher as busy
                            $this->markTeacherBusy($teacherId, $day, $periodNumber);
                        }
                    }
                }
            }
        }

        // Now randomize subject order per day to avoid same pattern
        $this->info("\nRandomizing subject order per day...");
        $randomizedCount = 0;

        foreach ($timetables as $timetable) {
            $periods = $timetable->periods()
                ->where('is_break', false)
                ->orderBy('day_of_week')
                ->orderBy('period_number')
                ->get();

            // Group by day
            $byDay = $periods->groupBy('day_of_week');

            foreach ($byDay as $day => $dayPeriods) {
                // Get unique subjects for this day
                $subjects = $dayPeriods->pluck('subject_id')->unique()->filter()->values()->toArray();
                
                if (count($subjects) < 2) {
                    continue;
                }

                // Shuffle subjects
                shuffle($subjects);

                // Reassign subjects to periods (keeping teachers matched to subjects)
                $subjectIndex = 0;
                foreach ($dayPeriods as $period) {
                    if ($period->is_break) {
                        continue;
                    }

                    $newSubjectId = $subjects[$subjectIndex % count($subjects)];
                    
                    if ($period->subject_id !== $newSubjectId) {
                        // Find a teacher for this subject who is available
                        $subject = \App\Models\Subject::with('teachers')->find($newSubjectId);
                        $availableTeacher = null;

                        if ($subject && $subject->teachers->isNotEmpty()) {
                            foreach ($subject->teachers as $teacher) {
                                if ($this->isTeacherAvailable($teacher->id, $day, $period->period_number)) {
                                    $availableTeacher = $teacher->id;
                                    break;
                                }
                            }
                        }

                        $period->subject_id = $newSubjectId;
                        $period->teacher_profile_id = $availableTeacher;
                        
                        if ($availableTeacher) {
                            $this->markTeacherBusy($availableTeacher, $day, $period->period_number);
                        }
                        
                        $period->save();
                        $randomizedCount++;
                    }

                    $subjectIndex++;
                }
            }
        }

        $this->info("\nFixed {$fixedCount} teacher conflicts");
        $this->info("Randomized {$randomizedCount} period assignments");
        $this->info('Done!');

        return Command::SUCCESS;
    }

    private function findAlternativeTeacher(Period $period, string $day, int $periodNumber): ?string
    {
        if (!$period->subject) {
            return null;
        }

        $teachers = $period->subject->teachers;

        foreach ($teachers as $teacher) {
            if ($this->isTeacherAvailable($teacher->id, $day, $periodNumber)) {
                return $teacher->id;
            }
        }

        return null;
    }

    private function isTeacherAvailable(?string $teacherId, string $day, int $periodNumber): bool
    {
        if (!$teacherId) {
            return true;
        }

        $key = "{$teacherId}_{$day}_{$periodNumber}";
        return !isset($this->teacherSchedule[$key]);
    }

    private function markTeacherBusy(string $teacherId, string $day, int $periodNumber): void
    {
        $key = "{$teacherId}_{$day}_{$periodNumber}";
        $this->teacherSchedule[$key] = true;
    }
}
