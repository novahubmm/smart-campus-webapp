<?php

namespace App\Http\Controllers;

use App\DTOs\Finance\FeeFilterData;
use App\DTOs\Finance\FeeStructureData;
use App\DTOs\Finance\InvoiceData;
use App\DTOs\Finance\PaymentData;
use App\Http\Requests\Finance\StoreFeeStructureRequest;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Http\Requests\Finance\StorePaymentRequest;
use App\Http\Requests\Finance\UpdateFeeStructureRequest;
use App\Http\Requests\Finance\UpdateInvoiceRequest;
use App\Http\Requests\Finance\RejectPaymentProofRequest;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Batch;
use App\Models\Grade;
use App\Models\StudentProfile;
use App\Models\FeeType;
use App\Services\StudentFeeService;
use App\Services\Finance\PaymentProofService;
use App\Services\Finance\NotificationService;
use App\Repositories\Finance\InvoiceRepository;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentFeeController extends Controller
{
    use LogsActivity;

    public function __construct(
        private readonly StudentFeeService $service,
        private readonly PaymentProofService $paymentProofService,
        private readonly NotificationService $notificationService,
        private readonly InvoiceRepository $invoiceRepo
    ) {}

    public function index(Request $request): View
    {
        // Get selected month or default to current month
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        
        // Add month to request for DTO
        $requestData = $request->all();
        $requestData['month'] = $selectedMonth;
        
        $filter = FeeFilterData::from($requestData);
        
        $invoices = $this->service->invoices($filter);
        $payments = $this->service->payments($filter);
        $structures = $this->service->structures();

        // Build query for unpaid invoices in the selected month
        $monthStart = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        
        // Get rejected payment proof invoice IDs for the selected month
        $rejectedProofInvoiceIds = \App\Models\PaymentProof::where('status', 'rejected')
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->get()
            ->pluck('fee_ids')
            ->flatten()
            ->filter()
            ->unique()
            ->toArray();
        
        // Query unpaid invoices with relationships
        $unpaidInvoicesQuery = Invoice::with([
            'student.user', 
            'student.grade', 
            'student.classModel', 
            'feeStructure.feeType'
        ])
        ->where(function ($query) use ($rejectedProofInvoiceIds) {
            // Include invoices that are unpaid or sent
            $query->whereIn('status', ['unpaid', 'sent'])
                  // OR invoices that have rejected payment proofs
                  ->orWhereIn('id', $rejectedProofInvoiceIds);
        })
        ->whereBetween('invoice_date', [$monthStart, $monthEnd])
        ->whereHas('student', function ($q) {
            $q->where('status', 'active');
        });

        // Apply grade filter
        if ($request->filled('grade')) {
            $unpaidInvoicesQuery->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->grade);
            });
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $unpaidInvoicesQuery->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Get all students for stats calculation
        $allStudents = StudentProfile::with(['grade'])
            ->where('status', 'active')
            ->get();

        // Paginate invoices (10 per page)
        $unpaidInvoices = $unpaidInvoicesQuery
            ->orderBy('invoice_date', 'desc')
            ->orderBy('invoice_number', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Get payment proofs for the displayed invoices in the selected month
        $studentIds = $unpaidInvoices->pluck('student_id')->unique();
        $paymentProofsByStudent = \App\Models\PaymentProof::whereIn('student_id', $studentIds)
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->with('paymentMethod')
            ->get()
            ->groupBy('student_id');
        
        // Get rejected payment proofs by invoice ID for the selected month
        $rejectedProofsByInvoice = \App\Models\PaymentProof::where('status', 'rejected')
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->with('paymentMethod')
            ->get()
            ->flatMap(function ($proof) {
                return collect($proof->fee_ids)->mapWithKeys(function ($invoiceId) use ($proof) {
                    return [$invoiceId => $proof];
                });
            });

        $feeTypes = FeeType::select('id', 'name')->orderBy('name')->get();
        $grades = Grade::orderBy('level')->get();
        $batches = Batch::select('id', 'name')->orderBy('name')->get();
        $paymentMethods = \App\Models\PaymentMethod::orderBy('sort_order')->get();
        $paymentPromotions = \App\Models\PaymentPromotion::getAllActive();

        // Get fee from Grade's price_per_month field (set in academic management)
        $feeByGrade = $grades->pluck('price_per_month', 'id')->map(fn($v) => (float) ($v ?? 0));

        // Current month info
        $currentMonth = \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y');
        $currentMonthKey = $selectedMonth;

        // Calculate stats for selected month
        $monthStart = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        
        $totalReceivable = $allStudents->sum(fn($s) => $feeByGrade[$s->grade_id] ?? 0);
        $totalStudents = $allStudents->count();
        
        // Get invoice counts for selected month
        $paidInvoices = Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$monthStart, $monthEnd])
            ->count();
        $totalInvoices = Invoice::whereBetween('invoice_date', [$monthStart, $monthEnd])
            ->count();

        // Student counts by grade for Fee Structure tab
        $studentCountByGrade = $allStudents->groupBy('grade_id')->map->count();

        // Get pending payments from Guardian App (status = false means pending)
        $pendingAppPayments = \App\Models\Payment::where('status', false)
            ->with(['student.user', 'student.grade', 'student.classModel', 'items.invoice'])
            ->orderBy('payment_date', 'desc')
            ->get();
        
        // Get pending payment proofs from mobile API with filters
        $proofQuery = \App\Models\PaymentProof::where('status', 'pending_verification')
            ->with(['student.user', 'student.grade', 'student.classModel', 'paymentMethod']);
        
        // Apply proof filters
        $proofMonth = $request->input('proof_month', $selectedMonth);
        $proofMonthStart = \Carbon\Carbon::parse($proofMonth . '-01')->startOfMonth();
        $proofMonthEnd = $proofMonthStart->copy()->endOfMonth();
        $proofQuery->whereBetween('payment_date', [$proofMonthStart, $proofMonthEnd]);
        
        if ($request->filled('proof_grade')) {
            $proofQuery->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->proof_grade);
            });
        }
        
        if ($request->filled('proof_search')) {
            $search = $request->proof_search;
            $proofQuery->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $pendingPaymentProofs = $proofQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'proof_page')->withQueryString();
        
        // Generate month options (last 12 months)
        $monthOptions = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $monthOptions->push([
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ]);
        }

        return view('finance.student-fees', [
            'filter' => $filter,
            'invoices' => $invoices,
            'payments' => $payments,
            'structures' => $structures,
            'unpaidInvoices' => $unpaidInvoices,
            'feeTypes' => $feeTypes,
            'grades' => $grades,
            'batches' => $batches,
            'paymentMethods' => $paymentMethods,
            'paymentPromotions' => $paymentPromotions,
            'feeByGrade' => $feeByGrade,
            'currentMonth' => $currentMonth,
            'currentMonthKey' => $currentMonthKey,
            'totalReceivable' => $totalReceivable,
            'totalStudents' => $totalStudents,
            'paidInvoices' => $paidInvoices,
            'totalInvoices' => $totalInvoices,
            'studentCountByGrade' => $studentCountByGrade,
            'pendingAppPayments' => $pendingAppPayments,
            'pendingPaymentProofs' => $pendingPaymentProofs,
            'paymentProofsByStudent' => $paymentProofsByStudent,
            'rejectedProofsByInvoice' => $rejectedProofsByInvoice,
            'selectedMonth' => $selectedMonth,
            'monthOptions' => $monthOptions,
        ]);
    }

    public function storeStructure(StoreFeeStructureRequest $request): RedirectResponse
    {
        // If it's a one-time fee, wrap everything in a transaction
        if (in_array($request->frequency, ['one-time', 'one_time'])) {
            try {
                \DB::beginTransaction();
                
                $feeStructure = $this->service->createStructure(FeeStructureData::from($request->validated()));
                $this->generateInvoicesForOneTimeFee($feeStructure);
                
                \DB::commit();
                
                return redirect()->route('student-fees.index')->with('status', __('Fee structure saved and invoices generated for all students.'));
            } catch (\Exception $e) {
                \DB::rollBack();
                
                \Log::error('Failed to create one-time fee structure and generate invoices', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return redirect()->route('student-fees.index')->with('error', __('Failed to create fee structure. Error: ' . $e->getMessage()));
            }
        }

        // For non-one-time fees, create normally
        $feeStructure = $this->service->createStructure(FeeStructureData::from($request->validated()));
        return redirect()->route('student-fees.index')->with('status', __('Fee structure saved.'));
    }

    /**
     * Generate invoices for all students in a grade when a one-time fee is created
     */
    private function generateInvoicesForOneTimeFee(FeeStructure $feeStructure): void
    {
        $students = \App\Models\StudentProfile::where('grade_id', $feeStructure->grade_id)
            ->where('status', 'active')
            ->get();

        if ($students->isEmpty()) {
            return;
        }

        // Get current month and academic year
        $currentMonth = now()->format('Y-m');
        $academicYear = now()->format('Y');
        $now = now();
        $dueDate = $now->copy()->addDays(30);
        $userId = auth()->id();

        // Filter out students who already have invoices for this fee structure
        $existingStudentIds = Invoice::where('fee_structure_id', $feeStructure->id)
            ->where('academic_year', $academicYear)
            ->pluck('student_id')
            ->toArray();

        $studentsToInvoice = $students->reject(function ($student) use ($existingStudentIds) {
            return in_array($student->id, $existingStudentIds);
        });

        if ($studentsToInvoice->isEmpty()) {
            \Log::info('No new invoices to create - all students already have invoices', [
                'fee_structure_id' => $feeStructure->id,
                'grade_id' => $feeStructure->grade_id,
            ]);
            return;
        }

        // Get next invoice number with lock - check by invoice_number pattern for today
        $todayPrefix = 'INV' . date('Ymd');
        $lastInvoice = Invoice::where('invoice_number', 'like', $todayPrefix . '%')
            ->lockForUpdate()
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        $counter = 1;
        if ($lastInvoice && preg_match('/INV\d{8}-(\d{4})/', $lastInvoice->invoice_number, $matches)) {
            $counter = intval($matches[1]) + 1;
        }

        // Prepare batch insert data
        $invoicesToInsert = [];
        $datePrefix = date('Ymd');

        foreach ($studentsToInvoice as $student) {
            $invoicesToInsert[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'invoice_number' => sprintf('INV%s-%04d', $datePrefix, $counter),
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'invoice_date' => $now,
                'due_date' => $dueDate,
                'month' => $currentMonth,
                'academic_year' => $academicYear,
                'subtotal' => $feeStructure->amount,
                'discount' => 0,
                'total_amount' => $feeStructure->amount,
                'paid_amount' => 0,
                'balance' => $feeStructure->amount,
                'status' => 'unpaid',
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $counter++;
        }

        // Batch insert all invoices at once
        Invoice::insert($invoicesToInsert);

        \Log::info('Generated invoices for one-time fee', [
            'fee_structure_id' => $feeStructure->id,
            'grade_id' => $feeStructure->grade_id,
            'students_count' => $students->count(),
            'invoices_created' => count($invoicesToInsert),
        ]);
    }

    public function updateStructure(UpdateFeeStructureRequest $request, FeeStructure $structure): RedirectResponse
    {
        $this->service->updateStructure($structure, FeeStructureData::from($request->validated()));

        return redirect()->route('student-fees.index')->with('status', __('Fee structure updated.'));
    }

    public function destroyStructure(FeeStructure $structure): RedirectResponse
    {
        try {
            \DB::beginTransaction();
            
            // Delete all invoices associated with this fee structure
            $invoiceCount = Invoice::where('fee_structure_id', $structure->id)->count();
            Invoice::where('fee_structure_id', $structure->id)->delete();
            
            // Delete the fee structure
            $this->service->deleteStructure($structure);
            
            \DB::commit();
            
            $message = $invoiceCount > 0 
                ? __('Fee structure and :count invoices removed.', ['count' => $invoiceCount])
                : __('Fee structure removed.');
            
            return redirect()->route('student-fees.index')->with('status', $message);
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Failed to delete fee structure', [
                'fee_structure_id' => $structure->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('student-fees.index')->with('error', __('Failed to delete fee structure. Error: ' . $e->getMessage()));
        }
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:fee_types,code',
            'description' => 'nullable|string',
            'is_mandatory' => 'boolean',
            'status' => 'boolean',
        ]);

        \App\Models\FeeType::create($validated);

        return redirect()->route('student-fees.index')->with('status', __('Fee category created.'));
    }

    public function updateCategory(Request $request, \App\Models\FeeType $feeType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:fee_types,code,' . $feeType->id,
            'description' => 'nullable|string',
            'is_mandatory' => 'boolean',
            'status' => 'boolean',
        ]);

        $feeType->update($validated);

        return redirect()->route('student-fees.index')->with('status', __('Fee category updated.'));
    }

    public function destroyCategory(\App\Models\FeeType $feeType): RedirectResponse
    {
        // Check if category is in use
        if ($feeType->feeStructures()->count() > 0 || $feeType->invoiceItems()->count() > 0) {
            return redirect()->route('student-fees.index')->with('error', __('Cannot delete category that is in use.'));
        }

        $feeType->delete();

        return redirect()->route('student-fees.index')->with('status', __('Fee category deleted.'));
    }

    public function storeInvoice(StoreInvoiceRequest $request): RedirectResponse
    {
        $data = InvoiceData::from($request->validated());
        $this->service->createInvoice($data, $request->user()?->id);

        return redirect()->route('student-fees.index')->with('status', __('Invoice created.'));
    }

    public function updateInvoice(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $data = InvoiceData::from($request->validated());
        $this->service->updateInvoice($invoice, $data);

        return redirect()->route('student-fees.index')->with('status', __('Invoice updated.'));
    }

    /**
     * Delete an invoice
     * 
     * This will delete the invoice and all its items.
     * The deletion will automatically be reflected in the guardian app
     * since the app queries invoices in real-time.
     */
    public function destroyInvoice(Invoice $invoice): RedirectResponse
    {
        try {
            // Check if invoice has any payments
            if ($invoice->paid_amount > 0) {
                return redirect()->route('student-fees.index')
                    ->with('error', __('Cannot delete invoice that has payments. Please delete payments first.'));
            }

            $invoiceNumber = $invoice->invoice_number;
            $studentName = $invoice->student?->user?->name ?? 'Unknown';

            // Delete invoice items first (cascade should handle this, but being explicit)
            $invoice->items()->delete();
            
            // Delete the invoice
            $invoice->delete();

            $this->logDelete('Invoice', $invoice->id, "Deleted invoice: {$invoiceNumber} for student: {$studentName}");

            return redirect()->route('student-fees.index')
                ->with('status', __('Invoice deleted successfully.'));
                
        } catch (\Exception $e) {
            return redirect()->route('student-fees.index')
                ->with('error', __('Error deleting invoice: ') . $e->getMessage());
        }
    }

    public function storePayment(StorePaymentRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = PaymentData::from($request->validated(), $request->user()?->id);
        $payment = $this->service->createPayment($data);

        // Load relationships for receipt
        $payment->load('student.user');

        $this->logCreate('FeePayment', $payment->id, "Payment: {$payment->payment_number}");

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Payment recorded.'),
                'payment' => [
                    'payment_number' => $payment->payment_number,
                    'student_name' => $payment->student?->user?->name ?? '-',
                    'student_id' => $payment->student?->student_identifier ?? '-',
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date?->format('M j, Y'),
                    'receptionist_id' => $payment->receptionist_id,
                    'receptionist_name' => $payment->receptionist_name,
                ],
            ]);
        }

        return redirect()->route('student-fees.index')->with('status', __('Payment recorded.'));
    }

    public function confirmPayment(Request $request, \App\Models\Payment $payment): RedirectResponse
    {
        // Validate that payment is pending
        if ($payment->status) {
            return redirect()->route('student-fees.index')->with('error', __('Payment already confirmed.'));
        }

        // Confirm the payment
        $payment->update([
            'status' => true,
            'notes' => ($payment->notes ?? '') . "\nConfirmed by admin on " . now()->format('Y-m-d H:i:s'),
        ]);

        // Update invoice paid amount and status
        foreach ($payment->items as $item) {
            $invoice = $item->invoice;
            if ($invoice) {
                $invoice->paid_amount = ($invoice->paid_amount ?? 0) + $item->amount;
                $invoice->balance = $invoice->total_amount - $invoice->paid_amount;
                
                // Update invoice status
                if ($invoice->balance <= 0) {
                    $invoice->status = 'paid';
                } elseif ($invoice->paid_amount > 0) {
                    $invoice->status = 'partial';
                }
                
                $invoice->save();
            }
        }

        $this->logUpdate('Payment', $payment->id, "Confirmed payment: {$payment->payment_number}");

        return redirect()->route('student-fees.index')->with('status', __('Payment confirmed successfully.'));
    }

    public function rejectPayment(Request $request, \App\Models\Payment $payment): RedirectResponse
    {
        // Validate that payment is pending
        if ($payment->status) {
            return redirect()->route('student-fees.index')->with('error', __('Payment already confirmed.'));
        }

        $reason = $request->input('reason', 'No reason provided');

        // Update payment with rejection note
        $payment->update([
            'notes' => ($payment->notes ?? '') . "\nRejected by admin on " . now()->format('Y-m-d H:i:s') . ". Reason: " . $reason,
        ]);

        // Optionally delete the payment or mark it as rejected
        // For now, we'll just add a note. You can add a 'rejected' status if needed

        $this->logDelete('Payment', $payment->id, "Rejected payment: {$payment->payment_number}");

        return redirect()->route('student-fees.index')->with('status', __('Payment rejected.'));
    }

    public function updateGradeFee(Request $request, Grade $grade): RedirectResponse
    {
        $validated = $request->validate([
            'price_per_month' => ['required', 'numeric', 'min:0'],
        ]);

        $grade->update(['price_per_month' => $validated['price_per_month']]);

        return redirect()->route('student-fees.index')->with('status', __('Grade fee updated.'));
    }

    /**
     * Approve Payment Proof from Mobile API
     */
    public function approvePaymentProof(Request $request, \App\Models\PaymentProof $paymentProof): RedirectResponse
    {
        try {
            // Use PaymentProofService to handle approval
            $payment = $this->paymentProofService->approvePaymentProof(
                $paymentProof->id,
                $request->user()->id
            );

            $this->logUpdate('PaymentProof', $paymentProof->id, "Approved payment proof for student: {$paymentProof->student->student_identifier}");

            return redirect()->route('student-fees.index')->with('status', __('Payment approved successfully.'));
        } catch (\Exception $e) {
            \Log::error('Payment proof approval failed', [
                'payment_proof_id' => $paymentProof->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('student-fees.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Reject Payment Proof from Mobile API
     */
    public function rejectPaymentProof(RejectPaymentProofRequest $request, \App\Models\PaymentProof $paymentProof): RedirectResponse
    {
        $validated = $request->validated();

        try {
            // Use PaymentProofService to handle rejection
            $paymentProof = $this->paymentProofService->rejectPaymentProof(
                $paymentProof->id,
                $request->user()->id,
                $validated['rejection_reason']
            );

            // Send rejection notification after transaction completes
            $this->paymentProofService->sendRejectionNotification($paymentProof);

            $this->logUpdate('PaymentProof', $paymentProof->id, "Rejected payment proof for student: {$paymentProof->student->student_identifier}");

            return redirect()->route('student-fees.index')->with('status', __('Payment rejected and guardian notified.'));
        } catch (\Exception $e) {
            \Log::error('Payment proof rejection failed', [
                'payment_proof_id' => $paymentProof->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('student-fees.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Get Payment Proof Details (AJAX)
     */
    public function getPaymentProofDetails(\App\Models\PaymentProof $paymentProof): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->paymentProofService->getPaymentProofDetails($paymentProof->id);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get payment proof details', [
                'payment_proof_id' => $paymentProof->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment proof history for an invoice
     */
    public function getInvoiceHistory(\App\Models\Invoice $invoice): \Illuminate\Http\JsonResponse
    {
        try {
            // Get all payment proofs that include this invoice
            $paymentProofs = \App\Models\PaymentProof::whereJsonContains('fee_ids', $invoice->id)
                ->with(['paymentMethod', 'verifiedBy'])
                ->orderBy('created_at', 'desc')
                ->get();

            $data = [
                'invoice' => [
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'student_name' => $invoice->student->user->name ?? 'N/A',
                    'month' => $invoice->invoice_date?->format('F Y') ?? 'N/A',
                ],
                'payment_proofs' => $paymentProofs->map(function ($proof) {
                    return [
                        'id' => $proof->id,
                        'payment_amount' => $proof->payment_amount,
                        'payment_months' => $proof->payment_months,
                        'payment_date' => $proof->payment_date->format('Y-m-d'),
                        'payment_method' => $proof->paymentMethod->name ?? 'N/A',
                        'status' => $proof->status,
                        'status_label' => match($proof->status) {
                            'pending_verification' => __('finance.Pending'),
                            'verified' => __('finance.Approved'),
                            'rejected' => __('finance.Rejected'),
                            default => $proof->status,
                        },
                        'submitted_at' => $proof->created_at->format('Y-m-d H:i'),
                        'verified_at' => $proof->verified_at?->format('Y-m-d H:i'),
                        'rejection_reason' => $proof->rejection_reason,
                        'notes' => $proof->notes,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get invoice history', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send reinform notification to guardian about unpaid fees
     */
    public function reinform(Request $request, StudentProfile $student): RedirectResponse
    {
        try {
            // Get unpaid invoices for this student
            $unpaidInvoices = $this->invoiceRepo->getUnpaidForStudent($student->id);

            if ($unpaidInvoices->isEmpty()) {
                return redirect()->route('student-fees.index')->with('error', __('No unpaid fees found for this student.'));
            }

            // Send reinform notification
            $this->notificationService->sendReinformNotification($student->id, $unpaidInvoices);

            $this->logCreate('NotificationLog', null, "Sent reinform notification to guardian of student: {$student->student_identifier}");

            return redirect()->route('student-fees.index')->with('status', __('Reminder notification sent to guardian.'));
        } catch (\Exception $e) {
            \Log::error('Failed to send reinform notification', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('student-fees.index')->with('error', __('Failed to send notification. Please try again.'));
        }
    }

    public function storePaymentMethod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'type' => 'required|in:bank,mobile_wallet',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_name_mm' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'instructions' => 'nullable|string',
            'instructions_mm' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        \App\Models\PaymentMethod::create($validated);

        return redirect()->route('student-fees.index')->with('status', __('Payment method created successfully.'));
    }

    public function updatePaymentMethod(Request $request, \App\Models\PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'type' => 'required|in:bank,mobile_wallet',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_name_mm' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'instructions' => 'nullable|string',
            'instructions_mm' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $paymentMethod->update($validated);

        return redirect()->route('student-fees.index')->with('status', __('Payment method updated successfully.'));
    }

    public function destroyPaymentMethod(\App\Models\PaymentMethod $paymentMethod): RedirectResponse
    {
        // Check if payment method is in use
        $inUse = \App\Models\PaymentProof::where('payment_method_id', $paymentMethod->id)->exists();
        
        if ($inUse) {
            return redirect()->route('student-fees.index')->with('error', __('Cannot delete payment method that is in use.'));
        }

        $paymentMethod->delete();

        return redirect()->route('student-fees.index')->with('status', __('Payment method deleted successfully.'));
    }

    public function updatePromotion(Request $request, \App\Models\PaymentPromotion $promotion): RedirectResponse
    {
        $validated = $request->validate([
            'discount_percent' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $promotion->update($validated);

        return redirect()->route('student-fees.index')->with('status', __('Promotion updated successfully.'));
    }
}
