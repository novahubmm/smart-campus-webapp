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
        Schema::table('teacher_attendance', function (Blueprint $table) {
            // Check and add missing columns
            if (!Schema::hasColumn('teacher_attendance', 'check_in_time')) {
                $table->time('check_in_time')->nullable()->after('day_of_week');
            }
            if (!Schema::hasColumn('teacher_attendance', 'check_out_time')) {
                $table->time('check_out_time')->nullable()->after('check_in_time');
            }
            if (!Schema::hasColumn('teacher_attendance', 'check_in_timestamp')) {
                $table->timestamp('check_in_timestamp')->nullable()->after('check_out_time');
            }
            if (!Schema::hasColumn('teacher_attendance', 'check_out_timestamp')) {
                $table->timestamp('check_out_timestamp')->nullable()->after('check_in_timestamp');
            }
            if (!Schema::hasColumn('teacher_attendance', 'working_hours_decimal')) {
                $table->decimal('working_hours_decimal', 5, 2)->nullable()->after('check_out_timestamp');
            }
            if (!Schema::hasColumn('teacher_attendance', 'leave_type')) {
                $table->string('leave_type', 50)->nullable()->after('status');
            }
            if (!Schema::hasColumn('teacher_attendance', 'remarks')) {
                $table->text('remarks')->nullable()->after('leave_type');
            }
            if (!Schema::hasColumn('teacher_attendance', 'location_lat')) {
                $table->decimal('location_lat', 10, 8)->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('teacher_attendance', 'location_lng')) {
                $table->decimal('location_lng', 11, 8)->nullable()->after('location_lat');
            }
            if (!Schema::hasColumn('teacher_attendance', 'device_info')) {
                $table->string('device_info', 100)->nullable()->after('location_lng');
            }
            if (!Schema::hasColumn('teacher_attendance', 'app_version')) {
                $table->string('app_version', 20)->nullable()->after('device_info');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_attendance', function (Blueprint $table) {
            $columns = [
                'check_in_time',
                'check_out_time',
                'check_in_timestamp',
                'check_out_timestamp',
                'working_hours_decimal',
                'leave_type',
                'remarks',
                'location_lat',
                'location_lng',
                'device_info',
                'app_version'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('teacher_attendance', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
