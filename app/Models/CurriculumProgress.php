<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CurriculumProgress extends Model
{
    use HasUuids;

    protected $table = 'curriculum_progress';

    protected $fillable = [
        'topic_id',
        'class_id',
        'teacher_id',
        'status',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
    ];

    public function topic()
    {
        return $this->belongsTo(CurriculumTopic::class, 'topic_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }
}
