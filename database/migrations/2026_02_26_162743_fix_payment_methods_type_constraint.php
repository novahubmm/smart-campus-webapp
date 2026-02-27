<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table without the enum constraint
        // because SQLite's CHECK constraint doesn't work well with parameter binding
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Disable foreign key checks
            DB::statement('PRAGMA foreign_keys = OFF');
            
            // Create a temporary table
            Schema::create('payment_methods_temp', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('name_mm')->nullable();
                $table->string('type')->default('bank'); // Changed from enum to string
                $table->string('account_number');
                $table->string('account_name');
                $table->string('account_name_mm')->nullable();
                $table->string('logo_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('instructions')->nullable();
                $table->text('instructions_mm')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                
                $table->index('type');
                $table->index('is_active');
                $table->index('sort_order');
            });

            // Copy data from old table to new table
            DB::statement('INSERT INTO payment_methods_temp SELECT * FROM payment_methods');

            // Drop old table
            Schema::dropIfExists('payment_methods');

            // Rename temp table to original name
            Schema::rename('payment_methods_temp', 'payment_methods');
            
            // Re-enable foreign key checks
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    public function down(): void
    {
        // Revert back to enum if needed
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::create('payment_methods_temp', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('name_mm')->nullable();
                $table->enum('type', ['bank', 'mobile_wallet', 'other'])->default('bank');
                $table->string('account_number');
                $table->string('account_name');
                $table->string('account_name_mm')->nullable();
                $table->string('logo_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('instructions')->nullable();
                $table->text('instructions_mm')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                
                $table->index('type');
                $table->index('is_active');
                $table->index('sort_order');
            });

            DB::statement('INSERT INTO payment_methods_temp SELECT * FROM payment_methods');
            Schema::dropIfExists('payment_methods');
            Schema::rename('payment_methods_temp', 'payment_methods');
        }
    }
};
