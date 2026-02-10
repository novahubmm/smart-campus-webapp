<?php

namespace App\Console\Commands;

use App\Services\Finance\InvoiceService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-monthly 
                            {--month= : Month in Y-m format (e.g., 2024-01)}
                            {--academic-year= : Academic year (e.g., 2024)}';

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
        $month = $this->option('month') ?? now()->format('Y-m');
        $academicYear = $this->option('academic-year') ?? now()->format('Y');

        $this->info("Generating monthly invoices for {$month} (Academic Year: {$academicYear})...");
        $this->newLine();

        try {
            $stats = $invoiceService->generateMonthlyInvoices($month, $academicYear);

            // Display results
            $this->info('Invoice Generation Complete!');
            $this->newLine();
            
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Students', $stats['total_students']],
                    ['Invoices Created', $stats['invoices_created']],
                    ['Invoices Skipped', $stats['invoices_skipped']],
                    ['Errors', count($stats['errors'])],
                ]
            );

            // Display errors if any
            if (count($stats['errors']) > 0) {
                $this->newLine();
                $this->error('Errors encountered:');
                $this->table(
                    ['Student ID', 'Student Name', 'Error'],
                    array_map(function ($error) {
                        return [
                            $error['student_id'],
                            $error['student_name'],
                            $error['error'],
                        ];
                    }, $stats['errors'])
                );
            }

            $this->newLine();
            $this->info('✓ Invoice generation completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate invoices: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
