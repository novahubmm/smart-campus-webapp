<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table to change enum values
        if (Schema::hasTable('period_switch_requests')) {
            // Update any 'approved' status to 'accepted'
            DB::table('period_switch_requests')
                ->where('status', 'approved')
                ->update(['status' => 'accepted']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('period_switch_requests')) {
            DB::table('period_switch_requests')
                ->where('status', 'accepted')
                ->update(['status' => 'approved']);
        }
    }
};
