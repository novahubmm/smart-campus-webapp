<?php

namespace Database\Seeders;

use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class MonthlyInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Generates monthly invoices for all active students using the same logic
     * as the MonthlyInvoiceGenerationJob.
     * 
     * @param string|null $month Month in Y-m format (e.g., '2026-03' for March 2026)
     */
    public function run(?string $month = null): void
    {
        $targetMonth = $month ? \Carbon\Carbon::parse($month . '-01') : now();
        $monthDisplay = $targetMonth->format('F Y');
        
        $this->command->info("🔄 Generating monthly invoices for {$monthDisplay}...");
        $this->command->newLine();

        try {
            $invoiceService = app(InvoiceService::class);
            
            // Generate invoices for specified month
            $invoicesCreated = $invoiceService->generateMonthlyInvoices($targetMonth);

            $this->command->newLine();
            $this->command->info("✅ Monthly invoice generation completed for {$monthDisplay}!");
            $this->command->info("📊 Invoices created: {$invoicesCreated}");
            
            if ($invoicesCreated === 0) {
                $this->command->warn('⚠️  No invoices were created. Possible reasons:');
                $this->command->warn('   - No active students found');
                $this->command->warn('   - Students have no grade assigned');
                $this->command->warn('   - No monthly fee structures configured');
                $this->command->warn("   - Invoices already exist for {$monthDisplay}");
            }

        } catch (\Exception $e) {
            $this->command->error('❌ Failed to generate monthly invoices');
            $this->command->error('Error: ' . $e->getMessage());
            
            Log::error('MonthlyInvoiceSeeder failed', [
                'month' => $monthDisplay,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}
