<?php

namespace Database\Seeders\Demo;

use App\Models\Period;
use App\Models\StaffAttendance;
use App\Models\TeacherAttendance;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoAttendanceSeeder extends DemoBaseSeeder
{
    public function run(array $studentProfiles, array $teacherProfiles, array $staffProfiles, array $classes, User $adminUser): void
    {
        $this->createStudentAttendance($studentProfiles, $classes, $adminUser);
        $this->createTeacherAttendance($teacherProfiles, $adminUser);
        $this->createStaffAttendance($staffProfiles, $adminUser);
    }

    private function createStudentAttendance(array $studentProfiles, array $classes, User $adminUser): void
    {
        $this->command->info('Creating Student Attendance (period by period)...');

        // Status enum is: present, absent, leave (no 'late' in DB constraint)
        $statuses = ['present', 'absent', 'leave'];
        $weights = [87, 5, 8];
        $workingDays = $this->getWorkingDaysArray();

        // Get all periods for each class grouped by day
        $classPeriodsByDay = [];
        foreach ($classes as $class) {
            $timetable = Timetable::where('class_id', $class->id)
                ->where('is_active', true)
                ->first();
            
            if ($timetable) {
                $periods = Period::where('timetable_id', $timetable->id)
                    ->where('is_break', false)
                    ->orderBy('period_number')
                    ->get();
                
                foreach ($periods as $period) {
                    $dayKey = strtolower($period->day_of_week);
                    $classPeriodsByDay[$class->id][$dayKey][] = $period;
                }
            }
        }

        $attendanceData = [];
        $count = 0;
        $batchSize = 100;

        foreach ($workingDays as $date) {
            $dayOfWeek = strtolower($date->englishDayOfWeek);
            
            foreach ($studentProfiles as $student) {
                // Get periods for this class on this day
                $periods = $classPeriodsByDay[$student->class_id][$dayOfWeek] ?? [];
                
                if (empty($periods)) {
                    continue;
                }

                // Generate a base status for the day (student tends to have consistent attendance)
                $dayBaseStatus = $this->getWeightedStatus($statuses, $weights);
                
                foreach ($periods as $period) {
                    // 90% chance to keep the same status as base, 10% chance to vary
                    $status = (rand(1, 100) <= 90) ? $dayBaseStatus : $this->getWeightedStatus($statuses, $weights);
                    
                    // Format collect time based on period start time
                    $collectTime = $period->starts_at;
                    if ($collectTime instanceof \DateTimeInterface) {
                        $collectTime = $collectTime->format('H:i');
                    } elseif (is_string($collectTime)) {
                        $collectTime = substr($collectTime, 11, 5) ?: substr($collectTime, 0, 5);
                    }

                    $attendanceData[] = [
                        'id' => (string) Str::uuid(),
                        'student_id' => $student->id,
                        'period_id' => $period->id,
                        'period_number' => $period->period_number,
                        'date' => $date->format('Y-m-d'),
                        'status' => $status,
                        'marked_by' => $adminUser->id,
                        'collect_time' => $collectTime,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $count++;

                    if ($count % $batchSize === 0) {
                        DB::table('student_attendance')->insert($attendanceData);
                        $attendanceData = [];
                        $this->command->info("  Inserted {$count} student attendance records...");
                    }
                }
            }
        }

        if (!empty($attendanceData)) {
            DB::table('student_attendance')->insert($attendanceData);
        }

        $this->command->info("  Created {$count} student attendance records total.");
    }

    private function createTeacherAttendance(array $teacherProfiles, User $adminUser): void
    {
        $this->command->info('Creating Teacher Attendance...');

        // Status enum is: present, absent, leave
        $statuses = ['present', 'absent', 'leave'];
        $weights = [90, 3, 7];
        $workingDays = $this->getWorkingDaysArray();

        foreach ($workingDays as $date) {
            foreach ($teacherProfiles as $teacher) {
                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'date' => $date,
                    'status' => $this->getWeightedStatus($statuses, $weights),
                    'marked_by' => $adminUser->id,
                    'start_time' => '07:45',
                    'end_time' => '15:00',
                ]);
            }
        }
    }

    private function createStaffAttendance(array $staffProfiles, User $adminUser): void
    {
        $this->command->info('Creating Staff Attendance...');

        // Status enum is: present, absent, late, half-day, on-leave
        $statuses = ['present', 'absent', 'late', 'half-day', 'on-leave'];
        $weights = [85, 3, 5, 4, 3];
        $workingDays = $this->getWorkingDaysArray();

        foreach ($workingDays as $date) {
            foreach ($staffProfiles as $staff) {
                StaffAttendance::create([
                    'staff_id' => $staff->id,
                    'date' => $date,
                    'status' => $this->getWeightedStatus($statuses, $weights),
                    'marked_by' => $adminUser->id,
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                ]);
            }
        }
    }

    private function getWeightedStatus(array $statuses, array $weights): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($statuses as $index => $status) {
            $cumulative += $weights[$index];
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return $statuses[0];
    }
}
