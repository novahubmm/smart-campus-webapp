<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherAttendance extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $table = 'teacher_attendance';

    protected $fillable = [
        'teacher_id',
        'date',
        'status',
        'remark',
        'marked_by',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }

    public function markedByUser()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
