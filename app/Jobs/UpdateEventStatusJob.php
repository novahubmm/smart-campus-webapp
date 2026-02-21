<?php

namespace App\Jobs;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateEventStatusJob implements ShouldQueue
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

        // Update to 'upcoming' - events that haven't started yet
        $upcomingCount = Event::where('status', '!=', 'upcoming')
            ->where('status', '!=', 'result') // Don't auto-update result status
            ->where('start_date', '>', $today)
            ->update(['status' => 'upcoming']);

        // Update to 'ongoing' - events that are currently happening
        $ongoingCount = Event::where('status', '!=', 'ongoing')
            ->where('status', '!=', 'result') // Don't auto-update result status
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->update(['status' => 'ongoing']);

        // Update to 'completed' - events that have ended (but not manually set to 'result')
        $completedCount = Event::where('status', '!=', 'completed')
            ->where('status', '!=', 'result') // Don't auto-update result status
            ->where('end_date', '<', $today)
            ->update(['status' => 'completed']);

        $updatedCount = $upcomingCount + $ongoingCount + $completedCount;

        Log::info('Event status update completed', [
            'upcoming' => $upcomingCount,
            'ongoing' => $ongoingCount,
            'completed' => $completedCount,
            'total_updated' => $updatedCount,
        ]);
    }
}
