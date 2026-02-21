<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeType extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const FEE_TYPES = [
        'Transportation',
        'Book',
        'Hostel',
        'Camp',
        'Academic',
        'Event',
        'Competition',
        'Other',
    ];

    protected $fillable = [
        'name',
        'name_mm',
        'code',
        'description',
        'description_mm',
        'fee_type',
        'amount',
        'due_date',
        'partial_status',
        'discount_status',
        'is_mandatory',
        'status',
        'due_date_type',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'status' => 'boolean',
        'partial_status' => 'boolean',
        'discount_status' => 'boolean',
        'amount' => 'decimal:2',
        'due_date' => 'integer',
    ];

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function frequency()
    {
        return $this->hasOne(FeeTypeFrequency::class, 'fee_type_id');
    }
}
