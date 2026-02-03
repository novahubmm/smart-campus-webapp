<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeCategory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['name', 'color'];

    // Relationships
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
