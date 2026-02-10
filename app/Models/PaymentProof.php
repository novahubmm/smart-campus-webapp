<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentProof extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'student_id',
        'payment_method_id',
        'payment_amount',
        'payment_months',
        'payment_date',
        'receipt_image',
        'notes',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'fee_ids',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'payment_months' => 'integer',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'fee_ids' => 'array',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function invoices()
    {
        if (!$this->fee_ids || !is_array($this->fee_ids)) {
            return collect();
        }
        return Invoice::whereIn('id', $this->fee_ids)->get();
    }

    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class, 'payment_proof_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending_verification');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Business Logic Methods
    public function approve(string $adminId): void
    {
        $this->update([
            'status' => 'verified',
            'verified_by' => $adminId,
            'verified_at' => now(),
        ]);
    }

    public function reject(string $adminId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $adminId,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function getInvoices()
    {
        return $this->invoices();
    }
}
