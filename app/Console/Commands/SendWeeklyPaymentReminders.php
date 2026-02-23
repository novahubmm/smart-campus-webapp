<?php

namespace App\Console\Commands;

use App\Models\StudentProfile;
use App\Models\Invoice;
use App\Services\GuardianNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendWeeklyPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:send-weekly-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly payment reminders to guardians for unpaid invoices';

    protected $guardianNotificationService;

    public function __construct(GuardianNotificationService $guardianNotificationService)
    {
        parent::__construct();
        $this->guardianNotificationService = $guardianNotificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting weekly payment reminders...');
        Log::info('Weekly payment reminders started');

        // Get all unpaid or partially paid invoices
        $unpaidInvoices = Invoice::whereIn('status', ['unpaid', 'partially_paid'])
            ->with(['student.user', 'student.guardians'])
            ->get();

        $sentCount = 0;
        $errorCount = 0;

        foreach ($unpaidInvoices as $invoice) {
            try {
                $student = $invoice->student;
                
                if (!$student) {
                    $this->warn("Invoice {$invoice->id} has no student");
                    continue;
                }

                // Calculate outstanding amount
                $outstandingAmount = $invoice->total_amount - $invoice->paid_amount;

                if ($outstandingAmount <= 0) {
                    continue;
                }

                // Send reminder
                $this->guardianNotificationService->sendPaymentReminder(
                    $student->id,
                    $student->user?->name ?? 'Student',
                    $outstandingAmount,
                    $invoice->due_date?->format('Y-m-d') ?? 'N/A'
                );

                $sentCount++;
                $this->info("Sent reminder for student: {$student->user?->name}");
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("Failed to send reminder for invoice {$invoice->id}: {$e->getMessage()}");
                Log::error('Failed to send payment reminder', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Payment reminders completed!");
        $this->info("Sent: {$sentCount}, Errors: {$errorCount}");
        Log::info('Weekly payment reminders completed', [
            'sent' => $sentCount,
            'errors' => $errorCount,
        ]);

        return Command::SUCCESS;
    }
}
