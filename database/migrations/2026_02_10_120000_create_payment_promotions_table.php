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
        Schema::create('payment_promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('months')->unique()->comment('Number of months (1, 2, 3, 6, 12)');
            $table->decimal('discount_percent', 5, 2)->default(0)->comment('Discount percentage');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default promotion values
        DB::table('payment_promotions')->insert([
            ['id' => Str::uuid(), 'months' => 1, 'discount_percent' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'months' => 2, 'discount_percent' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'months' => 3, 'discount_percent' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'months' => 6, 'discount_percent' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'months' => 12, 'discount_percent' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_promotions');
    }
};
