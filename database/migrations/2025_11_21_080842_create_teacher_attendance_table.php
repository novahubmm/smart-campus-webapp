<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('teacher_id');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'leave'])->default('present');
            $table->text('remark')->nullable();
            $table->uuid('marked_by')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->foreign('teacher_id')->references('id')->on('teacher_profiles')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance');
    }
};
