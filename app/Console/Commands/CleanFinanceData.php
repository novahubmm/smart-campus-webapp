<?php

namespace App\Console\Commands;

use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\PaymentMethod;
use App\Models\PaymentProof;
use App\Models\PaymentPromotion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanFinanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:clean 
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all finance data (invoices, payments, fee structures, etc.) for fresh testing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will DELETE ALL finance data. Are you sure?', false)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('Cleaning finance data...');
        $this->newLine();

        try {
            DB::beginTransaction();

            $stats = [
                'payment_items' => 0,
                'payments' => 0,
                'payment_proofs' => 0,
                'invoice_items' => 0,
                'invoices' => 0,
                'fee_structures' => 0,
                'fee_types' => 0,
                'payment_methods' => 0,
                'payment_promotions' => 0,
                'notification_logs' => 0,
                'grade_fees_reset' => 0,
            ];

            // 1. Delete payment items
            $this->info('Deleting payment items...');
            $stats['payment_items'] = PaymentItem::count();
            PaymentItem::query()->delete();

            // 2. Delete payments
            $this->info('Deleting payments...');
            $stats['payments'] = Payment::count();
            Payment::query()->delete();

            // 3. Delete payment proofs
            $this->info('Deleting payment proofs...');
            $stats['payment_proofs'] = PaymentProof::count();
            PaymentProof::query()->delete();

            // 4. Delete invoice items
            $this->info('Deleting invoice items...');
            $stats['invoice_items'] = InvoiceItem::count();
            InvoiceItem::query()->delete();

            // 5. Delete invoices
            $this->info('Deleting invoices...');
            $stats['invoices'] = Invoice::count();
            Invoice::query()->delete();

            // 6. Delete fee structures
            $this->info('Deleting fee structures...');
            $stats['fee_structures'] = FeeStructure::count();
            FeeStructure::query()->delete();

            // 7. Delete fee types
            $this->info('Deleting fee types...');
            $stats['fee_types'] = FeeType::count();
            FeeType::query()->delete();

            // 8. Delete payment methods
            $this->info('Deleting payment methods...');
            $stats['payment_methods'] = PaymentMethod::count();
            PaymentMethod::query()->delete();

            // 9. Delete payment promotions
            $this->info('Deleting payment promotions...');
            $stats['payment_promotions'] = PaymentPromotion::count();
            PaymentPromotion::query()->delete();

            // 10. Delete notification logs
            $this->info('Deleting notification logs...');
            $stats['notification_logs'] = NotificationLog::count();
            NotificationLog::query()->delete();

            // 11. Reset grade price_per_month to 0
            $this->info('Resetting grade fees...');
            $stats['grade_fees_reset'] = Grade::where('price_per_month', '>', 0)->count();
            Grade::query()->update(['price_per_month' => 0]);

            DB::commit();

            $this->newLine();
            $this->info('✓ Finance data cleaned successfully!');
            $this->newLine();

            // Display statistics
            $this->table(
                ['Item', 'Deleted Count'],
                [
                    ['Payment Items', $stats['payment_items']],
                    ['Payments', $stats['payments']],
                    ['Payment Proofs', $stats['payment_proofs']],
                    ['Invoice Items', $stats['invoice_items']],
                    ['Invoices', $stats['invoices']],
                    ['Fee Structures', $stats['fee_structures']],
                    ['Fee Types', $stats['fee_types']],
                    ['Payment Methods', $stats['payment_methods']],
                    ['Payment Promotions', $stats['payment_promotions']],
                    ['Notification Logs', $stats['notification_logs']],
                    ['Grade Fees Reset', $stats['grade_fees_reset']],
                ]
            );

            $this->newLine();
            $this->info('You can now set up fresh finance data:');
            $this->line('1. Set grade fees in Academic Management');
            $this->line('2. Create fee types and structures in Finance > Fee Structure');
            $this->line('3. Generate invoices: php artisan invoices:generate-monthly');
            $this->line('4. Or seed test data: php artisan db:seed --class=PaymentProofSeeder');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to clean finance data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
