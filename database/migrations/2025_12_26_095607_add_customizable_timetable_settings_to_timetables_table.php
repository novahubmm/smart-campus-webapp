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
        Schema::table('timetables', function (Blueprint $table) {
            // Add customizable timetable settings
            if (!Schema::hasColumn('timetables', 'number_of_periods_per_day')) {
                $table->integer('number_of_periods_per_day')->nullable()->after('week_days');
            }
            if (!Schema::hasColumn('timetables', 'custom_period_times')) {
                $table->json('custom_period_times')->nullable()->after('number_of_periods_per_day');
            }
            if (!Schema::hasColumn('timetables', 'use_custom_settings')) {
                $table->boolean('use_custom_settings')->default(false)->after('custom_period_times');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            if (Schema::hasColumn('timetables', 'number_of_periods_per_day')) {
                $table->dropColumn('number_of_periods_per_day');
            }
            if (Schema::hasColumn('timetables', 'custom_period_times')) {
                $table->dropColumn('custom_period_times');
            }
            if (Schema::hasColumn('timetables', 'use_custom_settings')) {
                $table->dropColumn('use_custom_settings');
            }
        });
    }
};