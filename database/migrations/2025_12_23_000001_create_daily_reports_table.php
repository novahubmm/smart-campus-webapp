<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignUuid('teacher_profile_id')->constrained('teacher_profiles')->cascadeOnDelete();
            $table->foreignUuid('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->date('report_date');
            $table->enum('report_type', ['daily', 'weekly', 'incident', 'progress'])->default('daily');
            $table->text('summary')->nullable();
            $table->text('activities_completed')->nullable();
            $table->text('homework_assigned')->nullable();
            $table->text('student_behavior')->nullable();
            $table->text('concerns')->nullable();
            $table->text('notes')->nullable();
            $table->integer('students_present')->nullable();
            $table->integer('students_absent')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'acknowledged'])->default('submitted');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'teacher_profile_id', 'report_date', 'report_type'], 'daily_report_unique');
            $table->index(['report_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
