<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Batch;
use App\Models\Department;
use App\Models\GuardianProfile;
use App\Models\Grade;
use App\Models\GradeCategory;
use App\Models\Period;
use App\Models\Role;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\TeacherProfile;
use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Demo\DemoBaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OneGradeDemoReadySeeder extends DemoBaseSeeder
{
    private const STUDENTS_PER_CLASS = 7;
    private const SECTIONS = ['A', 'B', 'C'];

    public function run(): void
    {
        DB::disableQueryLog();
        DemoBaseSeeder::init();

        $this->command->info('Starting OneGradeDemoReadySeeder...');

        DB::transaction(function () {
            $this->ensureRolesExist();
            $departments = $this->createDepartments();
            $batch = $this->createBatch();
            $grade = $this->createKindergartenGrade($batch);
            $rooms = $this->createRooms();
            $teachers = $this->createTeachers($departments['TCH']);
            $classes = $this->createClasses($batch, $grade, $rooms, $teachers);
            $this->createStaff($departments);
            $subjects = $this->createSubjects($grade, $teachers);
            $this->createTimetables($batch, $grade, $classes, $subjects, $teachers);
            $this->createStudents($batch, $grade, $classes);

            $this->command->newLine();
            $this->command->info('One-grade demo dataset created successfully.');
            $this->command->info('Batch: ' . $batch->name);
            $this->command->info('Grade: Kindergarten (0)');
            $this->command->info('Classes: 3 (A/B/C)');
            $this->command->info('Teachers: 3');
            $this->command->info('Staff: 3');
            $this->command->info('Students: 21 (7 per class)');
            $this->command->info('Guardians: 21 (1 per student)');
        });
    }

    private function ensureRolesExist(): void
    {
        foreach (RoleEnum::values() as $roleName) {
            Role::firstOrCreate(['name' => $roleName], ['guard_name' => 'web']);
        }
    }

    private function createDepartments(): array
    {
        $this->command->info('Creating departments...');

        $definitions = [
            ['code' => 'FIN', 'name' => 'Finance Department'],
            ['code' => 'MGT', 'name' => 'Management Department'],
            ['code' => 'TCH', 'name' => 'Teaching Department'],
        ];

        $departments = [];
        foreach ($definitions as $definition) {
            $departments[$definition['code']] = Department::firstOrCreate(
                ['code' => $definition['code']],
                ['name' => $definition['name'], 'is_active' => true]
            );
        }

        return $departments;
    }

    private function createBatch(): Batch
    {
        $this->command->info('Creating demo batch...');

        $startYear = (int) now()->format('Y');
        $batchName = "One Grade Demo {$startYear}-" . ($startYear + 1);
        $hasActiveBatch = Batch::where('status', true)->exists();

        return Batch::firstOrCreate(
            ['name' => $batchName],
            [
                'start_date' => Carbon::create($startYear, 6, 1)->toDateString(),
                'end_date' => Carbon::create($startYear + 1, 3, 31)->toDateString(),
                'status' => !$hasActiveBatch,
            ]
        );
    }

    private function createKindergartenGrade(Batch $batch): Grade
    {
        $this->command->info('Creating Kindergarten grade (0)...');

        $primaryCategory = GradeCategory::firstOrCreate(
            ['name' => 'Primary'],
            ['color' => '#3B82F6']
        );

        // Use updateOrCreate to ensure we're linking to the correct batch
        return Grade::updateOrCreate(
            ['level' => 0, 'batch_id' => $batch->id],
            [
                'grade_category_id' => $primaryCategory->id,
                'price_per_month' => 80000,
            ]
        );
    }

    private function createRooms(): array
    {
        $this->command->info('Creating rooms...');

        $rooms = [];
        foreach (self::SECTIONS as $index => $section) {
            $rooms[$section] = Room::firstOrCreate(
                ['name' => "OGD Kindergarten Room {$section}"],
                [
                    'building' => 'Demo Building',
                    'floor' => '1',
                    'capacity' => 30 + $index,
                ]
            );
        }

        return $rooms;
    }

    private function createTeachers(Department $teacherDepartment): array
    {
        $this->command->info('Creating teachers (3)...');

        $definitions = [
            ['section' => 'A', 'email' => 'onegrade.teacher.a@smartcampusedu.com', 'gender' => 'female'],
            ['section' => 'B', 'email' => 'onegrade.teacher.b@smartcampusedu.com', 'gender' => 'male'],
            ['section' => 'C', 'email' => 'onegrade.teacher.c@smartcampusedu.com', 'gender' => 'female'],
        ];

        $teachers = [];

        foreach ($definitions as $index => $definition) {
            $user = $this->upsertUser($definition['email'], $this->generateUniqueName($definition['gender']));
            $user->assignRole(RoleEnum::TEACHER->value);

            $teachers[$definition['section']] = TeacherProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => sprintf('TER%04d', $index + 1),
                    'position' => 'Teacher',
                    'department_id' => $teacherDepartment->id,
                    'hire_date' => $this->getSchoolOpenDate()->copy()->subMonths($index + 1)->toDateString(),
                    'basic_salary' => 450000 + ($index * 25000),
                    'gender' => $definition['gender'],
                    'dob' => now()->subYears(28 + $index)->toDateString(),
                    'phone_no' => $this->phoneFromNumber(100000000 + $index + 1),
                    'address' => 'Yangon, Myanmar',
                    'qualification' => 'B.Ed',
                    'previous_experience_years' => 2 + $index,
                    'status' => 'active',
                    'current_grades' => [0],
                    'current_classes' => [$definition['section']],
                    'responsible_class' => $definition['section'],
                ]
            );
        }

        return $teachers;
    }

    private function createStaff(array $departments): void
    {
        $this->command->info('Creating staff (3)...');

        $definitions = [
            ['email' => 'onegrade.staff.1@smartcampusedu.com', 'gender' => 'female', 'position' => 'Accountant', 'department' => 'FIN'],
            ['email' => 'onegrade.staff.2@smartcampusedu.com', 'gender' => 'male', 'position' => 'Admin Officer', 'department' => 'MGT'],
            ['email' => 'onegrade.staff.3@smartcampusedu.com', 'gender' => 'female', 'position' => 'Office Assistant', 'department' => 'MGT'],
        ];

        foreach ($definitions as $index => $definition) {
            $user = $this->upsertUser($definition['email'], $this->generateUniqueName($definition['gender']));
            $user->assignRole(RoleEnum::STAFF->value);

            StaffProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => sprintf('STF%04d', $index + 1),
                    'position' => $definition['position'],
                    'department_id' => $departments[$definition['department']]->id,
                    'hire_date' => $this->getSchoolOpenDate()->copy()->subMonths($index + 2)->toDateString(),
                    'basic_salary' => 320000 + ($index * 20000),
                    'gender' => $definition['gender'],
                    'dob' => now()->subYears(26 + $index)->toDateString(),
                    'phone_no' => $this->phoneFromNumber(200000000 + $index + 1),
                    'address' => 'Yangon, Myanmar',
                    'status' => 'active',
                ]
            );
        }
    }

    private function createClasses(Batch $batch, Grade $grade, array $rooms, array $teachers): array
    {
        $this->command->info('Creating classes (A/B/C)...');

        $classes = [];
        foreach (self::SECTIONS as $section) {
            // Use only the section letter (A, B, C) as the class name
            $classes[$section] = SchoolClass::updateOrCreate(
                ['name' => $section, 'grade_id' => $grade->id],
                [
                    'batch_id' => $batch->id,
                    'teacher_id' => $teachers[$section]->id,
                    'room_id' => $rooms[$section]->id,
                ]
            );
        }

        return $classes;
    }

    private function createSubjects(Grade $grade, array $teachers): array
    {
        $this->command->info('Creating kindergarten subjects...');

        $subjectType = SubjectType::firstOrCreate(['name' => 'Core']);

        $definitions = [
            ['code' => 'OGD-KG-MYA', 'name' => 'Myanmar'],
            ['code' => 'OGD-KG-ENG', 'name' => 'English'],
            ['code' => 'OGD-KG-MTH', 'name' => 'Mathematics'],
            ['code' => 'OGD-KG-SCI', 'name' => 'General Science'],
            ['code' => 'OGD-KG-ART', 'name' => 'Art & Craft'],
            ['code' => 'OGD-KG-PE', 'name' => 'Physical Education'],
        ];

        $subjects = [];

        foreach ($definitions as $index => $definition) {
            $subject = Subject::updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'subject_type_id' => $subjectType->id,
                ]
            );

            $grade->subjects()->syncWithoutDetaching([$subject->id]);

            $section = self::SECTIONS[$index % count(self::SECTIONS)];
            $subject->teachers()->syncWithoutDetaching([$teachers[$section]->id]);
            $subjects[] = $subject;
        }

        return $subjects;
    }

    private function createTimetables(Batch $batch, Grade $grade, array $classes, array $subjects, array $teachers): void
    {
        $this->command->info('Creating active timetables...');

        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        $periodTemplates = [
            1 => ['starts_at' => '08:00:00', 'ends_at' => '08:45:00', 'is_break' => false, 'subject_index' => 0],
            2 => ['starts_at' => '08:45:00', 'ends_at' => '09:30:00', 'is_break' => false, 'subject_index' => 1],
            3 => ['starts_at' => '09:30:00', 'ends_at' => '10:15:00', 'is_break' => false, 'subject_index' => 2],
            4 => ['starts_at' => '10:15:00', 'ends_at' => '11:00:00', 'is_break' => true, 'subject_index' => null],
            5 => ['starts_at' => '11:15:00', 'ends_at' => '12:00:00', 'is_break' => false, 'subject_index' => 3],
            6 => ['starts_at' => '12:00:00', 'ends_at' => '12:45:00', 'is_break' => false, 'subject_index' => 4],
            7 => ['starts_at' => '12:45:00', 'ends_at' => '13:30:00', 'is_break' => false, 'subject_index' => 5],
        ];

        foreach (self::SECTIONS as $section) {
            $class = $classes[$section];
            $teacher = $teachers[$section];

            $timetable = Timetable::updateOrCreate(
                ['class_id' => $class->id, 'version_name' => 'One Grade Demo'],
                [
                    'batch_id' => $batch->id,
                    'grade_id' => $grade->id,
                    'name' => 'One Grade Demo Timetable',
                    'is_active' => true,
                    'published_at' => now(),
                    'effective_from' => $batch->start_date,
                    'effective_to' => $batch->end_date,
                    'minutes_per_period' => 45,
                    'break_duration' => 15,
                    'school_start_time' => '08:00:00',
                    'school_end_time' => '13:30:00',
                    'week_days' => $weekDays,
                    'number_of_periods_per_day' => 7,
                    'use_custom_settings' => true,
                    'version' => 1,
                    'created_by' => $teacher->user_id,
                ]
            );

            Timetable::where('class_id', $class->id)
                ->where('id', '!=', $timetable->id)
                ->update(['is_active' => false]);

            foreach ($weekDays as $day) {
                foreach ($periodTemplates as $periodNumber => $template) {
                    $subject = is_null($template['subject_index']) ? null : $subjects[$template['subject_index']];

                    $period = Period::withTrashed()->firstOrNew([
                        'timetable_id' => $timetable->id,
                        'day_of_week' => $day,
                        'period_number' => $periodNumber,
                    ]);

                    $period->starts_at = $template['starts_at'];
                    $period->ends_at = $template['ends_at'];
                    $period->is_break = $template['is_break'];
                    $period->subject_id = $subject?->id;
                    $period->teacher_profile_id = $template['is_break'] ? null : $teacher->id;
                    $period->room_id = $class->room_id;
                    $period->notes = 'One-grade demo timetable';
                    $period->deleted_at = null;
                    $period->save();
                }
            }
        }
    }

    private function createStudents(Batch $batch, Grade $grade, array $classes): void
    {
        $this->command->info('Creating students (7 per class)...');

        $studentCounter = 1;

        foreach (self::SECTIONS as $sectionIndex => $section) {
            $class = $classes[$section];

            for ($i = 1; $i <= self::STUDENTS_PER_CLASS; $i++) {
                $email = sprintf('onegrade.student.%s%02d@smartcampusedu.com', strtolower($section), $i);
                $gender = (($i + $sectionIndex) % 2 === 0) ? 'female' : 'male';
                $name = $this->generateUniqueName($gender);

                $user = $this->upsertUser($email, $name);
                $user->assignRole(RoleEnum::STUDENT->value);

                $studentProfile = StudentProfile::firstOrNew(['user_id' => $user->id]);
                $studentProfile->student_id = sprintf('STD%04d', $studentCounter);
                $studentProfile->student_identifier = sprintf('STD%04d', $studentCounter);
                $studentProfile->grade_id = $grade->id;
                $studentProfile->class_id = $class->id;
                $studentProfile->date_of_joining = $this->getSchoolOpenDate()->toDateString();
                $studentProfile->gender = $gender;
                $studentProfile->dob = now()->subYears(5)->subMonths($i)->toDateString();
                $studentProfile->address = 'Yangon, Myanmar';
                $studentProfile->father_name = $this->generateUniqueName('male');
                $studentProfile->father_phone_no = $this->phoneFromNumber(300000000 + $studentCounter);
                $studentProfile->mother_name = $this->generateUniqueName('female');
                $studentProfile->mother_phone_no = $this->phoneFromNumber(400000000 + $studentCounter);
                $studentProfile->ethnicity = 'Myanmar';
                $studentProfile->religious = 'Buddhism';
                $studentProfile->status = 'active';
                $studentProfile->save();

                $existingStudentClass = DB::table('student_class')
                    ->where('student_id', $studentProfile->id)
                    ->where('class_id', $class->id)
                    ->exists();

                if (!$existingStudentClass) {
                    DB::table('student_class')->insert([
                        'id' => (string) Str::uuid(),
                        'student_id' => $studentProfile->id,
                        'batch_id' => $batch->id,
                        'grade_id' => $grade->id,
                        'class_id' => $class->id,
                        'status' => 'enrolled',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $this->createGuardianForStudent($studentProfile, $section, $i);

                $studentCounter++;
            }
        }
    }

    private function createGuardianForStudent(StudentProfile $studentProfile, string $section, int $index): void
    {
        $guardianGender = $studentProfile->gender === 'male'
            ? (rand(0, 2) === 0 ? 'male' : 'female')
            : (rand(0, 2) === 0 ? 'female' : 'male');

        $guardianEmail = sprintf('onegrade.guardian.%s%02d@smartcampusedu.com', strtolower($section), $index);
        $guardianName = $this->generateUniqueName($guardianGender);

        $guardianUser = $this->upsertUser($guardianEmail, $guardianName);
        $guardianUser->assignRole(RoleEnum::GUARDIAN->value);

        $guardianProfile = GuardianProfile::updateOrCreate(
            ['user_id' => $guardianUser->id],
            [
                'occupation' => 'Parent',
                'address' => $studentProfile->address,
                'notes' => 'Created by OneGradeDemoReadySeeder',
            ]
        );

        DB::table('guardian_student')->updateOrInsert(
            [
                'guardian_profile_id' => $guardianProfile->id,
                'student_profile_id' => $studentProfile->id,
            ],
            [
                'relationship' => 'parent',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function upsertUser(string $email, string $name): User
    {
        $user = User::firstOrNew(['email' => $email]);

        $user->name = $name;
        $user->password = $this->getHashedPassword();
        $user->is_active = true;
        $user->email_verified_at = $user->email_verified_at ?: now();
        $user->save();

        return $user;
    }

    private function phoneFromNumber(int $number): string
    {
        return '09' . str_pad((string) $number, 9, '0', STR_PAD_LEFT);
    }
}
