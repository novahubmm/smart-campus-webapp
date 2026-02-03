<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyContact extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'setting_id',
        'name',
        'role',
        'email',
        'phone',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function setting()
    {
        return $this->belongsTo(Setting::class);
    }
}
