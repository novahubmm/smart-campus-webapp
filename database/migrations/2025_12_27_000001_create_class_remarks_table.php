<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_remarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignUuid('period_id')->nullable()->constrained('periods')->nullOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teacher_profiles')->cascadeOnDelete();
            $table->date('date');
            $table->text('remark');
            $table->enum('type', ['note', 'positive', 'concern'])->default('note');
            $table->timestamps();

            $table->index(['class_id', 'date']);
            $table->index(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_remarks');
    }
};
