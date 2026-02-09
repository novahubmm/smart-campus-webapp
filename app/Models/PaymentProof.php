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
}
