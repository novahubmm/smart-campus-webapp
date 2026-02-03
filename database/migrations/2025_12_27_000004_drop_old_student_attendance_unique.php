<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table without the old unique constraint
        // First check if the constraint exists
        $indexes = Schema::getIndexes('student_attendance');
        $hasOldConstraint = collect($indexes)->contains(fn($idx) => $idx['name'] === 'student_attendance_unique');
        
        if ($hasOldConstraint) {
            // SQLite doesn't support DROP INDEX on unique constraints easily
            // We'll use raw SQL
            DB::statement('DROP INDEX IF EXISTS student_attendance_unique');
        }
    }

    public function down(): void
    {
        // Re-add the old constraint if needed
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->unique(['student_id', 'date', 'period_id'], 'student_attendance_unique');
        });
    }
};
