<?php

namespace App\Console\Commands;

use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-monthly 
                            {--month= : Month in Y-m format (e.g., 2024-01)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for all active students';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceService $invoiceService): int
    {
        $month = $this->option('month') ? \Carbon\Carbon::parse($this->option('month') . '-01') : now();
        $monthDisplay = $month->format('F Y');

        $this->info("Generating monthly invoices for {$monthDisplay}...");
        $this->newLine();

        try {
            $invoicesCreated = $invoiceService->generateMonthlyInvoices($month);

            // Display results
            $this->info('Invoice Generation Complete!');
            $this->newLine();
            
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Invoices Created', $invoicesCreated],
                ]
            );

            $this->newLine();
            $this->info('✓ Invoice generation completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate invoices: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
