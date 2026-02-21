<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianFeeRepositoryInterface;
use App\Models\Invoice;
use App\Models\PaymentSystem\Payment;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class GuardianFeeRepository implements GuardianFeeRepositoryInterface
{
    public function getPendingFee(StudentProfile $student): ?array
    {
        $invoice = Invoice::where('student_id', $student->id)
            ->where('status', 'pending')
            ->with('items.feeType')
            ->orderBy('due_date')
            ->first();

        if (!$invoice) {
            return null;
        }

        // Group items by category
        $breakdown = $invoice->items->map(function ($item) {
            return [
                'category' => $item->feeType?->name ?? 'Other',
                'amount' => (float) $item->amount,
            ];
        })->toArray();

        return [
            'id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'amount' => (float) $invoice->total_amount,
            'currency' => 'MMK',
            'term' => $this->getInvoiceTerm($invoice),
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'status' => $invoice->status,
            'breakdown' => $breakdown,
            'payment_methods' => ['easy_pay', 'bank_transfer', 'cash'],
            'created_at' => $invoice->created_at->toISOString(),
            'updated_at' => $invoice->updated_at->toISOString(),
        ];
    }

    public function getFeeDetails(string $feeId, StudentProfile $student): ?array
    {
        $invoice = Invoice::where('id', $feeId)
            ->where('student_id', $student->id)
            ->with(['items.feeType', 'payments.payment'])
            ->first();

        if (!$invoice) {
            return null;
        }

        // Get payment history for this invoice
        $paymentHistory = $invoice->payments->map(function ($paymentItem) {
            $payment = $paymentItem->payment;
            return [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'currency' => 'MMK',
                'payment_method' => $payment->payment_method,
                'status' => $payment->status ? 'completed' : 'pending',
                'transaction_id' => $payment->transaction_id,
                'paid_at' => $payment->payment_date?->toISOString(),
            ];
        })->toArray();

        // Breakdown by category
        $breakdown = $invoice->items->map(function ($item) {
            return [
                'item' => $item->feeType?->name ?? $item->description,
                'amount' => (float) $item->amount,
            ];
        })->toArray();

        return [
            'id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'student_name' => $student->user?->name ?? 'N/A',
            'amount' => (float) $invoice->total_amount,
            'paid_amount' => (float) $invoice->paid_amount,
            'balance' => (float) $invoice->balance,
            'currency' => 'MMK',
            'term' => $this->getInvoiceTerm($invoice),
            'academic_year' => Carbon::now()->year,
            'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'status' => $invoice->status,
            'payment_methods' => ['easy_pay', 'bank_transfer', 'cash'],
            'breakdown' => $breakdown,
            'payment_history' => $paymentHistory,
            'created_at' => $invoice->created_at->toISOString(),
            'updated_at' => $invoice->updated_at->toISOString(),
        ];
    }

    public function getAllFees(StudentProfile $student, array $filters): LengthAwarePaginator
    {
        $query = Invoice::where('student_id', $student->id)
            ->with('items.feeType');

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = min($filters['per_page'] ?? 10, 50);
        $invoices = $query->orderBy('due_date', 'desc')->paginate($perPage);

        // Transform the data
        $invoices->getCollection()->transform(function ($invoice) {
            return [
                'id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'amount' => (float) $invoice->total_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'balance' => (float) $invoice->balance,
                'currency' => 'MMK',
                'term' => $this->getInvoiceTerm($invoice),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'status' => $invoice->status,
                'created_at' => $invoice->created_at->toISOString(),
            ];
        });

        return $invoices;
    }

    public function initiatePayment(string $feeId, StudentProfile $student, array $data): array
    {
        $invoice = Invoice::where('id', $feeId)
            ->where('student_id', $student->id)
            ->first();

        if (!$invoice) {
            throw new \Exception('Invoice not found');
        }

        // Create payment record with pending status
        $payment = Payment::create([
            'payment_number' => $this->generatePaymentNumber(),
            'student_id' => $student->id,
            'amount' => $data['amount'],
            'payment_date' => Carbon::now(),
            'payment_method' => $data['payment_method'],
            'transaction_id' => Str::uuid(),
            'reference_number' => $this->generateReferenceCode($invoice),
            'status' => false, // Pending - waiting for admin confirmation
            'notes' => 'Payment initiated from Guardian App - Awaiting confirmation',
        ]);

        // Link payment to invoice
        $payment->items()->create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount' => $data['amount'],
        ]);

        // Return payment details based on method
        return $this->formatPaymentResponse($payment, $data['payment_method'], $invoice);
    }

    public function getPaymentHistory(StudentProfile $student, array $filters): LengthAwarePaginator
    {
        $query = Payment::where('student_id', $student->id)
            ->with('items');

        // Apply filters
        if (isset($filters['status'])) {
            $statusMap = [
                'completed' => true,
                'pending' => false,
                'failed' => false,
            ];
            $query->where('status', $statusMap[$filters['status']] ?? false);
        }

        $perPage = $filters['per_page'] ?? 10;
        $payments = $query->orderBy('payment_date', 'desc')->paginate($perPage);

        // Transform the data
        $payments->getCollection()->transform(function ($payment) {
            return [
                'id' => $payment->id,
                'fee_id' => $payment->items->first()?->invoice_id ?? null,
                'amount' => (float) $payment->amount,
                'currency' => 'MMK',
                'payment_method' => $payment->payment_method,
                'status' => $payment->status ? 'completed' : 'pending',
                'transaction_id' => $payment->transaction_id,
                'reference_number' => $payment->reference_number,
                'paid_at' => $payment->payment_date?->toISOString(),
                'receipt_url' => $payment->status ? route('api.v1.guardian.fees.receipt', ['payment_id' => $payment->id]) : null,
                'notes' => $payment->notes,
            ];
        });

        return $payments;
    }

    public function generateReceipt(string $paymentId, StudentProfile $student): array
    {
        $payment = Payment::where('id', $paymentId)
            ->where('student_id', $student->id)
            ->where('status', true) // Only completed payments
            ->with(['items.invoice.items.feeType'])
            ->first();

        if (!$payment) {
            throw new \Exception('Payment not found or not completed');
        }

        $invoice = $payment->items->first()?->invoice;

        return [
            'receipt_number' => $payment->payment_number,
            'payment_id' => $payment->id,
            'student' => [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'N/A',
                'student_id' => $student->student_identifier ?? $student->student_id,
                'grade' => $student->grade?->name ?? 'N/A',
                'section' => $student->classModel?->section ?? 'N/A',
            ],
            'payment_details' => [
                'amount' => (float) $payment->amount,
                'currency' => 'MMK',
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'reference_number' => $payment->reference_number,
                'payment_date' => $payment->payment_date->format('Y-m-d H:i:s'),
            ],
            'invoice_details' => $invoice ? [
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'term' => $this->getInvoiceTerm($invoice),
                'items' => $invoice->items->map(function ($item) {
                    return [
                        'description' => $item->feeType?->name ?? $item->description,
                        'amount' => (float) $item->amount,
                    ];
                })->toArray(),
            ] : null,
            'school_info' => [
                'name' => config('app.name', 'SmartCampus School'),
                'address' => 'School Address Here',
                'phone' => 'School Phone Here',
                'email' => 'school@example.com',
            ],
            'generated_at' => Carbon::now()->toISOString(),
        ];
    }

    public function downloadReceipt(string $paymentId, StudentProfile $student): string
    {
        $receiptData = $this->generateReceipt($paymentId, $student);
        
        // In a real implementation, this would generate a PDF
        // For now, return a URL to the receipt endpoint
        return route('api.v1.guardian.fees.receipt', ['payment_id' => $paymentId]);
    }

    public function getPaymentSummary(StudentProfile $student, ?int $year = null): array
    {
        $year = $year ?? Carbon::now()->year;
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        // Get all invoices for the year
        $invoices = Invoice::where('student_id', $student->id)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        // Calculate totals
        // 'sent' and 'partial' are considered pending (not yet fully paid)
        $pendingInvoices = $invoices->whereIn('status', ['sent', 'partial', 'draft']);
        $overdueInvoices = $invoices->where('status', 'overdue');

        $totalPending = (float) $pendingInvoices->sum('balance');
        $totalOverdue = (float) $overdueInvoices->sum('balance');
        $pendingCount = $pendingInvoices->count();
        $overdueCount = $overdueInvoices->count();

        // Get earliest due date from pending and overdue invoices
        $unpaidInvoices = $invoices->whereIn('status', ['sent', 'partial', 'draft', 'overdue']);
        $earliestDueDate = $unpaidInvoices->min('due_date');

        return [
            'total_pending' => $totalPending,
            'total_overdue' => $totalOverdue,
            'earliest_due_date' => $earliestDueDate ? Carbon::parse($earliestDueDate)->format('Y-m-d') : null,
            'pending_count' => $pendingCount,
            'overdue_count' => $overdueCount,
        ];
    }

    private function getInvoiceTerm(Invoice $invoice): string
    {
        $month = $invoice->invoice_date->format('F Y');
        $categories = $invoice->items->pluck('feeType.name')->filter()->unique()->implode(', ');
        
        if ($categories) {
            return $month . ' - ' . $categories;
        }
        
        return $month . ' - School Fees';
    }

    private function generatePaymentNumber(): string
    {
        return 'PAY-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    private function generateReferenceCode(Invoice $invoice): string
    {
        return 'INV-' . $invoice->invoice_number . '-' . strtoupper(Str::random(4));
    }

    private function formatPaymentResponse(Payment $payment, string $method, Invoice $invoice): array
    {
        $baseResponse = [
            'payment_id' => $payment->id,
            'payment_method' => $method,
            'amount' => (float) $payment->amount,
            'currency' => 'MMK',
            'status' => 'pending',
            'reference_number' => $payment->reference_number,
            'note' => 'Payment is pending admin confirmation. Please keep your transaction reference for verification.',
        ];

        switch ($method) {
            case 'easy_pay':
                return array_merge($baseResponse, [
                    'redirect_url' => 'https://easypay.com/payment/' . $payment->id,
                    'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
                    'expires_at' => Carbon::now()->addHours(1)->toISOString(),
                    'instructions' => 'Complete the payment through Easy Pay. Once confirmed, admin will verify and update your payment status.',
                ]);

            case 'bank_transfer':
                return array_merge($baseResponse, [
                    'bank_details' => [
                        'bank_name' => 'KBZ Bank',
                        'account_name' => 'SmartCampus School',
                        'account_number' => '1234567890',
                        'reference_code' => $payment->reference_number,
                    ],
                    'instructions' => 'Please transfer the amount to the bank account above and use the reference code: ' . $payment->reference_number . '. Admin will verify your payment within 1-2 business days.',
                ]);

            case 'cash':
                return array_merge($baseResponse, [
                    'instructions' => 'Please visit the school office to make the payment. Bring this reference code: ' . $payment->reference_number,
                    'office_hours' => 'Monday - Friday: 8:00 AM - 4:00 PM',
                    'note' => 'Payment will be confirmed immediately upon receipt at the school office.',
                ]);

            default:
                return $baseResponse;
        }
    }
}
