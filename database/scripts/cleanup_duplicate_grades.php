<?php

/**
 * Script to identify and clean up duplicate grades
 * Run this before applying the unique constraint migration
 * 
 * Usage: php artisan tinker < database/scripts/cleanup_duplicate_grades.php
 */

use App\Models\Grade;
use Illuminate\Support\Facades\DB;

echo "Checking for duplicate grades...\n\n";

// Find duplicates
$duplicates = Grade::select('batch_id', 'level', DB::raw('COUNT(*) as count'))
    ->groupBy('batch_id', 'level')
    ->having('count', '>', 1)
    ->get();

if ($duplicates->isEmpty()) {
    echo "No duplicate grades found. Safe to run migration.\n";
    exit(0);
}

echo "Found " . $duplicates->count() . " duplicate grade combinations:\n\n";

foreach ($duplicates as $duplicate) {
    $grades = Grade::where('batch_id', $duplicate->batch_id)
        ->where('level', $duplicate->level)
        ->with(['batch', 'gradeCategory', 'classes'])
        ->get();
    
    echo "Batch: {$grades->first()->batch->name}, Level: {$duplicate->level}\n";
    echo "Found {$grades->count()} duplicates:\n";
    
    foreach ($grades as $index => $grade) {
        $classCount = $grade->classes->count();
        $studentCount = $grade->students()->count();
        $category = $grade->gradeCategory ? $grade->gradeCategory->name : 'N/A';
        echo "  [{$index}] ID: {$grade->id} | Category: {$category} | Classes: {$classCount} | Students: {$studentCount} | Created: {$grade->created_at}\n";
    }
    
    echo "\n";
}

echo "\nTo fix duplicates, you need to:\n";
echo "1. Manually review which grade records to keep\n";
echo "2. Reassign students/classes from duplicate grades to the one you want to keep\n";
echo "3. Delete the duplicate grade records\n";
echo "\nExample commands:\n";
echo "// Keep the first grade, delete others\n";
echo "\$keepGrade = Grade::find('grade-id-to-keep');\n";
echo "\$deleteGrade = Grade::find('grade-id-to-delete');\n";
echo "// Reassign classes\n";
echo "\$deleteGrade->classes()->update(['grade_id' => \$keepGrade->id]);\n";
echo "// Reassign students\n";
echo "\$deleteGrade->students()->update(['grade_id' => \$keepGrade->id]);\n";
echo "// Delete duplicate\n";
echo "\$deleteGrade->delete();\n";
