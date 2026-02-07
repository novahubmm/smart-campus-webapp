<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Office hours for staff/teachers
            $table->time('office_start_time')->nullable()->after('school_end_time');
            $table->time('office_end_time')->nullable()->after('office_start_time');
            
            // Break duration for staff in minutes (separate from student break)
            $table->integer('office_break_duration_minutes')->default(60)->after('office_end_time');
            
            // Required working hours (decimal, e.g., 8.0 or 7.5)
            $table->decimal('required_working_hours', 4, 2)->default(8.00)->after('office_break_duration_minutes');
            
            // Working days for staff (JSON array, can be different from school days)
            $table->json('office_working_days')->nullable()->after('required_working_hours');
            
            // Policies
            $table->boolean('allow_early_checkout')->default(true)->after('office_working_days');
            $table->integer('late_arrival_grace_minutes')->default(15)->after('allow_early_checkout');
            $table->boolean('track_overtime')->default(true)->after('late_arrival_grace_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'office_start_time',
                'office_end_time',
                'office_break_duration_minutes',
                'required_working_hours',
                'office_working_days',
                'allow_early_checkout',
                'late_arrival_grace_minutes',
                'track_overtime',
            ]);
        });
    }
};
