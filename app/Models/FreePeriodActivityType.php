<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreePeriodActivityType extends Model
{
    use HasUuidPrimaryKey;

    protected $fillable = [
        'code',
        'label',
        'color',
        'icon_svg',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(TeacherFreePeriodActivity::class, 'activity_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the localized label for this activity type
     */
    public function getLocalizedLabelAttribute()
    {
        $translationKey = "activity_types.{$this->code}";
        $translated = __($translationKey);
        
        // If translation exists, use it; otherwise fall back to the stored label
        return $translated !== $translationKey ? $translated : $this->label;
    }
}
