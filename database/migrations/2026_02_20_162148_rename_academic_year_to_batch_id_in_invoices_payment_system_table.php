<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices_payment_system', function (Blueprint $table) {
            // Add batch_id column
            $table->uuid('batch_id')->nullable()->after('student_id');
        });
        
        // Populate batch_id from student's grade's batch
        DB::statement('
            UPDATE invoices_payment_system 
            SET batch_id = (
                SELECT grades.batch_id 
                FROM student_profiles 
                JOIN grades ON grades.id = student_profiles.grade_id
                WHERE student_profiles.id = invoices_payment_system.student_id
            )
        ');
        
        Schema::table('invoices_payment_system', function (Blueprint $table) {
            // Drop the old academic_year column
            $table->dropColumn('academic_year');
            
            // Add foreign key constraint
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('invoices_payment_system', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['batch_id']);
            
            // Add back academic_year column as nullable first
            $table->string('academic_year', 20)->nullable()->after('student_id');
            
            // Drop batch_id column
            $table->dropColumn('batch_id');
        });
    }
};
