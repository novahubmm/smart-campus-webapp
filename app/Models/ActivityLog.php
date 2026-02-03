<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute(): string
    {
        $alertActions = ['failed_login', 'unauthorized_access', 'suspicious_activity'];
        return in_array($this->action, $alertActions) ? 'alert' : 'ok';
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'login' => 'Logged in',
            'logout' => 'Logged out',
            'failed_login' => 'Failed login',
            'create' => 'Created ' . class_basename($this->model_type ?? 'record'),
            'update' => 'Updated ' . class_basename($this->model_type ?? 'record'),
            'delete' => 'Deleted ' . class_basename($this->model_type ?? 'record'),
            'view' => 'Viewed ' . class_basename($this->model_type ?? 'record'),
            'password_change' => 'Password change',
            'profile_update' => 'Profile update',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
