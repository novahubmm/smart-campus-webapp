<?php

namespace App\Http\Controllers\Api\V1\PaymentSystem;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PaymentSystem\Payment;
use App\Services\PaymentSystem\PaymentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly PaymentVerificationService $verificationService
    ) {}

    /**
     * Verify a payment submission.
     * 
     * POST /api/v1/payment-system/admin/payments/{paymentId}/verify
     * 
     * Updates payment status to 'verified', records verification timestamp and admin user.
     * Sends notification to guardian about payment verification.
     * 
     * Validates: Requirements 13.2, 13.3, 13.4
     *
     * @param Request $request
     * @param string $paymentId
     * @return JsonResponse
     */
    public function verify(Request $request, string $paymentId): JsonResponse
    {
        try {
            // Find the payment
            $payment = Payment::findOrFail($paymentId);

            // Check if payment is already verified or rejected
            if ($payment->isVerified()) {
                return ApiResponse::error('Payment is already verified', 422);
            }

            if ($payment->isRejected()) {
                return ApiResponse::error('Cannot verify a rejected payment', 422);
            }

            // Get authenticated admin user
            $admin = $request->user();

            // Verify the payment
            $this->verificationService->verifyPayment($payment, $admin);

            // Reload payment to get updated data
            $payment->refresh();

            return ApiResponse::success([
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'status' => $payment->status,
                    'verified_at' => $payment->verified_at?->toISOString(),
                    'verified_by' => $payment->verified_by,
                ],
            ], 'Payment verified successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Payment not found', 404);
        } catch (\Exception $e) {
            Log::error('Failed to verify payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to verify payment: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Reject a payment submission.
     * 
     * POST /api/v1/payment-system/admin/payments/{paymentId}/reject
     * 
     * Updates payment status to 'rejected', stores rejection reason, and rolls back
     * all invoice amount updates. Sends notification to guardian with rejection reason.
     * 
     * Validates: Requirements 13.5, 13.6, 13.7, 13.8, 13.9, 13.10
     *
     * @param Request $request
     * @param string $paymentId
     * @return JsonResponse
     */
    public function reject(Request $request, string $paymentId): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validate([
                'reason' => 'required|string|min:10|max:500',
            ]);

            // Find the payment
            $payment = Payment::findOrFail($paymentId);

            // Check if payment is already verified or rejected
            if ($payment->isVerified()) {
                return ApiResponse::error('Cannot reject a verified payment', 422);
            }

            if ($payment->isRejected()) {
                return ApiResponse::error('Payment is already rejected', 422);
            }

            // Get authenticated admin user
            $admin = $request->user();

            // Reject the payment
            $this->verificationService->rejectPayment($payment, $validated['reason'], $admin);

            // Reload payment to get updated data
            $payment->refresh();

            return ApiResponse::success([
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'status' => $payment->status,
                    'rejection_reason' => $payment->rejection_reason,
                    'verified_at' => $payment->verified_at?->toISOString(),
                    'verified_by' => $payment->verified_by,
                ],
            ], 'Payment rejected successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Payment not found', 404);
        } catch (\Exception $e) {
            Log::error('Failed to reject payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to reject payment: ' . $e->getMessage(),
                500
            );
        }
    }
}
