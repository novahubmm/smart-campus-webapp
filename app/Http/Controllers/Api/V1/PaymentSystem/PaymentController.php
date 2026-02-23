<?php

namespace App\Http\Controllers\Api\V1\PaymentSystem;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentSystem\SubmitPaymentRequest;
use App\Models\PaymentSystem\Payment;
use App\Services\PaymentSystem\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    /**
     * Submit a payment for an invoice.
     * 
     * POST /api/v1/students/{studentId}/payments/submit
     * 
     * Validates request, processes payment submission, and returns payment details.
     * 
     * Validates: Requirements 9.1, 9.8
     *
     * @param SubmitPaymentRequest $request
     * @param string $studentId
     * @return JsonResponse
     */
    public function store(SubmitPaymentRequest $request, string $studentId): JsonResponse
    {
        try {
            // Add student_id to validated data if not present
            $data = $request->validated();
            if (!isset($data['student_id'])) {
                $data['student_id'] = $studentId;
            }
            
            // Submit the payment
            $payment = $this->paymentService->submitPayment($data);

            // Format response
            $paymentData = [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'invoice_id' => $payment->invoice_id,
                'payment_method_id' => $payment->payment_method_id,
                'payment_amount' => $payment->payment_amount,
                'payment_type' => $payment->payment_type,
                'payment_months' => $payment->payment_months,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'receipt_image_url' => $payment->receipt_image_url,
                'status' => $payment->status,
                'notes' => $payment->notes,
                'created_at' => $payment->created_at->toISOString(),
                'fee_details' => $payment->feeDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'fee_name' => $detail->fee_name,
                        'fee_name_mm' => $detail->fee_name_mm,
                        'full_amount' => $detail->full_amount,
                        'paid_amount' => $detail->paid_amount,
                        'is_partial' => $detail->is_partial,
                        'payment_months' => $detail->payment_months ?? 1,
                    ];
                }),
            ];

            return ApiResponse::success([
                'payment' => $paymentData,
                'message' => 'Payment submitted successfully. Your payment is pending verification.',
            ], 'Payment submitted successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to submit payment', [
                'student_id' => $studentId,
                'request_data' => $request->except(['receipt_image']), // Don't log large base64 image
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to submit payment: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get payment history for a student.
     * 
     * GET /api/v1/payment-system/students/{studentId}/payments/history
     * 
     * Returns paginated payment history with optional status filter,
     * ordered by payment_date descending.
     * 
     * Validates: Requirements 12.1, 12.2, 12.3, 12.4, 12.5, 12.6
     *
     * @param Request $request
     * @param string $studentId
     * @return JsonResponse
     */
    public function history(Request $request, string $studentId): JsonResponse
    {
        try {
            // Validate query parameters
            $validated = $request->validate([
                'status' => 'nullable|in:pending_verification,verified,rejected,all',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            // Build query
            $query = Payment::where('student_id', $studentId)
                ->with(['feeDetails', 'invoice', 'paymentMethod'])
                ->orderBy('payment_date', 'desc');

            // Apply status filter if provided (skip if 'all')
            if (isset($validated['status']) && $validated['status'] !== 'all') {
                $query->where('status', $validated['status']);
            }

            // Paginate results
            $perPage = $validated['per_page'] ?? 15;
            $payments = $query->paginate($perPage);

            // Format response
            $paymentsData = $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'invoice_number' => $payment->invoice->invoice_number,
                    'payment_method' => [
                        'id' => $payment->paymentMethod->id,
                        'name' => $payment->paymentMethod->name,
                        'name_mm' => $payment->paymentMethod->name_mm,
                        'type' => $payment->paymentMethod->type,
                    ],
                    'payment_amount' => $payment->payment_amount,
                    'payment_type' => $payment->payment_type,
                    'payment_months' => $payment->payment_months,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'receipt_image_url' => $payment->receipt_image_url,
                    'status' => $payment->status,
                    'verified_at' => $payment->verified_at?->toISOString(),
                    'rejection_reason' => $payment->rejection_reason,
                    'notes' => $payment->notes,
                    'created_at' => $payment->created_at->toISOString(),
                    'fee_breakdown' => $payment->feeDetails->map(function ($detail) {
                        return [
                            'fee_name' => $detail->fee_name,
                            'fee_name_mm' => $detail->fee_name_mm,
                            'full_amount' => $detail->full_amount,
                            'paid_amount' => $detail->paid_amount,
                            'is_partial' => $detail->is_partial,
                            'payment_months' => $detail->payment_months ?? 1,
                        ];
                    }),
                ];
            });

            return ApiResponse::success([
                'payments' => $paymentsData,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'last_page' => $payments->lastPage(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                ],
            ], 'Payment history retrieved successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment history', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to retrieve payment history: ' . $e->getMessage(),
                500
            );
        }
    }
}
