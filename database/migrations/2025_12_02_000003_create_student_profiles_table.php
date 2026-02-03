<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->string('student_identifier')->nullable();
            $table->string('starting_grade_at_school')->nullable();
            $table->string('current_grade')->nullable();
            $table->string('current_class')->nullable();
            $table->string('guardian_teacher')->nullable();
            $table->string('assistant_teacher')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->string('gender')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('religious')->nullable();
            $table->string('nrc')->nullable();
            $table->date('dob')->nullable();
            $table->string('previous_school_name')->nullable();
            $table->string('previous_school_address')->nullable();
            $table->string('address')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_nrc')->nullable();
            $table->string('father_phone_no')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_nrc')->nullable();
            $table->string('mother_phone_no')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('emergency_contact_phone_no')->nullable();
            $table->string('in_school_relative_name')->nullable();
            $table->string('in_school_relative_grade')->nullable();
            $table->string('in_school_relative_relationship')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->string('medicine_allergy')->nullable();
            $table->string('food_allergy')->nullable();
            $table->text('medical_directory')->nullable();
            $table->string('photo_path')->nullable();
            $table->uuid('class_id')->nullable();
            $table->uuid('grade_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
