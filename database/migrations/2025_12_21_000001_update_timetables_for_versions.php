<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            // Drop unique constraint on class_id to allow multiple versions
            $table->dropUnique(['class_id']);
            
            // Add is_active column to track which version is currently active
            $table->boolean('is_active')->default(false)->after('status');
            
            // Add version_name for user-friendly naming
            $table->string('version_name')->nullable()->after('name');
            
            // Add published_at timestamp
            $table->timestamp('published_at')->nullable()->after('is_active');
            
            // Add index for faster queries
            $table->index(['class_id', 'is_active']);
            $table->index(['class_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            // Try to drop indexes if they exist
            try {
                $table->dropIndex(['class_id', 'is_active']);
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
            
            try {
                $table->dropIndex(['class_id', 'status']);
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
            
            $table->dropColumn(['is_active', 'version_name', 'published_at']);
            $table->unique('class_id');
        });
    }
};
