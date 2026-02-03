<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianFeeRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeeController extends Controller
{
    public function __construct(
        private readonly GuardianFeeRepositoryInterface $feeRepository
    ) {}

    /**
     * Get Pending Fee
     * GET /api/v1/guardian/fees/pending?student_id={id}
     */
    public function pending(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $fee = $this->feeRepository->getPendingFee($student);

            if (!$fee) {
                return ApiResponse::success(null, 'No pending fees');
            }

            return ApiResponse::success($fee, 'Pending fee retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve pending fee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Fee Details
     * GET /api/v1/guardian/fees/{fee_id}
     */
    public function show(Request $request, string $feeId): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $fee = $this->feeRepository->getFeeDetails($feeId, $student);

            if (!$fee) {
                return ApiResponse::error('Fee not found', 404);
            }

            return ApiResponse::success($fee, 'Fee details retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve fee details: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get All Fees
     * GET /api/v1/guardian/fees?student_id={id}&status={status}&page={page}&per_page={per_page}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $filters = [
                'status' => $request->input('status'),
                'academic_year' => $request->input('academic_year'),
                'per_page' => $request->input('per_page', 10),
            ];

            $fees = $this->feeRepository->getAllFees($student, $filters);

            return ApiResponse::success([
                'data' => $fees->items(),
                'meta' => [
                    'current_page' => $fees->currentPage(),
                    'per_page' => $fees->perPage(),
                    'total' => $fees->total(),
                    'last_page' => $fees->lastPage(),
                ],
            ], 'Fees retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve fees: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Initiate Payment
     * POST /api/v1/guardian/fees/{fee_id}/payment
     */
    public function initiatePayment(Request $request, string $feeId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|in:easy_pay,bank_transfer,cash',
                'amount' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation error', 400, $validator->errors()->toArray());
            }

            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $paymentData = $this->feeRepository->initiatePayment(
                $feeId,
                $student,
                $request->only(['payment_method', 'amount'])
            );

            return ApiResponse::success($paymentData, 'Payment initiated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to initiate payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Payment History
     * GET /api/v1/guardian/fees/payment-history?student_id={id}&status={status}&page={page}
     */
    public function paymentHistory(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $filters = [
                'status' => $request->input('status'),
                'per_page' => $request->input('per_page', 10),
            ];

            $payments = $this->feeRepository->getPaymentHistory($student, $filters);

            return ApiResponse::success([
                'data' => $payments->items(),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'last_page' => $payments->lastPage(),
                ],
            ], 'Payment history retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payment history: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper to get authorized student
     */
    private function getAuthorizedStudent(Request $request): ?StudentProfile
    {
        $studentId = $request->input('student_id');
        if (!$studentId) {
            return null;
        }

        $user = $request->user();
        $guardianProfile = $user->guardianProfile;

        if (!$guardianProfile) {
            return null;
        }

        // Check if the student belongs to this guardian
        $student = $guardianProfile->students()
            ->where('student_profiles.id', $studentId)
            ->with(['user'])
            ->first();

        return $student;
    }
}
