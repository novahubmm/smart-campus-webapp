<?php

namespace App\Http\Controllers\Api\V1\PaymentSystem;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PaymentPromotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentOptionController extends Controller
{
    /**
     * Get all active payment promotions (payment options).
     * 
     * GET /api/v1/payment-system/payment-options?student_id={student_id}
     * 
     * Returns active payment promotions filtered by remaining months in batch.
     * If student_id is provided, calculates remaining months dynamically.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $studentId = request('student_id');
            
            if ($studentId) {
                // Get student with batch to calculate remaining months
                $student = \App\Models\StudentProfile::with('batch')->find($studentId);
                
                if ($student && $student->batch && $student->batch->end_date) {
                    // Calculate remaining months using same logic as frontend
                    $endDate = \Carbon\Carbon::parse($student->batch->end_date);
                    $now = \Carbon\Carbon::now();
                    
                    // Calculate difference in months
                    $months = ($endDate->year - $now->year) * 12;
                    $months += $endDate->month - $now->month;
                    
                    // Add 1 if we're not at the end of current month
                    if ($endDate->day >= $now->day) {
                        $months += 1;
                    }
                    
                    $remainingMonths = max(1, $months);
                    
                    // Get filtered options based on remaining months
                    $promotions = PaymentPromotion::getAvailableOptions($remainingMonths);
                } else {
                    // Fallback: assume 10 months remaining (matches frontend fallback)
                    $remainingMonths = 10;
                    $promotions = PaymentPromotion::getAvailableOptions($remainingMonths);
                }
            } else {
                // No student specified: return all active promotions
                $promotions = PaymentPromotion::getAllActive();
            }

            // Format the response data
            $options = $promotions->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'months' => $promotion->months,
                    'discount_percent' => (float) $promotion->discount_percent,
                    'is_active' => $promotion->is_active,
                ];
            });

            return ApiResponse::success([
                'payment_options' => $options,
            ], 'Payment options retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to retrieve payment options: ' . $e->getMessage(),
                500
            );
        }
    }
}
