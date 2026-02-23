<?php

namespace App\Models\PaymentSystem;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceFee extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_fees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'invoice_id',
        'fee_id',
        'fee_type_id',
        'fee_name',
        'fee_name_mm',
        'amount',
        'paid_amount',
        'remaining_amount',
        'supports_payment_period',
        'due_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'supports_payment_period' => 'boolean',
        'due_date' => 'date',
    ];

    /**
     * Get the invoice that owns this fee.
     *
     * @return BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Get the fee structure that this invoice fee is based on.
     *
     * @return BelongsTo
     */
    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_id');
    }

    /**
     * Get the fee type that this invoice fee belongs to.
     *
     * @return BelongsTo
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(\App\Models\FeeType::class, 'fee_type_id');
    }

    /**
     * Check if the invoice fee is overdue.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->remaining_amount > 0;
    }

    /**
     * Check if the invoice fee can accept partial payment.
     *
     * @return bool
     */
    public function canAcceptPartialPayment(): bool
    {
        // Cannot accept partial payment if fee is fully paid
        if ($this->remaining_amount == 0) {
            return false;
        }

        // Cannot accept partial payment if due date has passed
        return !$this->due_date->isPast();
    }
}
