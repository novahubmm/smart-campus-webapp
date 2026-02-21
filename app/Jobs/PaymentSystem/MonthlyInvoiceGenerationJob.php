<?php

namespace App\Jobs\PaymentSystem;

use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MonthlyInvoiceGenerationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * 
     * Generates monthly invoices for all active students.
     * This job should be scheduled to run on the 1st of each month.
     * 
     * Validates: Requirement 3.1
     */
    public function handle(InvoiceService $invoiceService): void
    {
        try {
            Log::info('Starting monthly invoice generation');

            $invoicesCreated = $invoiceService->generateMonthlyInvoices();

            Log::info('Monthly invoice generation completed', [
                'invoices_created' => $invoicesCreated,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate monthly invoices', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
