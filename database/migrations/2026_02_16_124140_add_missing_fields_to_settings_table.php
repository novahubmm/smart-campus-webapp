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
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('settings', 'school_about_us_mm')) {
                $table->text('school_about_us_mm')->nullable()->after('school_about_us');
            }
            if (!Schema::hasColumn('settings', 'pass_rate')) {
                $table->decimal('pass_rate', 5, 2)->nullable()->after('values_mm');
            }
            if (!Schema::hasColumn('settings', 'average_attendance')) {
                $table->decimal('average_attendance', 5, 2)->nullable()->after('pass_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['school_about_us_mm', 'pass_rate', 'average_attendance']);
        });
    }
};
