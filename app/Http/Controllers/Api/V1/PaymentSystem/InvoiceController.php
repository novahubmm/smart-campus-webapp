<?php

namespace App\Http\Controllers\Api\V1\PaymentSystem;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    /**
     * Get invoices for a student.
     * 
     * GET /api/v1/payment-system/students/{studentId}/invoices
     * 
     * Returns all invoices for the specified student with their associated fees.
     * Supports optional filtering by status and academic year.
     * Returns invoice counts (total, pending, overdue).
     * 
     * Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 14.3
     *
     * @param Request $request
     * @param string $studentId
     * @return JsonResponse
     */
    public function index(Request $request, string $studentId): JsonResponse
    {
        try {
            // Validate query parameters
            $validated = $request->validate([
                'status' => 'nullable|in:pending,partial,paid,overdue',
                'academic_year' => 'nullable|string',
            ]);

            // Get invoices for the student
            $result = $this->invoiceService->getInvoicesForStudent(
                $studentId,
                $validated['status'] ?? null,
                $validated['academic_year'] ?? null
            );

            // Format the response
            $invoicesData = $result['invoices']->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'remaining_amount' => $invoice->remaining_amount,
                    'due_date' => $invoice->due_date->format('M j, Y'),
                    'status' => $invoice->status,
                    'invoice_type' => $invoice->invoice_type,
                    'academic_year' => $invoice->academic_year,
                    'created_at' => $invoice->created_at->toISOString(),
                    'fees' => $invoice->fees->map(function ($fee) {
                        return [
                            'id' => $fee->id,
                            'fee_name' => $fee->fee_name,
                            'fee_name_mm' => $fee->fee_name_mm,
                            'amount' => $fee->amount,
                            'paid_amount' => $fee->paid_amount,
                            'remaining_amount' => $fee->remaining_amount,
                            'supports_payment_period' => $fee->supports_payment_period,
                            'supports_discount' => $fee->feeType?->discount_status ?? false,
                            'due_date' => $fee->due_date->format('M j, Y'),
                            'due_date_raw' => $fee->due_date->format('Y-m-d'), // For date comparisons
                            'status' => $fee->status,
                            'allow_adjustment' => true, // Allow adjust buttons for partial payments
                        ];
                    }),
                    'show_subtotal' => false, // Don't show subtotal in payment summary
                ];
            });

            return ApiResponse::success([
                'invoices' => $invoicesData,
                'counts' => $result['counts'],
            ], 'Invoices retrieved successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to retrieve invoices', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to retrieve invoices: ' . $e->getMessage(),
                500
            );
        }
    }
}
