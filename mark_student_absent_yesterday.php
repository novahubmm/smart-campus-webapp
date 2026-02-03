<?php

/**
 * Script to mark a student as absent for all periods yesterday
 * Usage: php mark_student_absent_yesterday.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Configuration
$studentId = '50c4ce90-6a92-4350-bcfd-91efcf316dd1';
$yesterday = '2026-01-23'; // Friday, January 23, 2026

echo "=== Mark Student Absent Script ===\n";
echo "Student ID: {$studentId}\n";
echo "Date: {$yesterday}\n\n";

// Verify student exists
$student = StudentProfile::find($studentId);
if (!$student) {
    echo "❌ Error: Student with ID {$studentId} not found!\n";
    exit(1);
}

echo "✓ Student found: {$student->name_en}\n";

// Get the student's timetable
if (!$student->grade_id) {
    echo "❌ Error: Student has no grade assigned!\n";
    exit(1);
}

// Get day of week name (lowercase)
$dayOfWeek = strtolower(Carbon::parse($yesterday)->format('l'));
echo "✓ Day of week: {$dayOfWeek} (" . Carbon::parse($yesterday)->format('l') . ")\n";

// Find all periods for this student's grade on yesterday
$periods = Period::whereHas('timetable', function ($query) use ($student) {
    $query->where('grade_id', $student->grade_id);
})
->where('day_of_week', $dayOfWeek)
->where('is_break', false) // Exclude break periods
->orderBy('period_number')
->get();

if ($periods->isEmpty()) {
    echo "⚠️  Warning: No periods found for this student's grade on {$yesterday}\n";
    exit(0);
}

echo "✓ Found {$periods->count()} periods\n\n";

// Mark attendance for each period
$processed = 0;
$errors = 0;

DB::beginTransaction();

try {
    foreach ($periods as $period) {
        echo "Processing Period {$period->period_number} ({$period->starts_at} - {$period->ends_at}) - Period ID: {$period->id}...\n";
        
        // Use updateOrCreate with student_id, date, period_number as unique keys
        $attendance = StudentAttendance::updateOrCreate(
            [
                'student_id' => $studentId,
                'date' => $yesterday,
                'period_number' => $period->period_number,
            ],
            [
                'period_id' => $period->id,
                'status' => 'absent',
                'remark' => 'Marked absent via script',
            ]
        );
        
        echo "  ✓ Marked absent\n";
        $processed++;
    }
    
    DB::commit();
    
    echo "\n=== Summary ===\n";
    echo "✓ Successfully marked student as absent\n";
    echo "  - Processed: {$processed} periods\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
