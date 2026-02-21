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
        'discount_percent' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

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

    /**
     * Get available payment options based on remaining months
     * Logic: Show [1, 3, 6, remaining] cards dynamically
     * 
     * @param int $remainingMonths Total months remaining in batch
     * @return \Illuminate\Support\Collection
     */
    public static function getAvailableOptions(int $remainingMonths): \Illuminate\Support\Collection
    {
        $allPromotions = self::getAllActive()->keyBy('months');
        $availableMonths = [];

        // Always include 1 month option
        $availableMonths[] = 1;

        if ($remainingMonths >= 10) {
            // 10+ months remaining: [1, 3, 6, remaining]
            $availableMonths = [1, 3, 6, $remainingMonths];
        } elseif ($remainingMonths >= 7 && $remainingMonths <= 9) {
            // 7-9 months: [1, 3, remaining]
            $availableMonths = [1, 3, $remainingMonths];
        } elseif ($remainingMonths >= 4 && $remainingMonths <= 6) {
            // 4-6 months: [1, 3, remaining] (if remaining != 6, otherwise [1, 3, 6])
            if ($remainingMonths == 6) {
                $availableMonths = [1, 3, 6];
            } else {
                $availableMonths = [1, 3, $remainingMonths];
            }
        } elseif ($remainingMonths == 3) {
            // Exactly 3 months: [1, 3]
            $availableMonths = [1, 3];
        } elseif ($remainingMonths == 2) {
            // Exactly 2 months: [1, 2]
            $availableMonths = [1, 2];
        } else {
            // 1 month: [1]
            $availableMonths = [1];
        }

        // Remove duplicates and sort
        $availableMonths = array_unique($availableMonths);
        sort($availableMonths);

        // Build collection with promotions
        $options = collect();
        foreach ($availableMonths as $months) {
            if (isset($allPromotions[$months])) {
                $options->push($allPromotions[$months]);
            } else {
                // Create a temporary promotion for custom month count
                $options->push(new self([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'months' => $months,
                    'discount_percent' => self::calculateDiscountForMonths($months),
                    'is_active' => true,
                ]));
            }
        }

        return $options;
    }

    /**
     * Calculate discount percentage for custom month counts
     * Interpolates between known values
     */
    private static function calculateDiscountForMonths(int $months): int
    {
        // Known discount tiers
        $tiers = [
            1 => 0,
            2 => 0,
            3 => 5,
            6 => 10,
            9 => 15,
            12 => 20,
        ];

        if (isset($tiers[$months])) {
            return $tiers[$months];
        }

        // Interpolate for values between tiers - return integer
        if ($months < 3) return 0;
        if ($months < 6) return (int) round(5 + (($months - 3) / 3) * 5); // 5-10%
        if ($months < 9) return (int) round(10 + (($months - 6) / 3) * 5); // 10-15%
        if ($months < 12) return (int) round(15 + (($months - 9) / 3) * 5); // 15-20%
        return 20; // 12+ months
    }
}
