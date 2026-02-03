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
        Schema::table('timetables', function (Blueprint $table) {
            // Drop all indexes that include the status column
            // First drop the composite index from performance indexes migration
            try {
                $table->dropIndex('timetables_class_active_status_idx');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
            
            // Then drop the simple index from versions migration
            try {
                $table->dropIndex(['class_id', 'status']);
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
            
            // Now we can safely drop the status column
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('name');
            // Recreate the index
            $table->index(['class_id', 'is_active', 'status'], 'timetables_class_active_status_idx');
        });
    }
};
