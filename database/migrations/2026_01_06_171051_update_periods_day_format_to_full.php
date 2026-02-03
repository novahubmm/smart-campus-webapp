<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update short day format to full day format
        $dayMap = [
            'mon' => 'monday',
            'tue' => 'tuesday',
            'wed' => 'wednesday',
            'thu' => 'thursday',
            'fri' => 'friday',
            'sat' => 'saturday',
            'sun' => 'sunday',
        ];

        foreach ($dayMap as $short => $full) {
            DB::table('periods')
                ->where('day_of_week', $short)
                ->update(['day_of_week' => $full]);
        }

        // Also update timetables week_days JSON field
        $timetables = DB::table('timetables')->get();
        
        foreach ($timetables as $timetable) {
            if ($timetable->week_days) {
                $weekDays = json_decode($timetable->week_days, true);
                
                if (is_array($weekDays)) {
                    $updatedDays = array_map(function($day) use ($dayMap) {
                        $lowerDay = strtolower($day);
                        return $dayMap[$lowerDay] ?? $day;
                    }, $weekDays);
                    
                    DB::table('timetables')
                        ->where('id', $timetable->id)
                        ->update(['week_days' => json_encode($updatedDays)]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert full day format back to short day format
        $dayMap = [
            'monday' => 'mon',
            'tuesday' => 'tue',
            'wednesday' => 'wed',
            'thursday' => 'thu',
            'friday' => 'fri',
            'saturday' => 'sat',
            'sunday' => 'sun',
        ];

        foreach ($dayMap as $full => $short) {
            DB::table('periods')
                ->where('day_of_week', $full)
                ->update(['day_of_week' => $short]);
        }

        // Also revert timetables week_days JSON field
        $timetables = DB::table('timetables')->get();
        
        foreach ($timetables as $timetable) {
            if ($timetable->week_days) {
                $weekDays = json_decode($timetable->week_days, true);
                
                if (is_array($weekDays)) {
                    $updatedDays = array_map(function($day) use ($dayMap) {
                        $lowerDay = strtolower($day);
                        return $dayMap[$lowerDay] ?? $day;
                    }, $weekDays);
                    
                    DB::table('timetables')
                        ->where('id', $timetable->id)
                        ->update(['week_days' => json_encode($updatedDays)]);
                }
            }
        }
    }
};

