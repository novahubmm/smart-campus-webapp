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
        // First, remove duplicate grades (keep the oldest one for each batch_id + level combination)
        $duplicates = DB::table('grades')
            ->select('batch_id', 'level', DB::raw('MIN(id) as keep_id'))
            ->groupBy('batch_id', 'level')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Delete all grades with this batch_id and level except the one we want to keep
            DB::table('grades')
                ->where('batch_id', $duplicate->batch_id)
                ->where('level', $duplicate->level)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('grades', function (Blueprint $table) {
            // Add unique constraint: one grade level per batch
            $table->unique(['batch_id', 'level'], 'unique_grade_per_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique('unique_grade_per_batch');
        });
    }
};
