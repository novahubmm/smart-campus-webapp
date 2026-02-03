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
        'icon',
        'icon_color',
        'progress_color',
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
        return $this->belongsToMany(TeacherProfile::class, 'subject_teacher')->withTimestamps();
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
}
