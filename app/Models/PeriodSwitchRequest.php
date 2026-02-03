<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodSwitchRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'period_id',
        'from_teacher_id',
        'to_teacher_id',
        'date',
        'reason',
        'to_subject',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function fromTeacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'from_teacher_id');
    }

    public function toTeacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'to_teacher_id');
    }
}
