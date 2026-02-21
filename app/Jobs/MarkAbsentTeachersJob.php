<?php

namespace App\Jobs;

use App\Models\TeacherAttendance;
use App\Models\TeacherProfile;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MarkAbsentTeachersJob implements ShouldQueue
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
            Log::info('MarkAbsentTeachersJob: Yesterday is before current month, skipping.');
            return;
        }

        $markedCount = 0;
        $processedDates = [];

        // Get all active teachers
        $activeTeachers = TeacherProfile::whereHas('user', function ($query) {
            $query->where('is_active', true);
        })->where('status', 'active')->get();

        if ($activeTeachers->isEmpty()) {
            Log::info('MarkAbsentTeachersJob: No active teachers found.');
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
            $dayOfWeekName = $currentDate->format('l'); // Monday, Tuesday, etc.

            foreach ($activeTeachers as $teacher) {
                // Check if attendance record exists
                $exists = TeacherAttendance::where('teacher_id', $teacher->user_id)
                    ->where('date', $dateString)
                    ->exists();

                // If no record exists, create absent record
                if (!$exists) {
                    try {
                        $attendanceId = TeacherAttendance::generateId($dateString);
                        
                        TeacherAttendance::create([
                            'id' => $attendanceId,
                            'teacher_id' => $teacher->user_id,
                            'date' => $dateString,
                            'day_of_week' => $dayOfWeekName,
                            'status' => 'absent',
                            'remarks' => 'Auto-marked absent (no attendance recorded)',
                            'check_in_time' => null,
                            'check_out_time' => null,
                            'check_in_timestamp' => null,
                            'check_out_timestamp' => null,
                            'working_hours_decimal' => 0,
                            'leave_type' => null,
                            'location_lat' => null,
                            'location_lng' => null,
                            'device_info' => null,
                            'app_version' => null,
                        ]);
                        
                        $markedCount++;
                    } catch (\Exception $e) {
                        // Log error but continue processing
                        Log::error('MarkAbsentTeachersJob: Failed to create attendance record', [
                            'teacher_id' => $teacher->user_id,
                            'date' => $dateString,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $processedDates[] = $dateString;
            $currentDate->addDay();
        }

        Log::info('MarkAbsentTeachersJob completed', [
            'marked_absent_count' => $markedCount,
            'processed_dates' => $processedDates,
            'active_teachers_count' => $activeTeachers->count(),
        ]);
    }
}
