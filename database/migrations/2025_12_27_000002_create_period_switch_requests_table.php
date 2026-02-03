<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_switch_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->constrained('periods')->cascadeOnDelete();
            $table->foreignUuid('from_teacher_id')->constrained('teacher_profiles')->cascadeOnDelete();
            $table->foreignUuid('to_teacher_id')->constrained('teacher_profiles')->cascadeOnDelete();
            $table->date('date');
            $table->text('reason')->nullable();
            $table->string('to_subject')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_switch_requests');
    }
};
