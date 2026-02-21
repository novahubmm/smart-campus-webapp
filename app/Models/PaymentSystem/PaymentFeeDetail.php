<?php

namespace App\Models\PaymentSystem;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentFeeDetail extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_fee_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'payment_id',
        'invoice_fee_id',
        'fee_name',
        'fee_name_mm',
        'full_amount',
        'paid_amount',
        'is_partial',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'full_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_partial' => 'boolean',
    ];

    /**
     * Get the payment that owns this fee detail.
     *
     * @return BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Get the invoice fee that this detail is based on.
     *
     * @return BelongsTo
     */
    public function invoiceFee(): BelongsTo
    {
        return $this->belongsTo(InvoiceFee::class, 'invoice_fee_id');
    }
}
