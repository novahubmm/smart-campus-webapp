<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Mid-Term Exam - September 2025"
            $table->string('exam_id'); // e.g., "EX-100"
            $table->uuid('exam_type_id');
            $table->uuid('batch_id');
            $table->uuid('grade_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'completed', 'finished'])->default('upcoming');

            $table->foreign('exam_type_id')->references('id')->on('exam_types')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exam_id');
            $table->uuid('subject_id');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->uuid('room_id')->nullable();
            $table->decimal('total_marks', 5, 2)->default(100);
            $table->decimal('passing_marks', 5, 2)->default(40);

            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate exam schedules
            $table->unique(['exam_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exams');
    }
};
