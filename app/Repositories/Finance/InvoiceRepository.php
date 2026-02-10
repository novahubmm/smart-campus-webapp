<?php

namespace App\Repositories\Finance;

use App\Models\Invoice;
use App\Models\StudentProfile;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceRepository
{
    /**
     * Get unpaid invoices for a specific student
     */
    public function getUnpaidForStudent(string $studentId): Collection
    {
        return Invoice::where('student_id', $studentId)
            ->where('status', 'unpaid')
            ->with(['feeStructure.feeType', 'student'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get students with unpaid invoices with filters
     */
    public function getStudentsWithUnpaidInvoices(
        ?string $month = null,
        ?int $gradeId = null,
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = StudentProfile::query()
            ->with(['user', 'grade', 'classModel'])
            ->where('status', 'active')
            ->whereHas('invoices', function ($q) use ($month) {
                $q->where('status', 'unpaid');
                if ($month) {
                    $q->where('month', $month);
                }
            });

        // Apply grade filter
        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('student_identifier')->paginate($perPage);
    }

    /**
     * Update invoice status
     */
    public function updateInvoiceStatus(string $invoiceId, string $status): bool
    {
        return Invoice::where('id', $invoiceId)->update(['status' => $status]);
    }

    /**
     * Mark multiple invoices as paid
     */
    public function markInvoicesAsPaid(array $invoiceIds, $paymentDate): int
    {
        return Invoice::whereIn('id', $invoiceIds)
            ->update([
                'status' => 'paid',
                'paid_amount' => DB::raw('total_amount'),
                'balance' => 0,
                'payment_date' => $paymentDate,
            ]);
    }

    /**
     * Get invoices by IDs
     */
    public function getInvoicesByIds(array $invoiceIds): Collection
    {
        return Invoice::whereIn('id', $invoiceIds)
            ->with(['student', 'feeStructure.feeType'])
            ->get();
    }

    /**
     * Get invoices for a student in a specific month
     */
    public function getInvoicesForStudentMonth(string $studentId, string $month): Collection
    {
        return Invoice::where('student_id', $studentId)
            ->where('month', $month)
            ->with(['feeStructure.feeType'])
            ->get();
    }

    /**
     * Check if invoice exists for student and fee structure in a month or academic year
     */
    public function invoiceExists(string $studentId, string $feeStructureId, string $periodValue, string $periodType = 'month'): bool
    {
        $query = Invoice::where('student_id', $studentId)
            ->where('fee_structure_id', $feeStructureId);
        
        if ($periodType === 'academic_year') {
            $query->where('academic_year', $periodValue);
        } else {
            $query->where('month', $periodValue);
        }
        
        return $query->exists();
    }

    /**
     * Create a new invoice
     */
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    /**
     * Get total unpaid amount for a student
     */
    public function getTotalUnpaidAmount(int $studentId): float
    {
        return Invoice::where('student_id', $studentId)
            ->where('status', 'unpaid')
            ->sum('balance');
    }
}
