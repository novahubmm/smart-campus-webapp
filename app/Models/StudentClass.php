<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentClass extends Pivot
{
    use HasUuids;

    protected $table = 'student_class';

    public $incrementing = false;

    protected $keyType = 'string';

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
