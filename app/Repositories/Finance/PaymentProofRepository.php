<?php

namespace App\Repositories\Finance;

use App\Models\PaymentProof;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaymentProofRepository
{
    /**
     * Get pending payment proofs with filters
     */
    public function getPendingProofs(
        ?string $month = null,
        ?int $gradeId = null,
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = PaymentProof::where('status', 'pending_verification')
            ->with(['student.user', 'student.grade', 'student.classModel', 'paymentMethod']);

        // Apply month filter
        if ($month) {
            $monthStart = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $query->whereBetween('payment_date', [$monthStart, $monthEnd]);
        }

        // Apply grade filter
        if ($gradeId) {
            $query->whereHas('student', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            });
        }

        // Apply search filter
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get verified payment proofs with filters
     */
    public function getVerifiedProofs(
        ?string $month = null,
        ?int $gradeId = null,
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = PaymentProof::where('status', 'verified')
            ->with(['student.user', 'student.grade', 'student.classModel', 'paymentMethod', 'verifiedBy']);

        // Apply month filter
        if ($month) {
            $monthStart = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $query->whereBetween('payment_date', [$monthStart, $monthEnd]);
        }

        // Apply grade filter
        if ($gradeId) {
            $query->whereHas('student', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            });
        }

        // Apply search filter
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('verified_at', 'desc')->paginate($perPage);
    }

    /**
     * Get payment proof details by ID
     */
    public function getProofDetails(string $paymentProofId): ?PaymentProof
    {
        return PaymentProof::with([
            'student.user',
            'student.grade',
            'student.classModel',
            'paymentMethod',
            'verifiedBy'
        ])->find($paymentProofId);
    }

    /**
     * Update payment proof status
     */
    public function updateProofStatus(string $paymentProofId, array $data): bool
    {
        return PaymentProof::where('id', $paymentProofId)->update($data);
    }

    /**
     * Get payment proof by ID
     */
    public function find(string $id): ?PaymentProof
    {
        return PaymentProof::find($id);
    }

    /**
     * Create a new payment proof
     */
    public function create(array $data): PaymentProof
    {
        return PaymentProof::create($data);
    }

    /**
     * Get payment proofs for a student
     */
    public function getProofsForStudent(int $studentId, ?string $status = null): Collection
    {
        $query = PaymentProof::where('student_id', $studentId)
            ->with(['paymentMethod']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
