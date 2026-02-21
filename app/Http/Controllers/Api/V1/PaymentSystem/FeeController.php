<?php

namespace App\Http\Controllers\Api\V1\PaymentSystem;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentSystem\CreateFeeRequest;
use App\Jobs\PaymentSystem\GenerateOneTimeFeeInvoicesJob;
use App\Services\PaymentSystem\FeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FeeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly FeeService $feeService
    ) {}

    /**
     * Create a new fee category.
     * 
     * POST /api/v1/payment-system/fees
     * 
     * Validates request, creates fee category, and dispatches job to generate
     * invoices for one-time fees.
     * 
     * Validates: Requirements 1.1, 1.6, 1.7, 2.1
     *
     * @param CreateFeeRequest $request
     * @return JsonResponse
     */
    public function store(CreateFeeRequest $request): JsonResponse
    {
        try {
            // Create the fee category
            $feeStructure = $this->feeService->createFeeCategory($request->validated());

            // If one-time fee, dispatch job to generate invoices
            if ($feeStructure->isOneTime()) {
                GenerateOneTimeFeeInvoicesJob::dispatch($feeStructure);
                
                Log::info('One-time fee created, invoice generation job dispatched', [
                    'fee_structure_id' => $feeStructure->id,
                    'grade' => $feeStructure->grade,
                    'batch' => $feeStructure->batch,
                ]);
            }

            return ApiResponse::success([
                'fee' => [
                    'id' => $feeStructure->id,
                    'name' => $feeStructure->name,
                    'name_mm' => $feeStructure->name_mm,
                    'description' => $feeStructure->description,
                    'description_mm' => $feeStructure->description_mm,
                    'amount' => $feeStructure->amount,
                    'frequency' => $feeStructure->frequency,
                    'fee_type' => $feeStructure->fee_type,
                    'grade' => $feeStructure->grade,
                    'batch' => $feeStructure->batch,
                    'target_month' => $feeStructure->target_month,
                    'due_date' => $feeStructure->due_date->format('Y-m-d'),
                    'supports_payment_period' => $feeStructure->supports_payment_period,
                    'is_active' => $feeStructure->is_active,
                    'created_at' => $feeStructure->created_at->toISOString(),
                ],
                'message' => $feeStructure->isOneTime() 
                    ? 'Fee category created successfully. Invoices are being generated for students.'
                    : 'Fee category created successfully.',
            ], 'Fee category created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error(
                'Validation failed',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            Log::error('Failed to create fee category', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to create fee category: ' . $e->getMessage(),
                500
            );
        }
    }
}
