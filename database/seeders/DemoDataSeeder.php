<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Department;
use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\TeacherAttendance;
use App\Models\StudentAttendance;
use App\Models\Setting;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Timetable;
use App\Models\Period;
use App\Models\Homework;
use App\Models\CurriculumChapter;
use App\Models\CurriculumTopic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    private $startDate;
    private $endDate;
    private $batch;
    private $grades = [];
    private $classes = [];
    private $teachers = [];
    private $staff = [];
    private $students = [];
    private $subjects = [];
    private $departments = [];

    public function run(): void
    {
        $this->command->info('Starting Demo Data Seeder...');
        
        // Simulate 3 months of school operation (Oct 1 - Dec 21, 2025)
        $this->startDate = Carbon::create(2025, 10, 1);
        $this->endDate = Carbon::create(2025, 12, 21);

        // Update settings to mark setup as complete
        $this->updateSettings();
        
        // Create academic structure
        $this->createBatch();
        $this->createDepartments();
        $this->createGrades();
        $this->createSubjects();
        $this->createClasses();
        
        // Create people
        $this->createTeachers(20);
        $this->createStaff(20);
        $this->createStudents(200);
        
        // Assign teachers to subjects and classes
        $this->assignTeachersToSubjects();
        $this->assignStudentsToClasses();
        
        // Create operational data
        $this->createTimetables();
        $this->createAttendanceRecords();
        $this->createAnnouncements();
        $this->createEvents();
        $this->createHomework();
        $this->createCurriculum();

        $this->command->info('Demo Data Seeder completed!');
        $this->command->info("Created: {$this->batch->name} batch, 6 grades, " . count($this->classes) . " classes");
        $this->command->info("Created: 20 teachers, 20 staff, 200 students");
        $this->command->info("Simulated 3 months of attendance data");
    }

    private function updateSettings(): void
    {
        Setting::query()->update([
            'school_name' => 'Nova International School',
            'school_email' => 'info@novainternational.edu.mm',
            'school_phone' => '+95 9 123 456 789',
            'school_address' => 'No. 123, University Avenue, Kamayut Township, Yangon',
            'principal_name' => 'Dr. Aung Kyaw Moe',
            'setup_completed_school_info' => true,
            'setup_completed_academic' => true,
            'setup_completed_event_and_announcements' => true,
            'setup_completed_time_table_and_attendance' => true,
            'setup_completed_finance' => true,
            'number_of_periods_per_day' => 8,
            'minute_per_period' => 45,
            'break_duration' => 15,
            'school_start_time' => '08:00',
            'school_end_time' => '15:30',
            'week_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ]);
        $this->command->info('✓ Settings updated');
    }

    private function createBatch(): void
    {
        $this->batch = Batch::firstOrCreate(
            ['name' => '2025-2026'],
            [
                'start_date' => Carbon::create(2025, 6, 1),
                'end_date' => Carbon::create(2026, 3, 31),
                'status' => true,
            ]
        );
        $this->command->info('✓ Batch created: ' . $this->batch->name);
    }

    private function createDepartments(): void
    {
        $depts = [
            ['code' => 'ADMIN', 'name' => 'Administration'],
            ['code' => 'ACAD', 'name' => 'Academic Affairs'],
            ['code' => 'LANG', 'name' => 'Languages'],
            ['code' => 'MATH', 'name' => 'Mathematics'],
            ['code' => 'SCI', 'name' => 'Science'],
            ['code' => 'ARTS', 'name' => 'Arts & Music'],
            ['code' => 'PE', 'name' => 'Physical Education'],
            ['code' => 'IT', 'name' => 'Information Technology'],
        ];

        foreach ($depts as $dept) {
            $this->departments[$dept['code']] = Department::firstOrCreate(
                ['code' => $dept['code']],
                ['name' => $dept['name'], 'is_active' => true]
            );
        }
        $this->command->info('✓ Departments created: ' . count($this->departments));
    }

    private function createGrades(): void
    {
        // Get or create grade categories
        $primaryCategory = \App\Models\GradeCategory::firstOrCreate(['name' => 'Primary']);
        $middleCategory = \App\Models\GradeCategory::firstOrCreate(['name' => 'Middle School']);
        
        $gradeCategories = [
            1 => $primaryCategory->id,
            2 => $primaryCategory->id,
            3 => $primaryCategory->id,
            4 => $middleCategory->id,
            5 => $middleCategory->id,
            6 => $middleCategory->id,
        ];

        for ($level = 1; $level <= 6; $level++) {
            $this->grades[$level] = Grade::firstOrCreate(
                ['level' => $level, 'batch_id' => $this->batch->id],
                [
                    'price_per_month' => 150000 + ($level * 10000),
                    'grade_category_id' => $gradeCategories[$level],
                ]
            );
        }
        $this->command->info('✓ Grades created: ' . count($this->grades));
    }

    private function createSubjects(): void
    {
        // Get or create subject type
        $coreType = \App\Models\SubjectType::firstOrCreate(['name' => 'Core']);
        $electiveType = \App\Models\SubjectType::firstOrCreate(['name' => 'Elective']);

        $subjectList = [
            ['name' => 'Myanmar', 'code' => 'MYA', 'type' => 'core'],
            ['name' => 'English', 'code' => 'ENG', 'type' => 'core'],
            ['name' => 'Mathematics', 'code' => 'MATH', 'type' => 'core'],
            ['name' => 'Science', 'code' => 'SCI', 'type' => 'core'],
            ['name' => 'Social Studies', 'code' => 'SOC', 'type' => 'core'],
            ['name' => 'Art', 'code' => 'ART', 'type' => 'elective'],
            ['name' => 'Music', 'code' => 'MUS', 'type' => 'elective'],
            ['name' => 'Physical Education', 'code' => 'PE', 'type' => 'elective'],
            ['name' => 'Computer', 'code' => 'ICT', 'type' => 'elective'],
            ['name' => 'Life Skills', 'code' => 'LIFE', 'type' => 'elective'],
        ];

        foreach ($subjectList as $subj) {
            $subject = Subject::firstOrCreate(
                ['code' => $subj['code']],
                [
                    'name' => $subj['name'],
                    'subject_type_id' => $subj['type'] === 'core' ? $coreType->id : $electiveType->id,
                ]
            );
            $this->subjects[$subj['code']] = $subject;

            // Attach to all grades
            foreach ($this->grades as $grade) {
                $grade->subjects()->syncWithoutDetaching([$subject->id]);
            }
        }
        $this->command->info('✓ Subjects created: ' . count($this->subjects));
    }

    private function createClasses(): void
    {
        $sections = ['A', 'B', 'C'];
        
        foreach ($this->grades as $level => $grade) {
            $numSections = $level <= 3 ? 3 : 2; // Lower grades have more sections
            
            for ($i = 0; $i < $numSections; $i++) {
                // Use proper naming for Kindergarten (Grade 0) - though this seeder only creates grades 1-6
                if ($level === 0) {
                    $className = "Kindergarten {$sections[$i]}";
                } else {
                    $className = "Grade {$level} {$sections[$i]}";
                }
                $class = SchoolClass::firstOrCreate(
                    ['name' => $className, 'grade_id' => $grade->id, 'batch_id' => $this->batch->id],
                    []
                );
                $this->classes[] = $class;
            }
        }
        $this->command->info('✓ Classes created: ' . count($this->classes));
    }

    private function createTeachers(int $count): void
    {
        $myanmarNames = [
            'male' => ['Aung', 'Kyaw', 'Zaw', 'Min', 'Htet', 'Naing', 'Tun', 'Win', 'Myo', 'Htun'],
            'female' => ['Aye', 'Khin', 'Su', 'Thida', 'Nwe', 'Mya', 'Thin', 'Wai', 'Phyu', 'Ei']
        ];
        $lastNames = ['Aung', 'Win', 'Htun', 'Zaw', 'Kyaw', 'Naing', 'Oo', 'Lwin', 'Myint', 'Hlaing'];
        $positions = ['Senior Teacher', 'Teacher', 'Junior Teacher', 'Assistant Teacher'];
        $deptCodes = array_keys($this->departments);

        for ($i = 1; $i <= $count; $i++) {
            $gender = $i % 3 === 0 ? 'male' : 'female';
            $firstName = $myanmarNames[$gender][array_rand($myanmarNames[$gender])];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $gender === 'female' ? "Daw {$firstName} {$lastName}" : "U {$firstName} {$lastName}";
            $email = 'teacher' . $i . '@nova.edu.mm';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$user->hasRole('teacher')) {
                $user->assignRole('teacher');
            }

            $deptCode = $deptCodes[($i - 1) % count($deptCodes)];
            $hireDate = $this->startDate->copy()->subMonths(rand(1, 36));

            $teacher = TeacherProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'T' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'position' => $positions[array_rand($positions)],
                    'department_id' => $this->departments[$deptCode]->id,
                    'hire_date' => $hireDate,
                    'basic_salary' => rand(400000, 800000),
                    'gender' => $gender,
                    'dob' => Carbon::now()->subYears(rand(25, 50))->subDays(rand(0, 365)),
                    'phone_no' => '09' . rand(100000000, 999999999),
                    'address' => 'Yangon, Myanmar',
                    'status' => 'active',
                ]
            );
            $this->teachers[] = $teacher;
        }
        $this->command->info('✓ Teachers created: ' . count($this->teachers));
    }

    private function createStaff(int $count): void
    {
        $positions = ['Office Manager', 'Accountant', 'Receptionist', 'IT Support', 'Librarian', 'Security', 'Cleaner', 'Driver', 'Nurse', 'Counselor'];
        $myanmarNames = ['Aung', 'Kyaw', 'Zaw', 'Min', 'Aye', 'Khin', 'Su', 'Thida', 'Nwe', 'Mya'];
        $lastNames = ['Aung', 'Win', 'Htun', 'Zaw', 'Kyaw', 'Naing', 'Oo', 'Lwin', 'Myint', 'Hlaing'];

        for ($i = 1; $i <= $count; $i++) {
            $gender = $i % 2 === 0 ? 'male' : 'female';
            $firstName = $myanmarNames[array_rand($myanmarNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $gender === 'female' ? "Daw {$firstName} {$lastName}" : "U {$firstName} {$lastName}";
            $email = 'staff' . $i . '@nova.edu.mm';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$user->hasRole('staff')) {
                $user->assignRole('staff');
            }

            $hireDate = $this->startDate->copy()->subMonths(rand(1, 24));

            $staffMember = StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'S' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'position' => $positions[($i - 1) % count($positions)],
                    'department_id' => $this->departments['ADMIN']->id,
                    'hire_date' => $hireDate,
                    'basic_salary' => rand(250000, 500000),
                    'gender' => $gender,
                    'dob' => Carbon::now()->subYears(rand(22, 55))->subDays(rand(0, 365)),
                    'phone_no' => '09' . rand(100000000, 999999999),
                    'address' => 'Yangon, Myanmar',
                    'status' => 'active',
                ]
            );
            $this->staff[] = $staffMember;
        }
        $this->command->info('✓ Staff created: ' . count($this->staff));
    }

    private function createStudents(int $count): void
    {
        $myanmarNames = [
            'male' => ['Aung', 'Kyaw', 'Zaw', 'Min', 'Htet', 'Naing', 'Tun', 'Win', 'Myo', 'Htun', 'Kaung', 'Ye', 'Pyae', 'Khant'],
            'female' => ['Aye', 'Khin', 'Su', 'Thida', 'Nwe', 'Mya', 'Thin', 'Wai', 'Phyu', 'Ei', 'Hnin', 'May', 'Thiri', 'Yadanar']
        ];
        $lastNames = ['Aung', 'Win', 'Htun', 'Zaw', 'Kyaw', 'Naing', 'Oo', 'Lwin', 'Myint', 'Hlaing', 'Soe', 'Tun', 'Min', 'Htet'];

        for ($i = 1; $i <= $count; $i++) {
            $gender = rand(0, 1) === 0 ? 'male' : 'female';
            $firstName = $myanmarNames[$gender][array_rand($myanmarNames[$gender])];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $gender === 'female' ? "Ma {$firstName} {$lastName}" : "Mg {$firstName} {$lastName}";
            $email = 'student' . $i . '@nova.edu.mm';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$user->hasRole('student')) {
                $user->assignRole('student');
            }

            // Distribute students across grades (more in lower grades)
            $gradeLevel = $this->getWeightedGradeLevel();
            $grade = $this->grades[$gradeLevel];
            
            // Get a class for this grade
            $gradeClasses = array_filter($this->classes, fn($c) => $c->grade_id === $grade->id);
            $gradeClasses = array_values($gradeClasses);
            $class = $gradeClasses[array_rand($gradeClasses)];

            $age = 5 + $gradeLevel + rand(0, 1);
            $dob = Carbon::now()->subYears($age)->subDays(rand(0, 365));

            $student = StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'student_identifier' => 'STU' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'student_id' => 'STU' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'grade_id' => $grade->id,
                    'class_id' => $class->id,
                    'date_of_joining' => $this->startDate->copy()->subMonths(rand(0, 12)),
                    'gender' => $gender,
                    'dob' => $dob,
                    'address' => 'Yangon, Myanmar',
                    'father_name' => 'U ' . $lastNames[array_rand($lastNames)] . ' ' . $lastNames[array_rand($lastNames)],
                    'father_phone_no' => '09' . rand(100000000, 999999999),
                    'mother_name' => 'Daw ' . $myanmarNames['female'][array_rand($myanmarNames['female'])] . ' ' . $lastNames[array_rand($lastNames)],
                    'mother_phone_no' => '09' . rand(100000000, 999999999),
                    'status' => 'active',
                ]
            );
            $this->students[] = $student;
        }
        $this->command->info('✓ Students created: ' . count($this->students));
    }

    private function getWeightedGradeLevel(): int
    {
        // More students in lower grades
        $weights = [1 => 25, 2 => 22, 3 => 18, 4 => 15, 5 => 12, 6 => 8];
        $rand = rand(1, 100);
        $cumulative = 0;
        foreach ($weights as $level => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) return $level;
        }
        return 1;
    }

    private function assignTeachersToSubjects(): void
    {
        $subjectCodes = array_keys($this->subjects);
        
        foreach ($this->teachers as $index => $teacher) {
            // Each teacher teaches 2-3 subjects
            $numSubjects = rand(2, 3);
            $teacherSubjects = array_slice($subjectCodes, $index % count($subjectCodes), $numSubjects);
            
            foreach ($teacherSubjects as $code) {
                if (isset($this->subjects[$code])) {
                    $teacher->subjects()->syncWithoutDetaching([$this->subjects[$code]->id]);
                }
            }
        }

        // Assign class teachers
        foreach ($this->classes as $index => $class) {
            if (isset($this->teachers[$index % count($this->teachers)])) {
                $class->update(['teacher_id' => $this->teachers[$index % count($this->teachers)]->id]);
            }
        }
        $this->command->info('✓ Teachers assigned to subjects and classes');
    }

    private function assignStudentsToClasses(): void
    {
        // Students are already assigned via class_id in creation
        $this->command->info('✓ Students assigned to classes');
    }

    private function createTimetables(): void
    {
        $weekDays = ['mon', 'tue', 'wed', 'thu', 'fri'];
        $subjectCodes = array_keys($this->subjects);

        foreach ($this->classes as $class) {
            $timetable = Timetable::create([
                'batch_id' => $this->batch->id,
                'grade_id' => $class->grade_id,
                'class_id' => $class->id,
                'name' => 'Regular Schedule',
                'version_name' => 'Regular Week',
                'is_active' => true,
                'minutes_per_period' => 45,
                'break_duration' => 15,
                'school_start_time' => '08:00',
                'school_end_time' => '15:30',
                'week_days' => $weekDays,
                'version' => 1,
            ]);

            // Create periods for each day
            foreach ($weekDays as $day) {
                for ($period = 1; $period <= 8; $period++) {
                    $startMinutes = 480 + (($period - 1) * 45); // 8:00 AM start
                    if ($period > 4) $startMinutes += 30; // Lunch break after period 4
                    
                    $isBreak = $period === 4; // Period 4 is break
                    $subjectCode = $subjectCodes[($period + array_search($day, $weekDays)) % count($subjectCodes)];

                    Period::create([
                        'timetable_id' => $timetable->id,
                        'day_of_week' => $day,
                        'period_number' => $period,
                        'starts_at' => sprintf('%02d:%02d', floor($startMinutes / 60), $startMinutes % 60),
                        'ends_at' => sprintf('%02d:%02d', floor(($startMinutes + 45) / 60), ($startMinutes + 45) % 60),
                        'is_break' => $isBreak,
                        'subject_id' => $isBreak ? null : ($this->subjects[$subjectCode]->id ?? null),
                    ]);
                }
            }
        }
        $this->command->info('✓ Timetables created for all classes');
    }

    private function createAttendanceRecords(): void
    {
        $this->command->info('Creating attendance records (this may take a moment)...');
        
        $currentDate = $this->startDate->copy();
        $studentStatuses = ['present', 'present', 'present', 'present', 'present', 'present', 'present', 'present', 'absent', 'late'];
        $staffStatuses = ['present', 'present', 'present', 'present', 'present', 'present', 'present', 'present', 'absent', 'leave'];
        $studentRecordCount = 0;
        $teacherRecordCount = 0;
        $staffRecordCount = 0;

        while ($currentDate <= $this->endDate) {
            // Skip weekends
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }

            $dateStr = $currentDate->format('Y-m-d');

            // Student attendance
            foreach ($this->students as $student) {
                if (rand(1, 100) <= 95) {
                    $exists = StudentAttendance::where('student_id', $student->id)
                        ->where('date', $dateStr)
                        ->exists();
                    
                    if (!$exists) {
                        StudentAttendance::create([
                            'student_id' => $student->id,
                            'date' => $dateStr,
                            'status' => $studentStatuses[array_rand($studentStatuses)],
                        ]);
                        $studentRecordCount++;
                    }
                }
            }

            // Teacher attendance (try-catch in case FK still broken)
            foreach ($this->teachers as $teacher) {
                if (rand(1, 100) <= 96) {
                    try {
                        $exists = TeacherAttendance::where('teacher_id', $teacher->id)
                            ->where('date', $dateStr)
                            ->exists();
                        
                        if (!$exists) {
                            TeacherAttendance::create([
                                'teacher_id' => $teacher->id,
                                'date' => $dateStr,
                                'status' => $staffStatuses[array_rand($staffStatuses)],
                                'start_time' => '07:45',
                                'end_time' => '16:00',
                            ]);
                            $teacherRecordCount++;
                        }
                    } catch (\Exception $e) {
                        // Skip if FK constraint fails
                    }
                }
            }

            $currentDate->addDay();
        }
        $this->command->info("✓ Student attendance records: {$studentRecordCount}");
        $this->command->info("✓ Teacher attendance records: {$teacherRecordCount}");
    }

    private function createAnnouncements(): void
    {
        $announcements = [
            ['title' => 'Welcome to New Academic Year 2025-2026', 'content' => 'We are excited to welcome all students and staff to the new academic year. Let\'s make this year a great success!', 'days_ago' => 80],
            ['title' => 'Parent-Teacher Meeting Schedule', 'content' => 'The first parent-teacher meeting will be held on October 15th. Please mark your calendars.', 'days_ago' => 70],
            ['title' => 'Sports Day Announcement', 'content' => 'Annual Sports Day will be held on November 20th. All students are encouraged to participate.', 'days_ago' => 45],
            ['title' => 'Mid-Term Examination Schedule', 'content' => 'Mid-term examinations will begin from November 25th. Detailed schedule will be shared soon.', 'days_ago' => 35],
            ['title' => 'Winter Break Notice', 'content' => 'School will be closed for winter break from December 23rd to January 5th.', 'days_ago' => 10],
            ['title' => 'Library New Books Arrival', 'content' => 'New collection of books has arrived at the library. Students are encouraged to visit and explore.', 'days_ago' => 25],
            ['title' => 'Science Fair Registration Open', 'content' => 'Registration for the annual Science Fair is now open. Submit your projects by December 15th.', 'days_ago' => 20],
            ['title' => 'School Bus Route Changes', 'content' => 'Please note the updated school bus routes effective from November 1st.', 'days_ago' => 55],
        ];

        foreach ($announcements as $ann) {
            Announcement::create([
                'title' => $ann['title'],
                'content' => $ann['content'],
                'publish_date' => Carbon::now()->subDays($ann['days_ago']),
                'is_published' => true,
                'type' => 'general',
                'priority' => 'normal',
                'status' => true,
            ]);
        }
        $this->command->info('✓ Announcements created: ' . count($announcements));
    }

    private function createEvents(): void
    {
        // Create event categories first
        $categories = [
            'academic' => EventCategory::firstOrCreate(['name' => 'Academic'], ['color' => '#3B82F6', 'status' => true]),
            'meeting' => EventCategory::firstOrCreate(['name' => 'Meeting'], ['color' => '#10B981', 'status' => true]),
            'holiday' => EventCategory::firstOrCreate(['name' => 'Holiday'], ['color' => '#F59E0B', 'status' => true]),
            'sports' => EventCategory::firstOrCreate(['name' => 'Sports'], ['color' => '#8B5CF6', 'status' => true]),
            'cultural' => EventCategory::firstOrCreate(['name' => 'Cultural'], ['color' => '#EC4899', 'status' => true]),
            'exam' => EventCategory::firstOrCreate(['name' => 'Examination'], ['color' => '#EF4444', 'status' => true]),
        ];

        $events = [
            ['title' => 'First Day of School', 'start' => '2025-10-01', 'end' => '2025-10-01', 'type' => 'academic'],
            ['title' => 'Parent-Teacher Meeting', 'start' => '2025-10-15', 'end' => '2025-10-15', 'type' => 'meeting'],
            ['title' => 'Thadingyut Holiday', 'start' => '2025-10-20', 'end' => '2025-10-22', 'type' => 'holiday'],
            ['title' => 'Sports Day', 'start' => '2025-11-20', 'end' => '2025-11-20', 'type' => 'sports'],
            ['title' => 'Mid-Term Exams', 'start' => '2025-11-25', 'end' => '2025-11-29', 'type' => 'exam'],
            ['title' => 'Science Fair', 'start' => '2025-12-10', 'end' => '2025-12-10', 'type' => 'cultural'],
            ['title' => 'Christmas Celebration', 'start' => '2025-12-20', 'end' => '2025-12-20', 'type' => 'cultural'],
            ['title' => 'Winter Break Begins', 'start' => '2025-12-23', 'end' => '2026-01-05', 'type' => 'holiday'],
        ];

        foreach ($events as $evt) {
            Event::create([
                'title' => $evt['title'],
                'start_date' => $evt['start'],
                'end_date' => $evt['end'],
                'type' => $evt['type'],
                'event_category_id' => $categories[$evt['type']]->id,
                'description' => $evt['title'] . ' - School Event',
                'status' => true,
            ]);
        }
        $this->command->info('✓ Events created: ' . count($events));
    }

    private function createHomework(): void
    {
        $homeworkTitles = [
            'MYA' => ['Myanmar Grammar Exercise', 'Reading Comprehension', 'Essay Writing', 'Vocabulary Practice'],
            'ENG' => ['English Grammar Worksheet', 'Reading Assignment', 'Writing Practice', 'Spelling Test Prep'],
            'MATH' => ['Math Problem Set', 'Geometry Worksheet', 'Algebra Practice', 'Word Problems'],
            'SCI' => ['Science Lab Report', 'Chapter Review Questions', 'Experiment Observation', 'Research Assignment'],
            'SOC' => ['History Timeline', 'Geography Map Work', 'Current Events Report', 'Social Studies Quiz Prep'],
        ];

        $priorities = ['low', 'medium', 'medium', 'high'];
        $homeworkCount = 0;

        foreach ($this->classes as $class) {
            // Get subjects for this class's grade
            $gradeSubjects = $class->grade->subjects ?? collect();
            
            foreach ($gradeSubjects->take(5) as $subject) {
                $subjectCode = $subject->code ?? 'MATH';
                $titles = $homeworkTitles[$subjectCode] ?? $homeworkTitles['MATH'];
                
                // Create 2-3 homework per subject per class
                $numHomework = rand(2, 3);
                
                for ($i = 0; $i < $numHomework; $i++) {
                    $assignedDate = Carbon::now()->subDays(rand(1, 30));
                    $dueDate = $assignedDate->copy()->addDays(rand(3, 7));
                    
                    // Get a teacher for this subject
                    $teacher = $this->teachers[array_rand($this->teachers)];
                    
                    Homework::create([
                        'title' => $titles[array_rand($titles)] . ' - Week ' . rand(1, 12),
                        'description' => 'Complete all exercises and submit before the due date.',
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'assigned_date' => $assignedDate,
                        'due_date' => $dueDate,
                        'priority' => $priorities[array_rand($priorities)],
                        'status' => $dueDate->isPast() ? 'completed' : 'active',
                    ]);
                    $homeworkCount++;
                }
            }
        }
        
        $this->command->info("✓ Homework created: {$homeworkCount}");
    }

    private function createCurriculum(): void
    {
        $curriculumData = [
            'MATH' => [
                ['title' => 'Numbers and Operations', 'topics' => ['Whole Numbers', 'Fractions', 'Decimals', 'Percentages']],
                ['title' => 'Algebra Basics', 'topics' => ['Variables and Expressions', 'Equations', 'Inequalities', 'Functions']],
                ['title' => 'Geometry', 'topics' => ['Points, Lines, Angles', 'Triangles', 'Quadrilaterals', 'Circles', 'Area and Perimeter']],
                ['title' => 'Measurement', 'topics' => ['Length and Distance', 'Weight and Mass', 'Volume and Capacity', 'Time']],
                ['title' => 'Data and Statistics', 'topics' => ['Data Collection', 'Graphs and Charts', 'Mean, Median, Mode', 'Probability']],
            ],
            'ENG' => [
                ['title' => 'Grammar Fundamentals', 'topics' => ['Parts of Speech', 'Sentence Structure', 'Tenses', 'Subject-Verb Agreement']],
                ['title' => 'Reading Comprehension', 'topics' => ['Main Idea', 'Supporting Details', 'Inference', 'Vocabulary in Context']],
                ['title' => 'Writing Skills', 'topics' => ['Paragraph Writing', 'Essay Structure', 'Descriptive Writing', 'Narrative Writing']],
                ['title' => 'Literature', 'topics' => ['Poetry', 'Short Stories', 'Drama', 'Novel Study']],
            ],
            'SCI' => [
                ['title' => 'Living Things', 'topics' => ['Cells', 'Plants', 'Animals', 'Human Body', 'Ecosystems']],
                ['title' => 'Matter and Energy', 'topics' => ['States of Matter', 'Physical Changes', 'Chemical Changes', 'Energy Forms']],
                ['title' => 'Earth Science', 'topics' => ['Rocks and Minerals', 'Weather', 'Water Cycle', 'Solar System']],
                ['title' => 'Forces and Motion', 'topics' => ['Gravity', 'Friction', 'Simple Machines', 'Speed and Velocity']],
            ],
            'MYA' => [
                ['title' => 'စာဖတ်ခြင်း', 'topics' => ['အသံထွက်ဖတ်ခြင်း', 'နားလည်မှုစစ်ဆေးခြင်း', 'ဝေါဟာရ']],
                ['title' => 'စာရေးခြင်း', 'topics' => ['စာကြောင်းရေးခြင်း', 'စာပိုဒ်ရေးခြင်း', 'စာစီစာကုံးရေးခြင်း']],
                ['title' => 'သဒ္ဒါ', 'topics' => ['ဝါကျတည်ဆောက်ပုံ', 'ကြိယာ', 'နာမ်', 'နာမဝိသေသန']],
            ],
            'SOC' => [
                ['title' => 'History', 'topics' => ['Ancient Civilizations', 'Myanmar History', 'World History', 'Modern Era']],
                ['title' => 'Geography', 'topics' => ['Maps and Globes', 'Continents', 'Myanmar Geography', 'Climate']],
                ['title' => 'Civics', 'topics' => ['Government', 'Rights and Responsibilities', 'Community', 'Democracy']],
            ],
        ];

        $chapterCount = 0;
        $topicCount = 0;

        foreach ($this->subjects as $code => $subject) {
            if (!isset($curriculumData[$code])) continue;

            $chapters = $curriculumData[$code];
            
            foreach ($chapters as $order => $chapterData) {
                $chapter = CurriculumChapter::firstOrCreate(
                    ['subject_id' => $subject->id, 'title' => $chapterData['title']],
                    ['order' => $order + 1]
                );
                $chapterCount++;

                foreach ($chapterData['topics'] as $topicOrder => $topicTitle) {
                    CurriculumTopic::firstOrCreate(
                        ['chapter_id' => $chapter->id, 'title' => $topicTitle],
                        ['order' => $topicOrder + 1]
                    );
                    $topicCount++;
                }
            }
        }

        $this->command->info("✓ Curriculum created: {$chapterCount} chapters, {$topicCount} topics");
    }
}
