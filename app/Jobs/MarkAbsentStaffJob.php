<?php

namespace App\Jobs;

use App\Models\StaffAttendance;
use App\Models\StaffProfile;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MarkAbsentStaffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $today = Carbon::today();
        $currentMonth = Carbon::now()->startOfMonth();
        $yesterday = Carbon::yesterday();
        
        if ($yesterday->lt($currentMonth)) {
            Log::info('MarkAbsentStaffJob: Yesterday is before current month, skipping.');
            return;
        }

        $markedCount = 0;
        $processedDates = [];

        // Get all active staff members
        $activeStaff = StaffProfile::whereHas('user', function ($query) {
            $query->where('is_active', true);
        })->where('status', 'active')->get();

        if ($activeStaff->isEmpty()) {
            Log::info('MarkAbsentStaffJob: No active staff found.');
            return;
        }

        // Process each date from start of month to yesterday
        $currentDate = $currentMonth->copy();
        
        while ($currentDate->lte($yesterday)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($currentDate->dayOfWeek === Carbon::SATURDAY || $currentDate->dayOfWeek === Carbon::SUNDAY) {
                $currentDate->addDay();
                continue;
            }

            $dateString = $currentDate->toDateString();

            foreach ($activeStaff as $staff) {
                // Check if attendance record exists
                $exists = StaffAttendance::where('staff_id', $staff->id)
                    ->where('date', $dateString)
                    ->exists();

                // If no record exists, create absent record
                if (!$exists) {
                    try {
                        StaffAttendance::create([
                            'staff_id' => $staff->id,
                            'date' => $dateString,
                            'status' => 'absent',
                            'remark' => 'Auto-marked absent (no attendance recorded)',
                            'marked_by' => null, // System-generated
                            'start_time' => null,
                            'end_time' => null,
                        ]);
                        
                        $markedCount++;
                    } catch (\Exception $e) {
                        // Log error but continue processing
                        Log::error('MarkAbsentStaffJob: Failed to create attendance record', [
                            'staff_id' => $staff->id,
                            'date' => $dateString,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $processedDates[] = $dateString;
            $currentDate->addDay();
        }

        Log::info('MarkAbsentStaffJob completed', [
            'marked_absent_count' => $markedCount,
            'processed_dates' => $processedDates,
            'active_staff_count' => $activeStaff->count(),
        ]);
    }
}
