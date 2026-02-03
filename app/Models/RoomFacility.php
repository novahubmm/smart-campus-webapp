<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomFacility extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'room_id',
        'facility_name',
        'quantity',
        'is_working',
        'remark'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_working' => 'boolean',
    ];

    /**
     * Get the room this facility belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
