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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_name')->nullable();
            $table->string('school_email')->nullable();
            $table->string('school_phone')->nullable();
            $table->text('school_address')->nullable();
            $table->string('school_website')->nullable();
            $table->text('school_about_us')->nullable();
            $table->string('school_logo_path')->nullable();
            $table->string('school_short_logo_path')->nullable();
            $table->string('principal_name')->nullable();
            $table->boolean('setup_completed_academic')->default(false);
            $table->boolean('setup_completed_event_and_announcements')->default(false);
            $table->boolean('setup_completed_time_table_and_attendance')->default(false);
            $table->boolean('setup_completed_finance')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
