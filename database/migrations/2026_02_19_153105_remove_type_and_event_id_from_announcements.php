<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Add location field
            if (!Schema::hasColumn('announcements', 'location')) {
                $table->string('location')->nullable()->after('priority');
            }
            
            // Drop foreign key and event_id column if it exists
            if (Schema::hasColumn('announcements', 'event_id')) {
                $table->dropForeign(['event_id']);
                $table->dropColumn('event_id');
            }
            
            // Drop type column if it exists
            if (Schema::hasColumn('announcements', 'type')) {
                $table->dropColumn('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->enum('type', ['general', 'urgent', 'event', 'academic', 'holiday'])->default('general')->after('content');
            $table->uuid('event_id')->nullable()->after('location');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');
            $table->dropColumn('location');
        });
    }
};
