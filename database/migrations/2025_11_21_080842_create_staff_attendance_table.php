<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('staff_id');
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half-day', 'on-leave'])->default('present');
            $table->text('remark')->nullable();
            $table->uuid('marked_by')->nullable();

            $table->foreign('staff_id')->references('id')->on('staff_profiles')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['staff_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};
