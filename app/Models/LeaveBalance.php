<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'leave_type',
        'total_days',
        'used_days',
        'year',
    ];

    protected $casts = [
        'total_days' => 'integer',
        'used_days' => 'integer',
        'year' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRemainingDaysAttribute(): int
    {
        return max(0, $this->total_days - $this->used_days);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('year', now()->year);
    }

    /**
     * Get or create leave balance for a user
     */
    public static function getOrCreateForUser(string $userId, string $leaveType, ?int $year = null): self
    {
        $year = $year ?? now()->year;
        
        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'leave_type' => $leaveType,
                'year' => $year,
            ],
            [
                'total_days' => self::getDefaultDays($leaveType),
                'used_days' => 0,
            ]
        );
    }

    /**
     * Get default days for each leave type
     */
    public static function getDefaultDays(string $leaveType): int
    {
        return match ($leaveType) {
            'casual' => 12,
            'medical', 'sick' => 10,
            'earned' => 15,
            'emergency' => 5,
            default => 5,
        };
    }

    /**
     * Deduct days from balance
     */
    public function deductDays(int $days): bool
    {
        if ($this->remaining_days < $days) {
            return false;
        }

        $this->used_days += $days;
        return $this->save();
    }

    /**
     * Restore days to balance (e.g., when leave is cancelled)
     */
    public function restoreDays(int $days): bool
    {
        $this->used_days = max(0, $this->used_days - $days);
        return $this->save();
    }
}
