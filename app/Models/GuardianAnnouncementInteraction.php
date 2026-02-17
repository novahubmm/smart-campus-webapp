<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardianAnnouncementInteraction extends Model
{
    use HasUuidPrimaryKey;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'guardian_id',
        'announcement_id',
        'is_read',
        'read_at',
        'is_pinned',
        'pinned_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_pinned' => 'boolean',
        'read_at' => 'datetime',
        'pinned_at' => 'datetime',
    ];

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(GuardianProfile::class, 'guardian_id');
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }
}
