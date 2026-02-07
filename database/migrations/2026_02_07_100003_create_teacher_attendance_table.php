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
        Schema::create('teacher_attendance', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->uuid('teacher_id');
            $table->date('date');
            $table->string('day_of_week', 20)->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->timestamp('check_in_timestamp')->nullable();
            $table->timestamp('check_out_timestamp')->nullable();
            $table->decimal('working_hours_decimal', 5, 2)->nullable();
            $table->enum('status', ['present', 'absent', 'leave', 'half_day'])->default('present');
            $table->string('leave_type', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_lng', 11, 8)->nullable();
            $table->string('device_info', 100)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->timestamps();
            
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['teacher_id', 'date']);
            $table->index(['teacher_id', 'date']);
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance');
    }
};
