<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'student_id',
        'payment_proof_id',
        'payment_method_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'reference_number',
        'invoice_ids',
        'notes',
        'receptionist_id',
        'receptionist_name',
        'recorded_by',
        'collected_by',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'invoice_ids' => 'array',
        'status' => 'boolean',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function paymentProof(): BelongsTo
    {
        return $this->belongsTo(PaymentProof::class, 'payment_proof_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function invoices()
    {
        if (!$this->invoice_ids || !is_array($this->invoice_ids)) {
            return collect();
        }
        return Invoice::whereIn('id', $this->invoice_ids)->get();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    // Business Logic Methods
    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $lastPayment = self::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastPayment && preg_match('/PAY\d{8}-(\d{4})/', $lastPayment->payment_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s%s-%04d', $prefix, $date, $sequence);
    }

    public function getTotalInvoiceAmount(): float
    {
        return $this->invoices()->sum('total_amount');
    }
}
