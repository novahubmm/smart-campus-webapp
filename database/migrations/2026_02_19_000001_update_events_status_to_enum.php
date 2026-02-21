<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('events', function (Blueprint $table) {
                $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming')->after('banner_image');
            });
        } else {
            // For MySQL/PostgreSQL
            DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('events', function (Blueprint $table) {
                $table->boolean('status')->default(true);
            });
        } else {
            DB::statement("ALTER TABLE events MODIFY COLUMN status BOOLEAN DEFAULT TRUE");
        }
    }
};
