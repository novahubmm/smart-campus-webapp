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
        'title_mm',
        'description',
        'description_mm',
        'icon',
        'icon_color',
        'icon_bg_color',
        'icon_background_color',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(SchoolRule::class, 'rule_category_id')->orderBy('sort_order');
    }
}
