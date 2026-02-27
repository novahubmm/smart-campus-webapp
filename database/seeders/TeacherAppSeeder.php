<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Announcement;
use App\Models\Batch;
use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Grade;
use App\Models\GradeCategory;
use App\Models\GuardianProfile;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Period;
use App\Models\Role;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\TeacherProfile;
use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherAppSeeder extends Seeder
{
    private $teacherUser;
    private $teacherProfile;
    private $batch;
    private $department;
    private $grades = [];
    private $classes = [];
    private $subjects = [];
    private $students = [];
    private $rooms = [];

    public function run(): void
    {
        $this->command->info('Starting Teacher App Seeder...');

        $this->createRoles();
        $this->createDepartment();
        $this->createBatch();
        $this->createRooms();
        $this->createSubjects();
        $this->createGrades();
        $this->createClasses();
        $this->createTeacher();
        $this->createStudents();
        $this->createTimetable();
        $this->createAnnouncements();
        $this->createEvents();
        $this->createLeaveRequests();

        try {
            $this->createHomework();
        } catch (\Exception $e) {
            $this->command->warn('⚠ Homework creation skipped: ' . class_basename($e));
        }

        try {
            $this->createAttendance();
        } catch (\Exception $e) {
            $this->command->warn('⚠ Attendance creation skipped: ' . class_basename($e));
        }

        try {
            $this->createPayroll();
        } catch (\Exception $e) {
            $this->command->warn('⚠ Payroll creation skipped: ' . class_basename($e));
        }

        $this->createDailyReports();

        $this->command->info('Teacher App Seeder completed!');
        $this->command->info('');
        $this->command->info('Test Credentials:');
        $this->command->info('Teacher: teacher@smartcampusedu.com / password');
    }

    private function createRoles(): void
    {
        foreach (RoleEnum::values() as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
        }
        $this->command->info('✓ Roles created');
    }

    private function createDepartment(): void
    {
        $this->department = Department::firstOrCreate(
            ['code' => 'MATH'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Mathematics Department',
                'is_active' => true,
            ]
        );
        $this->command->info('✓ Department created');
    }

    private function createBatch(): void
    {
        $this->batch = Batch::firstOrCreate(
            ['name' => '2025-2026'],
            [
                'id' => (string) Str::uuid(),
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
                'status' => true,
            ]
        );
        $this->command->info('✓ Batch created');
    }

    private function createRooms(): void
    {
        $roomNames = ['Room 101', 'Room 102', 'Room 201', 'Room 202', 'Room 301', 'Room 302'];

        foreach ($roomNames as $name) {
            $floor = substr($name, 5, 1); // Extract floor number from room name (e.g., "Room 101" -> "1")
            $this->rooms[] = Room::firstOrCreate(
                ['name' => $name],
                [
                    'id' => (string) Str::uuid(),
                    'capacity' => 40,
                    'building' => 'Main Building',
                    'floor' => $floor,
                ]
            );
        }
        $this->command->info('✓ Rooms created');
    }

    private function createSubjects(): void
    {
        $subjectType = SubjectType::firstOrCreate(
            ['name' => 'Core'],
            ['id' => (string) Str::uuid()]
        );

        $subjectNames = ['Mathematics', 'English', 'Science', 'Myanmar', 'History', 'Geography'];

        foreach ($subjectNames as $name) {
            $this->subjects[$name] = Subject::firstOrCreate(
                ['name' => $name],
                [
                    'id' => (string) Str::uuid(),
                    'code' => strtoupper(substr($name, 0, 4)),
                    'subject_type_id' => $subjectType->id,
                ]
            );
        }
        $this->command->info('✓ Subjects created');
    }

    private function createGrades(): void
    {
        $gradeCategory = GradeCategory::firstOrCreate(
            ['name' => 'Middle School'],
            ['id' => (string) Str::uuid()]
        );

        $gradeLevels = ['7', '8', '9', '10'];

        foreach ($gradeLevels as $level) {
            $this->grades[$level] = Grade::firstOrCreate(
                ['level' => $level, 'batch_id' => $this->batch->id],
                [
                    'id' => (string) Str::uuid(),
                    'grade_category_id' => $gradeCategory->id,
                    'price_per_month' => 50000,
                ]
            );

            // Attach subjects to grade
            $this->grades[$level]->subjects()->syncWithoutDetaching(
                collect($this->subjects)->pluck('id')->toArray()
            );
        }
        $this->command->info('✓ Grades created');
    }

    private function createClasses(): void
    {
        $sections = ['A', 'B'];
        $roomIndex = 0;

        foreach ($this->grades as $level => $grade) {
            foreach ($sections as $section) {
                $className = $section;
                $this->classes["{$level}{$section}"] = SchoolClass::firstOrCreate(
                    ['grade_id' => $grade->id, 'name' => $className, 'batch_id' => $this->batch->id],
                    [
                        'id' => (string) Str::uuid(),
                        'room_id' => $this->rooms[$roomIndex % count($this->rooms)]->id,
                    ]
                );
                $roomIndex++;
            }
        }
        $this->command->info('✓ Classes created');
    }

    private function createTeacher(): void
    {
        $this->teacherUser = User::firstOrCreate(
            ['email' => 'teacher@smartcampusedu.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'U Kyaw Min',
                'phone' => '09770000003',
                'nrc' => '12/ABC(N)123458',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $teacherRole = Role::where('name', RoleEnum::TEACHER->value)->first();
        if ($teacherRole) {
            $this->teacherUser->roles()->sync([$teacherRole->id]);
        }

        // Check if teacher profile exists
        $existingProfile = TeacherProfile::where('user_id', $this->teacherUser->id)->first();

        if ($existingProfile) {
            // Update existing profile without changing ID
            $existingProfile->update([
                'employee_id' => 'TCH-2024-001',
                'position' => 'Senior Teacher',
                'department_id' => $this->department->id,
                'phone_no' => $this->teacherUser->phone,
                'nrc' => $this->teacherUser->nrc,
                'basic_salary' => 700000,
                'hire_date' => '2020-06-01',
                'gender' => 'male',
                'status' => 'active',
                'subjects_taught' => ['Mathematics', 'Science'],
                'current_classes' => ['Grade 8A', 'Grade 8B', 'Grade 9A'],
            ]);
            $this->teacherProfile = $existingProfile;
        } else {
            // Create new profile
            $this->teacherProfile = TeacherProfile::create([
                'id' => (string) Str::uuid(),
                'user_id' => $this->teacherUser->id,
                'employee_id' => 'TCH-2024-001',
                'position' => 'Senior Teacher',
                'department_id' => $this->department->id,
                'phone_no' => $this->teacherUser->phone,
                'nrc' => $this->teacherUser->nrc,
                'basic_salary' => 700000,
                'hire_date' => '2020-06-01',
                'gender' => 'male',
                'status' => 'active',
                'subjects_taught' => ['Mathematics', 'Science'],
                'current_classes' => ['Grade 8A', 'Grade 8B', 'Grade 9A'],
            ]);
        }

        // Set class teacher for 8A
        $class8A = $this->classes['8A'] ?? null;
        if ($class8A) {
            $class8A->update(['teacher_id' => $this->teacherProfile->id]);
        }

        $this->command->info('✓ Teacher created');
    }

    private function createStudents(): void
    {
        $studentNames = [
            'Aung Aung',
            'Mya Mya',
            'Kyaw Kyaw',
            'Thida Win',
            'Min Thu',
            'Su Su',
            'Zaw Zaw',
            'Hla Hla',
            'Ko Ko',
            'Aye Chan',
            'Nyi Nyi',
            'Phyu Phyu',
            'Tun Tun',
            'May May',
            'Lin Lin',
        ];

        $guardianRole = Role::where('name', RoleEnum::GUARDIAN->value)->first();
        $studentRole = Role::where('name', RoleEnum::STUDENT->value)->first();

        $studentIndex = 1;
        foreach ($this->classes as $classKey => $class) {
            // Create 8 students per class
            for ($i = 0; $i < 8; $i++) {
                $name = $studentNames[$i % count($studentNames)] . ' ' . $studentIndex;
                $email = 'student' . $studentIndex . '@smartcampusedu.com';

                $studentUser = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'id' => (string) Str::uuid(),
                        'name' => $name,
                        'phone' => '0977000' . str_pad($studentIndex + 100, 4, '0', STR_PAD_LEFT),
                        'password' => Hash::make('password'),
                        'is_active' => true,
                    ]
                );

                if ($studentRole) {
                    $studentUser->roles()->sync([$studentRole->id]);
                }

                $studentProfile = StudentProfile::firstOrCreate(
                    ['user_id' => $studentUser->id],
                    [
                        'id' => (string) Str::uuid(),
                        'student_identifier' => str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                        'class_id' => $class->id,
                        'grade_id' => $class->grade_id,
                        'gender' => $i % 2 === 0 ? 'male' : 'female',
                        'status' => 'active',
                    ]
                );

                // Link student to class with batch_id and grade_id
                $class->students()->syncWithoutDetaching([
                    $studentProfile->id => [
                        'batch_id' => $this->batch->id,
                        'grade_id' => $class->grade_id,
                    ]
                ]);

                $this->students[] = $studentProfile;
                $studentIndex++;
            }
        }
        $this->command->info('✓ Students created (' . count($this->students) . ' total)');
    }

    private function createTimetable(): void
    {
        // Create timetables for classes the teacher teaches
        $teacherClasses = ['8A', '8B', '9A'];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $periodTimes = [
            ['08:00', '08:45'],
            ['09:00', '09:45'],
            ['10:00', '10:45'],
            ['11:00', '11:45'],
            ['13:00', '13:45'],
            ['14:00', '14:45'],
        ];

        foreach ($teacherClasses as $classKey) {
            if (!isset($this->classes[$classKey]))
                continue;

            $class = $this->classes[$classKey];

            $timetable = Timetable::firstOrCreate(
                ['class_id' => $class->id, 'batch_id' => $this->batch->id],
                [
                    'id' => (string) Str::uuid(),
                    'grade_id' => $class->grade_id,
                    'name' => 'Timetable ' . $classKey,
                    'status' => 'active',
                    'effective_from' => '2025-06-01',
                    'effective_to' => '2026-03-31',
                    'minutes_per_period' => 45,
                    'break_duration' => 15,
                    'school_start_time' => '08:00',
                    'school_end_time' => '15:00',
                    'week_days' => $days,
                ]
            );

            // Create periods - assign Math to this teacher on specific days/periods
            $mathSubject = $this->subjects['Mathematics'];
            $scienceSubject = $this->subjects['Science'];

            // Monday P1 - Math
            $this->createPeriod($timetable, 'monday', 1, $periodTimes[0], $mathSubject);
            // Tuesday P2 - Math
            $this->createPeriod($timetable, 'tuesday', 2, $periodTimes[1], $mathSubject);
            // Wednesday P1 - Science
            $this->createPeriod($timetable, 'wednesday', 1, $periodTimes[0], $scienceSubject);
            // Thursday P3 - Math
            $this->createPeriod($timetable, 'thursday', 3, $periodTimes[2], $mathSubject);
            // Friday P1 - Math
            $this->createPeriod($timetable, 'friday', 1, $periodTimes[0], $mathSubject);
        }

        $this->command->info('✓ Timetable and periods created');
    }

    private function createPeriod(Timetable $timetable, string $day, int $periodNum, array $times, Subject $subject): void
    {
        Period::firstOrCreate(
            [
                'timetable_id' => $timetable->id,
                'day_of_week' => $day,
                'period_number' => $periodNum,
            ],
            [
                'id' => (string) Str::uuid(),
                'starts_at' => $times[0],
                'ends_at' => $times[1],
                'is_break' => false,
                'subject_id' => $subject->id,
                'teacher_profile_id' => $this->teacherProfile->id,
                'room_id' => $timetable->schoolClass->room_id,
                'notes' => 'Chapter ' . rand(1, 10) . ': ' . $subject->name . ' Lesson',
            ]
        );
    }

    private function createAnnouncements(): void
    {
        $announcements = [
            [
                'title' => 'Staff Meeting Tomorrow',
                'content' => 'All teachers are required to attend the staff meeting at 3 PM in the conference room. Topics include end of semester review and new curriculum updates.',
                'type' => 'meeting',
                'priority' => 'high',
                'target_roles' => ['teacher', 'staff'],
            ],
            [
                'title' => 'Winter Break Schedule',
                'content' => 'School will be closed from Dec 20 to Jan 5 for winter break. Please ensure all grades are submitted before Dec 18.',
                'type' => 'event',
                'priority' => 'high',
                'target_roles' => ['teacher', 'student', 'guardian'],
            ],
            [
                'title' => 'Grade Submission Deadline',
                'content' => 'Please submit all grades for the semester by Dec 15. Late submissions will not be accepted.',
                'type' => 'deadline',
                'priority' => 'high',
                'target_roles' => ['teacher'],
            ],
            [
                'title' => 'New Teaching Resources Available',
                'content' => 'New digital teaching resources are now available in the library portal. Please check and utilize them for your classes.',
                'type' => 'general',
                'priority' => 'medium',
                'target_roles' => ['teacher'],
            ],
            [
                'title' => 'Parent-Teacher Meeting',
                'content' => 'Parent-teacher meeting scheduled for Dec 10. Please prepare student progress reports.',
                'type' => 'meeting',
                'priority' => 'medium',
                'target_roles' => ['teacher', 'guardian'],
            ],
        ];

        foreach ($announcements as $index => $data) {
            Announcement::firstOrCreate(
                ['title' => $data['title']],
                [
                    'id' => (string) Str::uuid(),
                    'content' => $data['content'],
                    'type' => $data['type'],
                    'priority' => $data['priority'],
                    'target_roles' => $data['target_roles'],
                    'publish_date' => now()->subDays($index * 2),
                    'is_published' => true,
                    'status' => true,
                    'created_by' => $this->teacherUser->id,
                ]
            );
        }
        $this->command->info('✓ Announcements created');
    }

    private function createEvents(): void
    {
        // Check for soft-deleted record first and restore it
        $eventCategory = EventCategory::withTrashed()->where('slug', 'academic')->first();
        if ($eventCategory) {
            if ($eventCategory->trashed()) {
                $eventCategory->restore();
            }
        } else {
            $eventCategory = EventCategory::create([
                'id' => (string) Str::uuid(),
                'name' => 'Academic',
                'slug' => 'academic',
                'color' => '#3B82F6',
            ]);
        }

        $events = [
            ['title' => 'Mid-Term Exams Begin', 'description' => 'First day of mid-term examinations', 'start_date' => now()->addDays(5)],
            ['title' => 'Grade Submission Deadline', 'description' => 'Deadline for submitting all grades', 'start_date' => now()->addDays(15)],
            ['title' => 'Winter Break Starts', 'description' => 'School closes for winter holidays', 'start_date' => now()->addDays(20)],
            ['title' => 'Sports Day', 'description' => 'Annual sports day event', 'start_date' => now()->addDays(10)],
            ['title' => 'Science Fair', 'description' => 'Student science project exhibition', 'start_date' => now()->addDays(25)],
        ];

        foreach ($events as $data) {
            Event::firstOrCreate(
                ['title' => $data['title']],
                [
                    'id' => (string) Str::uuid(),
                    'description' => $data['description'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['start_date'],
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'venue' => 'School Campus',
                    'event_category_id' => $eventCategory->id,
                    'status' => 'upcoming',
                ]
            );
        }
        $this->command->info('✓ Events created');
    }

    private function createLeaveRequests(): void
    {
        // Teacher's own leave requests
        $teacherLeaves = [
            ['type' => 'casual', 'start' => now()->subDays(30), 'end' => now()->subDays(29), 'status' => 'approved', 'reason' => 'Personal work'],
            ['type' => 'medical', 'start' => now()->subDays(15), 'end' => now()->subDays(15), 'status' => 'approved', 'reason' => 'Medical appointment'],
            ['type' => 'casual', 'start' => now()->addDays(5), 'end' => now()->addDays(6), 'status' => 'pending', 'reason' => 'Family function'],
        ];

        foreach ($teacherLeaves as $leave) {
            $totalDays = $leave['start']->diffInDays($leave['end']) + 1;
            LeaveRequest::firstOrCreate(
                ['user_id' => $this->teacherUser->id, 'start_date' => $leave['start']->format('Y-m-d')],
                [
                    'id' => (string) Str::uuid(),
                    'user_type' => 'teacher',
                    'leave_type' => $leave['type'],
                    'end_date' => $leave['end'],
                    'total_days' => $totalDays,
                    'reason' => $leave['reason'],
                    'status' => $leave['status'],
                ]
            );
        }

        // Student leave requests (for class teacher to approve)
        $studentLeaves = [
            ['status' => 'pending', 'reason' => 'Family function - wedding ceremony'],
            ['status' => 'pending', 'reason' => 'Medical appointment - dental checkup'],
            ['status' => 'approved', 'reason' => 'Family emergency'],
        ];

        $class8AStudents = StudentProfile::where('class_id', $this->classes['8A']->id)->take(3)->get();

        foreach ($class8AStudents as $index => $student) {
            if (!isset($studentLeaves[$index]))
                continue;

            $leave = $studentLeaves[$index];
            LeaveRequest::firstOrCreate(
                ['user_id' => $student->user_id, 'start_date' => now()->addDays($index + 1)->format('Y-m-d')],
                [
                    'id' => (string) Str::uuid(),
                    'user_type' => 'student',
                    'leave_type' => 'personal',
                    'end_date' => now()->addDays($index + 2),
                    'total_days' => 2,
                    'reason' => $leave['reason'],
                    'status' => $leave['status'],
                ]
            );
        }

        $this->command->info('✓ Leave requests created');
    }

    private function createHomework(): void
    {
        $homeworks = [
            ['title' => 'Chapter 5: Algebra Problems', 'description' => 'Complete exercises 1-20 from Chapter 5', 'due' => 5],
            ['title' => 'Geometry Worksheet', 'description' => 'Complete the geometry worksheet', 'due' => 8],
            ['title' => 'Science Lab Report', 'description' => 'Write a lab report on the experiment', 'due' => -3],
        ];

        // Get a subject for homework
        $subject = !empty($this->subjects) ? reset($this->subjects) : null;
        if (!$subject) {
            $this->command->warn('⚠ No subjects found, skipping homework creation');
            return;
        }

        foreach ($this->classes as $classKey => $class) {
            if (!in_array($classKey, ['8A', '8B', '9A'])) {
                continue;
            }

            foreach ($homeworks as $index => $hw) {
                $homework = Homework::firstOrCreate(
                    ['class_id' => $class->id, 'title' => $hw['title']],
                    [
                        'id' => (string) Str::uuid(),
                        'teacher_id' => $this->teacherProfile->id,
                        'subject_id' => $subject->id,
                        'description' => $hw['description'],
                        'due_date' => now()->addDays($hw['due']),
                        'assigned_date' => now()->subDays(5),
                        'status' => $hw['due'] < 0 ? 'completed' : 'active',
                    ]
                );

                // Create some submissions
                $students = $class->students()->take(5)->get();
                foreach ($students as $i => $student) {
                    if ($i < 3 || $hw['due'] < 0) {
                        HomeworkSubmission::firstOrCreate(
                            ['homework_id' => $homework->id, 'student_id' => $student->id],
                            [
                                'id' => (string) Str::uuid(),
                                'submitted_at' => now()->subDays(rand(1, 3)),
                                'collected_by' => $this->teacherUser->id,
                            ]
                        );
                    }
                }
            }
        }
        $this->command->info('✓ Homework created');
    }

    private function createAttendance(): void
    {
        // Create attendance for the past 5 days
        for ($dayOffset = 1; $dayOffset <= 5; $dayOffset++) {
            $date = now()->subDays($dayOffset);
            if ($date->isWeekend())
                continue;

            foreach (['8A', '8B', '9A'] as $classKey) {
                if (!isset($this->classes[$classKey]))
                    continue;

                $class = $this->classes[$classKey];
                $students = $class->students;

                foreach ($students as $index => $student) {
                    $status = 'present';
                    if ($index === 2)
                        $status = 'absent';
                    if ($index === 5)
                        $status = 'excused';

                    StudentAttendance::firstOrCreate(
                        ['student_id' => $student->id, 'date' => $date->format('Y-m-d')],
                        [
                            'id' => (string) Str::uuid(),
                            'status' => $status,
                            'marked_by' => $this->teacherUser->id,
                        ]
                    );
                }
            }
        }
        $this->command->info('✓ Attendance records created');
    }

    private function createPayroll(): void
    {
        // Create payroll for past 6 months
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths($i);

            Payroll::firstOrCreate(
                ['employee_id' => $this->teacherProfile->id, 'year' => $date->year, 'month' => $date->month],
                [
                    'id' => (string) Str::uuid(),
                    'employee_type' => 'teacher',
                    'working_days' => 22,
                    'days_present' => 20,
                    'leave_days' => 2,
                    'annual_leave' => 0,
                    'days_absent' => 0,
                    'basic_salary' => 700000,
                    'attendance_allowance' => 100000,
                    'loyalty_bonus' => $i === 0 ? 0 : 50000,
                    'other_bonus' => 0,
                    'amount' => $i === 0 ? 800000 : 850000,
                    'status' => $i === 0 ? 'pending' : 'paid',
                    'payment_method' => 'Bank Transfer',
                    'paid_at' => $i === 0 ? null : $date->copy()->addDays(5),
                ]
            );
        }
        $this->command->info('✓ Payroll records created');
    }

    private function createDailyReports(): void
    {
        // Reports FROM teachers (incoming to admin)
        $incomingReports = [
            ['category' => 'suggestion', 'recipient' => 'admin', 'subject' => 'Library Hours Extension', 'status' => 'pending', 'message' => 'I would like to suggest extending the library hours until 6 PM on weekdays. Many students and teachers would benefit from additional study time.'],
            ['category' => 'feedback', 'recipient' => 'principal', 'subject' => 'Thank You for Support', 'status' => 'reviewed', 'message' => 'I wanted to express my gratitude for the support provided during the recent school event. The coordination was excellent.'],
            ['category' => 'complaint', 'recipient' => 'admin', 'subject' => 'Projector Not Working in Room 204', 'status' => 'resolved', 'message' => 'The projector in Room 204 has not been working for the past week. This is affecting my ability to conduct multimedia presentations.'],
            ['category' => 'request', 'recipient' => 'admin', 'subject' => 'Request for Additional Teaching Materials', 'status' => 'pending', 'message' => 'I am requesting additional science lab equipment for the upcoming semester. The current materials are insufficient for the new curriculum.'],
            ['category' => 'report', 'recipient' => 'principal', 'subject' => 'Weekly Class Progress Report', 'status' => 'pending', 'message' => 'This week, Class 5A completed Chapter 8 of Mathematics. All students showed good understanding of fractions and decimals.'],
        ];

        foreach ($incomingReports as $report) {
            DailyReport::firstOrCreate(
                ['user_id' => $this->teacherUser->id, 'subject' => $report['subject'], 'direction' => 'incoming'],
                [
                    'id' => (string) Str::uuid(),
                    'direction' => 'incoming',
                    'recipient' => $report['recipient'],
                    'category' => $report['category'],
                    'message' => $report['message'],
                    'status' => $report['status'],
                ]
            );
        }

        // Reports TO teachers (outgoing from admin)
        $adminUser = User::where('email', 'admin@smartcampusedu.com')->first();
        if ($adminUser) {
            $outgoingReports = [
                ['category' => 'notice', 'subject' => 'Staff Meeting Reminder', 'status' => 'pending', 'message' => 'Please be reminded that there will be a staff meeting this Friday at 3:00 PM in the conference room. Attendance is mandatory.'],
                ['category' => 'reminder', 'subject' => 'Submit Monthly Reports', 'status' => 'acknowledged', 'message' => 'Kindly submit your monthly class reports by the end of this week. Late submissions will not be accepted.'],
            ];

            foreach ($outgoingReports as $report) {
                DailyReport::firstOrCreate(
                    ['recipient_user_id' => $this->teacherUser->id, 'subject' => $report['subject'], 'direction' => 'outgoing'],
                    [
                        'id' => (string) Str::uuid(),
                        'user_id' => $adminUser->id,
                        'recipient_user_id' => $this->teacherUser->id,
                        'direction' => 'outgoing',
                        'recipient' => 'teacher',
                        'category' => $report['category'],
                        'message' => $report['message'],
                        'status' => $report['status'],
                    ]
                );
            }
        }

        $this->command->info('✓ Daily reports created');
    }
}
