<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\StudentAttendance;
use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Teacher1AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating attendance for Teacher1...');

        // Get teacher1
        $teacher = User::where('email', 'teacher1@smartcampusedu.com')->first();
        
        if (!$teacher || !$teacher->teacherProfile) {
            $this->command->error('Teacher1 not found or has no profile');
            return;
        }

        // Get teacher1's first period
        $period = Period::where('teacher_profile_id', $teacher->teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with(['timetable.schoolClass', 'subject'])
            ->first();

        if (!$period) {
            $this->command->error('No periods found for Teacher1');
            return;
        }

        $classId = $period->timetable->class_id;
        $this->command->info("Class ID: {$classId}");
        $this->command->info("Period ID: {$period->id}");
        $this->command->info("Subject: {$period->subject->name}");

        // Get all students in the class
        $students = StudentProfile::where('class_id', $classId)->get();
        $this->command->info("Total students: {$students->count()}");

        // Create attendance for 2026-01-13
        $date = '2026-01-13';
        $collectTime = '09:00:00';

        $attendanceData = [];
        foreach ($students as $index => $student) {
            // Mark most as present, some absent, some leave
            $status = 'present';
            if ($index % 10 == 0) {
                $status = 'absent';
            } elseif ($index % 15 == 0) {
                $status = 'leave';
            }

            $attendanceData[] = [
                'id' => (string) Str::uuid(),
                'student_id' => $student->id,
                'period_id' => $period->id,
                'date' => $date,
                'status' => $status,
                'marked_by' => $teacher->id,
                'collect_time' => $collectTime,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert attendance records
        StudentAttendance::insert($attendanceData);

        $present = collect($attendanceData)->where('status', 'present')->count();
        $absent = collect($attendanceData)->where('status', 'absent')->count();
        $leave = collect($attendanceData)->where('status', 'leave')->count();

        $this->command->info("✓ Created attendance for {$date}");
        $this->command->info("  Present: {$present}, Absent: {$absent}, Leave: {$leave}");
    }
}
