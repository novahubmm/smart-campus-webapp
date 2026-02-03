<?php

namespace Database\Seeders\Demo;

use App\Models\Batch;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamType;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;

class DemoExamSeeder extends DemoBaseSeeder
{
    public function run(Batch $batch, array $grades, array $subjects, array $rooms, array $staffProfiles, array $teacherProfiles): void
    {
        $this->createExams($batch, $grades, $subjects, $rooms);
        $this->createLeaveRequests($staffProfiles, $teacherProfiles);
    }

    private function createExams(Batch $batch, array $grades, array $subjects, array $rooms): void
    {
        $this->command->info('Creating Exams (per class)...');

        $examType = ExamType::where('name', 'LIKE', '%Monthly%')->first() ?? ExamType::first();
        $yesterday = $this->getToday()->copy()->subDay();
        $examCount = 0;

        foreach ($grades as $level => $grade) {
            // Get all classes for this grade
            $classes = SchoolClass::where('grade_id', $grade->id)
                ->where('batch_id', $batch->id)
                ->get();

            foreach ($classes as $class) {
                $examId = 'EXD-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);

                $exam = Exam::firstOrCreate(
                    ['exam_id' => $examId],
                    [
                        'name' => "Monthly Test - {$class->name}",
                        'exam_type_id' => $examType->id ?? null,
                        'batch_id' => $batch->id,
                        'grade_id' => $grade->id,
                        'class_id' => $class->id,
                        'start_date' => $yesterday,
                        'end_date' => $yesterday->copy()->addDays(5),
                        'status' => 'upcoming',
                    ]
                );

                // Get subjects for this class (via grade)
                $gradeSubjects = $subjects[$level] ?? [];
                $dayOffset = 0;

                foreach ($gradeSubjects as $subjectData) {
                    $examDate = $yesterday->copy()->addDays($dayOffset);

                    ExamSchedule::firstOrCreate(
                        ['exam_id' => $exam->id, 'subject_id' => $subjectData['subject']->id],
                        [
                            'exam_date' => $examDate,
                            'start_time' => '09:00',
                            'end_time' => '11:00',
                            'room_id' => $class->room_id ?? $rooms[array_rand($rooms)]->id,
                            'total_marks' => 100,
                            'passing_marks' => 40,
                        ]
                    );

                    $dayOffset++;
                }

                $examCount++;
            }
        }

        $this->command->info("Created {$examCount} exams (one per class)");
    }

    private function createLeaveRequests(array $staffProfiles, array $teacherProfiles): void
    {
        $this->command->info('Creating Leave Requests (13)...');

        $leaveTypes = ['sick', 'casual', 'emergency', 'other'];
        $statuses = ['pending', 'approved', 'rejected'];
        $reasons = ['Personal matters', 'Medical appointment', 'Family emergency', 'Not feeling well', 'Important work'];

        // 3 staff leave requests
        $randomStaff = array_rand($staffProfiles, min(3, count($staffProfiles)));
        if (!is_array($randomStaff)) $randomStaff = [$randomStaff];

        foreach ($randomStaff as $index) {
            $staff = $staffProfiles[$index];
            $startDate = $this->getSchoolOpenDate()->copy()->addDays(rand(0, 10));
            $totalDays = rand(1, 3);

            LeaveRequest::create([
                'user_id' => $staff->user_id,
                'user_type' => 'staff',
                'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addDays($totalDays - 1),
                'total_days' => $totalDays,
                'reason' => $reasons[array_rand($reasons)],
                'status' => $statuses[array_rand($statuses)],
            ]);
        }

        // 10 teacher leave requests
        $randomTeachers = array_rand($teacherProfiles, min(10, count($teacherProfiles)));
        if (!is_array($randomTeachers)) $randomTeachers = [$randomTeachers];

        foreach ($randomTeachers as $index) {
            $teacher = $teacherProfiles[$index];
            $startDate = $this->getSchoolOpenDate()->copy()->addDays(rand(0, 10));
            $totalDays = rand(1, 3);

            LeaveRequest::create([
                'user_id' => $teacher->user_id,
                'user_type' => 'teacher',
                'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addDays($totalDays - 1),
                'total_days' => $totalDays,
                'reason' => $reasons[array_rand($reasons)],
                'status' => $statuses[array_rand($statuses)],
            ]);
        }
    }
}
