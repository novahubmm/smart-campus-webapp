<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianPaymentRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly GuardianPaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * Get Fee Structure
     * GET /api/v1/guardian/students/{student_id}/fees/structure
     */
    public function feeStructure(Request $request, string $studentId): JsonResponse
    {
        $request->validate([
            'academic_year' => 'nullable|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $academicYear = $request->input('academic_year');
            $feeStructure = $this->paymentRepository->getFeeStructure($student, $academicYear);

            return ApiResponse::success($feeStructure, 'Fee structure retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve fee structure: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Payment Methods
     * GET /api/v1/guardian/payment-methods
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|string|in:bank,mobile_wallet,all',
            'active_only' => 'nullable|boolean',
        ]);

        try {
            $type = $request->input('type', 'all');
            $activeOnly = $request->input('active_only', true);

            $methods = $this->paymentRepository->getPaymentMethods($type, $activeOnly);

            return ApiResponse::success($methods, 'Payment methods retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment methods: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Submit Payment
     * POST /api/v1/guardian/students/{student_id}/fees/payments
     */
    public function submitPayment(Request $request, string $studentId): JsonResponse
    {
        $request->validate([
            'fee_ids' => 'required|array|min:1',
            'fee_ids.*' => 'required|string',
            'payment_method_id' => 'required|string|exists:payment_methods,id',
            'payment_amount' => 'required|numeric|min:0',
            'payment_months' => 'required|integer|min:1|max:12',
            'payment_date' => 'required|date',
            'receipt_image' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $paymentData = [
                'fee_ids' => $request->input('fee_ids'),
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
            return ApiResponse::error('Failed to submit payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Payment Options
     * GET /api/v1/guardian/payment-options
     */
    public function paymentOptions(Request $request): JsonResponse
    {
        try {
            $options = $this->paymentRepository->getPaymentOptions();

            return ApiResponse::success($options, 'Payment options retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment options: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Payment History
     * GET /api/v1/guardian/students/{student_id}/fees/payment-history
     */
    public function paymentHistory(Request $request, string $studentId): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|string|in:pending,verified,rejected,all',
            'limit' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $status = $request->input('status');
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $history = $this->paymentRepository->getPaymentHistory($student, $status, $limit, $page);

            return ApiResponse::success($history, 'Payment history retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment history: ' . $e->getMessage(), 500);
        }
    }

    private function getAuthorizedStudent(Request $request, string $studentId): ?StudentProfile
    {
        $user = $request->user();
        $guardianProfile = $user->guardianProfile;

        if (!$guardianProfile) {
            return null;
        }

        return $guardianProfile->students()
            ->where('student_profiles.id', $studentId)
            ->with(['user', 'grade', 'classModel'])
            ->first();
    }
}
