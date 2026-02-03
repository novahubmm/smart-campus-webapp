<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Change publish_date from date to datetime
            $table->datetime('publish_date')->change();
            
            // Remove expiry_date column
            $table->dropColumn('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Revert publish_date back to date
            $table->date('publish_date')->change();
            
            // Add back expiry_date column
            $table->date('expiry_date')->nullable()->after('publish_date');
        });
    }
};