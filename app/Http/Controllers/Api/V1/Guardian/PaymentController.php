<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianPaymentRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct(
        private readonly GuardianPaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * Get Fee Structure
     * GET /api/v1/guardian/students/{student_id}/fees/structure
     * 
     * @param Request $request
     * @param string $studentId
     * @return JsonResponse
     */
    public function feeStructure(Request $request, string $studentId): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404, [
                    'error_code' => 'STUDENT_NOT_FOUND'
                ]);
            }

            $academicYear = $request->input('academic_year');
            $feeStructure = $this->paymentRepository->getFeeStructure($student, $academicYear);

            return ApiResponse::success($feeStructure, 'Fee structure retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve fee structure: ' . $e->getMessage(), 500, [
                'error_code' => 'SERVER_ERROR'
            ]);
        }
    }

    /**
     * Get Payment Methods
     * GET /api/v1/guardian/payment-methods
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'all');
            $activeOnly = $request->input('active_only', true);

            // Convert string to boolean
            if (is_string($activeOnly)) {
                $activeOnly = filter_var($activeOnly, FILTER_VALIDATE_BOOLEAN);
            }

            $paymentMethods = $this->paymentRepository->getPaymentMethods($type, $activeOnly);

            return ApiResponse::success($paymentMethods, 'Payment methods retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment methods: ' . $e->getMessage(), 500, [
                'error_code' => 'SERVER_ERROR'
            ]);
        }
    }

    /**
     * Submit Payment
     * POST /api/v1/guardian/students/{student_id}/fees/payments
     * 
     * @param Request $request
     * @param string $studentId
     * @return JsonResponse
     */
    public function submitPayment(Request $request, string $studentId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'invoice_ids' => 'required|array|min:1',
                'invoice_ids.*' => 'required|string',
                'payment_method_id' => 'required|string|exists:payment_methods,id',
                'payment_amount' => 'required|numeric|min:0',
                'payment_months' => 'required|integer|min:1|max:12',
                'payment_date' => 'required|date|date_format:Y-m-d',
                'receipt_image' => 'required|string',
                'notes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation error', 422, [
                    'error_code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()->toArray()
                ]);
            }

            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404, [
                    'error_code' => 'STUDENT_NOT_FOUND'
                ]);
            }

            // Validate invoice IDs belong to this student
            $invoiceIds = $request->input('invoice_ids');
            $validInvoices = \App\Models\Invoice::where('student_id', $student->id)
                ->whereIn('id', $invoiceIds)
                ->where('status', 'unpaid')
                ->pluck('id')
                ->toArray();

            if (count($validInvoices) !== count($invoiceIds)) {
                $invalidInvoices = array_diff($invoiceIds, $validInvoices);
                return ApiResponse::error('Invalid invoice IDs provided', 400, [
                    'error_code' => 'INVALID_INVOICE_IDS',
                    'invalid_invoices' => array_values($invalidInvoices),
                    'message' => 'Some invoice IDs do not belong to this student, do not exist, or are already paid'
                ]);
            }

            $paymentData = [
                'invoice_ids' => $invoiceIds,
                'payment_method_id' => $request->input('payment_method_id'),
                'payment_amount' => $request->input('payment_amount'),
                'payment_months' => $request->input('payment_months'),
                'payment_date' => $request->input('payment_date'),
                'receipt_image' => $request->input('receipt_image'),
                'notes' => $request->input('notes'),
            ];

            $result = $this->paymentRepository->submitPayment($student, $paymentData);

            return ApiResponse::success($result, 'Payment submitted successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to submit payment: ' . $e->getMessage(), 500, [
                'error_code' => 'SERVER_ERROR',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Payment Options
     * GET /api/v1/guardian/payment-options
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentOptions(Request $request): JsonResponse
    {
        try {
            $paymentOptions = $this->paymentRepository->getPaymentOptions();

            return ApiResponse::success($paymentOptions, 'Payment options retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment options: ' . $e->getMessage(), 500, [
                'error_code' => 'SERVER_ERROR'
            ]);
        }
    }

    /**
     * Get Payment History
     * GET /api/v1/guardian/students/{student_id}/fees/payment-history
     * 
     * @param Request $request
     * @param string $studentId
     * @return JsonResponse
     */
    public function paymentHistory(Request $request, string $studentId): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404, [
                    'error_code' => 'STUDENT_NOT_FOUND'
                ]);
            }

            $status = $request->input('status');
            $limit = (int) $request->input('limit', 10);
            $page = (int) $request->input('page', 1);

            // Validate limit
            if ($limit > 50) {
                $limit = 50;
            }

            $paymentHistory = $this->paymentRepository->getPaymentHistory($student, $status, $limit, $page);

            return ApiResponse::success($paymentHistory, 'Payment history retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment history: ' . $e->getMessage(), 500, [
                'error_code' => 'SERVER_ERROR'
            ]);
        }
    }

    /**
     * Get Payment Proof Submissions
     * GET /api/v1/guardian/students/{student_id}/fees/payment-proofs
     * 
     * @param Request $request
     * @param string $studentId
     * @return JsonResponse
     */
    public function paymentProofs(Request $request, string $studentId): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404, [
                    'error_code' => 'STUDENT_NOT_FOUND'
                ]);
            }

            $status = $request->input('status'); // pending_verification, verified, rejected
            $limit = (int) $request->input('limit', 10);

            $query = \App\Models\PaymentProof::where('student_id', $student->id)
                ->with('paymentMethod');

            if ($status) {
                $query->where('status', $status);
            }

            $proofs = $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($proof) {
                    return [
                        'id' => $proof->id,
                        'payment_amount' => $proof->payment_amount,
                        'payment_date' => $proof->payment_date->format('Y-m-d'),
                        'payment_method' => $proof->paymentMethod?->name,
                        'status' => $proof->status,
                        'status_label' => match($proof->status) {
                            'pending_verification' => 'Pending Verification',
                            'verified' => 'Approved',
                            'rejected' => 'Rejected',
                            default => 'Unknown'
                        },
                        'submitted_at' => $proof->created_at->format('Y-m-d H:i:s'),
                        'verified_at' => $proof->verified_at?->format('Y-m-d H:i:s'),
                        'rejection_reason' => $proof->rejection_reason,
                        'notes' => $proof->notes,
                    ];
                });

            return ApiResponse::success([
                'proofs' => $proofs,
                'total' => $proofs->count(),
            ], 'Payment proofs retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment proofs: ' . $e->getMessage(), 500, [
                'error_code' => 'SERVER_ERROR'
            ]);
        }
    }

    /**
     * Get Payment Proof Detail
     * GET /api/v1/guardian/students/{student_id}/fees/receipts/{payment_proof_id}
     * 
     * @param Request $request
     * @param string $studentId
     * @param string $paymentProofId
     * @return JsonResponse
     */
    public function paymentProofDetail(Request $request, string $studentId, string $paymentProofId): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404, [
                    'error_code' => 'STUDENT_NOT_FOUND'
                ]);
            }

            $paymentProofDetail = $this->paymentRepository->getPaymentProofDetail($paymentProofId, $student);

            if (!$paymentProofDetail) {
                return ApiResponse::error('Payment proof not found', 404, [
                    'error_code' => 'PAYMENT_PROOF_NOT_FOUND'
                ]);
            }

            return ApiResponse::success($paymentProofDetail, 'Payment proof detail retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment proof detail: ' . $e->getMessage(), 500, [
                'error_code' => 'SERVER_ERROR'
            ]);
        }
    }

    /**
     * Helper to get authorized student
     * 
     * @param Request $request
     * @param string $studentId
     * @return StudentProfile|null
     */
    private function getAuthorizedStudent(Request $request, string $studentId): ?StudentProfile
    {
        $user = $request->user();
        $guardianProfile = $user->guardianProfile;

        if (!$guardianProfile) {
            return null;
        }

        // Check if the student belongs to this guardian
        $student = $guardianProfile->students()
            ->where('student_profiles.id', $studentId)
            ->with(['user', 'grade', 'classModel'])
            ->first();

        return $student;
    }
}
