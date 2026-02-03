<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('timetable_id');
            $table->string('day_of_week', 10);
            $table->unsignedTinyInteger('period_number');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('is_break')->default(false);
            $table->uuid('subject_id')->nullable();
            $table->uuid('teacher_profile_id')->nullable();
            $table->uuid('room_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['timetable_id', 'day_of_week', 'period_number']);

            $table->foreign('timetable_id')->references('id')->on('timetables')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
            $table->foreign('teacher_profile_id')->references('id')->on('teacher_profiles')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
