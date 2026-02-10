<?php

namespace App\Services\Finance;

use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\StudentProfile;
use App\Repositories\Finance\InvoiceRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        private InvoiceRepository $invoiceRepo
    ) {}

    /**
     * Generate monthly invoices for all active students
     */
    public function generateMonthlyInvoices(?string $month = null, ?string $academicYear = null): array
    {
        $month = $month ?? now()->format('Y-m');
        $academicYear = $academicYear ?? now()->format('Y');
        
        $stats = [
            'total_students' => 0,
            'invoices_created' => 0,
            'invoices_skipped' => 0,
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            // Get all active students
            $students = StudentProfile::where('status', 'active')
                ->with(['grade', 'user'])
                ->get();

            $stats['total_students'] = $students->count();

            // Initialize invoice counter for unique numbering
            $invoiceCounter = $this->getNextInvoiceSequence();

            foreach ($students as $student) {
                try {
                    $result = $this->generateInvoicesForStudent($student, $month, $academicYear, $invoiceCounter);
                    $stats['invoices_created'] += $result['created'];
                    $stats['invoices_skipped'] += $result['skipped'];
                    $invoiceCounter = $result['next_counter'];
                } catch (\Exception $e) {
                    $stats['errors'][] = [
                        'student_id' => $student->id,
                        'student_name' => $student->user->name,
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Failed to generate invoice for student', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Monthly invoices generated', $stats);

            return $stats;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate monthly invoices', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate invoices for a specific student
     */
    public function generateInvoicesForStudent(
        StudentProfile $student,
        ?string $month = null,
        ?string $academicYear = null,
        int $invoiceCounter = 1
    ): array {
        $month = $month ?? now()->format('Y-m');
        $academicYear = $academicYear ?? now()->format('Y');
        
        $stats = [
            'created' => 0,
            'skipped' => 0,
            'next_counter' => $invoiceCounter,
        ];

        // Get active fee structures for this student's grade
        $feeStructures = FeeStructure::where('grade_id', $student->grade_id)
            ->where('status', true)
            ->with('feeType')
            ->get();

        foreach ($feeStructures as $structure) {
            // Check if invoice already exists
            if ($structure->frequency === 'one-time' || $structure->frequency === 'one_time') {
                // For one-time fees, check by academic year
                $exists = $this->invoiceRepo->invoiceExists(
                    $student->id,
                    $structure->id,
                    $academicYear,
                    'academic_year'
                );

                if ($exists) {
                    $stats['skipped']++;
                    continue;
                }
            } else {
                // For recurring fees, check by month
                $exists = $this->invoiceRepo->invoiceExists(
                    $student->id,
                    $structure->id,
                    $month,
                    'month'
                );

                if ($exists) {
                    $stats['skipped']++;
                    continue;
                }
            }

            // Create invoice
            $invoiceNumber = $this->generateInvoiceNumber($invoiceCounter);
            $dueDate = now()->addDays(30); // 30 days from now

            $invoice = $this->invoiceRepo->create([
                'invoice_number' => $invoiceNumber,
                'student_id' => $student->id,
                'fee_structure_id' => $structure->id,
                'invoice_date' => now(),
                'due_date' => $dueDate,
                'month' => $month,
                'academic_year' => $academicYear,
                'subtotal' => $structure->amount,
                'discount' => 0,
                'total_amount' => $structure->amount,
                'paid_amount' => 0,
                'balance' => $structure->amount,
                'status' => 'unpaid',
                'created_by' => auth()->id(),
            ]);

            $stats['created']++;
            $stats['next_counter'] = ++$invoiceCounter;

            Log::info('Invoice created', [
                'invoice_id' => $invoice->id,
                'student_id' => $student->id,
                'amount' => $structure->amount,
            ]);
        }

        return $stats;
    }

    /**
     * Get unpaid invoices for a student (for Guardian API)
     */
    public function getUnpaidInvoicesForStudent(int $studentId): Collection
    {
        return $this->invoiceRepo->getUnpaidForStudent($studentId);
    }

    /**
     * Get students with unpaid invoices (for admin table)
     */
    public function getStudentsWithUnpaidInvoices(array $filters = []): Collection
    {
        return $this->invoiceRepo->getStudentsWithUnpaidInvoices($filters);
    }

    /**
     * Get next invoice sequence number for today
     */
    private function getNextInvoiceSequence(): int
    {
        $lastInvoice = Invoice::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastInvoice && preg_match('/INV\d{8}-(\d{4})/', $lastInvoice->invoice_number, $matches)) {
            return intval($matches[1]) + 1;
        }
        
        return 1;
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(int $sequence = null): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        
        if ($sequence === null) {
            $sequence = $this->getNextInvoiceSequence();
        }
        
        return sprintf('%s%s-%04d', $prefix, $date, $sequence);
    }
}
