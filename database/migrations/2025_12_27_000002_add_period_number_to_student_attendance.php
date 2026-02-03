<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add column if it doesn't exist
        if (!Schema::hasColumn('student_attendance', 'period_number')) {
            Schema::table('student_attendance', function (Blueprint $table) {
                $table->unsignedTinyInteger('period_number')->nullable()->after('period_id');
            });
        }
        
        // Add index for period-based attendance queries
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->index(['student_id', 'date', 'period_number'], 'student_attendance_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropIndex('student_attendance_period_idx');
            $table->dropColumn('period_number');
        });
    }
};
