<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Period;
use App\Models\StudentAttendance;
use App\Models\ClassRemark;
use App\Models\StudentRemark;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\CurriculumProgress;
use App\Models\TeacherProfile;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\CurriculumChapter;
use App\Models\CurriculumTopic;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClassRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding class records data...');

        // Clear existing data first
        DB::statement('DELETE FROM student_attendance');
        DB::statement('DELETE FROM class_remarks');
        DB::statement('DELETE FROM student_remarks');
        DB::statement('DELETE FROM homework_submissions');
        DB::statement('DELETE FROM homework');
        DB::statement('DELETE FROM curriculum_progress');
        DB::statement('DELETE FROM curriculum_topics');
        DB::statement('DELETE FROM curriculum_chapters');
        DB::statement('DELETE FROM periods');
        DB::statement('DELETE FROM timetables');

        // Date range: 2026-01-05 to 2026-01-11 (Sunday to Saturday)
        $startDate = Carbon::parse('2026-01-12');
        $endDate = Carbon::parse('2026-01-15');
        
        // Get first teacher profile
        $teacher = TeacherProfile::first();
        if (!$teacher) {
            $this->command->error('No teacher profiles found. Please run TeacherProfileSeeder first.');
            return;
        }

        // Get first class and subject
        $class = SchoolClass::first();
        $subject = Subject::first();
        
        if (!$class || !$subject) {
            $this->command->error('No classes or subjects found. Please run basic seeders first.');
            return;
        }

        $this->seedClassRecordsData($teacher, $class, $subject, $startDate, $endDate);
        
        $this->command->info('Class records seeded successfully!');
    }
    private function seedClassRecordsData($teacher, $class, $subject, $startDate, $endDate)
    {
        // Create or get timetable
        $timetable = Timetable::firstOrCreate([
            'class_id' => $class->id,
            'is_active' => true,
        ], [
            'name' => 'Weekly Timetable',
            'batch_id' => $class->batch_id ?? \App\Models\Batch::first()->id,
            'grade_id' => $class->grade_id,
            'effective_from' => $startDate->format('Y-m-d'),
            'effective_to' => $endDate->format('Y-m-d'),
            'minutes_per_period' => 45,
            'break_duration' => 15,
            'school_start_time' => '08:00:00',
            'school_end_time' => '15:00:00',
            'week_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ]);

        // Create periods for each weekday (Monday to Friday)
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $periods = [];

        foreach ($weekdays as $index => $day) {
            for ($periodNum = 1; $periodNum <= 3; $periodNum++) {
                $startTime = sprintf('%02d:00:00', 8 + ($periodNum - 1));
                $endTime = sprintf('%02d:00:00', 9 + ($periodNum - 1));

                $period = Period::firstOrCreate([
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $day,
                    'period_number' => $periodNum,
                ], [
                    'subject_id' => $subject->id,
                    'teacher_profile_id' => $teacher->id,
                    'starts_at' => $startTime,
                    'ends_at' => $endTime,
                    'is_break' => false,
                    'notes' => "Chapter " . ($periodNum + $index) . " - Sample Topic",
                ]);

                // Load the teacher profile relationship
                $period->load('teacher');
                $periods[] = $period;
            }
        }

        // Get students from the class
        $students = StudentProfile::where('class_id', $class->id)->take(20)->get();
        
        if ($students->isEmpty()) {
            // Create some sample students if none exist
            $students = $this->createSampleStudents($class);
        }

        // Create attendance records for each day in the date range
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->isWeekday()) { // Only weekdays
                $dayName = strtolower($currentDate->format('l'));
                $dayPeriods = collect($periods)->where('day_of_week', $dayName);

                foreach ($dayPeriods as $period) {
                    $this->createAttendanceForPeriod($period, $students, $currentDate);
                    $this->createClassRemarks($period, $class, $subject, $currentDate);
                    $this->createStudentRemarks($period, $class, $subject, $students, $currentDate);
                    $this->createHomework($period, $class, $subject, $students, $currentDate);
                }
            }
            $currentDate->addDay();
        }

        // Create curriculum data
        $this->createCurriculumData($subject, $class);
    }

    private function createSampleStudents($class)
    {
        $students = collect();
        
        for ($i = 1; $i <= 20; $i++) {
            $user = \App\Models\User::create([
                'name' => "Student {$i}",
                'email' => "student{$i}@school.edu",
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            $student = StudentProfile::create([
                'user_id' => $user->id,
                'class_id' => $class->id,
                'grade_id' => $class->grade_id,
                'student_identifier' => sprintf('STU%03d', $i),
                'roll_number' => $i,
                'admission_date' => now()->subMonths(6),
                'status' => 'active',
            ]);

            $students->push($student);
        }

        return $students;
    }
    private function createAttendanceForPeriod($period, $students, $date)
    {
        // Get the teacher's user ID
        $teacherUserId = $period->teacher->user_id ?? \App\Models\User::first()->id;
        
        foreach ($students as $student) {
            // Skip if attendance already exists
            $exists = StudentAttendance::where('student_id', $student->id)
                ->where('period_id', $period->id)
                ->where('date', $date->format('Y-m-d'))
                ->exists();
                
            if ($exists) {
                continue;
            }
            
            // 85% chance of being present, 10% absent, 5% leave
            $rand = rand(1, 100);
            if ($rand <= 85) {
                $status = 'present';
            } elseif ($rand <= 95) {
                $status = 'absent';
            } else {
                $status = 'leave';
            }

            StudentAttendance::create([
                'student_id' => $student->id,
                'period_id' => $period->id,
                'date' => $date->format('Y-m-d'),
                'status' => $status,
                'remark' => $status === 'absent' ? 'No reason provided' : null,
                'marked_by' => $teacherUserId,
                'collect_time' => sprintf('%02d:%02d:00', rand(8, 15), rand(0, 59)),
            ]);
        }
    }

    private function createClassRemarks($period, $class, $subject, $date)
    {
        // 60% chance of having a class remark
        if (rand(1, 100) <= 60) {
            $remarks = [
                'Students were very attentive today.',
                'Covered the chapter thoroughly with good participation.',
                'Need to review previous topics in next class.',
                'Excellent class discussion on the topic.',
                'Some students need extra help with this concept.',
                'Class completed all assigned exercises.',
            ];

            ClassRemark::create([
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'period_id' => $period->id,
                'teacher_id' => $period->teacher_profile_id,
                'date' => $date->format('Y-m-d'),
                'remark' => $remarks[array_rand($remarks)],
                'type' => 'note',
            ]);
        }
    }

    private function createStudentRemarks($period, $class, $subject, $students, $date)
    {
        // 30% chance of having student remarks
        if (rand(1, 100) <= 30) {
            $randomStudents = $students->random(rand(1, 3));
            
            $remarks = [
                'Excellent participation in class discussion.',
                'Needs to pay more attention during lessons.',
                'Showed great improvement in understanding.',
                'Should complete homework regularly.',
                'Very helpful to other students.',
                'Needs extra practice with this topic.',
            ];

            foreach ($randomStudents as $student) {
                StudentRemark::create([
                    'student_id' => $student->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'period_id' => $period->id,
                    'teacher_id' => $period->teacher_profile_id,
                    'date' => $date->format('Y-m-d'),
                    'remark' => $remarks[array_rand($remarks)],
                    'type' => 'note',
                ]);
            }
        }
    }
    private function createHomework($period, $class, $subject, $students, $date)
    {
        // 40% chance of assigning homework
        if (rand(1, 100) <= 40) {
            $homeworkTitles = [
                'Chapter Review Questions',
                'Practice Exercises 1-10',
                'Research Assignment',
                'Problem Solving Worksheet',
                'Reading Assignment',
                'Project Work - Phase 1',
            ];

            $homework = Homework::create([
                'title' => $homeworkTitles[array_rand($homeworkTitles)],
                'description' => 'Complete the assigned exercises and submit by due date.',
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $period->teacher_profile_id,
                'period_id' => $period->id,
                'assigned_date' => $date->format('Y-m-d'),
                'due_date' => $date->copy()->addDays(rand(2, 7))->format('Y-m-d'),
                'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                'status' => 'active',
            ]);

            // Create submissions for some students (70% submission rate)
            foreach ($students as $student) {
                if (rand(1, 100) <= 70) {
                    $submissionDate = $date->copy()->addDays(rand(1, 3));
                    $isLate = $submissionDate->gt(Carbon::parse($homework->due_date));

                    HomeworkSubmission::create([
                        'homework_id' => $homework->id,
                        'student_id' => $student->id,
                        'content' => 'Homework completed as assigned.',
                        'submitted_at' => $submissionDate,
                        'status' => $isLate ? 'late' : 'submitted',
                        'grade' => rand(70, 100) / 10, // Grade out of 10
                        'feedback' => 'Good work!',
                    ]);
                }
            }
        }
    }

    private function createCurriculumData($subject, $class)
    {
        // Create sample chapters if they don't exist
        $chapters = [];
        for ($i = 1; $i <= 5; $i++) {
            $chapter = CurriculumChapter::firstOrCreate([
                'subject_id' => $subject->id,
                'title' => "Chapter {$i}: Sample Chapter Title {$i}",
            ], [
                'description' => "Description for chapter {$i}",
                'order' => $i,
                'estimated_hours' => rand(8, 16),
            ]);

            $chapters[] = $chapter;

            // Create topics for each chapter
            for ($j = 1; $j <= rand(3, 6); $j++) {
                $topic = CurriculumTopic::firstOrCreate([
                    'chapter_id' => $chapter->id,
                    'title' => "Topic {$i}.{$j}: Sample Topic",
                ], [
                    'description' => "Description for topic {$i}.{$j}",
                    'order' => $j,
                    'estimated_minutes' => rand(45, 90),
                ]);

                // Mark some topics as completed (60% completion rate)
                if (rand(1, 100) <= 60) {
                    CurriculumProgress::firstOrCreate([
                        'topic_id' => $topic->id,
                        'class_id' => $class->id,
                    ], [
                        'status' => 'completed',
                        'completed_at' => now()->subDays(rand(1, 30)),
                        'teacher_id' => TeacherProfile::first()->id,
                        'notes' => 'Topic completed successfully',
                    ]);
                }
            }
        }
    }
}