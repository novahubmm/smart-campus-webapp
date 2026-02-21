<?php

namespace App\Jobs;

use App\Models\Period;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarkAbsentStudentsJob implements ShouldQueue
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
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Only process dates from start of current month to yesterday
        // (Don't mark today as absent since attendance might still be taken)
        $yesterday = Carbon::yesterday();
        
        if ($yesterday->lt($currentMonth)) {
            Log::info('MarkAbsentStudentsJob: Yesterday is before current month, skipping.');
            return;
        }

        $markedCount = 0;
        $processedDates = [];

        // Get all active students
        $activeStudents = StudentProfile::whereHas('user', function ($query) {
            $query->where('is_active', true);
        })->where('status', 'active')->get();

        if ($activeStudents->isEmpty()) {
            Log::info('MarkAbsentStudentsJob: No active students found.');
            return;
        }

        // Get all periods (excluding breaks)
        $periods = Period::where('is_break', false)->get();

        if ($periods->isEmpty()) {
            Log::info('MarkAbsentStudentsJob: No periods found.');
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
            $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            
            // Get periods for this day of week
            $dayPeriods = $periods->where('day_of_week', $dayOfWeek);

            foreach ($activeStudents as $student) {
                foreach ($dayPeriods as $period) {
                    // Check if attendance record exists
                    $exists = StudentAttendance::where('student_id', $student->id)
                        ->where('date', $dateString)
                        ->where('period_id', $period->id)
                        ->exists();

                    // If no record exists, create absent record
                    if (!$exists) {
                        try {
                            StudentAttendance::create([
                                'student_id' => $student->id,
                                'period_id' => $period->id,
                                'date' => $dateString,
                                'status' => 'absent',
                                'remark' => 'Auto-marked absent (no attendance recorded)',
                                'marked_by' => null, // System-generated
                                'collect_time' => null,
                                'period_number' => $period->period_number,
                            ]);
                            
                            $markedCount++;
                        } catch (\Exception $e) {
                            // Log error but continue processing
                            Log::error('MarkAbsentStudentsJob: Failed to create attendance record', [
                                'student_id' => $student->id,
                                'period_id' => $period->id,
                                'date' => $dateString,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            $processedDates[] = $dateString;
            $currentDate->addDay();
        }

        Log::info('MarkAbsentStudentsJob completed', [
            'marked_absent_count' => $markedCount,
            'processed_dates' => $processedDates,
            'active_students_count' => $activeStudents->count(),
            'periods_count' => $periods->count(),
        ]);
    }
}
