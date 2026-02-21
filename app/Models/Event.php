<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'event_category_id',
        'type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'venue',
        'organized_by',
        'banner_image',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organized_by');
    }

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['upcoming', 'ongoing']);
    }
}
