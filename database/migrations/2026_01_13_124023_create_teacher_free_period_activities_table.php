<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_free_period_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_profile_id')->constrained('teacher_profiles')->cascadeOnDelete();
            $table->foreignUuid('activity_type_id')->constrained('free_period_activity_types')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index for efficient queries
            $table->index(['teacher_profile_id', 'date']);
            
            // Prevent same activity type for same teacher at same time slot
            $table->unique(['teacher_profile_id', 'date', 'start_time', 'activity_type_id'], 'unique_teacher_activity_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_free_period_activities');
    }
};
