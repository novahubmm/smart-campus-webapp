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
        // Curriculum chapters (Table of Contents for subjects)
        Schema::create('curriculum_chapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subject_id');
            $table->uuid('grade_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->integer('estimated_hours')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');
        });

        // Curriculum topics (sub-items under chapters)
        Schema::create('curriculum_topics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('chapter_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->integer('order')->default(0);
            $table->integer('estimated_minutes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('chapter_id')->references('id')->on('curriculum_chapters')->onDelete('cascade');
        });

        // Curriculum progress tracking for teachers
        Schema::create('curriculum_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('topic_id');
            $table->uuid('class_id');
            $table->uuid('teacher_id');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('topic_id')->references('id')->on('curriculum_topics')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teacher_profiles')->onDelete('cascade');

            $table->unique(['topic_id', 'class_id', 'teacher_id'], 'curriculum_progress_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_progress');
        Schema::dropIfExists('curriculum_topics');
        Schema::dropIfExists('curriculum_chapters');
    }
};
