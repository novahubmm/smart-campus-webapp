<?php

namespace App\Services\Finance;

use App\Models\FeeStructure;
use App\Models\FeeType;
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
     * Generate one-time fee invoices for all students in the target grade
     *
     * @param FeeStructure $fee The one-time fee structure
     * @return int Number of invoices created
     */
    public function generateOneTimeFeeInvoice(FeeStructure $fee): int
    {
        $invoicesCreated = 0;

        try {
            DB::beginTransaction();

            // Query all active students in the target grade
            $students = StudentProfile::where('status', 'active')
                ->where('grade_id', $fee->grade_id)
                ->with(['grade', 'user'])
                ->get();

            // Get next invoice sequence
            $invoiceCounter = $this->getNextInvoiceSequence();

            foreach ($students as $student) {
                try {
                    // Generate unique invoice number
                    $invoiceNumber = $this->generateInvoiceNumber($invoiceCounter);

                    // Calculate due date based on fee type's due_date_type
                    $dueDate = $this->calculateDueDate($fee->feeType);

                    // Set academic year
                    $academicYear = $fee->batch->name ?? now()->format('Y');

                    // Attempt to create invoice with retry logic for unique constraint
                    $attempt = 0;
                    $maxAttempts = 10;
                    $created = false;

                    while (!$created && $attempt < $maxAttempts) {
                        try {
                            $invoice = $this->invoiceRepo->create([
                                'invoice_number' => $invoiceNumber,
                                'student_id' => $student->id,
                                'fee_structure_id' => $fee->id,
                                'invoice_date' => now(),
                                'due_date' => $dueDate,
                                'month' => now()->format('Y-m'),
                                'academic_year' => $academicYear,
                                'subtotal' => $fee->amount,
                                'discount' => 0,
                                'total_amount' => $fee->amount,
                                'paid_amount' => 0,
                                'balance' => $fee->amount,
                                'status' => 'unpaid',
                                'created_by' => auth()->id(),
                            ]);

                            $invoicesCreated++;
                            $invoiceCounter++;

                            Log::info('One-time fee invoice created', [
                                'invoice_id' => $invoice->id,
                                'student_id' => $student->id,
                                'fee_structure_id' => $fee->id,
                                'amount' => $fee->amount,
                            ]);

                            $created = true;
                        } catch (\Exception $e) {
                            // If unique constraint on invoice_number, increment counter and retry
                            $attempt++;
                            $invoiceCounter++;
                            $invoiceNumber = $this->generateInvoiceNumber($invoiceCounter);

                            if ($attempt >= $maxAttempts) {
                                Log::error('Failed to create one-time fee invoice after retries', [
                                    'student_id' => $student->id,
                                    'fee_structure_id' => $fee->id,
                                    'error' => $e->getMessage(),
                                ]);
                                throw $e;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to generate one-time fee invoice for student', [
                        'student_id' => $student->id,
                        'student_name' => $student->user->name ?? 'Unknown',
                        'fee_structure_id' => $fee->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with next student instead of failing entire batch
                }
            }

            DB::commit();

            Log::info('One-time fee invoices generated', [
                'fee_structure_id' => $fee->id,
                'grade_id' => $fee->grade_id,
                'total_students' => $students->count(),
                'invoices_created' => $invoicesCreated,
            ]);

            return $invoicesCreated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate one-time fee invoices', [
                'fee_structure_id' => $fee->id,
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

        // Ensure grade monthly price is represented as a FeeStructure so it can be invoiced
        $grade = $student->grade()->first();
        if ($grade && ! empty($grade->price_per_month) && $grade->price_per_month > 0) {
            // Find or create a FeeType for tuition
            $tuitionType = FeeType::where('code', 'tuition')->orWhere('name', 'Tuition')->first();
            if (! $tuitionType) {
                $tuitionType = FeeType::create([
                    'name' => 'Tuition',
                    'code' => 'tuition',
                    'status' => true,
                    'is_mandatory' => true,
                ]);
            }

            // Ensure a monthly FeeStructure exists for this grade and tuition fee type
            $existingTuitionStructure = FeeStructure::where('grade_id', $grade->id)
                ->where('fee_type_id', $tuitionType->id)
                ->whereIn('frequency', ['monthly', 'month'])
                ->first();

            if (! $existingTuitionStructure) {
                FeeStructure::create([
                    'grade_id' => $grade->id,
                    'batch_id' => $grade->batch_id,
                    'fee_type_id' => $tuitionType->id,
                    'amount' => $grade->price_per_month,
                    'frequency' => 'monthly',
                    'status' => true,
                ]);
            }
        }

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

            // Calculate due date based on fee type's due_date_type
            $dueDate = $this->calculateDueDate($structure->feeType);

            // Create invoice
            $invoiceNumber = $this->generateInvoiceNumber($invoiceCounter);

            // Attempt to create invoice, retrying with incremented sequence if invoice_number collides
            $attempt = 0;
            $maxAttempts = 10;
            $created = false;
            while (! $created && $attempt < $maxAttempts) {
                try {
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

                    $created = true;
                } catch (\Exception $e) {
                    // If unique constraint on invoice_number, increment counter and retry
                    $attempt++;
                    $invoiceCounter++;
                    $invoiceNumber = $this->generateInvoiceNumber($invoiceCounter);
                    if ($attempt >= $maxAttempts) {
                        Log::error('Failed to create invoice after retries', [
                            'student_id' => $student->id,
                            'fee_structure_id' => $structure->id,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                }
            }
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

    /**
     * Calculate due date based on fee type's due_date_type
     */
    private function calculateDueDate(?FeeType $feeType): \Carbon\Carbon
    {
        if (!$feeType || !$feeType->due_date_type) {
            // Default to end of month if no fee type or due_date_type
            return now()->endOfMonth();
        }

        return match($feeType->due_date_type) {
            'end_of_month' => now()->endOfMonth(),
            'next_15_days' => now()->addDays(15),
            'today' => now(),
            default => now()->endOfMonth(),
        };
    }
}
