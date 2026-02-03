<?php

namespace Database\Seeders;

use Database\Seeders\Demo\DemoAcademicSeeder;
use Database\Seeders\Demo\DemoAttendanceSeeder;
use Database\Seeders\Demo\DemoBaseSeeder;
use Database\Seeders\Demo\DemoClassRemarkSeeder;
use Database\Seeders\Demo\DemoCurriculumSeeder;
use Database\Seeders\Demo\DemoDepartmentSeeder;
use Database\Seeders\Demo\DemoEventSeeder;
use Database\Seeders\Demo\DemoExamSeeder;
use Database\Seeders\Demo\DemoFinanceSeeder;
use Database\Seeders\Demo\DemoHomeworkSeeder;
use Database\Seeders\Demo\DemoStudentSeeder;
use Database\Seeders\Demo\DemoTimetableSeeder;
use Database\Seeders\Demo\DemoUserSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoReadySeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        // Initialize shared state
        DemoBaseSeeder::init();

        $this->command->info("School Open Date: " . DemoBaseSeeder::$schoolOpenDate->format('Y-m-d'));
        $this->command->info("Today: " . DemoBaseSeeder::$today->format('Y-m-d'));
        $this->command->info("Working Days: " . count(DemoBaseSeeder::$workingDays));
        $this->command->newLine();

        // 1. Create Departments
        $departmentSeeder = new DemoDepartmentSeeder();
        $departmentSeeder->setCommand($this->command);
        $departments = $departmentSeeder->run();

        // 2. Create Users (Admin, Key Contacts, Staff, Teachers)
        $userSeeder = new DemoUserSeeder();
        $userSeeder->setCommand($this->command);
        $users = $userSeeder->run($departments);

        // 3. Create Academic Structure (Batch, Grades, Rooms, Subjects, Classes)
        $academicSeeder = new DemoAcademicSeeder();
        $academicSeeder->setCommand($this->command);
        $academic = $academicSeeder->run($users['teacherProfiles']);

        // 4. Create Students and Guardians
        $studentSeeder = new DemoStudentSeeder();
        $studentSeeder->setCommand($this->command);
        $studentProfiles = $studentSeeder->run($academic['classes']);

        // 5. Create Curriculum Chapters and Topics
        $curriculumSeeder = new DemoCurriculumSeeder();
        $curriculumSeeder->setCommand($this->command);
        $curriculumSeeder->run($academic['subjects']);

        // 6. Create Timetables (returns periods for homework/class records)
        $timetableSeeder = new DemoTimetableSeeder();
        $timetableSeeder->setCommand($this->command);
        $periods = $timetableSeeder->run($academic['batch'], $academic['classes'], $academic['subjects']);

        // 7. Create Events and Announcements
        $eventSeeder = new DemoEventSeeder();
        $eventSeeder->setCommand($this->command);
        $eventSeeder->run($users['adminUser']);

        // 8. Create Exams and Leave Requests
        $examSeeder = new DemoExamSeeder();
        $examSeeder->setCommand($this->command);
        $examSeeder->run(
            $academic['batch'],
            $academic['grades'],
            $academic['subjects'],
            $academic['rooms'],
            $users['staffProfiles'],
            $users['teacherProfiles']
        );

        // 9. Create Attendance Records
        $attendanceSeeder = new DemoAttendanceSeeder();
        $attendanceSeeder->setCommand($this->command);
        $attendanceSeeder->run(
            $studentProfiles,
            $users['teacherProfiles'],
            $users['staffProfiles'],
            $academic['classes'],
            $users['adminUser']
        );

        // 10. Create Finance Records (Student Fees, Payroll, Settings)
        $financeSeeder = new DemoFinanceSeeder();
        $financeSeeder->setCommand($this->command);
        $financeSeeder->run($studentProfiles, $users['teacherProfiles'], $users['staffProfiles']);

        // 11. Create Homework Assignments and Submissions
        $homeworkSeeder = new DemoHomeworkSeeder();
        $homeworkSeeder->setCommand($this->command);
        $homeworkSeeder->run($periods, $studentProfiles);

        // 12. Create Class Remarks (Class Records)
        $classRemarkSeeder = new DemoClassRemarkSeeder();
        $classRemarkSeeder->setCommand($this->command);
        $classRemarkSeeder->run($periods);

        $this->command->newLine();
        $this->command->info('✅ Demo data seeded successfully!');
    }
}
