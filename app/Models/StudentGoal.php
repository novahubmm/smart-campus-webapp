<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGoal extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'student_id',
        'guardian_id',
        'type',
        'title',
        'description',
        'target_value',
        'current_value',
        'target_date',
        'status',
    ];

    protected $casts = [
        'target_value' => 'float',
        'current_value' => 'float',
        'target_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(GuardianProfile::class, 'guardian_id');
    }

    public function getProgressAttribute(): float
    {
        if ($this->target_value <= 0) {
            return 0;
        }
        return min(100, round(($this->current_value / $this->target_value) * 100, 1));
    }
}
