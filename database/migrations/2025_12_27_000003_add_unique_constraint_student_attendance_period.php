<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the index and add unique constraint instead
        Schema::table('student_attendance', function (Blueprint $table) {
            // Drop the non-unique index if it exists
            try {
                $table->dropIndex('student_attendance_period_idx');
            } catch (\Exception $e) {
                // Index might not exist
            }
        });
        
        Schema::table('student_attendance', function (Blueprint $table) {
            // Add unique constraint for period-based attendance
            $table->unique(['student_id', 'date', 'period_number'], 'student_attendance_student_date_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropUnique('student_attendance_student_date_period_unique');
            $table->index(['student_id', 'date', 'period_number'], 'student_attendance_period_idx');
        });
    }
};
