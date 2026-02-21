<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeTypeFrequency extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'fee_type_id',
        'frequency',
        'start_month',
        'end_month',
    ];

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }
}
