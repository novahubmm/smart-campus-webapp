<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_marks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exam_id');
            $table->uuid('student_id');
            $table->uuid('subject_id');
            $table->decimal('marks_obtained', 6, 2)->nullable();
            $table->decimal('total_marks', 6, 2)->default(100);
            $table->decimal('percentage', 6, 2)->nullable();
            $table->string('grade', 50)->nullable();
            $table->string('remark')->nullable();
            $table->uuid('entered_by')->nullable();
            $table->boolean('is_absent')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->foreign('entered_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_marks');
    }
};
