<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\Room;
use App\Models\Timetable;
use App\Models\Period;
use App\Models\Batch;
use App\Models\StudentProfile;
use Carbon\Carbon;

class Teacher1TodayClassesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Setting up Teacher1 with active timetable for today\'s classes...');

        // Get or create teacher1
        $teacher1 = $this->getOrCreateTeacher1();
        
        // Get or create subjects
        $subjects = $this->getOrCreateSubjects();
        
        // Get or create class (019b5a7c-3b82-701a-9fc1-7ab069cb0945)
        $class = $this->getOrCreateClass();
        
        // Get or create rooms
        $rooms = $this->getOrCreateRooms();
        
        // Get or create batch
        $batch = $this->getOrCreateBatch();
        
        // Create timetable version 2 and make it active
        $this->createActiveTimetableVersion2($teacher1->teacherProfile, $class, $batch, $subjects, $rooms);
        
        // Create some students for the class
        $this->createStudentsForClass($class);
        
        $this->command->info('✅ Teacher1 setup complete! You can now test /today-classes API');
        $this->command->info('📋 Class ID: ' . $class->id);
        $this->command->info('👨‍🏫 Teacher1 ID: ' . $teacher1->id);
        $this->command->info('📚 Active timetable version 2 created for today');
    }

    private function getOrCreateTeacher1(): User
    {
        $teacher1 = User::where('email', 'teacher1@school.com')->first();
        
        if (!$teacher1) {
            $teacher1 = User::create([
                'name' => 'Teacher One',
                'email' => 'teacher1@school.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Get or create teacher profile
        $teacherProfile = TeacherProfile::where('user_id', $teacher1->id)->first();
        if (!$teacherProfile) {
            $teacherProfile = TeacherProfile::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'user_id' => $teacher1->id,
                'employee_id' => 'T001',
                'dob' => '1985-01-01',
                'hire_date' => '2020-01-01',
                'current_classes' => json_encode(['7A', '8B', '9C']),
                'phone_no' => '09123456789',
                'address' => 'Yangon',
                'position' => 'Senior Teacher',
                'status' => 'active',
            ]);
        }

        $teacher1->load('teacherProfile');
        return $teacher1;
    }

    private function getOrCreateSubjects(): array
    {
        $subjectsData = [
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Physics', 'code' => 'PHY'],
            ['name' => 'Chemistry', 'code' => 'CHEM'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Myanmar Language', 'code' => 'MYA'],
        ];

        $subjects = [];
        foreach ($subjectsData as $subjectData) {
            $subject = Subject::firstOrCreate(
                ['code' => $subjectData['code']],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => $subjectData['name'],
                    'code' => $subjectData['code'],
                ]
            );
            $subjects[] = $subject;
        }

        return $subjects;
    }

    private function getOrCreateClass(): SchoolClass
    {
        // Try to get the specific class ID from the URL
        $classId = '019b5a7c-3b82-701a-9fc1-7ab069cb0945';
        $class = SchoolClass::find($classId);
        
        if (!$class) {
            // Create grade first
            $grade = Grade::firstOrCreate(
                ['level' => 7],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => 'Grade 7',
                    'level' => 7,
                ]
            );

            // Create the class with specific ID
            $class = SchoolClass::create([
                'id' => $classId,
                'grade_id' => $grade->id,
                'name' => 'A',
            ]);
        }

        return $class;
    }

    private function getOrCreateRooms(): array
    {
        $roomNames = ['Room 101', 'Room 102', 'Room 103', 'Lab A', 'Lab B'];
        $rooms = [];

        foreach ($roomNames as $roomName) {
            $room = Room::firstOrCreate(
                ['name' => $roomName],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => $roomName,
                    'building' => 'Main Building',
                    'floor' => '1st Floor',
                    'capacity' => 40,
                ]
            );
            $rooms[] = $room;
        }

        return $rooms;
    }

    private function getOrCreateBatch(): Batch
    {
        return Batch::firstOrCreate(
            ['name' => '2024-2025'],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => '2024-2025',
                'start_date' => '2024-06-01',
                'end_date' => '2025-03-31',
            ]
        );
    }

    private function createActiveTimetableVersion2($teacherProfile, $class, $batch, $subjects, $rooms): void
    {
        // First, deactivate any existing active timetables for this class
        Timetable::where('class_id', $class->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Create version 2 timetable
        $timetable = Timetable::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'batch_id' => $batch->id,
            'grade_id' => $class->grade_id,
            'class_id' => $class->id,
            'name' => 'Version 2 - Teacher1 Schedule',
            'version_name' => 'Version 2',
            'version' => 2,
            'is_active' => true,
            'effective_from' => now()->subDays(7),
            'effective_to' => now()->addMonths(3),
            'minutes_per_period' => 45,
            'break_duration' => 15,
            'school_start_time' => '08:00',
            'school_end_time' => '15:00',
            'week_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'number_of_periods_per_day' => 6,
            'use_custom_settings' => false,
            'created_by' => $teacherProfile->user_id,
        ]);

        $this->command->info('📅 Created timetable version 2: ' . $timetable->id);

        // Create periods for today
        $this->createPeriodsForToday($timetable, $teacherProfile, $subjects, $rooms);
    }

    private function createPeriodsForToday($timetable, $teacherProfile, $subjects, $rooms): void
    {
        $today = Carbon::today();
        $dayOfWeek = strtolower($today->format('l'));
        $currentTime = Carbon::now();

        $this->command->info("📅 Creating periods for {$dayOfWeek}");

        // Define periods with times that create ongoing and upcoming status
        $periodsData = [
            [
                'period_number' => 1,
                'starts_at' => '08:00:00',
                'ends_at' => '08:45:00',
                'subject_id' => $subjects[0]->id, // Mathematics
            ],
            [
                'period_number' => 2,
                'starts_at' => '08:45:00',
                'ends_at' => '09:30:00',
                'subject_id' => $subjects[1]->id, // Physics
            ],
            [
                'period_number' => 3,
                'starts_at' => '09:45:00',
                'ends_at' => '10:30:00',
                'subject_id' => $subjects[2]->id, // Chemistry
            ],
            [
                'period_number' => 4,
                'starts_at' => '10:30:00',
                'ends_at' => '11:15:00',
                'subject_id' => $subjects[3]->id, // English
            ],
            [
                'period_number' => 5,
                'starts_at' => '11:30:00',
                'ends_at' => '12:15:00',
                'subject_id' => $subjects[4]->id, // Myanmar Language
            ],
            [
                'period_number' => 6,
                'starts_at' => '14:00:00',
                'ends_at' => '14:45:00',
                'subject_id' => $subjects[0]->id, // Mathematics
            ],
        ];

        foreach ($periodsData as $index => $periodData) {
            $period = Period::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'timetable_id' => $timetable->id,
                'teacher_profile_id' => $teacherProfile->id,
                'subject_id' => $periodData['subject_id'],
                'room_id' => $rooms[$index % count($rooms)]->id,
                'period_number' => $periodData['period_number'],
                'day_of_week' => $dayOfWeek,
                'starts_at' => $periodData['starts_at'],
                'ends_at' => $periodData['ends_at'],
                'is_break' => false,
            ]);

            $subject = collect($subjects)->firstWhere('id', $periodData['subject_id']);
            $this->command->info("  ⏰ Period {$periodData['period_number']}: {$subject->name} ({$periodData['starts_at']} - {$periodData['ends_at']})");
        }

        // Also create periods for other days of the week
        $this->createPeriodsForOtherDays($timetable, $teacherProfile, $subjects, $rooms);
    }

    private function createPeriodsForOtherDays($timetable, $teacherProfile, $subjects, $rooms): void
    {
        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $today = strtolower(Carbon::today()->format('l'));

        foreach ($weekDays as $day) {
            if ($day === $today) {
                continue; // Skip today as we already created it
            }

            $periodsData = [
                [
                    'period_number' => 1,
                    'starts_at' => '08:00:00',
                    'ends_at' => '08:45:00',
                    'subject_id' => $subjects[rand(0, count($subjects) - 1)]->id,
                ],
                [
                    'period_number' => 2,
                    'starts_at' => '08:45:00',
                    'ends_at' => '09:30:00',
                    'subject_id' => $subjects[rand(0, count($subjects) - 1)]->id,
                ],
                [
                    'period_number' => 3,
                    'starts_at' => '09:45:00',
                    'ends_at' => '10:30:00',
                    'subject_id' => $subjects[rand(0, count($subjects) - 1)]->id,
                ],
                [
                    'period_number' => 4,
                    'starts_at' => '10:30:00',
                    'ends_at' => '11:15:00',
                    'subject_id' => $subjects[rand(0, count($subjects) - 1)]->id,
                ],
                [
                    'period_number' => 5,
                    'starts_at' => '11:30:00',
                    'ends_at' => '12:15:00',
                    'subject_id' => $subjects[rand(0, count($subjects) - 1)]->id,
                ],
                [
                    'period_number' => 6,
                    'starts_at' => '14:00:00',
                    'ends_at' => '14:45:00',
                    'subject_id' => $subjects[rand(0, count($subjects) - 1)]->id,
                ],
            ];

            foreach ($periodsData as $index => $periodData) {
                Period::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'timetable_id' => $timetable->id,
                    'teacher_profile_id' => $teacherProfile->id,
                    'subject_id' => $periodData['subject_id'],
                    'room_id' => $rooms[$index % count($rooms)]->id,
                    'period_number' => $periodData['period_number'],
                    'day_of_week' => $day,
                    'starts_at' => $periodData['starts_at'],
                    'ends_at' => $periodData['ends_at'],
                    'is_break' => false,
                ]);
            }
        }
    }

    private function createStudentsForClass($class): void
    {
        $studentNames = [
            'Aung Aung', 'Thant Zin', 'Khin Khin', 'Mya Mya',
            'Zaw Zaw', 'Htun Htun', 'Nwe Nwe', 'Aye Aye',
            'Min Min', 'Kyaw Kyaw', 'Thida Thida', 'Soe Soe',
        ];

        foreach ($studentNames as $index => $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '.class' . substr($class->id, -4) . '@student.com';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $user->id,
                    'student_id' => 'STU' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'class_id' => $class->id,
                    'dob' => '2010-01-01',
                    'address' => 'Yangon',
                ]
            );
        }

        $this->command->info('👥 Created ' . count($studentNames) . ' students for the class');
    }
}