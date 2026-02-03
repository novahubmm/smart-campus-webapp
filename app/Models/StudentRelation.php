<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentRelation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'from_student_id',
        'to_student_id',
        'relation_id'
    ];

    /**
     * Get the student who has the relation (from).
     */
    public function fromStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'from_student_id');
    }

    /**
     * Get the student who is related to (to).
     */
    public function toStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'to_student_id');
    }

    /**
     * Get the relation type.
     */
    public function relation(): BelongsTo
    {
        return $this->belongsTo(Relation::class);
    }
}
