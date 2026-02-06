@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Fees',
    'headerTitle' => 'Fees',
    'activeNav' => 'fees',
    'role' => 'guardian'
])

@section('content')
<div class="pwa-container">
    <!-- Student Selector -->
    <div class="pwa-card">
        <div class="pwa-form-group">
            <label class="pwa-label">Select Student</label>
            <select class="pwa-select" id="student-selector">
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Fee Summary -->
    <div class="pwa-card">
        <h3 class="pwa-card-title">Fee Summary</h3>
        <div class="pwa-fee-summary">
            <div class="pwa-fee-item">
                <span class="pwa-fee-label">Total Fees</span>
                <span class="pwa-fee-value">{{ number_format($selectedStudent->total_fees) }} MMK</span>
            </div>
            <div class="pwa-fee-item">
                <span class="pwa-fee-label">Paid</span>
                <span class="pwa-fee-value paid">{{ number_format($selectedStudent->paid_fees) }} MMK</span>
            </div>
            <div class="pwa-fee-item">
                <span class="pwa-fee-label">Outstanding</span>
                <span class="pwa-fee-value outstanding">{{ number_format($selectedStudent->outstanding_fees) }} MMK</span>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="pwa-section">
        <h3 class="pwa-section-title">Payment History</h3>
        @forelse($payments as $payment)
            <div class="pwa-card">
                <div class="pwa-payment-header">
                    <div>
                        <h3 class="pwa-payment-title">{{ $payment->fee_type }}</h3>
                        <p class="pwa-payment-date">{{ $payment->paid_at->format('M d, Y') }}</p>
                    </div>
                    <span class="pwa-badge pwa-badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                </div>
                <div class="pwa-payment-amount">{{ number_format($payment->amount) }} MMK</div>
                @if($payment->receipt_number)
                    <div class="pwa-payment-receipt">
                        Receipt #{{ $payment->receipt_number }}
                    </div>
                @endif
            </div>
        @empty
            <div class="pwa-empty-state">
                <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="pwa-empty-text">No payment history</p>
            </div>
        @endforelse
    </div>

    <!-- Pending Invoices -->
    @if(count($pendingInvoices) > 0)
    <div class="pwa-section">
        <h3 class="pwa-section-title">Pending Invoices</h3>
        @foreach($pendingInvoices as $invoice)
            <div class="pwa-card pwa-invoice-card">
                <div class="pwa-invoice-header">
                    <div>
                        <h3 class="pwa-invoice-title">{{ $invoice->fee_type }}</h3>
                        <p class="pwa-invoice-due">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                    </div>
                    <div class="pwa-invoice-amount">{{ number_format($invoice->amount) }} MMK</div>
                </div>
                <button class="pwa-btn pwa-btn-primary" onclick="payInvoice({{ $invoice->id }})">
                    Pay Now
                </button>
            </div>
        @endforeach
    </div>
    @endif
</div>

<style>
.pwa-fee-summary {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: 16px;
}

.pwa-fee-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 16px;
    border-bottom: 1px solid #E5E5EA;
}

.pwa-fee-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.pwa-fee-label {
    font-size: 14px;
    color: #6E6E73;
}

.pwa-fee-value {
    font-size: 18px;
    font-weight: 700;
    color: #1C1C1E;
}

.pwa-fee-value.paid {
    color: #34C759;
}

.pwa-fee-value.outstanding {
    color: #FF3B30;
}

.pwa-payment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.pwa-payment-title {
    font-size: 16px;
    font-weight: 600;
    color: #1C1C1E;
    margin: 0 0 4px 0;
}

.pwa-payment-date {
    font-size: 13px;
    color: #6E6E73;
    margin: 0;
}

.pwa-payment-amount {
    font-size: 20px;
    font-weight: 700;
    color: #26BFFF;
    margin-bottom: 8px;
}

.pwa-payment-receipt {
    font-size: 12px;
    color: #6E6E73;
    padding: 8px 12px;
    background: #F7F9FC;
    border-radius: 6px;
    display: inline-block;
}

.pwa-invoice-card {
    border-left: 4px solid #FF9500;
}

.pwa-invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.pwa-invoice-title {
    font-size: 16px;
    font-weight: 600;
    color: #1C1C1E;
    margin: 0 0 4px 0;
}

.pwa-invoice-due {
    font-size: 13px;
    color: #FF9500;
    font-weight: 600;
    margin: 0;
}

.pwa-invoice-amount {
    font-size: 20px;
    font-weight: 700;
    color: #FF3B30;
}

.pwa-badge-paid {
    background: #E8F5E9;
    color: #2E7D32;
}

.pwa-badge-pending {
    background: #FFF3E0;
    color: #E65100;
}
</style>

<script>
document.getElementById('student-selector').addEventListener('change', function() {
    window.location.href = '{{ route("guardian-pwa.fees") }}?student=' + this.value;
});

function payInvoice(id) {
    alert('Payment gateway will open for invoice ' + id);
}
</script>
@endsection
