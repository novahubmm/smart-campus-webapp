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
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');

            // Organizational
            $table->string('employee_id')->nullable();
            $table->string('position')->nullable();
            $table->uuid('department_id')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('basic_salary', 12, 2)->nullable();

            // Personal
            $table->string('gender')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('religious')->nullable();
            $table->string('nrc')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('active');

            // Academic
            $table->string('current_grades')->nullable();
            $table->string('current_classes')->nullable();
            $table->string('subjects_taught')->nullable();
            $table->string('responsible_class')->nullable();
            $table->string('previous_school')->nullable();

            // Education/experience
            $table->string('qualification')->nullable();
            $table->unsignedInteger('previous_experience_years')->nullable();
            $table->string('green_card')->nullable();

            // Family
            $table->string('father_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('emergency_contact')->nullable();

            // Marital
            $table->string('marital_status')->nullable();
            $table->string('partner_name')->nullable();
            $table->string('partner_phone')->nullable();

            // Medical
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('medicine_allergy')->nullable();
            $table->string('food_allergy')->nullable();
            $table->text('medical_directory')->nullable();

            $table->string('photo_path')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
