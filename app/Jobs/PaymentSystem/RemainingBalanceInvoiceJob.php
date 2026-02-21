<?php

namespace App\Jobs\PaymentSystem;

use App\Models\PaymentSystem\Invoice;
use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RemainingBalanceInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The invoice to generate remaining balance for.
     */
    protected Invoice $invoice;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     * 
     * Generates a remaining balance invoice for partially paid invoices.
     * 
     * Validates: Requirements 14.1, 14.2
     */
    public function handle(InvoiceService $invoiceService): void
    {
        try {
            Log::info('Starting remaining balance invoice generation', [
                'invoice_id' => $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number,
                'remaining_amount' => $this->invoice->remaining_amount,
            ]);

            $remainingInvoice = $invoiceService->generateRemainingBalanceInvoice($this->invoice);

            Log::info('Remaining balance invoice generated successfully', [
                'parent_invoice_id' => $this->invoice->id,
                'new_invoice_id' => $remainingInvoice->id,
                'new_invoice_number' => $remainingInvoice->invoice_number,
                'amount' => $remainingInvoice->total_amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate remaining balance invoice', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
