<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory, HasUuidPrimaryKey, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'content',
        'announcement_type_id',
        'priority',
        'location',
        'target_roles',
        'target_grades',
        'target_departments',
        'publish_date',
        'is_published',
        'attachment',
        'status',
        'created_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'target_grades' => 'array',
        'target_departments' => 'array',
        'publish_date' => 'datetime',
        'is_published' => 'boolean',
        'status' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function announcementType(): BelongsTo
    {
        return $this->belongsTo(AnnouncementType::class);
    }

    public function interactions()
    {
        return $this->hasMany(GuardianAnnouncementInteraction::class, 'announcement_id');
    }
}
