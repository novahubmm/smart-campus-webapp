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
            // Social Media Links
            $table->string('social_facebook')->nullable()->after('school_website');
            $table->string('social_twitter')->nullable()->after('social_facebook');
            $table->string('social_instagram')->nullable()->after('social_twitter');
            $table->string('social_youtube')->nullable()->after('social_instagram');
            $table->string('social_linkedin')->nullable()->after('social_youtube');
            
            // School Info
            $table->string('school_code')->nullable()->after('school_name');
            $table->string('school_name_mm')->nullable()->after('school_code');
            $table->integer('established_year')->nullable()->after('school_name_mm');
            $table->string('motto')->nullable()->after('established_year');
            $table->string('motto_mm')->nullable()->after('motto');
            
            // About Info
            $table->text('school_about_us_mm')->nullable()->after('school_about_us');
            $table->text('vision')->nullable()->after('school_about_us_mm');
            $table->text('vision_mm')->nullable()->after('vision');
            $table->text('mission')->nullable()->after('vision_mm');
            $table->text('mission_mm')->nullable()->after('mission');
            $table->json('values')->nullable()->after('mission_mm');
            $table->json('values_mm')->nullable()->after('values');
            
            // Statistics
            $table->decimal('pass_rate', 5, 2)->nullable()->after('values_mm');
            $table->decimal('average_attendance', 5, 2)->nullable()->after('pass_rate');
            
            // Accreditations (JSON array)
            $table->json('accreditations')->nullable()->after('average_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'social_facebook',
                'social_twitter',
                'social_instagram',
                'social_youtube',
                'social_linkedin',
                'school_code',
                'school_name_mm',
                'established_year',
                'motto',
                'motto_mm',
                'school_about_us_mm',
                'vision',
                'vision_mm',
                'mission',
                'mission_mm',
                'values',
                'values_mm',
                'pass_rate',
                'average_attendance',
                'accreditations',
            ]);
        });
    }
};
