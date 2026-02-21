<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'student_id',
        'fee_structure_id',
        'invoice_date',
        'due_date',
        'payment_date',
        'month',
        'academic_year',
        'subtotal',
        'discount',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(PaymentSystem\InvoiceFee::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentItem::class, 'invoice_id');
    }

    public function paymentProof(): BelongsTo
    {
        return $this->belongsTo(PaymentProof::class, 'payment_proof_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending_verification');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForMonth($query, string $month)
    {
        return $query->where('month', $month);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForAcademicYear($query, string $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    // Business Logic Methods
    public function markAsPaid($paymentId, $paymentDate): void
    {
        $this->update([
            'status' => 'paid',
            'paid_amount' => $this->total_amount,
            'balance' => 0,
            'payment_date' => $paymentDate,
        ]);
    }

    public function markAsPendingVerification(): void
    {
        $this->update([
            'status' => 'pending_verification',
        ]);
    }

    public function markAsUnpaid(): void
    {
        $this->update([
            'status' => 'unpaid',
            'paid_amount' => 0,
            'balance' => $this->total_amount,
            'payment_date' => null,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid' 
            && $this->due_date 
            && $this->due_date->isPast();
    }
}
