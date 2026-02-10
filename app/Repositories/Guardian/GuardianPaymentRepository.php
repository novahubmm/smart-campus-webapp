<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianPaymentRepositoryInterface;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\PaymentMethod;
use App\Models\PaymentProof;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuardianPaymentRepository implements GuardianPaymentRepositoryInterface
{
    public function getFeeStructure(StudentProfile $student, ?string $academicYear = null): array
    {
        // Get current academic year if not provided
        if (!$academicYear) {
            $currentYear = Carbon::now()->year;
            $academicYear = $currentYear . '-' . ($currentYear + 1);
        }

        // Get current month for invoices
        $currentMonth = Carbon::now()->format('Y-m');

        // Get unpaid invoices for the current month
        $invoices = \App\Models\Invoice::where('student_id', $student->id)
            ->where('month', $currentMonth)
            ->where('status', 'unpaid')
            ->with(['feeStructure.feeType'])
            ->get();

        $monthlyFees = [];
        $additionalFees = [];

        foreach ($invoices as $invoice) {
            $feeStructure = $invoice->feeStructure;
            $feeType = $feeStructure?->feeType;
            
            if (!$feeStructure || !$feeType) {
                continue;
            }

            $feeItem = [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'fee_structure_id' => $feeStructure->id,
                'name' => $feeType->name ?? 'Fee',
                'name_mm' => $feeType->name_mm ?? $feeType->name ?? 'ကြေး',
                'amount' => (float) $invoice->total_amount,
                'removable' => !$feeType->is_mandatory,
                'description' => $feeType->description ?? '',
                'description_mm' => $feeType->description_mm ?? $feeType->description ?? '',
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'status' => $invoice->status,
            ];

            if ($feeStructure->frequency === 'monthly') {
                $monthlyFees[] = $feeItem;
            } else {
                $additionalFees[] = $feeItem;
            }
        }

        $totalMonthly = collect($monthlyFees)->sum('amount') + collect($additionalFees)->sum('amount');

        return [
            'student_id' => $student->id,
            'student_name' => $student->user->name ?? 'N/A',
            'grade' => $student->grade->name ?? 'N/A',
            'section' => $student->classModel->name ?? 'N/A',
            'academic_year' => $academicYear,
            'current_month' => $currentMonth,
            'monthly_fees' => $monthlyFees,
            'additional_fees' => $additionalFees,
            'total_monthly' => $totalMonthly,
            'currency' => 'MMK',
            'currency_symbol' => 'MMK',
        ];
    }

    public function getPaymentMethods(?string $type = null, bool $activeOnly = true): array
    {
        $query = PaymentMethod::query();

        if ($activeOnly) {
            $query->active();
        }

        if ($type && $type !== 'all') {
            $query->byType($type);
        }

        $methods = $query->ordered()->get();

        return [
            'methods' => $methods->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'name_mm' => $method->name_mm,
                    'type' => $method->type,
                    'account_number' => $method->account_number,
                    'account_name' => $method->account_name,
                    'account_name_mm' => $method->account_name_mm,
                    'logo_url' => $method->logo_url ? url($method->logo_url) : null,
                    'is_active' => $method->is_active,
                    'instructions' => $method->instructions,
                    'instructions_mm' => $method->instructions_mm,
                    'sort_order' => $method->sort_order,
                ];
            })->toArray(),
            'total_count' => $methods->count(),
            'active_count' => $methods->where('is_active', true)->count(),
        ];
    }

    public function submitPayment(StudentProfile $student, array $paymentData): array
    {
        // Handle receipt image upload
        $receiptPath = null;
        if (isset($paymentData['receipt_image'])) {
            $receiptPath = $this->uploadReceiptImage($paymentData['receipt_image']);
        }

        // Create payment proof record
        $paymentProof = PaymentProof::create([
            'student_id' => $student->id,
            'payment_method_id' => $paymentData['payment_method_id'],
            'payment_amount' => $paymentData['payment_amount'],
            'payment_months' => $paymentData['payment_months'],
            'payment_date' => $paymentData['payment_date'],
            'receipt_image' => $receiptPath,
            'notes' => $paymentData['notes'] ?? null,
            'fee_ids' => $paymentData['invoice_ids'] ?? [], // Store invoice_ids in fee_ids column
            'status' => 'pending_verification',
        ]);

        // Update invoices to pending_verification status
        if (!empty($paymentData['invoice_ids'])) {
            \App\Models\Invoice::whereIn('id', $paymentData['invoice_ids'])
                ->update(['status' => 'pending_verification']);
        }

        $paymentMethod = PaymentMethod::find($paymentData['payment_method_id']);

        return [
            'payment_id' => $paymentProof->id,
            'status' => 'pending_verification',
            'submitted_at' => $paymentProof->created_at->toIso8601String(),
            'verification_eta' => '24 hours',
            'verification_eta_mm' => '၂၄ နာရီ',
            'receipt_url' => $receiptPath ? url(Storage::url($receiptPath)) : null,
            'payment_details' => [
                'student_id' => $student->id,
                'student_name' => $student->user->name ?? 'N/A',
                'invoice_ids' => $paymentData['invoice_ids'] ?? [],
                'payment_method' => $paymentMethod->name ?? 'N/A',
                'payment_amount' => (float) $paymentData['payment_amount'],
                'payment_months' => (int) $paymentData['payment_months'],
                'payment_date' => $paymentData['payment_date'],
            ],
        ];
    }

    public function getPaymentOptions(): array
    {
        // Get promotions from database
        $promotions = \App\Models\PaymentPromotion::getAllActive();
        
        $options = $promotions->map(function ($promotion) {
            $badge = null;
            if ($promotion->discount_percent > 0) {
                $badge = '-' . number_format($promotion->discount_percent, 0) . '%';
            }
            
            return [
                'months' => $promotion->months,
                'discount_percent' => (float) $promotion->discount_percent,
                'label' => $promotion->months . ($promotion->months == 1 ? ' month' : ' months'),
                'label_mm' => $this->convertToMyanmarNumber($promotion->months) . ' လ',
                'badge' => $badge,
                'is_default' => $promotion->months === 1,
            ];
        })->toArray();

        return [
            'options' => $options,
            'default_months' => 1,
            'max_months' => 12,
            'currency' => 'MMK',
        ];
    }

    /**
     * Convert number to Myanmar numerals
     */
    private function convertToMyanmarNumber(int $number): string
    {
        $myanmarNumerals = ['၀', '၁', '၂', '၃', '၄', '၅', '၆', '၇', '၈', '၉'];
        return implode('', array_map(fn($digit) => $myanmarNumerals[$digit], str_split((string)$number)));
    }

    public function getPaymentHistory(StudentProfile $student, ?string $status = null, int $limit = 10, int $page = 1): array
    {
        $query = PaymentProof::where('student_id', $student->id)
            ->with(['paymentMethod']);

        if ($status && $status !== 'all') {
            switch ($status) {
                case 'pending':
                    $query->pending();
                    break;
                case 'verified':
                    $query->verified();
                    break;
                case 'rejected':
                    $query->rejected();
                    break;
            }
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return [
            'data' => collect($payments->items())->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'payment_amount' => (float) $payment->payment_amount,
                    'payment_months' => $payment->payment_months,
                    'payment_method' => $payment->paymentMethod->name ?? 'N/A',
                    'status' => $payment->status,
                    'status_mm' => $this->translateStatus($payment->status),
                    'submitted_at' => $payment->created_at->toIso8601String(),
                    'verified_at' => $payment->verified_at?->toIso8601String(),
                    'receipt_url' => $payment->receipt_image ? url(Storage::url($payment->receipt_image)) : null,
                    'notes' => $payment->notes,
                    'rejection_reason' => $payment->rejection_reason,
                ];
            })->toArray(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'last_page' => $payments->lastPage(),
            ],
        ];
    }

    private function uploadReceiptImage(string $imageData): string
    {
        // Check if it's a base64 encoded image
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                throw new \Exception('Base64 decode failed');
            }

            $fileName = 'receipt_' . Str::uuid() . '.' . $type;
            $path = 'receipts/' . date('Y/m');
            
            Storage::disk('public')->put($path . '/' . $fileName, $imageData);

            return $path . '/' . $fileName;
        }

        throw new \Exception('Invalid image format');
    }

    public function getPaymentProofDetail(string $paymentProofId, StudentProfile $student): ?array
    {
        $proof = PaymentProof::where('id', $paymentProofId)
            ->where('student_id', $student->id)
            ->with(['paymentMethod'])
            ->first();

        if (!$proof) {
            return null;
        }

        return [
            'id' => $proof->id,
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? 'N/A',
                'student_id' => $student->student_identifier ?? $student->student_id,
                'grade' => $student->grade->name ?? 'N/A',
                'section' => $student->classModel->name ?? 'N/A',
            ],
            'payment_details' => [
                'amount' => (float) $proof->payment_amount,
                'months' => $proof->payment_months,
                'payment_date' => $proof->payment_date->format('Y-m-d'),
                'payment_method' => [
                    'id' => $proof->paymentMethod->id ?? null,
                    'name' => $proof->paymentMethod->name ?? 'N/A',
                    'name_mm' => $proof->paymentMethod->name_mm ?? 'N/A',
                    'type' => $proof->paymentMethod->type ?? null,
                ],
                'currency' => 'MMK',
                'currency_symbol' => 'MMK',
            ],
            'fee_ids' => $proof->invoice_ids ?? $proof->fee_ids ?? [],
            'invoice_ids' => $proof->invoice_ids ?? $proof->fee_ids ?? [],
            'receipt_image' => $proof->receipt_image ? url(Storage::url($proof->receipt_image)) : null,
            'notes' => $proof->notes,
            'status' => $proof->status,
            'status_mm' => $this->translateStatus($proof->status),
            'status_label' => match($proof->status) {
                'pending_verification' => 'Pending Verification',
                'verified' => 'Approved',
                'rejected' => 'Rejected',
                default => 'Unknown'
            },
            'status_label_mm' => $this->translateStatus($proof->status),
            'submitted_at' => $proof->created_at->toIso8601String(),
            'verified_at' => $proof->verified_at?->toIso8601String(),
            'verified_by' => $proof->verified_by,
            'rejection_reason' => $proof->rejection_reason,
            'rejection_reason_mm' => $proof->rejection_reason, // TODO: Add translation if needed
        ];
    }

    private function translateStatus(string $status): string
    {
        return match ($status) {
            'pending_verification' => 'စစ်ဆေးဆဲ',
            'verified' => 'အတည်ပြုပြီး',
            'rejected' => 'ငြင်းပယ်ခံရသည်',
            default => $status,
        };
    }
}
