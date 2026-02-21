<?php

namespace App\Models\PaymentSystem;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fee_structures_payment_system';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'name_mm',
        'description',
        'description_mm',
        'amount',
        'frequency',
        'fee_type',
        'grade',
        'batch',
        'target_month',
        'due_date',
        'supports_payment_period',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'supports_payment_period' => 'boolean',
        'is_active' => 'boolean',
        'due_date' => 'date',
    ];

    /**
     * Check if the fee is a monthly fee.
     *
     * @return bool
     */
    public function isMonthly(): bool
    {
        return $this->frequency === 'monthly';
    }

    /**
     * Check if the fee is a one-time fee.
     *
     * @return bool
     */
    public function isOneTime(): bool
    {
        return $this->frequency === 'one_time';
    }

    /**
     * Check if the fee supports payment periods.
     *
     * @return bool
     */
    public function supportsPaymentPeriod(): bool
    {
        return $this->supports_payment_period === true;
    }
}
