#!/usr/bin/env php
<?php

/**
 * Fix existing teacher double-booking issues in the database
 * 
 * This script identifies and fixes periods where teachers are
 * assigned to multiple classes at the same time.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Timetable;
use App\Models\Period;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\DB;

echo "\n=== Fix Teacher Double-Booking Issues ===\n\n";

// Get all active timetables with their periods
$activeTimetables = Timetable::where('is_active', true)
    ->with(['periods' => function($q) {
        $q->whereNotNull('teacher_profile_id')
          ->where('is_break', false)
          ->orderBy('day_of_week')
          ->orderBy('starts_at');
    }, 'schoolClass'])
    ->get();

echo "Found {$activeTimetables->count()} active timetables\n\n";

// Track conflicts: [teacher_id][day][time_slot] = [period_ids]
$conflicts = [];
$conflictCount = 0;

// First pass: identify all conflicts
echo "=== Identifying Conflicts ===\n";

foreach ($activeTimetables as $timetable) {
    foreach ($timetable->periods as $period) {
        $teacherId = $period->teacher_profile_id;
        $day = $period->day_of_week;
        $timeSlot = "{$period->starts_at}-{$period->ends_at}";
        
        if (!isset($conflicts[$teacherId])) {
            $conflicts[$teacherId] = [];
        }
        
        if (!isset($conflicts[$teacherId][$day])) {
            $conflicts[$teacherId][$day] = [];
        }
        
        if (!isset($conflicts[$teacherId][$day][$timeSlot])) {
            $conflicts[$teacherId][$day][$timeSlot] = [];
        }
        
        $conflicts[$teacherId][$day][$timeSlot][] = [
            'period' => $period,
            'timetable' => $timetable,
        ];
    }
}

// Find actual conflicts (more than one period in same time slot)
$doubleBookings = [];

foreach ($conflicts as $teacherId => $days) {
    foreach ($days as $day => $timeSlots) {
        foreach ($timeSlots as $timeSlot => $assignments) {
            if (count($assignments) > 1) {
                $teacher = TeacherProfile::with('user')->find($teacherId);
                $teacherName = $teacher?->user?->name ?? 'Unknown';
                
                echo "❌ Conflict found:\n";
                echo "   Teacher: {$teacherName}\n";
                echo "   Day: {$day}\n";
                echo "   Time: {$timeSlot}\n";
                echo "   Classes:\n";
                
                foreach ($assignments as $assignment) {
                    $className = $assignment['timetable']->schoolClass->name;
                    $subjectName = $assignment['period']->subject?->name ?? 'Unknown';
                    echo "     - {$className} ({$subjectName})\n";
                }
                
                $doubleBookings[] = [
                    'teacher_id' => $teacherId,
                    'teacher_name' => $teacherName,
                    'day' => $day,
                    'time_slot' => $timeSlot,
                    'assignments' => $assignments,
                ];
                
                $conflictCount++;
                echo "\n";
            }
        }
    }
}

if ($conflictCount === 0) {
    echo "✓ No double-booking conflicts found!\n\n";
    exit(0);
}

echo "=== Summary ===\n";
echo "Total conflicts found: {$conflictCount}\n\n";

// Ask for confirmation
echo "Do you want to fix these conflicts? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "Aborted. No changes made.\n\n";
    exit(0);
}

// Second pass: fix conflicts
echo "\n=== Fixing Conflicts ===\n";

$fixedCount = 0;

foreach ($doubleBookings as $conflict) {
    $assignments = $conflict['assignments'];
    
    // Keep the first assignment, clear teacher from others
    $keepAssignment = array_shift($assignments);
    
    echo "Keeping: {$keepAssignment['timetable']->schoolClass->name}\n";
    
    foreach ($assignments as $assignment) {
        $period = $assignment['period'];
        $className = $assignment['timetable']->schoolClass->name;
        
        // Clear the teacher assignment
        $period->update(['teacher_profile_id' => null]);
        
        echo "  Cleared teacher from: {$className}\n";
        $fixedCount++;
    }
    
    echo "\n";
}

echo "=== Results ===\n";
echo "Fixed {$fixedCount} double-booking issues\n";
echo "Teachers have been removed from conflicting periods\n";
echo "Please manually reassign teachers to the cleared periods\n\n";

echo "✓ Done!\n\n";
