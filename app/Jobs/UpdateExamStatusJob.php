<?php

namespace App\Jobs;

use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateExamStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $today = Carbon::today();
        $updatedCount = 0;

        // Update to 'upcoming' - exams that haven't started yet
        $upcomingCount = Exam::where('status', '!=', 'upcoming')
            ->where('start_date', '>', $today)
            ->update(['status' => 'upcoming']);

        // Update to 'ongoing' - exams that are currently happening
        $ongoingCount = Exam::where('status', '!=', 'ongoing')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->update(['status' => 'ongoing']);

        // Update to 'completed' - exams that have ended
        $completedCount = Exam::where('status', '!=', 'completed')
            ->where('end_date', '<', $today)
            ->update(['status' => 'completed']);

        $updatedCount = $upcomingCount + $ongoingCount + $completedCount;

        Log::info('Exam status update completed', [
            'upcoming' => $upcomingCount,
            'ongoing' => $ongoingCount,
            'completed' => $completedCount,
            'total_updated' => $updatedCount,
        ]);
    }
}
