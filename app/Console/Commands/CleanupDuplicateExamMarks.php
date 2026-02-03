<?php

namespace App\Console\Commands;

use App\Models\ExamMark;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateExamMarks extends Command
{
    protected $signature = 'exam-marks:cleanup-duplicates';
    protected $description = 'Remove duplicate exam marks (same exam, student, subject)';

    public function handle()
    {
        $this->info('Starting cleanup of duplicate exam marks (including soft-deleted)...');

        // Find duplicates including soft-deleted records
        $duplicates = DB::table('exam_marks')
            ->select('exam_id', 'student_id', 'subject_id', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('exam_id', 'student_id', 'subject_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate exam marks found.');
            return 0;
        }

        $this->info("Found {$duplicates->count()} sets of duplicate records.");

        $totalDeleted = 0;

        foreach ($duplicates as $duplicate) {
            // Keep the first record (oldest) and force delete the rest
            $toDelete = ExamMark::withTrashed()
                ->where('exam_id', $duplicate->exam_id)
                ->where('student_id', $duplicate->student_id)
                ->where('subject_id', $duplicate->subject_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->get();

            foreach ($toDelete as $record) {
                $record->forceDelete();
                $totalDeleted++;
            }

            $this->line("Kept record {$duplicate->keep_id}, force deleted {$toDelete->count()} duplicate(s) for exam {$duplicate->exam_id}, student {$duplicate->student_id}, subject {$duplicate->subject_id}");
        }

        $this->info("Cleanup completed. Force deleted {$totalDeleted} duplicate records.");
        return 0;
    }
}