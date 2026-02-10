<?php

namespace App\Http\Controllers;

use App\Models\PaymentProof;
use App\Models\Grade;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentProofController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of payment proofs
     */
    public function index(Request $request): View
    {
        $query = PaymentProof::with(['student.user', 'student.grade', 'student.classModel', 'paymentMethod', 'verifiedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by grade
        if ($request->filled('grade')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->grade);
            });
        }

        // Search by student name or ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_identifier', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $paymentProofs = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'pending' => PaymentProof::pending()->count(),
            'verified' => PaymentProof::verified()->count(),
            'rejected' => PaymentProof::rejected()->count(),
            'total_pending_amount' => PaymentProof::pending()->sum('payment_amount'),
        ];

        $grades = Grade::orderBy('level')->get();

        return view('student-fees.payment-proofs.index', compact('paymentProofs', 'stats', 'grades'));
    }

    /**
     * Display the specified payment proof
     */
    public function show(string $id): View
    {
        $paymentProof = PaymentProof::with([
            'student.user',
            'student.grade',
            'student.classModel',
            'paymentMethod',
            'verifiedBy'
        ])->findOrFail($id);

        return view('student-fees.payment-proofs.show', compact('paymentProof'));
    }

    /**
     * Approve a payment proof
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        $paymentProof = PaymentProof::findOrFail($id);

        if ($paymentProof->status !== 'pending_verification') {
            return back()->with('error', 'This payment proof has already been processed.');
        }

        $paymentProof->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->logActivity(
            'approved',
            'PaymentProof',
            $paymentProof->id,
            "Approved payment proof for student {$paymentProof->student->user->name} - Amount: {$paymentProof->payment_amount} MMK"
        );

        return back()->with('success', 'Payment proof approved successfully.');
    }

    /**
     * Reject a payment proof
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $paymentProof = PaymentProof::findOrFail($id);

        if ($paymentProof->status !== 'pending_verification') {
            return back()->with('error', 'This payment proof has already been processed.');
        }

        $paymentProof->update([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        $this->logActivity(
            'rejected',
            'PaymentProof',
            $paymentProof->id,
            "Rejected payment proof for student {$paymentProof->student->user->name} - Reason: {$request->rejection_reason}"
        );

        return back()->with('success', 'Payment proof rejected.');
    }

    /**
     * Bulk approve payment proofs
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_proof_ids' => 'required|array',
            'payment_proof_ids.*' => 'exists:payment_proofs,id',
        ]);

        $count = 0;
        foreach ($request->payment_proof_ids as $id) {
            $paymentProof = PaymentProof::find($id);
            
            if ($paymentProof && $paymentProof->status === 'pending_verification') {
                $paymentProof->update([
                    'status' => 'verified',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);
                $count++;
            }
        }

        $this->logActivity(
            'bulk_approved',
            'PaymentProof',
            null,
            "Bulk approved {$count} payment proofs"
        );

        return back()->with('success', "{$count} payment proofs approved successfully.");
    }

    /**
     * Download receipt image
     */
    public function downloadReceipt(string $id)
    {
        $paymentProof = PaymentProof::findOrFail($id);

        if (!$paymentProof->receipt_image) {
            return back()->with('error', 'No receipt image found.');
        }

        $path = storage_path('app/public/' . $paymentProof->receipt_image);

        if (!file_exists($path)) {
            return back()->with('error', 'Receipt file not found.');
        }

        return response()->download($path);
    }
}
