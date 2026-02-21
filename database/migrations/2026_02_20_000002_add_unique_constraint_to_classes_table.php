<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, remove duplicate classes (keep the oldest one for each grade_id + name combination)
        $duplicates = DB::table('classes')
            ->select('grade_id', 'name', DB::raw('MIN(id) as keep_id'))
            ->groupBy('grade_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Delete all classes with this grade_id and name except the one we want to keep
            DB::table('classes')
                ->where('grade_id', $duplicate->grade_id)
                ->where('name', $duplicate->name)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('classes', function (Blueprint $table) {
            // Add unique constraint: one class name per grade
            $table->unique(['grade_id', 'name'], 'unique_class_name_per_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique('unique_class_name_per_grade');
        });
    }
};
