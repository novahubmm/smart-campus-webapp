<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('class_id');
            $table->uuid('subject_id');
            $table->uuid('teacher_id');
            $table->uuid('period_id')->nullable();
            $table->date('assigned_date');
            $table->date('due_date');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teacher_profiles')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('set null');
        });

        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('homework_id');
            $table->uuid('student_id');
            $table->text('content')->nullable();
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'submitted', 'late', 'graded'])->default('pending');
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->uuid('graded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('homework_id')->references('id')->on('homework')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('student_profiles')->onDelete('cascade');
            $table->foreign('graded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework');
    }
};
