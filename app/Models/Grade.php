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
    ];

    protected $casts = [
        'price_per_month' => 'decimal:2',
    ];

    // Computed attributes
    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return GradeHelper::getLocalizedName($this->level);
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
