<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnouncementType extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public static function getActive()
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }
}
