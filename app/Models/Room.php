<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'building',
        'floor',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * Get the classes using this room.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Get the facilities in this room.
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'room_facilities')
            ->withPivot('quantity', 'is_working', 'remark')
            ->withTimestamps();
    }
}
