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
        Schema::table('classes', function (Blueprint $table) {
            // Check if columns already exist (in case of re-running migration)
            if (!Schema::hasColumn('classes', 'male_class_leader_id')) {
                // Add new columns for dual leaders
                $table->uuid('male_class_leader_id')->nullable()->after('teacher_id');
                $table->uuid('female_class_leader_id')->nullable()->after('male_class_leader_id');
                
                // Add foreign key constraints
                $table->foreign('male_class_leader_id')
                      ->references('id')
                      ->on('student_profiles')
                      ->onDelete('set null');
                      
                $table->foreign('female_class_leader_id')
                      ->references('id')
                      ->on('student_profiles')
                      ->onDelete('set null');
            }
        });
        
        // Migrate existing data: copy male leaders from old column if it exists
        // Note: This assumes there might be a class_leader_id column from previous implementation
        // If the column doesn't exist, this will be skipped
        if (Schema::hasColumn('classes', 'class_leader_id')) {
            // Get database driver
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'sqlite') {
                // SQLite-compatible syntax
                DB::statement("
                    UPDATE classes
                    SET male_class_leader_id = class_leader_id
                    WHERE class_leader_id IN (
                        SELECT id FROM student_profiles WHERE LOWER(gender) = 'male'
                    )
                ");
                
                DB::statement("
                    UPDATE classes
                    SET female_class_leader_id = class_leader_id
                    WHERE class_leader_id IN (
                        SELECT id FROM student_profiles WHERE LOWER(gender) = 'female'
                    )
                ");
            } else {
                // MySQL-compatible syntax
                DB::statement("
                    UPDATE classes c
                    INNER JOIN student_profiles s ON c.class_leader_id = s.id
                    SET c.male_class_leader_id = c.class_leader_id
                    WHERE LOWER(s.gender) = 'male'
                ");
                
                DB::statement("
                    UPDATE classes c
                    INNER JOIN student_profiles s ON c.class_leader_id = s.id
                    SET c.female_class_leader_id = c.class_leader_id
                    WHERE LOWER(s.gender) = 'female'
                ");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['male_class_leader_id']);
            $table->dropForeign(['female_class_leader_id']);
            $table->dropColumn(['male_class_leader_id', 'female_class_leader_id']);
        });
    }
};
