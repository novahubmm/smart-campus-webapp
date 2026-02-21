<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->string('fee_type')->default('Other')->after('description');
            $table->decimal('amount', 12, 2)->default(0)->after('fee_type');
            $table->date('due_date')->nullable()->after('amount');
            $table->boolean('partial_status')->default(false)->after('due_date');
            $table->boolean('discount_status')->default(false)->after('partial_status');
        });
    }

    public function down(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->dropColumn(['fee_type', 'amount', 'due_date', 'partial_status', 'discount_status']);
        });
    }
};
