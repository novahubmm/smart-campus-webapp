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
        Schema::create('free_period_activities', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->uuid('teacher_id');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['teacher_id', 'date']);
            $table->index('date');
            $table->index(['teacher_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('free_period_activities');
    }
};
