<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Expense;
use App\Models\PaymentMethod;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the Cash payment method
        $cashPaymentMethod = PaymentMethod::where('name', 'Cash')->first();
        
        if ($cashPaymentMethod) {
            // Update all salary expenses that have null payment_method_id
            Expense::whereNull('payment_method_id')
                ->where('title', 'like', 'Salary Payment -%')
                ->update(['payment_method_id' => $cashPaymentMethod->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set payment_method_id back to null for salary expenses
        Expense::where('title', 'like', 'Salary Payment -%')
            ->update(['payment_method_id' => null]);
    }
};
