<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'timetable_time_format')) {
                // '24h' or '12h'
                $table->string('timetable_time_format', 3)->default('24h')->after('week_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'timetable_time_format')) {
                $table->dropColumn('timetable_time_format');
            }
        });
    }
};
