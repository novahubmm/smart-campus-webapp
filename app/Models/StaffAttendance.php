<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffAttendance extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $table = 'staff_attendance';

    protected $fillable = [
        'staff_id',
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

    public function staff()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_id');
    }

    public function markedByUser()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
