<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('guardian_id');
            $table->string('type'); // gpa, attendance, rank, subject, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('guardian_id')->references('id')->on('guardian_profiles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_goals');
    }
};
