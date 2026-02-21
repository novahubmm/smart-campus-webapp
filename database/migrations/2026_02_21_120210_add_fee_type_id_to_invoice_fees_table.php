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
        Schema::table('invoice_fees', function (Blueprint $table) {
            $table->uuid('fee_type_id')->nullable()->after('fee_id');
            
            // Add foreign key constraint
            $table->foreign('fee_type_id')
                  ->references('id')
                  ->on('fee_types')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_fees', function (Blueprint $table) {
            $table->dropForeign(['fee_type_id']);
            $table->dropColumn('fee_type_id');
        });
    }
};
