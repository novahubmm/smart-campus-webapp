<?php

namespace App\Console\Commands;

use App\Jobs\PaymentSystem\GenerateOneTimeFeeInvoicesJob;
use App\Models\PaymentSystem\FeeStructure;
use Illuminate\Console\Command;

class GenerateOneTimeFeeInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:generate-one-time-invoices {fee_id? : The ID of the fee structure}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for one-time fees';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $feeId = $this->argument('fee_id');

        if ($feeId) {
            // Generate invoices for specific fee
            $feeStructure = FeeStructure::find($feeId);

            if (!$feeStructure) {
                $this->error("Fee structure with ID {$feeId} not found.");
                return self::FAILURE;
            }

            if ($feeStructure->frequency !== 'one_time') {
                $this->error("Fee structure {$feeId} is not a one-time fee.");
                return self::FAILURE;
            }

            $this->info("Dispatching invoice generation job for fee: {$feeStructure->name}");
            GenerateOneTimeFeeInvoicesJob::dispatch($feeStructure);
            $this->info("Job dispatched successfully!");

            return self::SUCCESS;
        }

        // Generate invoices for all one-time fees
        $oneTimeFees = FeeStructure::where('frequency', 'one_time')
            ->where('is_active', true)
            ->get();

        if ($oneTimeFees->isEmpty()) {
            $this->info('No active one-time fees found.');
            return self::SUCCESS;
        }

        $this->info("Found {$oneTimeFees->count()} one-time fee(s).");

        foreach ($oneTimeFees as $fee) {
            $this->info("Dispatching job for: {$fee->name} (ID: {$fee->id})");
            GenerateOneTimeFeeInvoicesJob::dispatch($fee);
        }

        $this->info("All jobs dispatched successfully!");

        return self::SUCCESS;
    }
}
