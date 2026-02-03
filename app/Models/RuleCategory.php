<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuleCategory extends Model
{
    use HasFactory, HasUuidPrimaryKey, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'icon_color',
        'icon_bg_color',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(SchoolRule::class)->orderBy('sort_order');
    }
}
