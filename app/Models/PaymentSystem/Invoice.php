<?php

namespace App\Models\PaymentSystem;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\StudentProfile;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoices_payment_system';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'invoice_number',
        'student_id',
        'batch_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'status',
        'invoice_type',
        'parent_invoice_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * Get the invoice fees for this invoice.
     *
     * @return HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(InvoiceFee::class, 'invoice_id');
    }

    /**
     * Get the payments for this invoice.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    /**
     * Get the student that owns this invoice.
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    /**
     * Check if the invoice is overdue.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->remaining_amount > 0;
    }

    /**
     * Check if the invoice is fully paid.
     *
     * @return bool
     */
    public function isFullyPaid(): bool
    {
        return $this->remaining_amount == 0;
    }

    /**
     * Check if the invoice can accept partial payment.
     *
     * @return bool
     */
    public function canAcceptPartialPayment(): bool
    {
        // Cannot accept partial payment if invoice is fully paid
        if ($this->isFullyPaid()) {
            return false;
        }

        // Cannot accept partial payment if all fees are overdue
        $allFeesOverdue = $this->fees()->get()->every(function ($fee) {
            return $fee->due_date->isPast();
        });

        return !$allFeesOverdue;
    }
}
