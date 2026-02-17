<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            // Rename current_grade and current_class to previous_grade and previous_class
            $table->renameColumn('current_grade', 'previous_grade');
            $table->renameColumn('current_class', 'previous_class');
            
            // Add new parent fields
            $table->string('father_religious')->nullable()->after('father_nrc');
            $table->string('father_address')->nullable()->after('father_occupation');
            $table->string('mother_religious')->nullable()->after('mother_nrc');
            $table->string('mother_address')->nullable()->after('mother_occupation');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            // Rename back
            $table->renameColumn('previous_grade', 'current_grade');
            $table->renameColumn('previous_class', 'current_class');
            
            // Drop new fields
            $table->dropColumn([
                'father_religious',
                'father_address',
                'mother_religious',
                'mother_address'
            ]);
        });
    }
};
