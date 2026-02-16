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
            // Check and add missing columns (SQLite compatible - no 'after' clause)
            if (!Schema::hasColumn('settings', 'school_code')) {
                $table->string('school_code')->nullable();
            }
            if (!Schema::hasColumn('settings', 'school_name_mm')) {
                $table->string('school_name_mm')->nullable();
            }
            if (!Schema::hasColumn('settings', 'established_year')) {
                $table->integer('established_year')->nullable();
            }
            if (!Schema::hasColumn('settings', 'motto')) {
                $table->string('motto')->nullable();
            }
            if (!Schema::hasColumn('settings', 'motto_mm')) {
                $table->string('motto_mm')->nullable();
            }
            if (!Schema::hasColumn('settings', 'social_facebook')) {
                $table->string('social_facebook')->nullable();
            }
            if (!Schema::hasColumn('settings', 'social_twitter')) {
                $table->string('social_twitter')->nullable();
            }
            if (!Schema::hasColumn('settings', 'social_instagram')) {
                $table->string('social_instagram')->nullable();
            }
            if (!Schema::hasColumn('settings', 'social_youtube')) {
                $table->string('social_youtube')->nullable();
            }
            if (!Schema::hasColumn('settings', 'social_linkedin')) {
                $table->string('social_linkedin')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'school_code',
                'school_name_mm',
                'established_year',
                'motto',
                'motto_mm',
                'social_facebook',
                'social_twitter',
                'social_instagram',
                'social_youtube',
                'social_linkedin',
            ]);
        });
    }
};
