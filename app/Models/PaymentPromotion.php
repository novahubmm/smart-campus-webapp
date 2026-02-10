<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPromotion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'months',
        'discount_percent',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active promotions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get promotion by months
     */
    public static function getByMonths(int $months): ?self
    {
        return self::where('months', $months)->first();
    }

    /**
     * Get all active promotions ordered by months
     */
    public static function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()->orderBy('months')->get();
    }
}
