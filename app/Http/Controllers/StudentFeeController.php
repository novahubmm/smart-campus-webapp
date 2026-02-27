<?php

namespace App\Http\Controllers;

use App\DTOs\Finance\FeeFilterData;
use App\DTOs\Finance\FeeStructureData;
use App\DTOs\Finance\InvoiceData;
use App\Http\Requests\Finance\StoreFeeStructureRequest;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Http\Requests\Finance\UpdateFeeStructureRequest;
use App\Http\Requests\Finance\UpdateInvoiceRequest;
use App\Http\Requests\Finance\RejectPaymentProofRequest;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Batch;
use App\Models\Grade;
use App\Models\StudentProfile;
use App\Models\FeeType;
use App\Services\Upload\FileUploadService;
use App\Services\StudentFeeService;
use App\Services\PaymentSystem\NotificationService as PaymentNotificationService;
use App\Services\PaymentSystem\PaymentVerificationService;
use App\Repositories\Finance\InvoiceRepository;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class StudentFeeController extends Controller
{
    use LogsActivity;

    public function __construct(
        private readonly StudentFeeService $service,
        private readonly InvoiceRepository $invoiceRepo,
        private readonly PaymentNotificationService $paymentNotificationService,
        private readonly PaymentVerificationService $verificationService
    ) {}

    /**
     * Manually generate monthly invoices for all active students
     */
    public function generateInvoices(Request $request): RedirectResponse
    {
        try {
            $month = $request->input('month', now()->format('Y-m'));
            $academicYear = $request->input('academic_year', now()->format('Y'));
            
            $stats = $this->invoiceService->generateMonthlyInvoices($month, $academicYear);
            
            $message = sprintf(
                'Generated %d invoices for %d students. Skipped %d (already have invoices).',
                $stats['invoices_created'],
                $stats['total_students'],
                $stats['invoices_skipped']
            );
            
            if (!empty($stats['errors'])) {
                $message .= sprintf(' %d errors occurred.', count($stats['errors']));
            }
            
            $this->logCreate('InvoiceGeneration', null, $message);
            
            return redirect()->route('student-fees.index')->with('status', $message);
        } catch (\Exception $e) {
            \Log::error('Manual invoice generation failed', [
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('student-fees.index')->with('error', __('Failed to generate invoices: ') . $e->getMessage());
        }
    }

    public function index(Request $request): View
    {
        // Get month filter (default to current month)
        $feeMonth = $request->input('fee_month', now()->format('Y-m'));
        $historyMonth = $request->input('history_month', now()->format('Y-m'));
        $pendingMonth = $request->input('pending_month', now()->format('Y-m'));
        $rejectedMonth = $request->input('rejected_month', now()->format('Y-m'));
        
        // For display purposes
        $filterDate = \Carbon\Carbon::parse($feeMonth . '-01');
        $currentMonth = $filterDate->translatedFormat('F Y');
        $currentMonthKey = $filterDate->format('Y-m');
        
        // Add date to request for DTO (use first day of month)
        $requestData = $request->all();
        $requestData['date'] = $filterDate->format('Y-m-d');
        
        $filter = FeeFilterData::from($requestData);
        
        $invoices = $this->service->invoices($filter);
        $payments = $this->service->payments($filter);
        $structures = $this->service->structures();

        // Build query for unpaid invoices in the selected month
        $monthStart = $filterDate->copy()->startOfMonth();
        $monthEnd = $filterDate->copy()->endOfMonth();
        
        // Get rejected payment proof invoice IDs for the selected month
        $rejectedProofInvoiceIds = \App\Models\PaymentProof::where('status', 'rejected')
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->get()
            ->pluck('fee_ids')
            ->flatten()
            ->filter()
            ->unique()
            ->toArray();
        
        // Query unpaid invoices with relationships (using PaymentSystem)
        // Show invoices that:
        // 1. Have due_date in the selected month (original invoices)
        // 2. OR are remaining_balance invoices created from payments in the selected month
        $unpaidInvoicesQuery = \App\Models\PaymentSystem\Invoice::with([
            'student.user', 
            'student.grade',
            'student.batch', // Add batch relationship
            'student.classModel', 
            'fees.feeStructure', // Load invoice fees with fee structure
            'fees.feeType' // Load fee type for discount status check
        ])
        ->where(function ($query) use ($filterDate, $monthStart, $monthEnd, $rejectedProofInvoiceIds) {
            // Original invoices due in the selected month
            $query->where(function ($q) use ($filterDate) {
                $q->whereYear('due_date', $filterDate->year)
                  ->whereMonth('due_date', $filterDate->month);
            })
            // OR remaining balance invoices created in the selected month
            ->orWhere(function ($q) use ($monthStart, $monthEnd) {
                $q->where('invoice_type', 'remaining_balance')
                  ->whereBetween('created_at', [$monthStart, $monthEnd]);
            })
            // OR one-time invoices created in the selected month
            ->orWhere(function ($q) use ($monthStart, $monthEnd) {
                $q->where('invoice_type', 'one_time')
                  ->whereBetween('created_at', [$monthStart, $monthEnd]);
            })
            // OR invoices with rejected payment proofs in the selected month
            ->orWhereIn('id', $rejectedProofInvoiceIds);
        })
        ->where(function ($query) {
            // Only show pending/unpaid invoices with remaining balance
            $query->whereIn('status', ['pending', 'unpaid'])
                  ->where('remaining_amount', '>', 0);
        })
        ->whereHas('student', function ($q) {
            $q->where('status', 'active');
        });

        // Apply grade filter
        if ($request->filled('fee_grade')) {
            $unpaidInvoicesQuery->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->fee_grade);
            });
        }

        // Apply fee type filter
        if ($request->filled('fee_fee_type')) {
            // Get the fee type code
            $feeType = \App\Models\FeeType::find($request->fee_fee_type);
            if ($feeType) {
                $feeTypeCode = $feeType->code;
                $unpaidInvoicesQuery->whereHas('fees', function ($q) use ($feeTypeCode) {
                    $q->whereHas('feeStructure', function ($fq) use ($feeTypeCode) {
                        $fq->where('fee_type', $feeTypeCode);
                    });
                });
            }
        }

        // Apply search filter (Student Name, Student ID, Guardian Name)
        if ($request->filled('fee_search')) {
            $search = $request->fee_search;
            $unpaidInvoicesQuery->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('guardian', function ($q) use ($search) {
                      $q->whereHas('user', function ($u) use ($search) {
                          $u->where('name', 'like', "%{$search}%");
                      });
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
        
        // Add partial payment count to each invoice
        $unpaidInvoices->getCollection()->transform(function ($invoice) {
            // Find the root invoice (original invoice)
            $rootInvoiceId = $invoice->parent_invoice_id ?: $invoice->id;
            
            // Count how many remaining balance invoices exist for this root invoice
            $partialPaymentCount = \App\Models\PaymentSystem\Invoice::where('parent_invoice_id', $rootInvoiceId)
                ->where('invoice_type', 'remaining_balance')
                ->count();
            
            $invoice->partial_payment_count = $partialPaymentCount;
            return $invoice;
        });

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

        // Transform payments to include computed attributes for receipt display
        $payments->getCollection()->transform(function ($payment) {
            // Add guardian name
            $payment->guardian_name = $payment->student?->guardians?->first()?->user?->name ?? 'N/A';
            
            // Add formatted class name (e.g., "Kindergarten A" for Grade 0, "Grade 1 A" for Grade 1)
            $payment->class_name = $payment->student?->formatted_class_name ?? '-';
            
            return $payment;
        });
        
        $feeTypes = FeeType::with('frequency')->orderBy('name')->get();
        $grades = Grade::orderBy('level')->get();
        $batches = Batch::select('id', 'name')->orderBy('name')->get();
        $paymentMethods = \App\Models\PaymentMethod::orderBy('sort_order')->get();
        
        // Get all active payment promotions (1-12 months)
        $allPaymentPromotions = \App\Models\PaymentPromotion::getAllActive();
        
        // Calculate remaining months dynamically based on batch end date
        // For now, we'll use a helper method to filter payment options
        // This will be calculated per-student when opening payment modal
        $paymentPromotions = $allPaymentPromotions;

        // Get fee from Grade's price_per_month field (set in academic management)
        $feeByGrade = $grades->pluck('price_per_month', 'id')->map(fn($v) => (float) ($v ?? 0));

        // Calculate total receivable and received (all time stats)
        $totalReceivable = \App\Models\PaymentSystem\Invoice::where('status', '!=', 'paid')->sum('remaining_amount');
        $totalReceived = \App\Models\PaymentSystem\Invoice::where('status', 'paid')->sum('paid_amount');
        
        // Get payment counts by date (using PaymentSystem verified payments)
        $paidInvoicesQuery = \App\Models\PaymentSystem\Invoice::where('status', 'paid');
        $paidInvoices = $paidInvoicesQuery->count();
        
        $totalInvoicesQuery = \App\Models\PaymentSystem\Invoice::query();
        $totalInvoices = $totalInvoicesQuery->count();

        // Student counts by grade for Fee Structure tab
        $studentCountByGrade = $allStudents->groupBy('grade_id')->map->count();

        // Legacy pendingAppPayments removed — consolidated to PaymentSystem only
        
        // Get pending payments (PaymentSystem)
        $pendingPaymentsQuery = \App\Models\PaymentSystem\Payment::where('status', 'pending_verification')
            ->with(['student.user', 'student.grade', 'student.classModel', 'paymentMethod', 'feeDetails', 'invoice']);
            
        // Apply grade filter
        if ($request->filled('pending_grade')) {
            $pendingPaymentsQuery->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->pending_grade);
            });
        }
        
        // Apply fee type filter
        if ($request->filled('pending_fee_type')) {
            $feeType = \App\Models\FeeType::find($request->pending_fee_type);
            if ($feeType) {
                $feeTypeCode = $feeType->code;
                $pendingPaymentsQuery->whereHas('feeDetails', function ($q) use ($feeTypeCode) {
                    $q->where('fee_type', $feeTypeCode);
                });
            }
        }
        
        // Apply search filter
        if ($request->filled('pending_search')) {
            $search = $request->pending_search;
            $pendingPaymentsQuery->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('guardian', function ($q) use ($search) {
                      $q->whereHas('user', function ($u) use ($search) {
                          $u->where('name', 'like', "%{$search}%");
                      });
                  });
            });
        }

        $pendingPayments = $pendingPaymentsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pending_page')
            ->withQueryString();

        // Get rejected payments (PaymentSystem) for the selected rejected month
        $rejectedFilterDate = \Carbon\Carbon::parse($rejectedMonth . '-01');
        $rejectedMonthStart = $rejectedFilterDate->copy()->startOfMonth();
        $rejectedMonthEnd = $rejectedFilterDate->copy()->endOfMonth();
        
        $rejectedPaymentsQuery = \App\Models\PaymentSystem\Payment::where('status', 'rejected')
            ->with(['student.user', 'student.grade', 'student.classModel', 'paymentMethod', 'feeDetails', 'invoice'])
            ->whereBetween('payment_date', [$rejectedMonthStart, $rejectedMonthEnd]);

        // Apply grade filter
        if ($request->filled('rejected_grade')) {
            $rejectedPaymentsQuery->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->rejected_grade);
            });
        }
        
        // Apply fee type filter
        if ($request->filled('rejected_fee_type')) {
            $feeType = \App\Models\FeeType::find($request->rejected_fee_type);
            if ($feeType) {
                $feeTypeCode = $feeType->code;
                $rejectedPaymentsQuery->whereHas('feeDetails', function ($q) use ($feeTypeCode) {
                    $q->where('fee_type', $feeTypeCode);
                });
            }
        }
        
        // Apply search filter
        if ($request->filled('rejected_search')) {
            $search = $request->rejected_search;
            $rejectedPaymentsQuery->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('guardian', function ($q) use ($search) {
                      $q->whereHas('user', function ($u) use ($search) {
                          $u->where('name', 'like', "%{$search}%");
                      });
                  });
            });
        }

        $rejectedPayments = $rejectedPaymentsQuery
            ->orderBy('updated_at', 'desc')
            ->paginate(10, ['*'], 'reject_page')
            ->withQueryString();

        // Get paid payments history (PaymentSystem) - Show invoices with status 'paid' AND have verified payments
        $historyFilterDate = \Carbon\Carbon::parse($historyMonth . '-01');
        $historyMonthStart = $historyFilterDate->copy()->startOfMonth();
        $historyMonthEnd = $historyFilterDate->copy()->endOfMonth();
        
        $paidPaymentsQuery = \App\Models\PaymentSystem\Invoice::with([
                'student.user', 
                'student.grade',
                'student.classModel', 
                'fees', // Load invoice fees
                'payments' => function($query) {
                    $query->where('status', 'verified')
                          ->with('paymentMethod') // Load payment method relationship
                          ->orderBy('created_at', 'desc');
                }
            ])
            ->where('status', 'paid') // Only show paid invoices
            ->whereHas('payments', function($query) use ($historyMonthStart, $historyMonthEnd) {
                $query->where('status', 'verified') // Must have at least one verified payment
                      ->whereBetween('payment_date', [$historyMonthStart, $historyMonthEnd]); // Filter by history month
            })
            ->whereHas('student', function ($q) {
                $q->where('status', 'active');
            });

        // Apply grade filter to paid invoices
        if ($request->filled('history_grade')) {
            $paidPaymentsQuery->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->history_grade);
            });
        }

        // Apply fee type filter to paid invoices
        if ($request->filled('history_fee_type')) {
            $feeType = \App\Models\FeeType::find($request->history_fee_type);
            if ($feeType) {
                $feeTypeCode = $feeType->code;
                $paidPaymentsQuery->whereHas('fees', function ($q) use ($feeTypeCode) {
                    $q->whereHas('feeStructure', function ($fq) use ($feeTypeCode) {
                        $fq->where('fee_type', $feeTypeCode);
                    });
                });
            }
        }

        // Apply search filter to paid invoices
        if ($request->filled('history_search')) {
            $search = $request->history_search;
            $paidPaymentsQuery->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('guardian', function ($q) use ($search) {
                      $q->whereHas('user', function ($u) use ($search) {
                          $u->where('name', 'like', "%{$search}%");
                      });
                  });
            });
        }

        $paidPayments = $paidPaymentsQuery
            ->orderBy('updated_at', 'desc') // Order by when it was marked as paid
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();
        
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
            'selectedMonth' => $feeMonth,
            'feeMonth' => $feeMonth,
            'historyMonth' => $historyMonth,
            'pendingMonth' => $pendingMonth,
            'rejectedMonth' => $rejectedMonth,
            'totalReceivable' => $totalReceivable,
            'totalReceived' => $totalReceived,
            'paidInvoices' => $paidInvoices,
            'totalInvoices' => $totalInvoices,
            'studentCountByGrade' => $studentCountByGrade,
            'pendingAppPayments' => collect(), // Legacy — kept for Blade compatibility, always empty
            'pendingPayments' => $pendingPayments,
            'rejectedPayments' => $rejectedPayments,
            'paidPayments' => $paidPayments,
            'paymentProofsByStudent' => $paymentProofsByStudent,
            'rejectedProofsByInvoice' => $rejectedProofsByInvoice,
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
            ->whereHas('grade', function($query) {
                $query->active(); // Only students in active grades
            })
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
            'name_mm' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:fee_types,code',
            'description' => 'nullable|string',
            'description_mm' => 'nullable|string',
            'fee_type' => 'required|in:' . implode(',', \App\Models\FeeType::FEE_TYPES),
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|integer|min:1|max:28',
            'partial_status' => 'nullable|boolean',
            'discount_status' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'frequency' => 'required|in:one_time,monthly',
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m|required_if:frequency,monthly',
        ]);

        // Validate month range if frequency is monthly
        if ($validated['frequency'] === 'monthly') {
            $startMonth = $validated['start_month'] ?? null;
            $endMonth = $validated['end_month'] ?? null;
            
            // Check if end month is before start month
            if ($startMonth && $endMonth && $endMonth < $startMonth) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['end_month' => __('finance.End month cannot be before start month.')]);
            }
        }

        // Handle boolean fields
        $validated['status'] = $validated['status'] === 'active';
        $validated['partial_status'] = $request->boolean('partial_status');
        $validated['discount_status'] = $request->boolean('discount_status');
        $validated['is_mandatory'] = false;

        // Generate code if not provided
        if (empty($validated['code'])) {
            $baseCode = strtoupper(Str::slug($validated['name']));
            
            // Fallback for non-latin names or symbols
            if (empty($baseCode)) {
                $baseCode = 'FEE-' . strtoupper(Str::random(6));
            }
            
            // Ensure uniqueness
            $code = $baseCode;
            $counter = 1;
            while (\App\Models\FeeType::where('code', $code)->exists()) {
                $code = $baseCode . '-' . $counter;
                $counter++;
            }
            $validated['code'] = $code;
        }

        $feeType = new \App\Models\FeeType();
        $feeType->fill($validated);
        $feeType->partial_status = $request->boolean('partial_status');
        $feeType->discount_status = $request->boolean('discount_status');
        $feeType->save();

        // Handle frequency
        $frequency = $validated['frequency'];
        $currentMonth = now()->month;
        
        if ($frequency === 'one_time') {
            // For one-time, set both start and end to current month
            $startMonth = $currentMonth;
            $endMonth = $currentMonth;
        } else {
            // For monthly, use provided months or default to current month
            $startMonth = $validated['start_month'] ? (int) date('n', strtotime($validated['start_month'] . '-01')) : $currentMonth;
            $endMonth = $validated['end_month'] ? (int) date('n', strtotime($validated['end_month'] . '-01')) : $currentMonth;
        }

        // Create frequency record
        $feeType->frequency()->create([
            'frequency' => $frequency,
            'start_month' => $startMonth,
            'end_month' => $endMonth,
        ]);

        return redirect()->route('student-fees.index', ['tab' => 'structure'])->with('status', __('Additional fee created.'));
    }

    public function updateCategory(Request $request, \App\Models\FeeType $feeType): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('=== UPDATE CATEGORY START ===');
        \Illuminate\Support\Facades\Log::info('Update Category Request:', $request->all());
        \Illuminate\Support\Facades\Log::info('FeeType ID:', ['id' => $feeType->id, 'name' => $feeType->name]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:fee_types,code,' . $feeType->id,
            'description' => 'nullable|string',
            'description_mm' => 'nullable|string',
            'fee_type' => 'required|in:' . implode(',', \App\Models\FeeType::FEE_TYPES),
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|integer|min:1|max:28',
            'partial_status' => 'nullable|boolean',
            'discount_status' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'frequency' => 'required|in:one_time,monthly',
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m|required_if:frequency,monthly',
        ]);

        // Validate due date if start/end month is current month
        if ($validated['frequency'] === 'monthly') {
            $currentMonth = now()->format('Y-m');
            $currentDay = now()->day;
            $dueDate = (int) $validated['due_date'];
            
            $startMonth = $validated['start_month'] ?? null;
            $endMonth = $validated['end_month'] ?? null;
            
            // Check if start month is current month and due date has passed
            // Only block if start_month or due_date actually CHANGED from the existing values
            $existingStartMonth = $feeType->start_month ?? ($feeType->frequency ? now()->format('Y') . '-' . str_pad($feeType->frequency->start_month, 2, '0', STR_PAD_LEFT) : null);
            $existingDueDate = (int) $feeType->due_date;
            $startMonthChanged = $startMonth !== $existingStartMonth;
            $dueDateChanged = $dueDate !== $existingDueDate;
            
            if ($startMonth === $currentMonth && $dueDate < $currentDay && ($startMonthChanged || $dueDateChanged)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['due_date' => __('finance.Due date has already passed for the current month. Please select a future date or start from next month.')]);
            }
            
            // Check if end month is before start month
            if ($startMonth && $endMonth && $endMonth < $startMonth) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['end_month' => __('finance.End month cannot be before start month.')]);
            }
        }

        // Preserve existing code if not provided
        if (!isset($validated['code']) || empty($validated['code'])) {
            $validated['code'] = $feeType->code;
        }

        // Handle frequency - compute months first so we can save on FeeType too
        $frequency = $validated['frequency'];
        $currentMonth = now()->month;
        
        if ($frequency === 'one_time') {
            $startMonth = $currentMonth;
            $endMonth = $currentMonth;
        } else {
            $startMonth = $validated['start_month'] ? (int) date('n', strtotime($validated['start_month'] . '-01')) : $currentMonth;
            $endMonth = $validated['end_month'] ? (int) date('n', strtotime($validated['end_month'] . '-01')) : $currentMonth;
        }

        // Handle boolean fields
        $validated['status'] = $validated['status'] === 'active';
        $validated['partial_status'] = $request->boolean('partial_status');
        $validated['discount_status'] = $request->boolean('discount_status');

        // Explicitly set start_month/end_month in Y-m format on fee_types table
        $validated['start_month'] = $validated['start_month'] ?? now()->format('Y') . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT);
        $validated['end_month'] = $validated['end_month'] ?? now()->format('Y') . '-' . str_pad($endMonth, 2, '0', STR_PAD_LEFT);

        // Fill and save FeeType
        $feeType->fill($validated);
        $feeType->partial_status = $request->boolean('partial_status');
        $feeType->discount_status = $request->boolean('discount_status');
        $feeType->save();

        \Log::info('Frequency data to save:', [
            'fee_type_id' => $feeType->id,
            'frequency' => $frequency,
            'start_month' => $startMonth,
            'end_month' => $endMonth,
        ]);

        // Update or create frequency record
        $frequencyRecord = $feeType->frequency()->updateOrCreate(
            ['fee_type_id' => $feeType->id],
            [
                'frequency' => $frequency,
                'start_month' => $startMonth,
                'end_month' => $endMonth,
            ]
        );

        \Log::info('Frequency record after save:', [
            'id' => $frequencyRecord->id,
            'frequency' => $frequencyRecord->frequency,
            'start_month' => $frequencyRecord->start_month,
            'end_month' => $frequencyRecord->end_month,
            'updated_at' => $frequencyRecord->updated_at,
        ]);

        \Log::info('=== UPDATE CATEGORY END - SUCCESS ===');

        // Check if request came from detail page
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, 'student-fees/categories/' . $feeType->id)) {
            return redirect()->route('student-fees.categories.show', $feeType->id)->with('status', __('Additional fee updated.'));
        }

        return redirect()->route('student-fees.index', ['tab' => 'structure'])->with('status', __('Additional fee updated.'));
    }

    public function showCategory(Request $request, \App\Models\FeeType $feeType): View
    {
        // Load frequency relationship
        $feeType->load('frequency');
        
        // Get all students with their fee type assignments
        $query = StudentProfile::with(['user', 'grade', 'classModel'])
            ->where('status', 'active');

        // Apply grade filter
        if ($request->filled('grade')) {
            $query->where('grade_id', $request->grade);
        }

        // Apply class filter
        if ($request->filled('class')) {
            $query->where('class_id', $request->class);
        }

        // Apply search filter (name or student ID)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter (active/inactive assignments)
        if ($request->filled('status')) {
            $statusFilter = $request->status;
            if ($statusFilter === 'active') {
                // Show only students with active assignments
                $query->whereHas('feeTypeAssignments', function ($q) use ($feeType) {
                    $q->where('fee_type_id', $feeType->id)
                      ->where('is_active', true);
                });
            } elseif ($statusFilter === 'inactive') {
                // Show students with inactive assignments OR no assignment at all
                $query->where(function ($q) use ($feeType) {
                    // Has inactive assignment
                    $q->whereHas('feeTypeAssignments', function ($subQ) use ($feeType) {
                        $subQ->where('fee_type_id', $feeType->id)
                             ->where('is_active', false);
                    })
                    // OR has no assignment for this fee type
                    ->orWhereDoesntHave('feeTypeAssignments', function ($subQ) use ($feeType) {
                        $subQ->where('fee_type_id', $feeType->id);
                    });
                });
            }
        }

        // Get count of active students for this fee type (before pagination)
        $activeStudentsCount = \App\Models\StudentFeeTypeAssignment::where('fee_type_id', $feeType->id)
            ->where('is_active', true)
            ->count();

        // Paginate students (15 per page)
        $studentsPaginated = $query->orderBy('student_identifier')
            ->paginate(15)
            ->withQueryString();

        // Transform paginated collection
        $studentsPaginated->getCollection()->transform(function ($student) use ($feeType) {
            // Check if this student has an active assignment for this fee type
            $assignment = \App\Models\StudentFeeTypeAssignment::where('student_id', $student->id)
                ->where('fee_type_id', $feeType->id)
                ->first();
            
            $student->assignment_is_active = $assignment ? $assignment->is_active : false;
            $student->assignment_id = $assignment?->id;
            
            // Format class name display - use the full class name directly
            $student->formatted_class_name = $student->classModel ? $student->classModel->name : 'N/A';
            
            return $student;
        });

        // Get grades and classes for filter dropdowns
        $grades = Grade::active()->orderBy('level')->get();
        
        // Get classes filtered by selected grade
        $classesQuery = \App\Models\SchoolClass::orderBy('name');
        if ($request->filled('grade')) {
            $classesQuery->where('grade_id', $request->grade);
        }
        $classes = $classesQuery->get();

        return view('finance.fee-type-detail', [
            'feeType' => $feeType,
            'students' => $studentsPaginated,
            'grades' => $grades,
            'classes' => $classes,
            'activeStudentsCount' => $activeStudentsCount,
        ]);
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

    public function toggleStudentFeeType(Request $request, string $feeTypeId, string $studentId): RedirectResponse|JsonResponse
    {
        $assignment = \App\Models\StudentFeeTypeAssignment::where('student_id', $studentId)
            ->where('fee_type_id', $feeTypeId)
            ->first();

        if ($assignment) {
            // Toggle the status
            $assignment->is_active = !$assignment->is_active;
            $assignment->save();
            
            $message = $assignment->is_active 
                ? __('finance.Student activated successfully')
                : __('finance.Student deactivated successfully');
            
            $isActive = $assignment->is_active;
        } else {
            // Create new assignment as active
            \App\Models\StudentFeeTypeAssignment::create([
                'student_id' => $studentId,
                'fee_type_id' => $feeTypeId,
                'is_active' => true,
            ]);
            
            $message = __('finance.Student activated successfully');
            $isActive = true;
        }

        // Check if it's an AJAX request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $isActive
            ]);
        }

        // Preserve query parameters (page, search, grade, class) for non-AJAX requests
        $queryParams = $request->only(['page', 'search', 'grade', 'class']);
        
        return redirect()->route('student-fees.categories.show', array_merge(['feeType' => $feeTypeId], $queryParams))
            ->with('status', $message);
    }

    public function activateAllStudents(Request $request, string $feeTypeId): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id'
        ]);

        $classId = $request->input('class_id');
        
        // Get all students in the selected class
        $students = StudentProfile::where('class_id', $classId)
            ->where('status', 'active')
            ->get();

        $activatedCount = 0;

        foreach ($students as $student) {
            $assignment = \App\Models\StudentFeeTypeAssignment::where('student_id', $student->id)
                ->where('fee_type_id', $feeTypeId)
                ->first();

            if ($assignment) {
                if (!$assignment->is_active) {
                    $assignment->is_active = true;
                    $assignment->save();
                    $activatedCount++;
                }
            } else {
                \App\Models\StudentFeeTypeAssignment::create([
                    'student_id' => $student->id,
                    'fee_type_id' => $feeTypeId,
                    'is_active' => true,
                ]);
                $activatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('finance.students_activated', ['count' => $activatedCount])
        ]);
    }

    public function sendInvoiceToStudent(Request $request, string $feeTypeId, string $studentId): JsonResponse
    {
        try {
            $feeType = FeeType::with('frequency')->findOrFail($feeTypeId);
            $student = StudentProfile::with(['user', 'guardians.user', 'grade', 'batch'])->findOrFail($studentId);
            
            // Check if student's grade is active
            if ($student->grade && $student->grade->hasEnded()) {
                return response()->json([
                    'success' => false,
                    'message' => __('finance.Cannot create invoice for inactive grade')
                ]);
            }
            
            // Check if student is activated for this fee type
            $assignment = \App\Models\StudentFeeTypeAssignment::where('student_id', $studentId)
                ->where('fee_type_id', $feeTypeId)
                ->where('is_active', true)
                ->first();
                
            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => __('finance.Student is not activated for this fee type')
                ]);
            }
            
            // Get current date and month
            $currentDate = now();
            $currentMonthStart = $currentDate->copy()->startOfMonth();
            $currentMonthEnd = $currentDate->copy()->endOfMonth();
            
            // Check if invoice already exists for this student and fee type for current month
            // Check both unpaid invoices and invoices from this month
            $existingInvoice = \App\Models\PaymentSystem\Invoice::where('student_id', $studentId)
                ->whereHas('fees', function($query) use ($feeTypeId) {
                    $query->where('fee_type_id', $feeTypeId);
                })
                ->where(function($query) use ($currentMonthStart, $currentMonthEnd) {
                    // Either has remaining amount OR was created this month
                    $query->where('remaining_amount', '>', 0)
                          ->orWhereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
                })
                ->first();
                
            if ($existingInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => __('finance.Invoice already exists for this fee type')
                ]);
            }
            
            // Generate unique invoice number
            $todayPrefix = 'INV' . date('Ymd');
            $lastInvoice = \App\Models\PaymentSystem\Invoice::where('invoice_number', 'like', $todayPrefix . '%')
                ->lockForUpdate()
                ->orderBy('invoice_number', 'desc')
                ->first();
            
            $counter = 1;
            if ($lastInvoice && preg_match('/INV\d{8}-(\d{4})/', $lastInvoice->invoice_number, $matches)) {
                $counter = intval($matches[1]) + 1;
            }
            
            $invoiceNumber = sprintf('INV%s-%04d', date('Ymd'), $counter);
            
            // Calculate due date based on fee type
            $dueDate = $currentDate->copy()->addDays($feeType->due_date ?? 15);
            
            // Get batch_id from student's grade
            $batchId = $student->grade?->batch_id;
            if (!$batchId) {
                // Fallback to active batch if student's grade doesn't have a batch
                $activeBatch = \App\Models\Batch::where('status', true)->first();
                $batchId = $activeBatch?->id;
            }
            
            // Create new invoice using PaymentSystem model
            $invoice = \App\Models\PaymentSystem\Invoice::create([
                'invoice_number' => $invoiceNumber,
                'student_id' => $studentId,
                'batch_id' => $batchId,
                'due_date' => $dueDate,
                'invoice_type' => 'one_time',
                'total_amount' => $feeType->amount,
                'paid_amount' => 0,
                'remaining_amount' => $feeType->amount,
                'status' => 'pending',
                'created_by' => $request->user()?->id,
            ]);
            
            // Create invoice fee item
            // Find or create the corresponding fee structure in payment system
            $feeStructure = \App\Models\PaymentSystem\FeeStructure::firstOrCreate(
                [
                    'fee_type' => $feeType->code ?? strtoupper(str_replace(' ', '_', $feeType->name)),
                    'grade' => (string) $student->grade->level,
                    'batch' => (string) $student->batch->name,
                ],
                [
                    'name' => $feeType->name,
                    'name_mm' => $feeType->name_mm,
                    'description' => $feeType->description,
                    'description_mm' => $feeType->description_mm,
                    'amount' => $feeType->amount,
                    'frequency' => $feeType->frequency?->name ?? 'one_time',
                    'target_month' => $feeType->target_month,
                    'due_date' => $dueDate,
                    'supports_payment_period' => $feeType->partial_status ?? false,
                    'is_active' => true,
                ]
            );
            
            \App\Models\PaymentSystem\InvoiceFee::create([
                'invoice_id' => $invoice->id,
                'fee_id' => $feeStructure->id,
                'fee_name' => $feeType->name,
                'fee_name_mm' => $feeType->name_mm,
                'amount' => $feeType->amount,
                'paid_amount' => 0,
                'remaining_amount' => $feeType->amount,
                'supports_payment_period' => $feeType->partial_status ?? false,
                'due_date' => $dueDate,
                'status' => 'unpaid',
                'fee_type_id' => $feeTypeId,
            ]);
            
            // Send FCM notification to guardians
            $this->sendInvoiceNotification($student, $invoice, $feeType);
            
            return response()->json([
                'success' => true,
                'message' => __('finance.Invoice sent successfully')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error sending invoice: ' . $e->getMessage(), [
                'fee_type_id' => $feeTypeId,
                'student_id' => $studentId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => __('finance.An error occurred')
            ]);
        }
    }

    /**
     * Bulk send invoices to all active students for a fee type
     */
    public function bulkSendInvoices(Request $request, string $feeTypeId): JsonResponse
    {
        try {
            $feeType = FeeType::with('frequency')->findOrFail($feeTypeId);

            // Get all active students assigned to this fee type
            $assignments = \App\Models\StudentFeeTypeAssignment::where('fee_type_id', $feeTypeId)
                ->where('is_active', true)
                ->with(['student.user', 'student.guardians.user', 'student.grade', 'student.batch'])
                ->get();

            if ($assignments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => __('finance.No active students found for this fee type')
                ]);
            }

            $currentDate = now();
            $createdCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($assignments as $assignment) {
                $student = $assignment->student;

                if (!$student) {
                    $skippedCount++;
                    continue;
                }
                
                // Skip if student's grade has ended
                if ($student->grade && $student->grade->hasEnded()) {
                    $skippedCount++;
                    continue;
                }

                try {
                    // Calculate due date based on fee type
                    $dueDate = $currentDate->copy()->addDays($feeType->due_date ?? 15);
                    $currentMonthStart = $currentDate->copy()->startOfMonth();
                    $currentMonthEnd = $currentDate->copy()->endOfMonth();

                    // Find or create the corresponding fee structure in payment system first
                    // This is needed for the duplicate check
                    $feeStructure = \App\Models\PaymentSystem\FeeStructure::firstOrCreate(
                        [
                            'fee_type' => $feeType->code ?? strtoupper(str_replace(' ', '_', $feeType->name)),
                            'grade' => (string) $student->grade->level,
                            'batch' => (string) $student->batch->name,
                        ],
                        [
                            'name' => $feeType->name,
                            'name_mm' => $feeType->name_mm,
                            'description' => $feeType->description,
                            'description_mm' => $feeType->description_mm,
                            'amount' => $feeType->amount,
                            'frequency' => $feeType->frequency?->name ?? 'one_time',
                            'target_month' => $feeType->target_month,
                            'due_date' => $dueDate,
                            'supports_payment_period' => $feeType->partial_status ?? false,
                            'is_active' => true,
                        ]
                    );
                    
                    // Check if invoice already exists for this student and fee type in current month
                    $existingInvoice = \App\Models\PaymentSystem\Invoice::where('student_id', $student->id)
                        ->whereHas('fees', function($query) use ($feeTypeId) {
                            $query->where('fee_type_id', $feeTypeId);
                        })
                        ->where(function($query) use ($currentMonthStart, $currentMonthEnd) {
                            // Either has remaining amount OR was created this month
                            $query->where('remaining_amount', '>', 0)
                                  ->orWhereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
                        })
                        ->first();

                    if ($existingInvoice) {
                        $skippedCount++;
                        continue;
                    }

                    // Generate unique invoice number
                    $todayPrefix = 'INV' . date('Ymd');
                    $lastInvoice = \App\Models\PaymentSystem\Invoice::where('invoice_number', 'like', $todayPrefix . '%')
                        ->lockForUpdate()
                        ->orderBy('invoice_number', 'desc')
                        ->first();

                    $counter = 1;
                    if ($lastInvoice && preg_match('/INV\d{8}-(\d{4})/', $lastInvoice->invoice_number, $matches)) {
                        $counter = intval($matches[1]) + 1;
                    }

                    $invoiceNumber = sprintf('INV%s-%04d', date('Ymd'), $counter);

                    // Get batch_id from student's grade
                    $batchId = $student->grade?->batch_id;
                    if (!$batchId) {
                        // Fallback to active batch if student's grade doesn't have a batch
                        $activeBatch = \App\Models\Batch::where('status', true)->first();
                        $batchId = $activeBatch?->id;
                    }

                    // Create new invoice using PaymentSystem model
                    $invoice = \App\Models\PaymentSystem\Invoice::create([
                        'invoice_number' => $invoiceNumber,
                        'student_id' => $student->id,
                        'batch_id' => $batchId,
                        'due_date' => $dueDate,
                        'invoice_type' => 'one_time',
                        'total_amount' => $feeType->amount,
                        'paid_amount' => 0,
                        'remaining_amount' => $feeType->amount,
                        'status' => 'pending',
                        'created_by' => $request->user()?->id,
                    ]);

                    \App\Models\PaymentSystem\InvoiceFee::create([
                        'invoice_id' => $invoice->id,
                        'fee_id' => $feeStructure->id,
                        'fee_name' => $feeType->name,
                        'fee_name_mm' => $feeType->name_mm,
                        'amount' => $feeType->amount,
                        'paid_amount' => 0,
                        'remaining_amount' => $feeType->amount,
                        'supports_payment_period' => $feeType->partial_status ?? false,
                        'due_date' => $dueDate,
                        'status' => 'unpaid',
                        'fee_type_id' => $feeTypeId,
                    ]);

                    // Send FCM notification to guardians
                    $this->sendInvoiceNotification($student, $invoice, $feeType);

                    $createdCount++;

                } catch (\Exception $e) {
                    \Log::error('Error creating invoice for student: ' . $e->getMessage(), [
                        'student_id' => $student->id,
                        'fee_type_id' => $feeTypeId,
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errors[] = $student->user->name ?? 'Unknown';
                    $skippedCount++;
                }
            }

            $message = __('finance.Invoices sent successfully') . ': ' . $createdCount;
            if ($skippedCount > 0) {
                $message .= ', ' . __('finance.Skipped') . ': ' . $skippedCount;
            }
            if (!empty($errors)) {
                $message .= ' (' . __('finance.Errors') . ': ' . implode(', ', $errors) . ')';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $createdCount,
                'skipped' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in bulk invoice generation: ' . $e->getMessage(), [
                'fee_type_id' => $feeTypeId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('finance.An error occurred')
            ]);
        }
    }

    
    private function sendInvoiceNotification($student, $invoice, $feeType)
    {
        // Get guardian FCM tokens from user relationship
        $guardians = $student->guardians;
        
        if ($guardians->isEmpty()) {
            \Log::warning('No guardians found for student', ['student_id' => $student->id]);
            return;
        }
        
        foreach ($guardians as $guardian) {
            $user = $guardian->user;
            
            if ($user && $user->fcm_token) {
                try {
                    // Send FCM notification
                    $notification = [
                        'title' => __('finance.New Invoice'),
                        'body' => __('finance.invoice_notification_body', [
                            'student' => $student->user->name,
                            'fee_type' => $feeType->name,
                            'amount' => number_format($invoice->total_amount, 0)
                        ]),
                        'data' => [
                            'type' => 'invoice',
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'amount' => $invoice->total_amount,
                            'student_id' => $student->id,
                            'fee_type_id' => $feeType->id,
                            'due_date' => $invoice->due_date->format('Y-m-d'),
                        ]
                    ];
                    
                    // Use your FCM service here
                    // Example: app('fcm')->sendNotification($user->fcm_token, $notification);
                    
                    \Log::info('Invoice notification prepared for guardian', [
                        'guardian_id' => $guardian->id,
                        'user_id' => $user->id,
                        'invoice_id' => $invoice->id,
                        'has_fcm_token' => !empty($user->fcm_token)
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send invoice notification', [
                        'guardian_id' => $guardian->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::warning('Guardian user has no FCM token', [
                    'guardian_id' => $guardian->id,
                    'user_id' => $user?->id
                ]);
            }
        }
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
    public function destroyInvoice(string $id): RedirectResponse
    {
        try {
            // Try to find in new PaymentSystem first
            $invoice = \App\Models\PaymentSystem\Invoice::find($id);
            $isLegacy = false;

            // If not found, try legacy system
            if (!$invoice) {
                $invoice = \App\Models\Invoice::find($id);
                $isLegacy = true;
            }

            if ($invoice) {
                // Check if this is a remaining balance invoice (partial payment invoice)
                if (!$isLegacy) {
                    $isRemainingInvoice = $invoice->invoice_type === 'remaining_balance' || $invoice->parent_invoice_id !== null;
                    
                    if ($isRemainingInvoice) {
                        return redirect()->route('student-fees.index')
                            ->with('error', __('finance.Cannot delete remaining balance invoices - this represents a partial payment that has been made.'));
                    }
                }
                
                // Check if this is a School Fee invoice (cannot be deleted)
                if (!$isLegacy) {
                    $isSchoolFee = $invoice->fees()->whereHas('feeType', function($q) {
                        $q->where('code', 'SCHOOL_FEE');
                    })->exists();
                    
                    if ($isSchoolFee) {
                        return redirect()->route('student-fees.index')
                            ->with('error', __('finance.Cannot delete School Fee invoices.'));
                    }
                }
                
                // Validation logic depending on system
                if (!$isLegacy) {
                    // New Payment System: Check for VERIFIED payments on THIS invoice
                    $hasVerifiedPayments = $invoice->payments()->where('status', 'verified')->exists();
                    
                    if ($hasVerifiedPayments) {
                         return redirect()->route('student-fees.index')
                            ->with('error', __('finance.Cannot delete invoice that has verified payments.'));
                    }
                    
                    // Check if there are OTHER verified payments for the same fee type and month
                    // Get the fee type ID from this invoice
                    $invoiceFee = $invoice->fees()->first();
                    if ($invoiceFee && $invoiceFee->fee_type_id) {
                        $invoiceMonthStart = $invoice->created_at->startOfMonth();
                        $invoiceMonthEnd = $invoice->created_at->copy()->endOfMonth();
                        
                        // Check if student has other verified payments for this fee type in this month
                        $hasOtherPayments = \App\Models\PaymentSystem\Payment::where('student_id', $invoice->student_id)
                            ->where('status', 'verified')
                            ->where('invoice_id', '!=', $invoice->id) // Exclude current invoice's payments
                            ->whereHas('invoice.fees', function($q) use ($invoiceFee) {
                                $q->where('fee_type_id', $invoiceFee->fee_type_id);
                            })
                            ->whereBetween('created_at', [$invoiceMonthStart, $invoiceMonthEnd])
                            ->exists();
                        
                        if ($hasOtherPayments) {
                            return redirect()->route('student-fees.index')
                                ->with('error', __('finance.Cannot delete invoice - student has already made payments for this fee type in this month.'));
                        }
                    }
                } else {
                    // Legacy System: Check paid_amount
                    if ($invoice->paid_amount > 0) {
                        return redirect()->route('student-fees.index')
                            ->with('error', __('finance.Cannot delete invoice that has payments. Please delete payments first.'));
                    }
                }
            } else {
                 return redirect()->route('student-fees.index')
                    ->with('error', __('finance.Invoice not found.'));
            }

            $invoiceNumber = $invoice->invoice_number;
            $studentName = $invoice->student?->user?->name ?? 'Unknown';

            // Delete related records first
            if ($isLegacy) {
                // Legacy system has items relation
                $invoice->items()->delete();
            } else {
                // New system: ensure payments are deleted (if not cascading)
                $invoice->payments()->delete();
            }
            
            // Both systems have fees relation
            $invoice->fees()->delete();
            
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

    // Legacy storePayment / confirmPayment / rejectPayment removed
    // All payment operations now use PaymentSystem models

    /**
     * Process Payment (PaymentSystem) - Full or Partial
     */
    public function processPaymentSystem(Request $request, \App\Models\PaymentSystem\Invoice $invoice)
    {
        // Calculate max months based on student's batch end date
        $student = $invoice->student;
        $maxMonths = 12; // Default fallback
        
        if ($student && $student->batch && $student->batch->end_date) {
            $now = now();
            $batchEndDate = $student->batch->end_date;
            $monthsUntilEnd = $now->diffInMonths($batchEndDate);
            $maxMonths = max(1, ceil($monthsUntilEnd)); // At least 1 month
        }
        
        $validated = $request->validate([
            'payment_type' => 'required|in:full,partial',
            'payment_months' => "required|integer|min:1|max:{$maxMonths}",
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'receipt_image' => 'nullable|image',
            'notes' => 'nullable|string',
            'fee_amounts' => 'nullable|array', // For partial payment
            'fee_amounts.*' => 'nullable|numeric|min:0',
            'fee_payment_months' => 'nullable|array', // For full payment with variable months per fee
            'fee_payment_months.*' => "nullable|integer|min:1|max:{$maxMonths}",
        ]);

        try {
            // Check if invoice has overdue fees (disable partial payment)
            $paymentService = app(\App\Services\PaymentSystem\PaymentProcessingService::class);
            
            if ($validated['payment_type'] === 'partial' && $paymentService->hasOverdueFees($invoice)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('finance.Cannot make partial payment - some fees are overdue')
                    ], 422);
                }
                return redirect()->back()->with('error', __('finance.Cannot make partial payment - some fees are overdue'));
            }
            
            // Check partial payment limit (max 2 partial payments per original invoice)
            if ($validated['payment_type'] === 'partial') {
                // Find the root/original invoice
                $rootInvoice = $invoice->parent_invoice_id ? 
                    \App\Models\PaymentSystem\Invoice::find($invoice->parent_invoice_id) : 
                    $invoice;
                
                // Count existing remaining balance invoices
                $existingRemainingCount = \App\Models\PaymentSystem\Invoice::where('parent_invoice_id', $rootInvoice->id)
                    ->where('invoice_type', 'remaining_balance')
                    ->count();
                
                if ($existingRemainingCount >= 2) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('finance.Maximum partial payment limit reached. Please pay the full remaining amount.')
                        ], 422);
                    }
                    return redirect()->back()->with('error', __('finance.Maximum partial payment limit reached. Please pay the full remaining amount.'));
                }
            }

            // Handle receipt image upload
            $receiptImageUrl = null;
            if ($request->hasFile('receipt_image')) {
                $receiptImageUrl = app(FileUploadService::class)->storeOptimizedUploadedImage(
                    $request->file('receipt_image'),
                    'payment-receipts',
                    'public',
                    'payment_receipt'
                );
            }

            // Prepare payment data
            $paymentData = [
                'payment_type' => $validated['payment_type'],
                'payment_months' => $validated['payment_months'],
                'payment_method_id' => $validated['payment_method_id'],
                'payment_date' => $validated['payment_date'],
                'receipt_image_url' => $receiptImageUrl,
                'notes' => $validated['notes'] ?? null,
                'fee_amounts' => $validated['fee_amounts'] ?? [],
                'fee_payment_months' => $validated['fee_payment_months'] ?? [],
            ];

            // Process payment
            $result = $paymentService->processPayment($invoice, $paymentData);

            if ($result['success']) {
                $message = __('finance.Payment processed successfully');
                
                if ($result['discount_applied'] > 0) {
                    $message .= ' ' . __('finance.Discount applied') . ': ' . number_format($result['discount_applied']) . ' MMK';
                }
                
                if ($result['remaining_invoice']) {
                    $message .= ' ' . __('finance.Remaining invoice created') . ': ' . $result['remaining_invoice']->invoice_number;
                }

                $this->logCreate('Payment', $result['payment']->id, "Processed payment: {$result['payment']->payment_number}");

                if ($request->expectsJson() || $request->ajax()) {
                    // Return redirect URL instead of payment data
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'redirect_url' => route('student-fees.payment-receipt', ['payment' => $result['payment']->id])
                    ]);
                }
                
                // For non-AJAX requests, redirect to receipt page
                return redirect()->route('student-fees.payment-receipt', ['payment' => $result['payment']->id])
                    ->with('status', $message);
            } else {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['error']
                    ], 422);
                }
                return redirect()->back()->with('error', $result['error']);
            }

        } catch (\Exception $e) {
            \Log::error('Payment processing error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('finance.Payment processing failed') . ': ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', __('finance.Payment processing failed') . ': ' . $e->getMessage());
        }
    }

    public function showPaymentReceipt(\App\Models\PaymentSystem\Payment $payment): View
    {
        // Load relationships
        $payment->load(['student.user', 'student.grade', 'student.classModel', 'student.guardians.user', 'paymentMethod', 'invoice.fees.feeStructure', 'feeDetails']);
        
        // Get guardian name
        $guardianName = $payment->student?->guardians?->first()?->user?->name ?? 'N/A';
        
        // Get formatted class name (e.g., "Kindergarten A" for Grade 0, "Grade 1 A" for Grade 1)
        $className = $payment->student?->formatted_class_name ?? 'N/A';
        
        // Calculate discount information
        $paymentMonths = $payment->payment_months ?? 1;
        $subtotal = 0;
        $discountAmount = 0;
        
        // Calculate subtotal from the fees that were actually paid
        // Use feeDetails to get the actual fees included in this payment
        if ($payment->feeDetails && $payment->feeDetails->count() > 0) {
            foreach ($payment->feeDetails as $feeDetail) {
                // For multi-month payments, subtotal = unit price × months
                $months = $feeDetail->payment_months ?? 1;
                $subtotal += $feeDetail->full_amount * $months;
            }
        } elseif ($payment->invoice && $payment->invoice->fees) {
            // Fallback: use invoice fees
            foreach ($payment->invoice->fees as $fee) {
                $subtotal += $fee->amount * $paymentMonths;
            }
        }
        
        // Calculate discount: subtotal - actual payment amount
        $discountAmount = $subtotal - $payment->payment_amount;
        
        // Check if this is a partial payment and get remaining invoice info
        $isPartialPayment = $payment->payment_type === 'partial';
        $remainingAmount = 0;
        $remainingInvoiceNumber = null;
        
        if ($isPartialPayment && $payment->invoice) {
            // For the first partial payment, get remaining amount from the original invoice
            if ($payment->invoice->invoice_type !== 'remaining_balance') {
                // This is the first partial payment on the original invoice
                $remainingAmount = $payment->invoice->remaining_amount;
            } else {
                // This is a payment on a remaining balance invoice
                // Find the next remaining balance invoice if it exists
                $remainingInvoice = \App\Models\PaymentSystem\Invoice::where('parent_invoice_id', $payment->invoice->parent_invoice_id ?: $payment->invoice->id)
                    ->where('invoice_type', 'remaining_balance')
                    ->where('created_at', '>', $payment->invoice->created_at)
                    ->orderBy('created_at', 'asc')
                    ->first();
                
                if ($remainingInvoice) {
                    $remainingInvoiceNumber = $remainingInvoice->invoice_number;
                    $remainingAmount = $remainingInvoice->total_amount;
                } else {
                    // No more remaining invoices, calculate from current invoice
                    $remainingAmount = $payment->invoice->remaining_amount;
                }
            }
        }
        
        return view('finance.payment-receipt', [
            'payment' => $payment,
            'guardianName' => $guardianName,
            'className' => $className,
            'paymentMonths' => $paymentMonths,
            'subtotal' => $subtotal,
            'discountAmount' => $discountAmount,
            'isPartialPayment' => $isPartialPayment,
            'remainingAmount' => $remainingAmount,
            'remainingInvoiceNumber' => $remainingInvoiceNumber,
        ]);
    }

    public function updateGradeFee(Request $request, Grade $grade): RedirectResponse
    {
        $validated = $request->validate([
            'price_per_month' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'integer', 'min:1', 'max:31'],
        ]);
      
        // Check if trying to clear the fee (set to 0)
        if ($validated['price_per_month'] == 0) {
            // Count how many grades currently have fees set
            $gradesWithFees = Grade::where('price_per_month', '>', 0)->count();
            
            // If this is the only grade with a fee, prevent clearing it
            if ($gradesWithFees <= 1 && $grade->price_per_month > 0) {
                return redirect()->route('student-fees.index', ['tab' => 'structure'])
                    ->with('error', __('finance.Please set at least one grade fee'));
            }
        }

        $grade->update([
            'price_per_month' => $validated['price_per_month'],
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return redirect()->route('student-fees.index', ['tab' => 'structure'])->with('status', __('Grade fee updated.'));
    }

    /**
     * Approve Payment Proof from Mobile API
     * DEPRECATED: This method is for the old Finance system
     */
    // public function approvePaymentProof(Request $request, \App\Models\PaymentProof $paymentProof): RedirectResponse
    // {
    //     // This method has been deprecated - use PaymentSystem instead
    //     return redirect()->route('student-fees.index')->with('error', __('This feature is no longer available. Please use the new payment system.'));
    // }

    /**
     * Approve PaymentSystem Payment from Mobile API
     */
    public function approvePaymentSystemPayment(Request $request, \App\Models\PaymentSystem\Payment $payment): RedirectResponse
    {
        // Guard: only pending_verification payments can be approved
        if ($payment->status !== 'pending_verification') {
            return redirect()->route('student-fees.index')
                ->with('error', __('Payment is not pending verification. Current status: ') . $payment->status);
        }

        try {
            \DB::beginTransaction();

            // Update payment status to verified
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => $request->user()->id,
            ]);

            // Update invoice paid amounts and statuses
            $invoice = $payment->invoice;
            if ($invoice) {
                $invoice->paid_amount += $payment->payment_amount;
                $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;
                
                // Update invoice status based on new 4-status system
                // pending -> waiting -> rejected/paid
                if ($invoice->remaining_amount <= 0) {
                    $invoice->status = 'paid'; // Fully paid
                } else {
                    // If partially paid but still has remaining amount, keep as waiting
                    // (guardian submitted payment, staff approved partial)
                    $invoice->status = 'waiting';
                }
                $invoice->save();

                // Update individual invoice fee amounts and statuses
                foreach ($payment->feeDetails as $feeDetail) {
                    $invoiceFee = $invoice->fees()->where('id', $feeDetail->invoice_fee_id)->first();
                    if ($invoiceFee) {
                        $invoiceFee->paid_amount += $feeDetail->paid_amount;
                        $invoiceFee->remaining_amount = $invoiceFee->amount - $invoiceFee->paid_amount;
                        
                        // Update fee status based on new system
                        if ($invoiceFee->remaining_amount <= 0) {
                            $invoiceFee->status = 'paid';
                        } else {
                            $invoiceFee->status = 'waiting'; // Partially paid
                        }
                        $invoiceFee->save();
                    }
                }
            }

            \DB::commit();

            $this->logUpdate('PaymentSystemPayment', $payment->id, "Approved payment: {$payment->payment_number} for student: {$payment->student->student_identifier}");

            // Send FCM notification to guardian (non-blocking)
            try {
                $this->paymentNotificationService->notifyGuardianOfVerification($payment);
            } catch (\Exception $e) {
                \Log::warning('Failed to send approval notification', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->route('student-fees.index')->with('status', __('Payment approved successfully.'));
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('PaymentSystem payment approval failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('student-fees.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Reject PaymentSystem Payment from Mobile API
     * Uses PaymentVerificationService for proper rollback + notification
     */
    public function rejectPaymentSystemPayment(Request $request, \App\Models\PaymentSystem\Payment $payment): RedirectResponse
    {
        // Guard: only pending_verification payments can be rejected
        if ($payment->status !== 'pending_verification') {
            return redirect()->route('student-fees.index')
                ->with('error', __('Payment is not pending verification. Current status: ') . $payment->status);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            // Delegate to PaymentVerificationService — handles rollback + notification
            $this->verificationService->rejectPayment(
                $payment,
                $validated['rejection_reason'],
                $request->user()
            );

            $this->logUpdate('PaymentSystemPayment', $payment->id, "Rejected payment: {$payment->payment_number} for student: {$payment->student->student_identifier}");

            return redirect()->route('student-fees.index')->with('status', __('Payment rejected. A new invoice has been created for the guardian to resubmit payment.'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('student-fees.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('PaymentSystem payment rejection failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('student-fees.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Get PaymentSystem Payment Details (AJAX)
     */
    public function getPaymentSystemPaymentDetails(\App\Models\PaymentSystem\Payment $payment): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'payment_number' => $payment->payment_number,
                'invoice_number' => $payment->invoice->invoice_number ?? 'N/A',
                'status' => $payment->status,
                'student' => [
                    'name' => $payment->student->user->name ?? 'N/A',
                    'identifier' => $payment->student->student_identifier ?? 'N/A',
                    'grade' => $payment->student->grade ? app(\App\Helpers\GradeHelper::class)::getLocalizedName($payment->student->grade->level) : 'N/A',
                    'class' => $payment->student->classModel ? $payment->student->classModel->name : 'N/A',
                ],
                'payment_amount' => $payment->payment_amount,
                'payment_months' => $payment->payment_months,
                'payment_date' => $payment->payment_date->format('M j, Y'),
                'payment_method' => $payment->paymentMethod->name ?? 'N/A',
                'receipt_image' => $payment->receipt_image_url,
                'notes' => $payment->notes,
                'submitted_at' => $payment->created_at->diffForHumans(),
                'rejection_reason' => $payment->rejection_reason,
                'fee_breakdown' => $payment->feeDetails->map(function ($detail) {
                    return [
                        'fee_name' => $detail->fee_name,
                        'fee_name_mm' => $detail->fee_name_mm,
                        'full_amount' => $detail->full_amount,
                        'paid_amount' => $detail->paid_amount,
                        'is_partial' => $detail->is_partial,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get PaymentSystem payment details', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject Payment Proof from Mobile API
     * DEPRECATED: This method is for the old Finance system
     */
    // public function rejectPaymentProof(RejectPaymentProofRequest $request, \App\Models\PaymentProof $paymentProof): RedirectResponse
    // {
    //     // This method has been deprecated - use PaymentSystem instead
    //     return redirect()->route('student-fees.index')->with('error', __('This feature is no longer available. Please use the new payment system.'));
    // }

    /**
     * Get Payment Proof Details (AJAX)
     * DEPRECATED: This method is for the old Finance system
     */
    // public function getPaymentProofDetails(\App\Models\PaymentProof $paymentProof): \Illuminate\Http\JsonResponse
    // {
    //     // This method has been deprecated - use PaymentSystem instead
    //     return response()->json([
    //         'success' => false,
    //         'message' => __('This feature is no longer available. Please use the new payment system.'),
    //     ], 410);
    // }

    /**
     * Get PaymentSystem Payment Details (AJAX) - For mobile payments
     */
    public function getPaymentSystemDetails(\App\Models\PaymentSystem\Payment $payment): \Illuminate\Http\JsonResponse
    {
        try {
            $payment->load(['student.user', 'student.grade', 'student.classModel', 'paymentMethod', 'invoice', 'feeDetails']);

            $data = [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'status' => $payment->status,
                'student' => [
                    'name' => $payment->student->user->name ?? 'N/A',
                    'identifier' => $payment->student->student_identifier ?? 'N/A',
                    'grade' => $payment->student->grade ? __('grades.' . $payment->student->grade->level) : 'N/A',
                    'class' => $payment->student->classModel->name ?? 'N/A',
                ],
                'invoice_number' => $payment->invoice->invoice_number ?? 'N/A',
                'payment_amount' => $payment->payment_amount,
                'payment_type' => $payment->payment_type,
                'payment_months' => $payment->payment_months,
                'payment_date' => $payment->payment_date->format('M j, Y'),
                'payment_method' => $payment->paymentMethod->name ?? 'N/A',
                'receipt_image' => $payment->receipt_image_url,
                'notes' => $payment->notes,
                'submitted_at' => $payment->created_at->diffForHumans(),
                'rejection_reason' => $payment->rejection_reason,
                'fee_details' => $payment->feeDetails->map(function ($detail) {
                    return [
                        'fee_name' => $detail->fee_name,
                        'fee_name_mm' => $detail->fee_name_mm,
                        'full_amount' => $detail->full_amount,
                        'paid_amount' => $detail->paid_amount,
                        'is_partial' => $detail->is_partial,
                        'payment_months' => $detail->payment_months ?? 1,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get payment system details', [
                'payment_id' => $payment->id,
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
            // Get unpaid invoices for this student from the Payment System
            $unpaidInvoices = \App\Models\PaymentSystem\Invoice::where('student_id', $student->id)
                ->where('status', '!=', 'paid')
                ->where('remaining_amount', '>', 0)
                ->with(['fees.feeStructure'])
                ->orderBy('due_date', 'asc')
                ->get();

            if ($unpaidInvoices->isEmpty()) {
                return redirect()->route('student-fees.index')->with('error', __('No unpaid fees found for this student.'));
            }

            // Send reinform notification
            $this->paymentNotificationService->sendReinformNotification($student->id, $unpaidInvoices);

            $this->logCreate('NotificationLog', $student->id, "Sent reinform notification to guardian of student: {$student->student_identifier}");

            return redirect()->route('student-fees.index')->with('status', __('Reminder notification sent to guardian.'));
        } catch (\Exception $e) {
            \Log::error('Failed to send reinform notification', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('student-fees.index')->with('error', __('Failed to send notification. Please try again.'));
        }
    }

    /**
     * Send reminder notifications to all unpaid students
     */
    public function remindAll(Request $request): RedirectResponse
    {
        try {
            $feeMonth = $request->input('fee_month', now()->format('Y-m'));
            $gradeId = $request->input('fee_grade');
            $feeTypeId = $request->input('fee_fee_type');
            $search = $request->input('fee_search');

            // Build query for unpaid invoices
            $invoicesQuery = \App\Models\PaymentSystem\Invoice::query()
                ->where('status', '!=', 'paid')
                ->where('remaining_amount', '>', 0)
                ->whereYear('due_date', '=', substr($feeMonth, 0, 4))
                ->whereMonth('due_date', '=', substr($feeMonth, 5, 2))
                ->with(['student.user', 'fees.feeStructure']);

            // Apply filters
            if ($gradeId) {
                $invoicesQuery->whereHas('student', function ($q) use ($gradeId) {
                    $q->where('grade_id', $gradeId);
                });
            }

            if ($feeTypeId) {
                $invoicesQuery->whereHas('fees.feeStructure', function ($q) use ($feeTypeId) {
                    $q->where('fee_type_id', $feeTypeId);
                });
            }

            if ($search) {
                $invoicesQuery->whereHas('student', function ($q) use ($search) {
                    $q->where('student_identifier', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $invoices = $invoicesQuery->get();

            if ($invoices->isEmpty()) {
                return redirect()->route('student-fees.index')->with('error', __('finance.No unpaid students found with the selected filters.'));
            }

            // Group invoices by student
            $studentInvoices = $invoices->groupBy('student_id');

            $successCount = 0;
            $failCount = 0;

            foreach ($studentInvoices as $studentId => $studentInvoiceGroup) {
                try {
                    // Send reminder notification
                    $this->paymentNotificationService->sendReinformNotification($studentId, $studentInvoiceGroup);
                    $successCount++;
                    
                    $student = $studentInvoiceGroup->first()->student;
                    $this->logCreate('NotificationLog', $studentId, "Sent bulk reminder notification to guardian of student: {$student->student_identifier}");
                } catch (\Exception $e) {
                    \Log::error('Failed to send reminder to student', [
                        'student_id' => $studentId,
                        'error' => $e->getMessage(),
                    ]);
                    $failCount++;
                }
            }

            $message = __('finance.Sent :count reminder(s) successfully.', ['count' => $successCount]);
            if ($failCount > 0) {
                $message .= ' ' . __('finance.:count failed.', ['count' => $failCount]);
            }

            return redirect()->route('student-fees.index')->with('status', $message);
        } catch (\Exception $e) {
            \Log::error('Failed to send bulk reminders', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('student-fees.index')->with('error', __('finance.Failed to send reminders. Please try again.'));
        }
    }

    public function storePaymentMethod(Request $request): RedirectResponse
        {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'name_mm' => 'nullable|string|max:255',
                'type' => 'required|in:bank,mobile_wallet,other',
                'account_number' => 'required|string|max:255',
                'account_name' => 'required|string|max:255',
                'account_name_mm' => 'nullable|string|max:255',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp',
                'is_active' => 'boolean',
                'instructions' => 'nullable|string',
                'instructions_mm' => 'nullable|string',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $validated['is_active'] = $request->has('is_active');

            // Handle logo upload with compression
            if ($request->hasFile('logo')) {
                $validated['logo_url'] = $this->uploadAndCompressLogo($request->file('logo'));
            }

            \App\Models\PaymentMethod::create($validated);

            return redirect()->route('student-fees.index', ['tab' => 'payment-methods'])->with('status', __('Payment method created successfully.'));
        }

    public function updatePaymentMethod(Request $request, \App\Models\PaymentMethod $paymentMethod): RedirectResponse
        {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'name_mm' => 'nullable|string|max:255',
                'type' => 'required|in:bank,mobile_wallet,other',
                'account_number' => 'required|string|max:255',
                'account_name' => 'required|string|max:255',
                'account_name_mm' => 'nullable|string|max:255',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp',
                'is_active' => 'boolean',
                'instructions' => 'nullable|string',
                'instructions_mm' => 'nullable|string',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $validated['is_active'] = $request->has('is_active');

            // Handle logo upload with compression
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($paymentMethod->logo_url && \Storage::disk('public')->exists($paymentMethod->logo_url)) {
                    \Storage::disk('public')->delete($paymentMethod->logo_url);
                }
                $validated['logo_url'] = $this->uploadAndCompressLogo($request->file('logo'));
            }

            // Update using Eloquent model (handles SQLite enum constraints properly)
            $paymentMethod->update($validated);

            return redirect()->route('student-fees.index', ['tab' => 'payment-methods'])->with('status', __('Payment method updated successfully.'));
        }

    public function destroyPaymentMethod(\App\Models\PaymentMethod $paymentMethod): RedirectResponse
    {
        // Check if payment method is in use
        $inUse = \App\Models\PaymentProof::where('payment_method_id', $paymentMethod->id)->exists();
        
        if ($inUse) {
            return redirect()->route('student-fees.index')->with('error', __('Cannot delete payment method that is in use.'));
        }

        $paymentMethod->delete();

        return redirect()->route('student-fees.index', ['tab' => 'payment-methods'])->with('status', __('Payment method deleted successfully.'));
    }

    public function updatePromotion(Request $request, string $promotionId): RedirectResponse
    {
        try {
            // Manually find the promotion by UUID
            $promotion = \App\Models\PaymentPromotion::findOrFail($promotionId);
            
            $validated = $request->validate([
                'discount_percent' => 'required|numeric|min:0|max:100',
                'is_active' => 'boolean',
            ]);

            $validated['is_active'] = $request->has('is_active');

            $promotion->update($validated);
       
            return redirect()->route('student-fees.index', ['tab' => 'structure'])->with('status', __('Promotion updated successfully.'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('student-fees.index', ['tab' => 'structure'])->with('error', __('Promotion not found.'));
        } catch (\Exception $e) {
            \Log::error('Failed to update promotion', [
                'promotion_id' => $promotionId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('student-fees.index', ['tab' => 'structure'])->with('error', __('Failed to update promotion: ' . $e->getMessage()));
        }
    }

    /**
     * Upload and compress payment method logo
     */
    private function uploadAndCompressLogo($file): string
    {
        return app(FileUploadService::class)->storeOptimizedUploadedImage(
            $file,
            'payment_methods',
            'public',
            'payment_logo'
        );
    }
}
