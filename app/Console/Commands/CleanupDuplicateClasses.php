<?php

namespace App\Console\Commands;

use App\Models\SchoolClass;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateClasses extends Command
{
    protected $signature = 'classes:check-duplicates';
    protected $description = 'Check for duplicate classes (same grade and name)';

    public function handle()
    {
        $this->info('Checking for duplicate classes...');
        $this->newLine();

        // Find duplicates
        $duplicates = SchoolClass::select('grade_id', 'name', DB::raw('COUNT(*) as count'))
            ->groupBy('grade_id', 'name')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✓ No duplicate classes found. Safe to run migration.');
            return 0;
        }

        $this->error('Found ' . $duplicates->count() . ' duplicate class combinations:');
        $this->newLine();

        foreach ($duplicates as $duplicate) {
            $classes = SchoolClass::where('grade_id', $duplicate->grade_id)
                ->where('name', $duplicate->name)
                ->with(['grade.batch', 'teacher.user', 'room'])
                ->get();
            
            $gradeName = $classes->first()->grade->name ?? 'N/A';
            $batchName = $classes->first()->grade->batch->name ?? 'N/A';
            
            $this->warn("Grade: {$gradeName}, Class: {$duplicate->name}, Batch: {$batchName}");
            $this->line("Found {$classes->count()} duplicates:");
            
            $tableData = [];
            foreach ($classes as $index => $class) {
                $studentCount = DB::table('student_profiles')->where('class_id', $class->id)->count();
                $teacher = $class->teacher ? $class->teacher->user->name : 'N/A';
                $room = $class->room ? $class->room->name : 'N/A';
                
                $tableData[] = [
                    'Index' => $index,
                    'ID' => substr($class->id, 0, 8) . '...',
                    'Teacher' => $teacher,
                    'Room' => $room,
                    'Students' => $studentCount,
                    'Created' => $class->created_at->format('Y-m-d H:i'),
                ];
            }
            
            $this->table(
                ['Index', 'ID', 'Teacher', 'Room', 'Students', 'Created'],
                $tableData
            );
            $this->newLine();
        }

        $this->newLine();
        $this->info('To fix duplicates:');
        $this->line('1. Identify which class to keep (usually the one with most students)');
        $this->line('2. Run: php artisan tinker');
        $this->line('3. Execute these commands:');
        $this->newLine();
        $this->comment('$keepClass = \App\Models\SchoolClass::find("class-id-to-keep");');
        $this->comment('$deleteClass = \App\Models\SchoolClass::find("class-id-to-delete");');
        $this->comment('// Reassign students');
        $this->comment('\DB::table("student_profiles")->where("class_id", $deleteClass->id)->update(["class_id" => $keepClass->id]);');
        $this->comment('// Reassign student_class pivot');
        $this->comment('\DB::table("student_class")->where("class_id", $deleteClass->id)->update(["class_id" => $keepClass->id]);');
        $this->comment('// Delete duplicate');
        $this->comment('$deleteClass->delete();');

        return 1;
    }
}
