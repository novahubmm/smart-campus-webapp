<?php

namespace App\Console\Commands;

use App\Models\Grade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateGrades extends Command
{
    protected $signature = 'grades:check-duplicates';
    protected $description = 'Check for duplicate grades (same batch and level)';

    public function handle()
    {
        $this->info('Checking for duplicate grades...');
        $this->newLine();

        // Find duplicates
        $duplicates = Grade::select('batch_id', 'level', DB::raw('COUNT(*) as count'))
            ->groupBy('batch_id', 'level')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✓ No duplicate grades found. Safe to run migration.');
            return 0;
        }

        $this->error('Found ' . $duplicates->count() . ' duplicate grade combinations:');
        $this->newLine();

        foreach ($duplicates as $duplicate) {
            $grades = Grade::where('batch_id', $duplicate->batch_id)
                ->where('level', $duplicate->level)
                ->with(['batch', 'gradeCategory', 'classes'])
                ->get();
            
            $this->warn("Batch: {$grades->first()->batch->name}, Level: {$duplicate->level}");
            $this->line("Found {$grades->count()} duplicates:");
            
            $tableData = [];
            foreach ($grades as $index => $grade) {
                $classCount = $grade->classes->count();
                $studentCount = DB::table('student_profiles')->where('grade_id', $grade->id)->count();
                $category = $grade->gradeCategory ? $grade->gradeCategory->name : 'N/A';
                
                $tableData[] = [
                    'Index' => $index,
                    'ID' => substr($grade->id, 0, 8) . '...',
                    'Category' => $category,
                    'Classes' => $classCount,
                    'Students' => $studentCount,
                    'Created' => $grade->created_at->format('Y-m-d H:i'),
                ];
            }
            
            $this->table(
                ['Index', 'ID', 'Category', 'Classes', 'Students', 'Created'],
                $tableData
            );
            $this->newLine();
        }

        $this->newLine();
        $this->info('To fix duplicates:');
        $this->line('1. Identify which grade to keep (usually the one with most students/classes)');
        $this->line('2. Run: php artisan tinker');
        $this->line('3. Execute these commands:');
        $this->newLine();
        $this->comment('$keepGrade = \App\Models\Grade::find("grade-id-to-keep");');
        $this->comment('$deleteGrade = \App\Models\Grade::find("grade-id-to-delete");');
        $this->comment('// Reassign classes');
        $this->comment('$deleteGrade->classes()->update(["grade_id" => $keepGrade->id]);');
        $this->comment('// Reassign students');
        $this->comment('\DB::table("student_profiles")->where("grade_id", $deleteGrade->id)->update(["grade_id" => $keepGrade->id]);');
        $this->comment('// Delete duplicate');
        $this->comment('$deleteGrade->delete();');

        return 1;
    }
}
