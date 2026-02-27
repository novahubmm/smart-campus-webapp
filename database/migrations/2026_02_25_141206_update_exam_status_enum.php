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
        // Add 'results' status
        // SQLite doesn't support ALTER COLUMN for ENUM
        // The original migration has: upcoming, ongoing, completed, finished
        // We'll update 'finished' to 'results' in existing data
        DB::table('exams')->where('status', 'finished')->update(['status' => 'results']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('exams')->where('status', 'results')->update(['status' => 'finished']);
    }
};
