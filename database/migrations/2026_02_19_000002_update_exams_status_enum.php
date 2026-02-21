<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the column
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('exams', function (Blueprint $table) {
                $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming')->after('end_date');
            });
        } else {
            // For MySQL/PostgreSQL
            DB::statement("ALTER TABLE exams MODIFY COLUMN status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('exams', function (Blueprint $table) {
                $table->enum('status', ['upcoming', 'completed', 'finished'])->default('upcoming');
            });
        } else {
            DB::statement("ALTER TABLE exams MODIFY COLUMN status ENUM('upcoming', 'completed', 'finished') DEFAULT 'upcoming'");
        }
    }
};
