<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add unique constraint on student_id, date, period_id for upsert operations
        // This is needed because the TeacherAttendanceApiRepository uses these columns for upsert
        
        if (DB::getDriverName() === 'sqlite') {
            // SQLite requires raw SQL for creating unique indexes
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS student_attendance_student_date_period_id_unique ON student_attendance(student_id, date, period_id)');
        } else {
            Schema::table('student_attendance', function (Blueprint $table) {
                $table->unique(['student_id', 'date', 'period_id'], 'student_attendance_student_date_period_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS student_attendance_student_date_period_id_unique');
        } else {
            Schema::table('student_attendance', function (Blueprint $table) {
                $table->dropUnique('student_attendance_student_date_period_id_unique');
            });
        }
    }
};
