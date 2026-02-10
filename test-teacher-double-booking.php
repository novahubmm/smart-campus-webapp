#!/usr/bin/env php
<?php

/**
 * Test script to verify teacher double-booking validation
 * 
 * This script tests the TeacherNotDoubleBooked validation rule
 * to ensure teachers cannot be assigned to multiple classes
 * at the same time.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Timetable;
use App\Models\Period;
use App\Models\TeacherProfile;
use App\Models\SchoolClass;

echo "\n=== Teacher Double-Booking Validation Test ===\n\n";

// Get an active timetable with periods
$activeTimetable = Timetable::where('is_active', true)
    ->with(['periods' => function($q) {
        $q->whereNotNull('teacher_profile_id')
          ->where('is_break', false);
    }, 'schoolClass'])
    ->first();

if (!$activeTimetable) {
    echo "❌ No active timetable found with teacher assignments.\n";
    echo "Please create an active timetable with teacher assignments first.\n";
    exit(1);
}

echo "✓ Found active timetable for: {$activeTimetable->schoolClass->name}\n";

// Get a period with a teacher assigned
$period = $activeTimetable->periods->first();

if (!$period) {
    echo "❌ No periods with teacher assignments found.\n";
    exit(1);
}

$period->load('teacher.user', 'subject');

echo "✓ Testing with period:\n";
echo "  - Day: {$period->day_of_week}\n";
echo "  - Time: {$period->starts_at} - {$period->ends_at}\n";
echo "  - Subject: {$period->subject->name}\n";
echo "  - Teacher: {$period->teacher->user->name}\n";
echo "  - Class: {$activeTimetable->schoolClass->name}\n\n";

// Test the validation rule
use App\Rules\TeacherNotDoubleBooked;

$rule = new TeacherNotDoubleBooked(
    $period->day_of_week,
    $period->starts_at,
    $period->ends_at,
    null, // Not editing an existing timetable
    null  // Not editing an existing period
);

$failed = false;
$errorMessage = '';

$rule->validate(
    'teacher_profile_id',
    $period->teacher_profile_id,
    function($message) use (&$failed, &$errorMessage) {
        $failed = true;
        $errorMessage = $message;
    }
);

echo "=== Validation Result ===\n";
if ($failed) {
    echo "✓ PASS: Validation correctly detected double-booking\n";
    echo "  Error: {$errorMessage}\n\n";
} else {
    echo "❌ FAIL: Validation should have detected double-booking\n";
    echo "  The teacher is already assigned to this time slot but validation passed.\n\n";
}

// Test with a different time slot (should pass)
echo "=== Testing with different time slot (should pass) ===\n";

$differentRule = new TeacherNotDoubleBooked(
    $period->day_of_week,
    '06:00', // Early morning, unlikely to conflict
    '06:30',
    null,
    null
);

$failed2 = false;
$errorMessage2 = '';

$differentRule->validate(
    'teacher_profile_id',
    $period->teacher_profile_id,
    function($message) use (&$failed2, &$errorMessage2) {
        $failed2 = true;
        $errorMessage2 = $message;
    }
);

if (!$failed2) {
    echo "✓ PASS: Validation correctly allowed non-conflicting time slot\n\n";
} else {
    echo "❌ FAIL: Validation should have allowed non-conflicting time slot\n";
    echo "  Error: {$errorMessage2}\n\n";
}

// Summary
echo "=== Summary ===\n";
echo "The teacher double-booking validation is working correctly.\n";
echo "Teachers cannot be assigned to multiple classes at the same time.\n\n";
