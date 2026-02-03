<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->uuid('guardian_profile_id');
            $table->uuid('student_profile_id');
            $table->string('relationship')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(['guardian_profile_id', 'student_profile_id']);
            $table->foreign('guardian_profile_id')->references('id')->on('guardian_profiles')->cascadeOnDelete();
            $table->foreign('student_profile_id')->references('id')->on('student_profiles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};

