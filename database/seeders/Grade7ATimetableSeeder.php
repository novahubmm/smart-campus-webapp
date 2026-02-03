<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Grade;
use App\Models\Period;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Grade7ATimetableSeeder extends Seeder
{
    private $batch;
    private $grade;
    private $class;
    private $subjects = [];
    private $teachers = [];
    private $room;

    public function run(): void
    {
        $this->command->info('Creating Grade 7A Timetable...');

        $this->setupBatchAndGrade();
        $this->setupSubjects();
        $this->setupTeachers();
        $this->setupRoom();
        $this->createTimetable();

        $this->command->info('✓ Grade 7A Timetable created successfully!');
    }

    private function setupBatchAndGrade(): void
    {
        // Get or create batch
        $this->batch = Batch::where('status', true)->first();
        if (!$this->batch) {
            $this->batch = Batch::create([
                'id' => (string) Str::uuid(),
                'name' => '2025-2026',
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
                'status' => true,
            ]);
        }

        // Get or create grade category
        $gradeCategory = \App\Models\GradeCategory::first();
        if (!$gradeCategory) {
            $gradeCategory = \App\Models\GradeCategory::create([
                'id' => (string) Str::uuid(),
                'name' => 'Middle School',
            ]);
        }

        // Get or create Grade 7
        $this->grade = Grade::where('level', '7')->where('batch_id', $this->batch->id)->first();
        if (!$this->grade) {
            $this->grade = Grade::create([
                'id' => (string) Str::uuid(),
                'level' => '7',
                'batch_id' => $this->batch->id,
                'grade_category_id' => $gradeCategory->id,
                'price_per_month' => 50000,
            ]);
        }

        // Get or create Class 7A
        $this->class = SchoolClass::where('grade_id', $this->grade->id)
            ->where('name', 'A')
            ->first();
        
        if (!$this->class) {
            $this->class = SchoolClass::create([
                'id' => (string) Str::uuid(),
                'grade_id' => $this->grade->id,
                'batch_id' => $this->batch->id,
                'name' => 'A',
            ]);
        }

        $this->command->info("  - Batch: {$this->batch->name}");
        $this->command->info("  - Grade: {$this->grade->level}");
        $this->command->info("  - Class: Grade {$this->grade->level}{$this->class->name}");
    }

    private function setupSubjects(): void
    {
        $subjectNames = ['Mathematics', 'English', 'Science', 'Myanmar', 'History', 'Geography'];

        foreach ($subjectNames as $name) {
            $subject = Subject::where('name', $name)->first();
            if ($subject) {
                $this->subjects[$name] = $subject;
                // Attach subject to grade if not already attached
                $this->grade->subjects()->syncWithoutDetaching([$subject->id]);
            }
        }

        $this->command->info("  - Subjects: " . count($this->subjects) . " found");
    }

    private function setupTeachers(): void
    {
        // Get teacher U Tint Htoo Aung
        $tintHtooAung = User::where('email', 'tinthtooaung@smartcampusedu.com')->first();
        if ($tintHtooAung && $tintHtooAung->teacherProfile) {
            $this->teachers['tinthtooaung'] = $tintHtooAung->teacherProfile;
            
            // Update current_classes to include Grade 7A
            $currentClasses = $tintHtooAung->teacherProfile->current_classes ?? [];
            if (!in_array('Grade 7A', $currentClasses)) {
                $currentClasses[] = 'Grade 7A';
                $tintHtooAung->teacherProfile->update(['current_classes' => $currentClasses]);
            }
        }

        // Get teacher U Kyaw Min
        $kyawMin = User::where('email', 'teacher@smartcampusedu.com')->first();
        if ($kyawMin && $kyawMin->teacherProfile) {
            $this->teachers['kyawmin'] = $kyawMin->teacherProfile;
        }

        // Get any other available teachers
        $otherTeachers = TeacherProfile::whereNotIn('id', collect($this->teachers)->pluck('id')->toArray())
            ->take(4)
            ->get();

        foreach ($otherTeachers as $index => $teacher) {
            $this->teachers["teacher{$index}"] = $teacher;
        }

        $this->command->info("  - Teachers: " . count($this->teachers) . " available");
    }

    private function setupRoom(): void
    {
        $this->room = Room::first();
        if (!$this->room) {
            $this->room = Room::create([
                'id' => (string) Str::uuid(),
                'name' => 'Room 101',
                'capacity' => 40,
                'building' => 'Main Building',
                'floor' => '1',
            ]);
        }

        // Update class room
        $this->class->update(['room_id' => $this->room->id]);

        $this->command->info("  - Room: {$this->room->name}");
    }

    private function createTimetable(): void
    {
        // Delete existing timetable for this class
        Timetable::where('class_id', $this->class->id)->delete();

        // Create new timetable
        $timetable = Timetable::create([
            'id' => (string) Str::uuid(),
            'batch_id' => $this->batch->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->class->id,
            'name' => 'Grade 7A Timetable',
            'version_name' => 'Version 1',
            'status' => 'active',
            'is_active' => true,
            'published_at' => now(),
            'effective_from' => '2025-06-01',
            'effective_to' => '2026-03-31',
            'minutes_per_period' => 45,
            'break_duration' => 15,
            'school_start_time' => '08:00',
            'school_end_time' => '15:00',
            'week_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'version' => 1,
        ]);

        $this->command->info("  - Timetable created: {$timetable->name}");

        // Define period times
        $periodTimes = [
            1 => ['08:00', '08:45'],
            2 => ['09:00', '09:45'],
            3 => ['10:00', '10:45'],
            4 => ['11:00', '11:45'],
            5 => ['13:00', '13:45'],  // After lunch
            6 => ['14:00', '14:45'],
        ];

        // Define schedule - subject assignments per day
        // Using U Tint Htoo Aung for Mathematics and Science
        $schedule = [
            'monday' => [
                1 => ['subject' => 'Mathematics', 'teacher' => 'tinthtooaung'],
                2 => ['subject' => 'English', 'teacher' => 'kyawmin'],
                3 => ['subject' => 'Myanmar', 'teacher' => 'teacher0'],
                4 => ['subject' => 'Science', 'teacher' => 'tinthtooaung'],
                5 => ['subject' => 'History', 'teacher' => 'teacher1'],
                6 => ['subject' => 'Geography', 'teacher' => 'teacher2'],
            ],
            'tuesday' => [
                1 => ['subject' => 'English', 'teacher' => 'kyawmin'],
                2 => ['subject' => 'Mathematics', 'teacher' => 'tinthtooaung'],
                3 => ['subject' => 'Science', 'teacher' => 'tinthtooaung'],
                4 => ['subject' => 'Myanmar', 'teacher' => 'teacher0'],
                5 => ['subject' => 'Geography', 'teacher' => 'teacher2'],
                6 => ['subject' => 'History', 'teacher' => 'teacher1'],
            ],
            'wednesday' => [
                1 => ['subject' => 'Myanmar', 'teacher' => 'teacher0'],
                2 => ['subject' => 'Mathematics', 'teacher' => 'tinthtooaung'],
                3 => ['subject' => 'English', 'teacher' => 'kyawmin'],
                4 => ['subject' => 'History', 'teacher' => 'teacher1'],
                5 => ['subject' => 'Science', 'teacher' => 'tinthtooaung'],
                6 => ['subject' => 'Geography', 'teacher' => 'teacher2'],
            ],
            'thursday' => [
                1 => ['subject' => 'Science', 'teacher' => 'tinthtooaung'],
                2 => ['subject' => 'Myanmar', 'teacher' => 'teacher0'],
                3 => ['subject' => 'Mathematics', 'teacher' => 'tinthtooaung'],
                4 => ['subject' => 'English', 'teacher' => 'kyawmin'],
                5 => ['subject' => 'History', 'teacher' => 'teacher1'],
                6 => ['subject' => 'Geography', 'teacher' => 'teacher2'],
            ],
            'friday' => [
                1 => ['subject' => 'Mathematics', 'teacher' => 'tinthtooaung'],
                2 => ['subject' => 'Science', 'teacher' => 'tinthtooaung'],
                3 => ['subject' => 'Myanmar', 'teacher' => 'teacher0'],
                4 => ['subject' => 'English', 'teacher' => 'kyawmin'],
                5 => ['subject' => 'Geography', 'teacher' => 'teacher2'],
                6 => ['subject' => 'History', 'teacher' => 'teacher1'],
            ],
        ];

        $periodsCreated = 0;

        foreach ($schedule as $day => $periods) {
            foreach ($periods as $periodNum => $assignment) {
                $subject = $this->subjects[$assignment['subject']] ?? null;
                $teacher = $this->teachers[$assignment['teacher']] ?? null;

                // If specific teacher not found, use first available
                if (!$teacher && count($this->teachers) > 0) {
                    $teacher = reset($this->teachers);
                }

                Period::create([
                    'id' => (string) Str::uuid(),
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $day,
                    'period_number' => $periodNum,
                    'starts_at' => $periodTimes[$periodNum][0],
                    'ends_at' => $periodTimes[$periodNum][1],
                    'is_break' => false,
                    'subject_id' => $subject?->id,
                    'teacher_profile_id' => $teacher?->id,
                    'room_id' => $this->room->id,
                    'notes' => $assignment['subject'] . ' - Period ' . $periodNum,
                ]);

                $periodsCreated++;
            }
        }

        $this->command->info("  - Periods created: {$periodsCreated}");
    }
}
