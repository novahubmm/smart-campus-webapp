<?php

namespace App\Http\Controllers\Api\V1\PaymentSystem;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    /**
     * Get all active payment methods.
     * 
     * GET /api/v1/payment-methods
     * 
     * Returns all active payment methods, optionally filtered by type,
     * ordered by sort_order.
     * 
     * Query Parameters:
     * - type: Filter by type (bank, mobile_wallet, all)
     * - active_only: Filter only active methods (true/false)
     * - exclude_cash: Exclude cash payment method for mobile apps (true/false, default: true)
     * 
     * Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get optional filters
            $type = $request->input('type');
            $excludeCash = $request->input('exclude_cash', 'true') === 'true'; // Default: exclude cash for mobile

            // Build query for active payment methods
            $query = PaymentMethod::active()->ordered();

            // Apply type filter if provided and not "all"
            if ($type && $type !== 'all') {
                $query->byType($type);
            }

            // Exclude cash for mobile apps (account_number = 'N/A')
            if ($excludeCash) {
                $query->where('account_number', '!=', 'N/A');
            }

            // Get payment methods
            $paymentMethods = $query->get();

            // Format response
            $data = $paymentMethods->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'name_mm' => $method->name_mm,
                    'type' => $method->type,
                    'account_number' => $method->account_number !== 'N/A' ? $method->account_number : null,
                    'account_name' => $method->account_name,
                    'account_name_mm' => $method->account_name_mm,
                    'logo_url' => $method->logo_url,
                    'instructions' => $method->instructions,
                    'instructions_mm' => $method->instructions_mm,
                    'sort_order' => $method->sort_order,
                    'is_cash' => $method->account_number === 'N/A', // Helper flag for frontend
                ];
            });

            return ApiResponse::success([
                'payment_methods' => $data,
                'total' => $data->count(),
            ], 'Payment methods retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment methods', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to retrieve payment methods: ' . $e->getMessage(),
                500
            );
        }
    }
}
