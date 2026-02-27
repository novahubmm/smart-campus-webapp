<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\GradeSubject;
use App\Helpers\GradeHelper;

class Grade extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'level',
        'batch_id',
        'grade_category_id',
        'price_per_month',
        'due_date',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'price_per_month' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Computed attributes
    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return GradeHelper::getLocalizedName($this->level);
    }

    // Scopes
    public function scopeActive($query)
    {
        $today = now()->toDateString();
        return $query->where(function ($q) use ($today) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', $today);
        });
    }

    public function scopeEnded($query)
    {
        $today = now()->toDateString();
        return $query->whereNotNull('end_date')
                     ->where('end_date', '<', $today);
    }

    // Helper methods
    public function isActive(): bool
    {
        if (is_null($this->end_date)) {
            return true;
        }
        return $this->end_date >= now()->toDateString();
    }

    public function hasEnded(): bool
    {
        return !$this->isActive();
    }

    // Relationships
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function students()
    {
        return $this->hasManyThrough(StudentProfile::class, StudentClass::class, 'grade_id', 'id', 'id', 'student_id');
    }

    public function gradeCategory()
    {
        return $this->belongsTo(GradeCategory::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'grade_subject')
            ->using(GradeSubject::class)
            ->withPivot(['id', 'deleted_at'])
            ->withTimestamps();
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }
}
