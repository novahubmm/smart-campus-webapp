<?php

namespace App\Models\PaymentSystem;

use App\Models\PaymentMethod;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments_payment_system';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'payment_number',
        'student_id',
        'invoice_id',
        'payment_method_id',
        'payment_amount',
        'payment_type',
        'payment_months',
        'payment_date',
        'receipt_image_url',
        'status',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_amount' => 'decimal:2',
        'payment_months' => 'integer',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the invoice for this payment.
     *
     * @return BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Get the student for this payment.
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    /**
     * Get the payment method for this payment.
     *
     * @return BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Get the fee details for this payment.
     *
     * @return HasMany
     */
    public function feeDetails(): HasMany
    {
        return $this->hasMany(PaymentFeeDetail::class, 'payment_id');
    }

    /**
     * Check if the payment is pending verification.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending_verification';
    }

    /**
     * Check if the payment is verified.
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if the payment is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
