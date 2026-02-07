<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreePeriodActivityItem extends Model
{
    protected $fillable = [
        'activity_id',
        'activity_type_id',
        'notes',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(FreePeriodActivity::class, 'activity_id');
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class, 'activity_type_id');
    }
}
