<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('period_id')->nullable();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('remark')->nullable();
            $table->uuid('marked_by')->nullable();
            $table->time('collect_time')->nullable();

            $table->foreign('student_id')->references('id')->on('student_profiles')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('set null');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['student_id', 'date', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
    }
};
