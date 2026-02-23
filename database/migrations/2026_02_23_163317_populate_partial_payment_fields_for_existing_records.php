<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Populate new fields for existing payroll records
        DB::table('payrolls')->update([
            'total_amount' => DB::raw('amount'),
            'paid_amount' => DB::raw("CASE WHEN status = 'paid' THEN amount ELSE 0 END"),
            'payment_count' => DB::raw("CASE WHEN status = 'paid' THEN 1 ELSE 0 END"),
        ]);
    }

    public function down(): void
    {
        // Reset fields to defaults
        DB::table('payrolls')->update([
            'total_amount' => 0,
            'paid_amount' => 0,
            'payment_count' => 0,
        ]);
    }
};
