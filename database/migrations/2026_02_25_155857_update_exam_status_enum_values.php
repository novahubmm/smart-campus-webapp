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
        // SQLite doesn't support ALTER COLUMN for ENUM
        // We need to recreate the table with the new enum values
        
        // Step 1: Create a temporary table with the new structure
        Schema::create('exams_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('exam_id');
            $table->uuid('exam_type_id');
            $table->uuid('batch_id');
            $table->uuid('grade_id')->nullable();
            $table->uuid('class_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'results'])->default('upcoming');
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Step 2: Copy data from old table to temp table with status mapping
        $exams = DB::table('exams')->get();
        foreach ($exams as $exam) {
            // Map old status values to new ones
            $status = $exam->status;
            if ($status === 'finished') {
                $status = 'results';
            } elseif (!in_array($status, ['upcoming', 'ongoing', 'completed', 'results'])) {
                $status = 'upcoming'; // Default for any unknown status
            }
            
            DB::table('exams_temp')->insert([
                'id' => $exam->id,
                'name' => $exam->name,
                'exam_id' => $exam->exam_id,
                'exam_type_id' => $exam->exam_type_id,
                'batch_id' => $exam->batch_id,
                'grade_id' => $exam->grade_id ?? null,
                'class_id' => $exam->class_id ?? null,
                'start_date' => $exam->start_date,
                'end_date' => $exam->end_date,
                'status' => $status,
                'created_at' => $exam->created_at,
                'updated_at' => $exam->updated_at,
                'deleted_at' => $exam->deleted_at ?? null,
            ]);
        }
        
        // Step 3: Drop the old table
        Schema::dropIfExists('exams');
        
        // Step 4: Rename temp table to original name
        Schema::rename('exams_temp', 'exams');
        
        // Step 5: Recreate foreign keys
        Schema::table('exams', function (Blueprint $table) {
            $table->foreign('exam_type_id')->references('id')->on('exam_types')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate with old enum values
        Schema::create('exams_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('exam_id');
            $table->uuid('exam_type_id');
            $table->uuid('batch_id');
            $table->uuid('grade_id')->nullable();
            $table->uuid('class_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'completed', 'finished'])->default('upcoming');
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Copy data back
        DB::statement('INSERT INTO exams_temp SELECT * FROM exams');
        
        // Drop and rename
        Schema::dropIfExists('exams');
        Schema::rename('exams_temp', 'exams');
        
        // Recreate foreign keys
        Schema::table('exams', function (Blueprint $table) {
            $table->foreign('exam_type_id')->references('id')->on('exam_types')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
        });
    }
};
