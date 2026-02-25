<?php

namespace App\Services\PaymentSystem;

use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Payment;
use App\Models\PaymentSystem\PaymentFeeDetail;
use App\Services\Upload\FileUploadService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Submit a payment for an invoice.
     * 
     * This method handles the complete payment submission workflow:
     * 1. Validates payment data
     * 2. Checks for duplicate payments
     * 3. Validates partial payment rules
     * 4. Checks due date restrictions
     * 5. Uploads receipt image (BEFORE transaction - Requirement 17.6)
     * 6. Creates payment record and updates invoice amounts (within transaction)
     * 
     * Error Handling (Requirement 17.6):
     * - If image upload fails, no database transaction is started
     * - If any database operation fails, the entire transaction is rolled back
     * - Uploaded images are NOT cleaned up on transaction failure (manual cleanup required)
     * 
     * @param array $data Payment submission data
     * @return Payment The created payment with fee details
     * @throws ValidationException If validation fails
     * @throws \Exception If upload or database operation fails
     */
    public function submitPayment(array $data): Payment
    {
        // Normalize invoice_id (support both single invoice_id and invoice_ids array)
        if (isset($data['invoice_ids']) && is_array($data['invoice_ids'])) {
            $data['invoice_id'] = $data['invoice_ids'][0]; // Use first invoice for now
        }
        
        // Normalize fee_payment_details (map fee_id to invoice_fee_id if needed)
        if (isset($data['fee_payment_details'])) {
            foreach ($data['fee_payment_details'] as $index => $detail) {
                if (isset($detail['fee_id']) && !isset($detail['invoice_fee_id'])) {
                    $data['fee_payment_details'][$index]['invoice_fee_id'] = $detail['fee_id'];
                }
            }
        }
        
        // Auto-correct fee amounts for multi-month payments if sum doesn't match total
        // This handles cases where client (e.g. mobile app) sends undiscounted fee amounts
        $paymentMonths = $data['payment_months'] ?? 1;
        if ($paymentMonths > 1 && isset($data['fee_payment_details'])) {
            $sumOfPaidAmounts = collect($data['fee_payment_details'])->sum('paid_amount');
            
            // If there's a mismatch > 1 MMK, we trust the total payment_amount (if plausible) 
            // and recalculate the breakdown based on discount rules
            if (abs(($data['payment_amount'] ?? 0) - $sumOfPaidAmounts) > 1.0) {
                // Pre-load invoice only if correction is needed
                if (!isset($invoice)) {
                    $invoice = Invoice::with('fees')->findOrFail($data['invoice_id']);
                }

                foreach ($data['fee_payment_details'] as $index => &$detail) {
                    $invoiceFeeId = $detail['invoice_fee_id'] ?? null;
                    $invoiceFee = $invoice->fees->where('id', $invoiceFeeId)->first();
                    
                    if ($invoiceFee) {
                        // Discount logic ONLY applies to School Fee (matching frontend behavior)
                        $isSchoolFee = str_contains(strtolower($invoiceFee->fee_name), 'school fee');
                        
                        // Recalculate amount using server-side discount logic
                        // Only apply discount if it's School Fee AND supports payment period
                        if ($isSchoolFee && $invoiceFee->supports_payment_period) {
                            $detail['paid_amount'] = $this->applyPaymentPeriodDiscount(
                                $invoiceFee->amount, 
                                $paymentMonths, 
                                true
                            );
                        } else {
                            // For fees that don't support payment period (e.g. Transportation),
                            // keep normal single-period behavior.
                            $detail['paid_amount'] = (float) $invoiceFee->remaining_amount;
                        }
                    }
                }
                unset($detail);
            }
        }

        $this->validatePaymentData($data);
        $this->checkDuplicatePayment($data);

        if (!isset($invoice)) {
            $invoice = Invoice::with('fees')->findOrFail($data['invoice_id']);
        }

        // Validate payment amounts (both partial and full)
        $paymentMonths = $data['payment_months'] ?? 1;
        $this->validatePaymentAmounts($data['fee_payment_details'], $invoice, $paymentMonths);
        
        if ($data['payment_type'] === 'partial') {
            $this->validatePartialPayment($data['fee_payment_details'], $invoice);
        }

        $this->checkDueDateRestrictions($invoice, $data['fee_payment_details']);
        
        // Upload receipt image BEFORE starting transaction (Requirement 17.6)
        // If upload fails, no database changes are made
        $receiptUrl = $this->uploadReceiptImage($data['receipt_image']);

        // All database operations within transaction
        // If any operation fails, all changes are rolled back
        return DB::transaction(function () use ($data, $invoice, $receiptUrl) {
            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'student_id' => $invoice->student_id,
                'invoice_id' => $data['invoice_id'],
                'payment_method_id' => $data['payment_method_id'],
                'payment_amount' => $data['payment_amount'],
                'payment_type' => $data['payment_type'],
                'payment_months' => $data['payment_months'] ?? 1,
                'payment_date' => $data['payment_date'],
                'receipt_image_url' => $receiptUrl,
                'status' => 'pending_verification',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['fee_payment_details'] as $feeDetail) {
                $invoiceFee = InvoiceFee::findOrFail($feeDetail['invoice_fee_id']);

                PaymentFeeDetail::create([
                    'payment_id' => $payment->id,
                    'invoice_fee_id' => $invoiceFee->id,
                    'fee_name' => $invoiceFee->fee_name,
                    'fee_name_mm' => $invoiceFee->fee_name_mm,
                    'full_amount' => $invoiceFee->amount,
                    'paid_amount' => $feeDetail['paid_amount'],
                    'is_partial' => $feeDetail['paid_amount'] < $invoiceFee->remaining_amount,
                    'payment_months' => $feeDetail['payment_months'] ?? 1, // Per-fee payment months
                ]);

                // Update InvoiceFee
                // If paid_amount exceeds remaining_amount (Advance Payment), cap it at remaining_amount
                // The surplus is implicitly tracked by PaymentFeeDetail having a higher amount than needed to close the fee.
                if ($feeDetail['paid_amount'] >= $invoiceFee->remaining_amount) {
                    $invoiceFee->paid_amount = $invoiceFee->amount; // Fully paid
                    $invoiceFee->remaining_amount = 0;
                    $invoiceFee->status = 'paid';
                } else {
                    $invoiceFee->paid_amount += $feeDetail['paid_amount'];
                    $invoiceFee->remaining_amount = $invoiceFee->amount - $invoiceFee->paid_amount;
                    $invoiceFee->status = $this->calculateInvoiceFeeStatus($invoiceFee);
                }
                $invoiceFee->save();
            }

            $invoice->paid_amount = $invoice->fees()->sum('paid_amount');
            $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;
            $invoice->status = $this->calculateInvoiceStatus($invoice);
            $invoice->save();

            return $payment->load('feeDetails');
        });
    }

    public function validatePaymentData(array $data): void
    {
        if (empty($data['fee_payment_details'])) {
            throw ValidationException::withMessages([
                'fee_payment_details' => ['At least one fee must be included in the payment.'],
            ]);
        }

        // Calculate sum of paid amounts from fee details
        $sumOfPaidAmounts = collect($data['fee_payment_details'])
            ->sum('paid_amount');

        // For multi-month payments (payment_months > 1), the payment_amount represents
        // the total for multiple months with discount applied.
        // The paid_amount in fee_payment_details should match the payment_amount.
        // Allow a small tolerance for floating point precision (1 MMK)
        $tolerance = 1.0;
        
        if (abs($data['payment_amount'] - $sumOfPaidAmounts) > $tolerance) {
            throw ValidationException::withMessages([
                'payment_amount' => [
                    "Payment amount ({$data['payment_amount']} MMK) must match the sum of fee amounts ({$sumOfPaidAmounts} MMK).",
                    "ငွေပေးချေမှု ({$data['payment_amount']} ကျပ်) သည် အခကြေးငွေ စုစုပေါင်း ({$sumOfPaidAmounts} ကျပ်) နှင့် ကိုက်ညီရမည်။"
                ],
            ]);
        }
    }

    /**
     * Validate payment amounts.
     * 
     * If payment_months > 1 (Advance Payment):
     * - Allow paid_amount > remaining_amount
     * - Require paid_amount to be at least the discounted amount for the selected months
     * - Allow paying up to the non-discounted amount for the selected months
     * 
     * If payment_months == 1 (Standard/Partial Payment):
     * - Reject if paid_amount > remaining_amount
     */
    protected function validatePaymentAmounts(array $feeDetails, Invoice $invoice, int $paymentMonths = 1): void
    {
        foreach ($feeDetails as $feeDetail) {
            $invoiceFee = $invoice->fees()->where('id', $feeDetail['invoice_fee_id'])->first();

            if (!$invoiceFee) {
                throw ValidationException::withMessages([
                    'fee_payment_details' => ['Invalid invoice fee ID.'],
                ]);
            }

            $paidAmount = (float) $feeDetail['paid_amount'];

            // Logic for Multi-Month (Advance) Payment
            if ($paymentMonths > 1) {
                if (!$invoiceFee->supports_payment_period) {
                    // Multi-month option does not apply to this fee; validate using single-period rules.
                    if ($paidAmount > ((float) $invoiceFee->remaining_amount + 1.0)) {
                        throw ValidationException::withMessages([
                            'fee_payment_details' => ['Payment amount exceeds remaining balance for fee: ' . $invoiceFee->fee_name],
                        ]);
                    }
                    continue;
                }

                $baseAmount = (float) $invoiceFee->amount;
                $minimumExpectedAmount = $this->applyPaymentPeriodDiscount($baseAmount, $paymentMonths, true);
                $maximumAllowedAmount = $baseAmount * $paymentMonths;
                $tolerance = 1.0;

                if ($paidAmount + $tolerance < $minimumExpectedAmount) {
                    throw ValidationException::withMessages([
                        'fee_payment_details' => [
                            "Payment amount for {$paymentMonths} months of {$invoiceFee->fee_name} is too low. Minimum expected: " . number_format($minimumExpectedAmount) . " MMK, Received: " . number_format($paidAmount) . " MMK.",
                            "{$invoiceFee->fee_name} အတွက် {$paymentMonths} လစာ ပေးသွင်းငွေ နည်းလွန်းပါသည်။ အနည်းဆုံး " . number_format($minimumExpectedAmount) . " ကျပ် ဖြစ်ရမည်။"
                        ],
                    ]);
                }

                if ($paidAmount - $tolerance > $maximumAllowedAmount) {
                    throw ValidationException::withMessages([
                        'fee_payment_details' => [
                            "Payment amount for {$paymentMonths} months of {$invoiceFee->fee_name} is too high. Maximum allowed: " . number_format($maximumAllowedAmount) . " MMK, Received: " . number_format($paidAmount) . " MMK.",
                            "{$invoiceFee->fee_name} အတွက် {$paymentMonths} လစာ ပေးသွင်းငွေ များလွန်းပါသည်။ အများဆုံး " . number_format($maximumAllowedAmount) . " ကျပ် အထိသာ ခွင့်ပြုပါသည်။"
                        ],
                    ]);
                }
                
                // Verify it covers at least the remaining balance (it should, as baseAmount * months > remaining)
                if ($paidAmount < $invoiceFee->remaining_amount) {
                     throw ValidationException::withMessages([
                        'fee_payment_details' => ["Multi-month payment must cover the current remaining balance."],
                    ]);
                }

            } else {
                // Standard validation for single month
                if ($paidAmount > $invoiceFee->remaining_amount) {
                    throw ValidationException::withMessages([
                        'fee_payment_details' => ['Payment amount exceeds remaining balance for fee: ' . $invoiceFee->fee_name],
                    ]);
                }
            }
        }
    }

    protected function checkDuplicatePayment(array $data): void
    {
        $invoice = Invoice::findOrFail($data['invoice_id']);
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        
        $duplicateExists = Payment::where('student_id', $invoice->student_id)
            ->where('invoice_id', $data['invoice_id'])
            ->where('payment_amount', $data['payment_amount'])
            ->where('payment_date', $data['payment_date'])
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'payment' => ['Duplicate payment detected. Please wait before submitting again.'],
            ]);
        }
    }

    /**
     * Validate partial payment amounts.
     * 
     * Validates:
     * - Each fee payment is at least 5,000 MMK (if greater than 0)
     * - Total payment is at least 10,000 MMK
     * - No fee payment exceeds the fee's remaining amount
     * - At least one fee is included in the payment
     * 
     * Validates: Requirements 10.1, 10.2, 10.3, 10.4
     *
     * @param array $feeDetails Array of fee payment details with invoice_fee_id and paid_amount
     * @param Invoice $invoice The invoice being paid
     * @return void
     * @throws ValidationException If validation fails
     */
    public function validatePartialPayment(array $feeDetails, Invoice $invoice): void
    {
        // Requirement 10.4: Check at least one fee is included
        if (empty($feeDetails)) {
            throw ValidationException::withMessages([
                'fee_payment_details' => [
                    'At least one fee must be included in the payment.',
                    'အနည်းဆုံး အခကြေးငွေ တစ်ခု ပါဝင်ရမည်။'
                ],
            ]);
        }

        $totalPayment = 0;
        $errors = [];

        foreach ($feeDetails as $index => $detail) {
            $paidAmount = $detail['paid_amount'] ?? 0;
            $invoiceFeeId = $detail['invoice_fee_id'] ?? null;

            // Find the invoice fee
            $invoiceFee = $invoice->fees()->find($invoiceFeeId);

            if (!$invoiceFee) {
                $errors["fee_payment_details.{$index}.invoice_fee_id"] = [
                    'Invalid invoice fee ID.',
                    'မမှန်ကန်သော အခကြေးငွေ ID။'
                ];
                continue;
            }

            // Requirement 10.1: Check minimum fee payment (5,000 MMK)
            if ($paidAmount > 0 && $paidAmount < 5000) {
                $errors["fee_payment_details.{$index}.paid_amount"] = [
                    "Minimum payment for {$invoiceFee->fee_name} is 5,000 MMK.",
                    "{$invoiceFee->fee_name_mm} အတွက် အနည်းဆုံး ငွေပေးချေမှု 5,000 ကျပ် ဖြစ်ရမည်။"
                ];
            }

            // Requirement 10.3: Check payment doesn't exceed remaining amount
            if ($paidAmount > $invoiceFee->remaining_amount) {
                $errors["fee_payment_details.{$index}.paid_amount"] = [
                    "Payment amount ({$paidAmount} MMK) exceeds remaining amount ({$invoiceFee->remaining_amount} MMK) for {$invoiceFee->fee_name}.",
                    "ငွေပေးချေမှု ({$paidAmount} ကျပ်) သည် {$invoiceFee->fee_name_mm} အတွက် ကျန်ရှိငွေ ({$invoiceFee->remaining_amount} ကျပ်) ထက် ကျော်လွန်နေပါသည်။"
                ];
            }

            $totalPayment += $paidAmount;
        }

        // Requirement 10.2: Check minimum total payment (10,000 MMK)
        if ($totalPayment < 10000) {
            $errors['payment_amount'] = [
                "Total payment must be at least 10,000 MMK. Current total: {$totalPayment} MMK.",
                "စုစုပေါင်း ငွေပေးချေမှု အနည်းဆုံး 10,000 ကျပ် ဖြစ်ရမည်။ လက်ရှိ စုစုပေါင်း: {$totalPayment} ကျပ်။"
            ];
        }

        // If there are any validation errors, throw exception
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Check due date restrictions for partial payments.
     * 
     * For each fee in the payment:
     * - Check if current_date >= due_date
     * - If overdue and payment is partial, throw ValidationException
     * - Allow full payment for overdue fees
     * 
     * Validates: Requirements 16.1, 16.2, 16.3, 16.4, 16.5, 16.6
     *
     * @param Invoice $invoice The invoice being paid
     * @param array $feeDetails Array of fee payment details with invoice_fee_id and paid_amount
     * @return void
     * @throws ValidationException If partial payment attempted on overdue fee
     */
    public function checkDueDateRestrictions(Invoice $invoice, array $feeDetails): void
    {
        $errors = [];
        $currentDate = now()->startOfDay();

        foreach ($feeDetails as $index => $detail) {
            $paidAmount = $detail['paid_amount'] ?? 0;
            $invoiceFeeId = $detail['invoice_fee_id'] ?? null;

            // Find the invoice fee
            $invoiceFee = $invoice->fees()->find($invoiceFeeId);

            if (!$invoiceFee) {
                continue; // Skip if fee not found (will be caught by other validation)
            }

            // Check if fee is overdue (current_date >= due_date)
            $dueDate = $invoiceFee->due_date->startOfDay();
            $isOverdue = $currentDate->greaterThanOrEqualTo($dueDate);

            // Check if payment is partial (paid_amount < remaining_amount)
            $isPartialPayment = $paidAmount < $invoiceFee->remaining_amount;

            // Requirement 16.2, 16.3: If overdue and partial payment, reject
            if ($isOverdue && $isPartialPayment) {
                $errors["fee_payment_details.{$index}.paid_amount"] = [
                    "Full payment is required for {$invoiceFee->fee_name} as it is overdue (due date: {$invoiceFee->due_date->format('Y-m-d')}). Remaining amount: {$invoiceFee->remaining_amount} MMK.",
                    "{$invoiceFee->fee_name_mm} သည် သတ်မှတ်ရက်ကျော်လွန်နေပြီဖြစ်သောကြောင့် (သတ်မှတ်ရက်: {$invoiceFee->due_date->format('Y-m-d')}) အပြည့်အဝ ပေးချေရန် လိုအပ်ပါသည်။ ကျန်ရှိငွေ: {$invoiceFee->remaining_amount} ကျပ်။"
                ];
            }
        }

        // If there are any validation errors, throw exception
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Upload a receipt image to storage.
     * 
     * Supports both:
     * - UploadedFile objects (multipart/form-data)
     * - Base64 encoded strings (JSON payload)
     * 
     * Validates:
     * - Image format
     * - Image size (<= 3MB)
     * 
     * Generates a unique filename and stores in public storage.
     * Returns a publicly accessible URL.
     * 
     * Validates: Requirements 9.2, 9.3, 9.4, 17.1, 17.2, 17.3, 17.4, 17.5
     *
     * @param UploadedFile|string $image The uploaded receipt image or base64 string
     * @return string The public URL of the uploaded image
     * @throws ValidationException If validation fails
     * @throws \Exception If upload fails
     */
    public function uploadReceiptImage($image): string
    {
        // Handle base64 string
        if (is_string($image)) {
            return $this->uploadBase64Image($image);
        }
        
        // Handle UploadedFile
        if ($image instanceof UploadedFile) {
            return $this->uploadFileImage($image);
        }
        
        throw ValidationException::withMessages([
            'receipt_image' => [
                'The receipt image must be a valid file or base64 string.',
            ],
        ]);
    }
    
    /**
     * Upload a base64 encoded image.
     *
     * @param string $base64String Base64 encoded image with or without data URI prefix
     * @return string The public URL of the uploaded image
     * @throws ValidationException If validation fails
     * @throws \Exception If upload fails
     */
    private function uploadBase64Image(string $base64String): string
    {
        try {
            $path = app(FileUploadService::class)->storeOptimizedBase64Image(
                $base64String,
                'payment_receipts',
                'public',
                'receipt'
            );

            return asset('storage/' . $path);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Invalid receipt image.';
            throw ValidationException::withMessages([
                'receipt_image' => [$message],
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Base64 receipt image upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Upload an UploadedFile image.
     *
     * @param UploadedFile $image The uploaded file
     * @return string The public URL of the uploaded image
     * @throws ValidationException If validation fails
     * @throws \Exception If upload fails
     */
    private function uploadFileImage(UploadedFile $image): string
    {
        try {
            $path = app(FileUploadService::class)->storeOptimizedUploadedImage(
                $image,
                'payment_receipts',
                'public',
                'receipt'
            );
            return asset('storage/' . $path);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Invalid receipt image.';
            throw ValidationException::withMessages([
                'receipt_image' => [$message],
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Receipt image upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(Str::random(6));
        
        return "{$prefix}-{$date}-{$random}";
    }

    protected function calculateInvoiceFeeStatus(InvoiceFee $invoiceFee): string
    {
        if ($invoiceFee->remaining_amount == 0) {
            return 'paid';
        } elseif ($invoiceFee->paid_amount > 0) {
            return 'partial';
        } else {
            return 'unpaid';
        }
    }

    protected function calculateInvoiceStatus(Invoice $invoice): string
    {
        if ($invoice->remaining_amount == 0) {
            return 'paid';
        } elseif ($invoice->paid_amount > 0) {
            return 'partial';
        } elseif ($invoice->due_date->isPast()) {
            return 'overdue';
        } else {
            return 'pending';
        }
    }

    public function applyPaymentPeriodDiscount(float $baseAmount, int $months, bool $supportsPaymentPeriod): float
    {
        if (!$supportsPaymentPeriod) {
            return $baseAmount;
        }
        
        $discountPercent = 0;
        if ($months >= 3 && $months < 6) {
            $discountPercent = 5 + (($months - 3) / 3) * 5;
        } elseif ($months >= 6 && $months < 9) {
            $discountPercent = 10 + (($months - 6) / 3) * 5;
        } elseif ($months >= 9 && $months < 12) {
            $discountPercent = 15 + (($months - 9) / 3) * 5;
        } elseif ($months >= 12) {
            $discountPercent = 20;
        }
        
        $discountRate = $discountPercent / 100;
        
        $totalBeforeDiscount = $baseAmount * $months;
        $discountAmount = $totalBeforeDiscount * $discountRate;
        
        return $totalBeforeDiscount - $discountAmount;
    }

    public function calculatePaymentAmount(array $feeDetails, int $paymentMonths): float
    {
        $totalAmount = 0.0;
        
        foreach ($feeDetails as $detail) {
            $paidAmount = (float) $detail['paid_amount'];
            $supportsPaymentPeriod = $detail['supports_payment_period'] ?? false;
            
            if ($supportsPaymentPeriod && $paymentMonths > 1) {
                $totalAmount += $this->applyPaymentPeriodDiscount($paidAmount, $paymentMonths, true);
            } else {
                $totalAmount += $paidAmount;
            }
        }
        
        return $totalAmount;
    }
}
