<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'income_number',
        'title',
        'category',
        'description',
        'amount',
        'income_date',
        'payment_method',
        'reference_number',
        'invoice_id',
        'grade_id',
        'class_id',
        'created_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
        'status' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
