<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('guardian_id');
            $table->string('title');
            $table->text('content');
            $table->string('category')->default('general'); // academic, behavior, health, etc.
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('guardian_id')->references('id')->on('guardian_profiles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_notes');
    }
};
