<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('student_id')->nullable()->after('user_id');
        });

        // Generate student IDs for existing students
        $students = DB::table('student_profiles')->orderBy('created_at')->get();
        $counter = 1;
        
        foreach ($students as $student) {
            $studentId = 'STU-' . date('Y') . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            DB::table('student_profiles')
                ->where('id', $student->id)
                ->update(['student_id' => $studentId]);
            $counter++;
        }

        // Make it non-nullable after populating
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('student_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('student_id');
        });
    }
};
