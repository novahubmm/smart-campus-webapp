<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkOverdueFees extends Command
{
    protected $signature = 'fees:mark-overdue';

    protected $description = 'Mark unpaid invoices as overdue when past due date';

    public function handle(): int
    {
        $this->info('🔍 Checking for overdue invoices...');

        $today = Carbon::today();

        // Find invoices that are past due date and not paid
        $overdueInvoices = Invoice::whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', $today)
            ->where('balance', '>', 0)
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('✅ No overdue invoices found.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'overdue']);
            $count++;
        }

        $this->info("✅ Marked {$count} invoice(s) as overdue.");

        return self::SUCCESS;
    }
}
