<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InboxMessage extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'guardian_profile_id',
        'student_profile_id',
        'subject',
        'category',
        'priority',
        'status',
        'assigned_to_type',
        'assigned_to_id',
    ];

    public function guardianProfile(): BelongsTo
    {
        return $this->belongsTo(GuardianProfile::class);
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function assignedTo(): MorphTo
    {
        return $this->morphTo();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(InboxMessageReply::class)->oldest();
    }
}
