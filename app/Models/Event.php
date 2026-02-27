<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'target_roles',
        'target_grades',
        'target_teacher_grades',
        'target_guardian_grades',
        'target_guardian_grades',
        'target_departments',
        'schedules',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_roles' => 'array',
        'target_grades' => 'array',
        'target_teacher_grades' => 'array',
        'target_guardian_grades' => 'array',
        'target_departments' => 'array',
        'schedules' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organized_by');
    }


    public function polls(): HasMany
    {
        return $this->hasMany(EventPoll::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EventAttachment::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EventResponse::class);
    }

    public function getAuthResponseAttribute(): ?string
    {
        return $this->responses()->where('user_id', auth()->id())->first()?->status;
    }

    public function getCalculatedStatusAttribute(): string
    {
        $now = now();

        // Use earliest start and latest end
        $start = \Carbon\Carbon::parse($this->start_date)->startOfDay();
        if ($this->start_time) {
            $stTime = \Carbon\Carbon::parse($this->start_time);
            $start->hour($stTime->hour)->minute($stTime->minute);
        }

        $endDate = $this->end_date ?? $this->start_date;
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();
        if ($this->end_time) {
            $enTime = \Carbon\Carbon::parse($this->end_time);
            $end->hour($enTime->hour)->minute($enTime->minute);
        }

        if ($now->lt($start))
            return 'upcoming';
        if ($now->gt($end))
            return 'completed';
        return 'active';
    }

    public function scopeUpcoming($query)
    {
        return $query->where(function ($q) {
            $q->where('start_date', '>', now()->toDateString())
                ->orWhere(function ($sq) {
                    $sq->where('start_date', '=', now()->toDateString())
                        ->where('start_time', '>', now()->toTimeString());
                });
        });
    }

    public function scopeOngoing($query)
    {
        return $query->where(function ($q) {
            $q->where('start_date', '<=', now()->toDateString())
                ->where(function ($eq) {
                    $eq->where('end_date', '>=', now()->toDateString())
                        ->orWhereNull('end_date');
                });
        })->where(function ($q) {
            // Complex ongoing check omitted for simplicity in scopes, 
            // usually you just check dates or use the accessor in PHP.
            // But let's try to keep it reasonably accurate.
        });
    }

    public function scopeCompleted($query)
    {
        return $query->where(function ($q) {
            $q->where('end_date', '<', now()->toDateString())
                ->orWhere(function ($sq) {
                    $sq->where('end_date', '=', now()->toDateString())
                        ->where('end_time', '<', now()->toTimeString());
                });
        });
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['upcoming', 'ongoing']);
    }

    public function getScheduleListAttribute(): array
    {
        if (!empty($this->schedules)) {
            return $this->schedules;
        }

        return [
            [
                'date' => $this->start_date->toDateString(),
                'start_time' => $this->start_time ?? '00:00',
                'end_time' => $this->end_time ?? '23:59',
                'label' => null,
                'description' => null,
            ]
        ];
    }
}
