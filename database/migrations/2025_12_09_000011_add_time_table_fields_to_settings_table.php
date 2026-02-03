<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'number_of_periods_per_day')) {
                $table->unsignedTinyInteger('number_of_periods_per_day')->nullable();
            }
            if (!Schema::hasColumn('settings', 'minute_per_period')) {
                $table->unsignedSmallInteger('minute_per_period')->nullable();
            }
            if (!Schema::hasColumn('settings', 'break_duration')) {
                $table->unsignedSmallInteger('break_duration')->nullable();
            }
            if (!Schema::hasColumn('settings', 'school_start_time')) {
                $table->time('school_start_time')->nullable();
            }
            if (!Schema::hasColumn('settings', 'school_end_time')) {
                $table->time('school_end_time')->nullable();
            }
            if (!Schema::hasColumn('settings', 'week_days')) {
                $table->json('week_days')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'number_of_periods_per_day')) {
                $table->dropColumn('number_of_periods_per_day');
            }
            if (Schema::hasColumn('settings', 'minute_per_period')) {
                $table->dropColumn('minute_per_period');
            }
            if (Schema::hasColumn('settings', 'break_duration')) {
                $table->dropColumn('break_duration');
            }
            if (Schema::hasColumn('settings', 'school_start_time')) {
                $table->dropColumn('school_start_time');
            }
            if (Schema::hasColumn('settings', 'school_end_time')) {
                $table->dropColumn('school_end_time');
            }
            if (Schema::hasColumn('settings', 'week_days')) {
                $table->dropColumn('week_days');
            }
        });
    }
};
