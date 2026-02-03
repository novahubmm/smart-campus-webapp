<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table to change enum
        if (DB::getDriverName() === 'sqlite') {
            // Create a new table with the correct enum
            DB::statement('CREATE TABLE student_attendance_new (
                id TEXT PRIMARY KEY,
                student_id TEXT NOT NULL,
                period_id TEXT,
                date DATE NOT NULL,
                status TEXT CHECK(status IN (\'present\', \'absent\', \'leave\')) DEFAULT \'present\',
                remark TEXT,
                marked_by TEXT,
                collect_time TIME,
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                deleted_at TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
                FOREIGN KEY (period_id) REFERENCES periods(id) ON DELETE SET NULL,
                FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
            )');

            // Copy data, converting 'late' and 'excused' to 'leave'
            DB::statement("INSERT INTO student_attendance_new 
                SELECT id, student_id, period_id, date, 
                    CASE 
                        WHEN status IN ('late', 'excused') THEN 'leave' 
                        ELSE status 
                    END as status,
                    remark, marked_by, collect_time, created_at, updated_at, deleted_at 
                FROM student_attendance");

            // Drop old table
            DB::statement('DROP TABLE student_attendance');

            // Rename new table
            DB::statement('ALTER TABLE student_attendance_new RENAME TO student_attendance');

            // Recreate unique index
            DB::statement('CREATE UNIQUE INDEX student_attendance_unique ON student_attendance(student_id, date, period_id)');
        } else {
            // For MySQL/PostgreSQL - first update values, then change enum
            DB::table('student_attendance')
                ->whereIn('status', ['late', 'excused'])
                ->update(['status' => 'leave']);
            
            DB::statement("ALTER TABLE student_attendance MODIFY COLUMN status ENUM('present', 'absent', 'leave') DEFAULT 'present'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TABLE student_attendance_new (
                id TEXT PRIMARY KEY,
                student_id TEXT NOT NULL,
                period_id TEXT,
                date DATE NOT NULL,
                status TEXT CHECK(status IN (\'present\', \'absent\', \'late\', \'excused\')) DEFAULT \'present\',
                remark TEXT,
                marked_by TEXT,
                collect_time TIME,
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                deleted_at TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
                FOREIGN KEY (period_id) REFERENCES periods(id) ON DELETE SET NULL,
                FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
            )');

            DB::statement("INSERT INTO student_attendance_new 
                SELECT id, student_id, period_id, date, 
                    CASE WHEN status = 'leave' THEN 'excused' ELSE status END as status,
                    remark, marked_by, collect_time, created_at, updated_at, deleted_at 
                FROM student_attendance");

            DB::statement('DROP TABLE student_attendance');
            DB::statement('ALTER TABLE student_attendance_new RENAME TO student_attendance');
            DB::statement('CREATE UNIQUE INDEX student_attendance_unique ON student_attendance(student_id, date, period_id)');
        } else {
            DB::table('student_attendance')
                ->where('status', 'leave')
                ->update(['status' => 'excused']);
            
            DB::statement("ALTER TABLE student_attendance MODIFY COLUMN status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present'");
        }
    }
};
