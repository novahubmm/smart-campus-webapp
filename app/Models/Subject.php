<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\GradeSubject;

class Subject extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'subject_type_id',
    ];

    // Relationships
    public function subjectType()
    {
        return $this->belongsTo(SubjectType::class);
    }

    public function grades()
    {
        return $this->belongsToMany(Grade::class, 'grade_subject')
            ->using(GradeSubject::class)
            ->withPivot(['id', 'deleted_at'])
            ->withTimestamps();
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'class_id')->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(TeacherProfile::class, 'subject_teacher')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->withTimestamps();
    }

    public function curriculumChapters()
    {
        return $this->hasMany(CurriculumChapter::class)->orderBy('order');
    }

    public function getCurriculumForGrade($gradeId)
    {
        return $this->curriculumChapters()
            ->where(function ($query) use ($gradeId) {
                $query->where('grade_id', $gradeId)
                    ->orWhereNull('grade_id');
            })
            ->with('topics')
            ->orderBy('order')
            ->get();
    }

    /**
     * Get periods where this subject is taught
     */
    public function periods()
    {
        return $this->hasMany(Period::class);
    }

    /**
     * Get formatted class names for this subject based on timetable periods
     * Returns class names like "Kindergarten A", "Grade 1 B"
     */
    public function getFormattedClassNamesAttribute(): string
    {
        $classNames = [];
        
        // Get unique classes from periods through timetables
        $periods = $this->periods()->with(['timetable.schoolClass.grade'])->get();
        
        foreach ($periods as $period) {
            if ($period->timetable && $period->timetable->schoolClass) {
                $class = $period->timetable->schoolClass;
                $gradeLevel = $class->grade?->level;
                
                if ($gradeLevel !== null) {
                    $className = \App\Helpers\SectionHelper::formatFullClassName($class->name, $gradeLevel);
                    $classNames[$className] = true; // Use array key to auto-deduplicate
                }
            }
        }
        
        $classNames = array_keys($classNames);
        sort($classNames);
        
        return !empty($classNames) ? implode(', ', $classNames) : '—';
    }
}
