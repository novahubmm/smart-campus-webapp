<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->string('start_month', 7)->nullable()->after('due_date'); // Format: YYYY-MM
            $table->string('end_month', 7)->nullable()->after('start_month'); // Format: YYYY-MM
        });
    }

    public function down(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->dropColumn(['start_month', 'end_month']);
        });
    }
};
