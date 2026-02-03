<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all 'late' status to 'submitted'
        DB::table('homework_submissions')
            ->where('status', 'late')
            ->update(['status' => 'submitted']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reliably reverse this migration
        // as we don't know which submissions were originally 'late'
    }
};
