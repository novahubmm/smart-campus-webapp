<?php

namespace App\Repositories;

use App\DTOs\Finance\FeeFilterData;
use App\DTOs\Finance\FeeStructureData;
use App\DTOs\Finance\InvoiceData;
use App\DTOs\Finance\PaymentData;
use App\Interfaces\StudentFeeRepositoryInterface;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\StudentProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StudentFeeRepository implements StudentFeeRepositoryInterface
{
    public function listInvoices(FeeFilterData $filter): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->with(['student.grade', 'student.classModel', 'items.feeType'])
            ->latest('invoice_date');

        if ($filter->status && $filter->status !== 'all') {
            $query->where('status', $filter->status);
        }

        if ($filter->grade_id) {
            $query->whereHas('student', fn($q) => $q->where('grade_id', $filter->grade_id));
        }

        if ($filter->class_id) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $filter->class_id));
        }

        if ($filter->month && $filter->month !== 'all') {
            // Handle Y-m format (e.g., "2026-01")
            if (preg_match('/^\d{4}-\d{2}$/', $filter->month)) {
                $monthStart = \Carbon\Carbon::parse($filter->month . '-01')->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $query->whereBetween('invoice_date', [$monthStart, $monthEnd]);
            } else {
                // Fallback to month number only
                $query->whereMonth('invoice_date', $filter->month);
            }
        }

        if ($filter->search) {
            $query->whereHas('student', function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter->search}%")
                    ->orWhere('student_identifier', 'like', "%{$filter->search}%");
            });
        }

        return $query->paginate(12);
    }

    public function listPayments(FeeFilterData $filter): LengthAwarePaginator
    {
        $query = Payment::query()
            ->with(['student.grade', 'student.classModel', 'student.guardians.user', 'collectedBy'])
            ->latest('payment_date');

        if ($filter->month && $filter->month !== 'all') {
            // Handle Y-m format (e.g., "2026-01")
            if (preg_match('/^\d{4}-\d{2}$/', $filter->month)) {
                $monthStart = \Carbon\Carbon::parse($filter->month . '-01')->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $query->whereBetween('payment_date', [$monthStart, $monthEnd]);
            } else {
                // Fallback to month number only
                $query->whereMonth('payment_date', $filter->month);
            }
        }

        if ($filter->search) {
            $query->whereHas('student', function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter->search}%")
                    ->orWhere('student_identifier', 'like', "%{$filter->search}%");
            });
        }

        return $query->paginate(12);
    }

    public function listStructures(): Collection
    {
        return FeeStructure::with(['grade', 'batch', 'feeType'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function createStructure(FeeStructureData $data): FeeStructure
    {
        return FeeStructure::create($data->toArray());
    }

    public function updateStructure(FeeStructure $structure, FeeStructureData $data): FeeStructure
    {
        $structure->update($data->toArray());

        return $structure->fresh(['grade', 'batch', 'feeType']);
    }

    public function deleteStructure(FeeStructure $structure): void
    {
        $structure->delete();
    }

    public function createInvoice(InvoiceData $data, ?string $createdBy = null): Invoice
    {
        $subtotal = collect($data->items)->sum(fn($item) => $item->amount());
        $total = max(0, $subtotal - $data->discount);

        $invoice = Invoice::create([
            'invoice_number' => $this->nextInvoiceNumber(),
            'student_id' => $data->student_id,
            'invoice_date' => $data->invoice_date,
            'due_date' => $data->due_date,
            'subtotal' => $subtotal,
            'discount' => $data->discount,
            'total_amount' => $total,
            'paid_amount' => 0,
            'balance' => $total,
            'status' => $data->status,
            'notes' => $data->notes,
            'created_by' => $createdBy,
        ]);

        foreach ($data->items as $item) {
            InvoiceItem::create(array_merge($item->toArray(), ['invoice_id' => $invoice->id]));
        }

        return $invoice->fresh(['items.feeType', 'student']);
    }

    public function updateInvoice(Invoice $invoice, InvoiceData $data): Invoice
    {
        $subtotal = collect($data->items)->sum(fn($item) => $item->amount());
        $total = max(0, $subtotal - $data->discount);
        $paid = $invoice->paid_amount;
        $balance = max(0, $total - $paid);

        $invoice->fill([
            'student_id' => $data->student_id,
            'invoice_date' => $data->invoice_date,
            'due_date' => $data->due_date,
            'subtotal' => $subtotal,
            'discount' => $data->discount,
            'total_amount' => $total,
            'balance' => $balance,
            'status' => $data->status,
            'notes' => $data->notes,
        ]);
        $invoice->save();

        $invoice->items()->delete();
        foreach ($data->items as $item) {
            InvoiceItem::create(array_merge($item->toArray(), ['invoice_id' => $invoice->id]));
        }

        return $invoice->fresh(['items.feeType', 'student']);
    }

    public function createPayment(PaymentData $data): Payment
    {
        $payment = Payment::create([
            'payment_number' => $this->nextPaymentNumber(),
            'student_id' => $data->student_id,
            'amount' => $data->amount,
            'payment_date' => $data->payment_date,
            'payment_method' => $data->payment_method,
            'transaction_id' => $data->transaction_id,
            'reference_number' => $data->reference_number,
            'notes' => $data->notes,
            'receptionist_id' => $data->receptionist_id,
            'receptionist_name' => $data->receptionist_name,
            'collected_by' => $data->collected_by,
            'status' => true,
        ]);

        // Process payment items
        foreach ($data->items as $item) {
            $invoiceId = $item->invoice_id;
            
            // If no invoice exists, create one for this student's monthly fee
            if (!$invoiceId) {
                $student = StudentProfile::with('grade')->find($data->student_id);
                if ($student) {
                    $invoice = Invoice::create([
                        'invoice_number' => $this->nextInvoiceNumber(),
                        'student_id' => $data->student_id,
                        'invoice_date' => now(),
                        'due_date' => now()->endOfMonth(),
                        'total_amount' => $item->amount,
                        'paid_amount' => $item->amount,
                        'balance' => 0,
                        'status' => 'paid',
                        'notes' => 'Auto-generated invoice for ' . now()->format('F Y') . ' fee payment',
                    ]);
                    $invoiceId = $invoice->id;
                }
            }
            
            if ($invoiceId) {
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoiceId,
                    'amount' => $item->amount,
                ]);

                // Update existing invoice if it wasn't just created
                $invoice = Invoice::find($invoiceId);
                if ($invoice && $invoice->status !== 'paid') {
                    $invoice->paid_amount += $item->amount;
                    $invoice->balance = max(0, $invoice->total_amount - $invoice->paid_amount);
                    $invoice->status = $invoice->balance <= 0 ? 'paid' : 'partial';
                    $invoice->save();
                }
            }
        }

        return $payment->fresh(['items.invoice', 'student']);
    }

    private function nextInvoiceNumber(): string
    {
        $count = Invoice::withTrashed()->count() + 1;
        return 'INV-' . Str::padLeft((string) $count, 5, '0');
    }

    private function nextPaymentNumber(): string
    {
        $count = Payment::withTrashed()->count() + 1;
        return 'PAY-' . Str::padLeft((string) $count, 5, '0');
    }
}
