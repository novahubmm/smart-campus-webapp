<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurriculumChapter extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'grade_id',
        'title',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function topics()
    {
        return $this->hasMany(CurriculumTopic::class, 'chapter_id')->orderBy('order');
    }

    public function getTopicsCountAttribute()
    {
        return $this->topics()->count();
    }
}
