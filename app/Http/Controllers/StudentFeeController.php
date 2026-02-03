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
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Batch;
use App\Models\Grade;
use App\Models\StudentProfile;
use App\Models\FeeType;
use App\Services\StudentFeeService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentFeeController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly StudentFeeService $service) {}

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

        // Build query for active students with filters
        $studentsQuery = StudentProfile::with(['user', 'grade', 'classModel'])
            ->where('status', 'active');

        // Apply grade filter
        if ($request->filled('grade')) {
            $studentsQuery->where('grade_id', $request->grade);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter (paid/pending based on invoice status)
        if ($request->filled('status')) {
            $statusFilter = $request->status;
            $monthStart = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            if ($statusFilter === 'paid') {
                // Students with paid invoices in selected month
                $paidStudentIds = Invoice::where('status', 'paid')
                    ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                    ->pluck('student_id')
                    ->unique();
                $studentsQuery->whereIn('id', $paidStudentIds);
            } elseif ($statusFilter === 'pending') {
                // Students without paid invoices in selected month
                $paidStudentIds = Invoice::where('status', 'paid')
                    ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                    ->pluck('student_id')
                    ->unique();
                $studentsQuery->whereNotIn('id', $paidStudentIds);
            }
        }

        // Get all students for stats calculation (before pagination)
        $allStudents = StudentProfile::with(['grade'])
            ->where('status', 'active')
            ->get();

        // Paginate students (10 per page)
        $students = $studentsQuery->orderBy('student_identifier')->paginate(10)->withQueryString();

        $feeTypes = FeeType::select('id', 'name')->orderBy('name')->get();
        $grades = Grade::orderBy('level')->get();
        $batches = Batch::select('id', 'name')->orderBy('name')->get();

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
            'students' => $students,
            'feeTypes' => $feeTypes,
            'grades' => $grades,
            'batches' => $batches,
            'feeByGrade' => $feeByGrade,
            'currentMonth' => $currentMonth,
            'currentMonthKey' => $currentMonthKey,
            'totalReceivable' => $totalReceivable,
            'totalStudents' => $totalStudents,
            'paidInvoices' => $paidInvoices,
            'totalInvoices' => $totalInvoices,
            'studentCountByGrade' => $studentCountByGrade,
            'pendingAppPayments' => $pendingAppPayments,
            'selectedMonth' => $selectedMonth,
            'monthOptions' => $monthOptions,
        ]);
    }

    public function storeStructure(StoreFeeStructureRequest $request): RedirectResponse
    {
        $this->service->createStructure(FeeStructureData::from($request->validated()));

        return redirect()->route('student-fees.index')->with('status', __('Fee structure saved.'));
    }

    public function updateStructure(UpdateFeeStructureRequest $request, FeeStructure $structure): RedirectResponse
    {
        $this->service->updateStructure($structure, FeeStructureData::from($request->validated()));

        return redirect()->route('student-fees.index')->with('status', __('Fee structure updated.'));
    }

    public function destroyStructure(FeeStructure $structure): RedirectResponse
    {
        $this->service->deleteStructure($structure);

        return redirect()->route('student-fees.index')->with('status', __('Fee structure removed.'));
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
}
