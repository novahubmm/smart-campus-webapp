<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\Period;

echo "Seeding Timetables for All Classes\n";
echo str_repeat('=', 80) . "\n\n";

// Get active batch or first batch
$batch = Batch::where('is_active', true)->first();
if (!$batch) {
    $batch = Batch::first();
}
if (!$batch) {
    echo "❌ No batch found\n";
    exit(1);
}

echo "Active Batch: {$batch->name}\n\n";

// Get all classes
$classes = SchoolClass::with(['grade', 'room'])->get();
echo "Total Classes: {$classes->count()}\n";

// Get all subjects grouped by grade using the pivot table
$subjectsData = [];
foreach ($classes as $class) {
    $gradeLevel = $class->grade->level;
    if (!isset($subjectsData[$gradeLevel])) {
        $subjects = $class->grade->subjects()->with('teacherProfiles')->get();
        
        $subjectsData[$gradeLevel] = $subjects->map(function($subject) {
            return [
                'subject' => $subject,
                'teacher' => $subject->teacherProfiles->first()
            ];
        })->toArray();
    }
}

$periodTimes = [
    1 => ['08:00', '08:45', false],
    2 => ['08:45', '09:30', false],
    3 => ['09:30', '10:15', false],
    4 => ['10:15', '11:15', true],   // Morning Break
    5 => ['11:15', '12:00', false],
    6 => ['12:00', '12:45', false],
    7 => ['12:45', '13:30', false],
];

$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

$created = 0;
$skipped = 0;

foreach ($classes as $class) {
    // Check if timetable already exists
    $existing = Timetable::where('class_id', $class->id)
        ->where('batch_id', $batch->id)
        ->where('is_active', true)
        ->first();
    
    if ($existing && $existing->periods()->count() >= 30) {
        echo "⏭️  Skipping {$class->name} (already has complete timetable)\n";
        $skipped++;
        continue;
    }

    // Delete incomplete timetable if exists
    if ($existing) {
        $existing->periods()->delete();
        $existing->delete();
    }

    $grade = $class->grade;
    $gradeLevel = $grade->level;
    
    $timetable = Timetable::create([
        'class_id' => $class->id,
        'batch_id' => $batch->id,
        'grade_id' => $grade->id,
        'name' => "Timetable - {$class->name}",
        'is_active' => true,
        'published_at' => now(),
        'effective_from' => now(),
        'minutes_per_period' => 45,
        'break_duration' => 15,
        'school_start_time' => '08:00',
        'school_end_time' => '14:30',
        'week_days' => $days,
        'version' => 1,
    ]);

    $gradeSubjects = $subjectsData[$gradeLevel] ?? [];
    if (empty($gradeSubjects)) {
        echo "⚠️  {$class->name} - No subjects found for grade level {$gradeLevel}\n";
        continue;
    }

    $periodCount = 0;
    foreach ($days as $day) {
        shuffle($gradeSubjects);
        $subjectIndex = 0;

        foreach ($periodTimes as $periodNumber => $periodData) {
            [$startTime, $endTime, $isBreak] = $periodData;

            $periodAttributes = [
                'timetable_id' => $timetable->id,
                'day_of_week' => $day,
                'period_number' => $periodNumber,
                'starts_at' => $startTime,
                'ends_at' => $endTime,
                'is_break' => $isBreak,
                'room_id' => $class->room_id,
            ];

            if ($isBreak) {
                $periodAttributes['subject_id'] = null;
                $periodAttributes['teacher_profile_id'] = null;
            } else {
                $subjectData = $gradeSubjects[$subjectIndex % count($gradeSubjects)];
                $periodAttributes['subject_id'] = $subjectData['subject']->id;
                $periodAttributes['teacher_profile_id'] = $subjectData['teacher']->id ?? null;
                $subjectIndex++;
            }

            Period::create($periodAttributes);
            $periodCount++;
        }
    }

    echo "✅ {$class->name} - Created {$periodCount} periods\n";
    $created++;
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "Summary:\n";
echo "  Created: {$created} timetables\n";
echo "  Skipped: {$skipped} timetables\n";
echo "  Total: " . ($created + $skipped) . " classes\n";
