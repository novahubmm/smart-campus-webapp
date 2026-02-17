<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Grade;
use App\Models\Period;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimetableDataSeeder extends Seeder
{
    private $batch;
    private $subjects = [];
    private $teachers = [];
    private $rooms = [];
    private $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    /**
     * Run the database seeds.
     * Creates sample timetable data for all classes
     */
    public function run(): void
    {
        $this->command->info('📅 Creating Timetable Sample Data for All Classes');
        $this->command->newLine();

        DB::beginTransaction();

        try {
            $this->getBatch();
            $this->getSubjects();
            $this->getTeachers();
            $this->getRooms();
            $this->createTimetables();

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ Timetable Data Created Successfully!');
            $this->displaySummary();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('   File: ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    private function getBatch(): void
    {
        $this->batch = Batch::where('status', true)
            ->orderBy('start_date', 'desc')
            ->first();

        if (!$this->batch) {
            throw new \Exception('No active batch found');
        }

        $this->command->info('✓ Using batch: ' . $this->batch->name);
    }

    private function getSubjects(): void
    {
        $this->subjects = Subject::all();
        
        if ($this->subjects->isEmpty()) {
            throw new \Exception('No subjects found. Please run subject seeder first.');
        }

        $this->command->info('✓ Found ' . $this->subjects->count() . ' subjects');
    }

    private function getTeachers(): void
    {
        $this->teachers = TeacherProfile::with('user')->get();
        
        if ($this->teachers->isEmpty()) {
            $this->command->warn('⚠ No teachers found. Periods will be created without teachers.');
        } else {
            $this->command->info('✓ Found ' . $this->teachers->count() . ' teachers');
        }
    }

    private function getRooms(): void
    {
        $this->rooms = Room::all();
        
        if ($this->rooms->isEmpty()) {
            $this->command->warn('⚠ No rooms found. Creating default rooms...');
            $this->createDefaultRooms();
        } else {
            $this->command->info('✓ Found ' . $this->rooms->count() . ' rooms');
        }
    }

    private function createDefaultRooms(): void
    {
        for ($i = 101; $i <= 110; $i++) {
            $room = Room::create([
                'id' => (string) Str::uuid(),
                'name' => "Room {$i}",
                'capacity' => 40,
                'is_active' => true,
            ]);
            $this->rooms->push($room);
        }
        $this->command->info('✓ Created ' . $this->rooms->count() . ' default rooms');
    }

    private function createTimetables(): void
    {
        $this->command->info('Creating timetables for all classes...');

        $grades = Grade::where('batch_id', $this->batch->id)->get();
        $totalTimetables = 0;
        $totalPeriods = 0;

        foreach ($grades as $grade) {
            $classes = SchoolClass::where('grade_id', $grade->id)
                ->where('batch_id', $this->batch->id)
                ->get();

            foreach ($classes as $class) {
                $timetable = $this->createTimetableForClass($class, $grade);
                if ($timetable) {
                    $periods = $this->createPeriodsForTimetable($timetable, $class);
                    $totalTimetables++;
                    $totalPeriods += $periods;
                }
            }
        }

        $this->command->info("✓ Created {$totalTimetables} timetables with {$totalPeriods} periods");
    }

    private function createTimetableForClass(SchoolClass $class, Grade $grade): ?Timetable
    {
        // Check if timetable already exists
        $existing = Timetable::where('class_id', $class->id)
            ->where('batch_id', $this->batch->id)
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return $existing;
        }

        $timetable = Timetable::create([
            'id' => (string) Str::uuid(),
            'batch_id' => $this->batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'name' => "Timetable for {$class->name}",
            'version_name' => 'Academic Year 2025-2026',
            'is_active' => true,
            'published_at' => Carbon::now(),
            'effective_from' => Carbon::parse('2025-06-01'),
            'effective_to' => Carbon::parse('2026-03-31'),
            'minutes_per_period' => 45,
            'break_duration' => 15,
            'school_start_time' => '08:00',
            'school_end_time' => '15:30',
            'week_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'number_of_periods_per_day' => 8,
            'use_custom_settings' => false,
            'version' => 1,
        ]);

        return $timetable;
    }

    private function createPeriodsForTimetable(Timetable $timetable, SchoolClass $class): int
    {
        $periodCount = 0;
        $periodsPerDay = 8;

        // Define period times
        $periodTimes = [
            1 => ['08:00', '08:45'],
            2 => ['08:45', '09:30'],
            3 => ['09:30', '10:15'],
            4 => ['10:30', '11:15'], // After morning break
            5 => ['11:15', '12:00'],
            6 => ['12:00', '12:45'],
            7 => ['13:45', '14:30'], // After lunch break
            8 => ['14:30', '15:15'],
        ];

        foreach ($this->weekDays as $day) {
            // Skip Sunday
            if ($day === 'Sunday') continue;

            // Saturday has fewer periods
            $dayPeriods = $day === 'Saturday' ? 4 : $periodsPerDay;

            for ($periodNum = 1; $periodNum <= $dayPeriods; $periodNum++) {
                // Check if period already exists
                $existing = Period::where('timetable_id', $timetable->id)
                    ->where('day_of_week', $day)
                    ->where('period_number', $periodNum)
                    ->first();

                if ($existing) {
                    continue;
                }

                // Randomly assign subject and teacher
                $subject = $this->subjects->random();
                $teacher = $this->teachers->isNotEmpty() ? $this->teachers->random() : null;
                $room = $this->rooms->random();

                Period::create([
                    'id' => (string) Str::uuid(),
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $day,
                    'period_number' => $periodNum,
                    'starts_at' => $periodTimes[$periodNum][0],
                    'ends_at' => $periodTimes[$periodNum][1],
                    'is_break' => false,
                    'subject_id' => $subject->id,
                    'teacher_profile_id' => $teacher?->id,
                    'room_id' => $room->id,
                    'notes' => null,
                ]);

                $periodCount++;
            }

            // Add break periods
            $this->createBreakPeriods($timetable, $day);
        }

        return $periodCount;
    }

    private function createBreakPeriods(Timetable $timetable, string $day): void
    {
        // Morning break (after period 3)
        $morningBreak = Period::where('timetable_id', $timetable->id)
            ->where('day_of_week', $day)
            ->where('period_number', 3.5)
            ->first();

        if (!$morningBreak) {
            Period::create([
                'id' => (string) Str::uuid(),
                'timetable_id' => $timetable->id,
                'day_of_week' => $day,
                'period_number' => 3.5,
                'starts_at' => '10:15',
                'ends_at' => '10:30',
                'is_break' => true,
                'subject_id' => null,
                'teacher_profile_id' => null,
                'room_id' => null,
                'notes' => 'Morning Break',
            ]);
        }

        // Lunch break (after period 6)
        if ($day !== 'Saturday') {
            $lunchBreak = Period::where('timetable_id', $timetable->id)
                ->where('day_of_week', $day)
                ->where('period_number', 6.5)
                ->first();

            if (!$lunchBreak) {
                Period::create([
                    'id' => (string) Str::uuid(),
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $day,
                    'period_number' => 6.5,
                    'starts_at' => '12:45',
                    'ends_at' => '13:45',
                    'is_break' => true,
                    'subject_id' => null,
                    'teacher_profile_id' => null,
                    'room_id' => null,
                    'notes' => 'Lunch Break',
                ]);
            }
        }
    }

    private function displaySummary(): void
    {
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('📅 TIMETABLE DATA SUMMARY');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->newLine();

        $totalTimetables = Timetable::where('batch_id', $this->batch->id)->count();
        $totalPeriods = Period::whereHas('timetable', function ($q) {
            $q->where('batch_id', $this->batch->id);
        })->count();
        $totalClasses = SchoolClass::where('batch_id', $this->batch->id)->count();

        $this->command->info('📊 STATISTICS:');
        $this->command->info("   Total Classes: {$totalClasses}");
        $this->command->info("   Total Timetables: {$totalTimetables}");
        $this->command->info("   Total Periods: {$totalPeriods}");
        $this->command->info("   Subjects: " . $this->subjects->count());
        $this->command->info("   Teachers: " . $this->teachers->count());
        $this->command->info("   Rooms: " . $this->rooms->count());
        $this->command->newLine();

        $this->command->info('📚 PERIOD STRUCTURE:');
        $this->command->info('   Periods per day: 8 (Mon-Fri), 4 (Sat)');
        $this->command->info('   Period duration: 45 minutes');
        $this->command->info('   Morning break: 10:15 - 10:30 (15 min)');
        $this->command->info('   Lunch break: 12:45 - 13:45 (60 min)');
        $this->command->newLine();

        $this->command->info('🧪 TESTING:');
        $this->command->info('   Test with any student ID:');
        $this->command->info('   GET /api/v1/guardian/students/{student_id}/timetable');
        $this->command->newLine();

        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
