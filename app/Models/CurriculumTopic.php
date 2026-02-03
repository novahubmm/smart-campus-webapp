<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurriculumTopic extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'chapter_id',
        'title',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function chapter()
    {
        return $this->belongsTo(CurriculumChapter::class, 'chapter_id');
    }

    public function progress()
    {
        return $this->hasMany(CurriculumProgress::class, 'topic_id');
    }

    public function getProgressForClass($classId, $teacherId = null)
    {
        $query = $this->progress()->where('class_id', $classId);

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        return $query->first();
    }
}
