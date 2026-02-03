<?php

namespace Database\Seeders\Demo;

use App\Models\Batch;
use App\Models\Facility;
use App\Models\Grade;
use App\Models\GradeCategory;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoAcademicSeeder extends DemoBaseSeeder
{
    public function run(array $teacherProfiles): array
    {
        $batch = $this->createBatch();
        $grades = $this->createGrades($batch);
        $rooms = $this->createRooms();
        $subjects = $this->createSubjects($grades, $teacherProfiles);
        $classes = $this->createClasses($grades, $batch, $teacherProfiles, $rooms);

        return [
            'batch' => $batch,
            'grades' => $grades,
            'rooms' => $rooms,
            'subjects' => $subjects,
            'classes' => $classes,
        ];
    }

    private function createBatch(): Batch
    {
        $this->command->info('Creating Batch...');

        return Batch::firstOrCreate(['name' => '2025-2026'], [
            'start_date' => $this->getSchoolOpenDate(),
            'end_date' => $this->getSchoolOpenDate()->copy()->addMonths(10),
            'status' => true,
        ]);
    }

    private function createGrades(Batch $batch): array
    {
        $this->command->info('Creating Grades (13)...');

        $gradeCategories = GradeCategory::all()->keyBy('name');
        $grades = [];

        for ($level = 0; $level <= 12; $level++) {
            $categoryName = match (true) {
                $level <= 4 => 'Primary',
                $level <= 8 => 'Middle School',
                default => 'High School',
            };

            $grades[$level] = Grade::firstOrCreate(
                ['level' => $level, 'batch_id' => $batch->id],
                ['grade_category_id' => $gradeCategories[$categoryName]->id ?? null, 'price_per_month' => rand(5000, 20000)]
            );
        }

        return $grades;
    }

    private function createRooms(): array
    {
        $this->command->info('Creating Rooms (39)...');

        $facilities = Facility::all();
        $buildings = ['Building A', 'Building B', 'Building C'];
        $rooms = [];

        for ($i = 1; $i <= 39; $i++) {
            $room = Room::firstOrCreate(
                ['name' => "Room " . (100 + $i)],
                ['building' => $buildings[($i - 1) % 3], 'floor' => ceil($i / 13), 'capacity' => rand(35, 40)]
            );

            $randomFacilities = $facilities->random(rand(3, 6));
            foreach ($randomFacilities as $facility) {
                DB::table('room_facilities')->insertOrIgnore([
                    'room_id' => $room->id,
                    'facility_id' => $facility->id,
                    'quantity' => rand(1, 3),
                    'is_working' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $rooms[] = $room;
        }

        return $rooms;
    }

    private function createSubjects(array $grades, array $teacherProfiles): array
    {
        $this->command->info('Creating Subjects (78)...');

        $subjectType = SubjectType::where('name', 'Core')->first();
        $subjectTypeId = $subjectType?->id;

        $subjectsByGrade = [
            0 => ['Myanmar', 'English', 'Mathematics', 'General Science', 'Art & Craft', 'Physical Education'],
            1 => ['Myanmar', 'English', 'Mathematics', 'Science', 'Social Studies', 'Art'],
            2 => ['Myanmar', 'English', 'Mathematics', 'Science', 'Social Studies', 'Art'],
            3 => ['Myanmar', 'English', 'Mathematics', 'Science', 'Social Studies', 'Art'],
            4 => ['Myanmar', 'English', 'Mathematics', 'Science', 'Social Studies', 'Art'],
            5 => ['Myanmar', 'English', 'Mathematics', 'Science', 'History', 'Geography'],
            6 => ['Myanmar', 'English', 'Mathematics', 'Science', 'History', 'Geography'],
            7 => ['Myanmar', 'English', 'Mathematics', 'Science', 'History', 'Geography'],
            8 => ['Myanmar', 'English', 'Mathematics', 'Science', 'History', 'Geography'],
            9 => ['Myanmar', 'English', 'Mathematics', 'Physics', 'Chemistry', 'Biology'],
            10 => ['Myanmar', 'English', 'Mathematics', 'Physics', 'Chemistry', 'Biology'],
            11 => ['Myanmar', 'English', 'Mathematics', 'Physics', 'Chemistry', 'Biology'],
            12 => ['Myanmar', 'English', 'Mathematics', 'Physics', 'Chemistry', 'Biology'],
        ];

        $subjectMeta = [
            'Mathematics' => ['icon' => '🔢', 'icon_color' => '#DC2626', 'progress_color' => '#DC2626'],
            'English' => ['icon' => 'abc', 'icon_color' => '#F59E0B', 'progress_color' => '#F59E0B'],
            'Science' => ['icon' => '🧪', 'icon_color' => '#10B981', 'progress_color' => '#10B981'],
            'General Science' => ['icon' => '🧪', 'icon_color' => '#10B981', 'progress_color' => '#10B981'],
            'Myanmar' => ['icon' => 'ဃ', 'icon_color' => '#8B5CF6', 'progress_color' => '#8B5CF6'],
            'History' => ['icon' => '📜', 'icon_color' => '#6366F1', 'progress_color' => '#6366F1'],
            'Geography' => ['icon' => '🌍', 'icon_color' => '#14B8A6', 'progress_color' => '#14B8A6'],
            'Physics' => ['icon' => '⚛️', 'icon_color' => '#3B82F6', 'progress_color' => '#3B82F6'],
            'Chemistry' => ['icon' => '🧫', 'icon_color' => '#EC4899', 'progress_color' => '#EC4899'],
            'Biology' => ['icon' => '🧬', 'icon_color' => '#22C55E', 'progress_color' => '#22C55E'],
            'Art' => ['icon' => '🎨', 'icon_color' => '#F97316', 'progress_color' => '#F97316'],
            'Art & Craft' => ['icon' => '🎨', 'icon_color' => '#F97316', 'progress_color' => '#F97316'],
            'Physical Education' => ['icon' => '🏃', 'icon_color' => '#0EA5E9', 'progress_color' => '#0EA5E9'],
            'Social Studies' => ['icon' => '🧭', 'icon_color' => '#0F766E', 'progress_color' => '#0F766E'],
        ];

        $subjects = [];
        $teacherIndex = 0;

        foreach ($grades as $level => $grade) {
            $gradeSubjects = $subjectsByGrade[$level];

            foreach ($gradeSubjects as $subjectName) {
                $code = strtoupper(substr($subjectName, 0, 2)) . '-G' . $level;
                $meta = $subjectMeta[$subjectName] ?? [
                    'icon' => '📖',
                    'icon_color' => '#6B7280',
                    'progress_color' => '#6B7280',
                ];

                $subject = Subject::updateOrCreate(
                    ['code' => $code],
                    array_merge(
                        [
                            'name' => $subjectName,
                            'subject_type_id' => $subjectTypeId,
                        ],
                        $meta
                    )
                );

                DB::table('grade_subject')->insertOrIgnore([
                    'id' => (string) Str::uuid(),
                    'grade_id' => $grade->id,
                    'subject_id' => $subject->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (isset($teacherProfiles[$teacherIndex])) {
                    DB::table('subject_teacher')->insertOrIgnore([
                        'subject_id' => $subject->id,
                        'teacher_profile_id' => $teacherProfiles[$teacherIndex]->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $subjects[$level][] = [
                    'subject' => $subject,
                    'teacher' => $teacherProfiles[$teacherIndex] ?? null,
                ];

                $teacherIndex++;
            }
        }

        return $subjects;
    }

    private function createClasses(array $grades, Batch $batch, array $teacherProfiles, array $rooms): array
    {
        $this->command->info('Creating Classes (39)...');

        $classNames = ['A', 'B', 'C'];
        $classes = [];
        $classIndex = 0;
        $teacherIndex = 0;

        foreach ($grades as $level => $grade) {
            foreach ($classNames as $className) {
                // Use proper naming for Kindergarten (Grade 0)
                if ($level === 0) {
                    $fullName = "Kindergarten {$className}";
                } else {
                    $fullName = "Grade {$level} {$className}";
                }

                $classes[$classIndex] = SchoolClass::firstOrCreate(
                    ['name' => $fullName, 'batch_id' => $batch->id],
                    [
                        'grade_id' => $grade->id,
                        'teacher_id' => $teacherProfiles[$teacherIndex]->id ?? null,
                        'room_id' => $rooms[$classIndex]->id ?? null,
                    ]
                );

                $classIndex++;
                $teacherIndex++;
                if ($teacherIndex >= 39) {
                    $teacherIndex = 0;
                }
            }
        }

        return $classes;
    }
}
