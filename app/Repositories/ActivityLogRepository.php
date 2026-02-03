<?php

namespace App\Repositories;

use App\DTOs\ActivityLog\ActivityLogFilterData;
use App\Interfaces\ActivityLogRepositoryInterface;
use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function list(ActivityLogFilterData $filter): LengthAwarePaginator
    {
        $query = ActivityLog::query()->with('user')->latest();

        $this->applyFilters($query, $filter);

        return $query->paginate($filter->perPage);
    }

    public function getStats(ActivityLogFilterData $filter): array
    {
        [$start, $end] = $filter->getDateRange();

        $baseQuery = ActivityLog::query();
        if ($start && $end) {
            $baseQuery->whereBetween('created_at', [$start, $end]);
        }

        // Active users (unique users who performed any action)
        $activeUsers = (clone $baseQuery)->distinct('user_id')->count('user_id');

        // Login count
        $logins = (clone $baseQuery)->where('action', 'login')->count();

        // Alerts (failed logins, unauthorized access, etc.)
        $alerts = (clone $baseQuery)->whereIn('action', ['failed_login', 'unauthorized_access', 'suspicious_activity'])->count();

        return [
            'active_users' => $activeUsers,
            'logins' => $logins,
            'alerts' => $alerts,
        ];
    }

    public function log(string $action, ?string $userId = null, ?string $modelType = null, ?string $modelId = null, ?string $description = null, ?array $properties = null): void
    {
        ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function applyFilters(Builder $query, ActivityLogFilterData $filter): void
    {
        [$start, $end] = $filter->getDateRange();

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        if ($filter->search) {
            $search = $filter->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($filter->action) {
            $query->where('action', $filter->action);
        }

        if ($filter->status === 'alert') {
            $query->whereIn('action', ['failed_login', 'unauthorized_access', 'suspicious_activity']);
        } elseif ($filter->status === 'ok') {
            $query->whereNotIn('action', ['failed_login', 'unauthorized_access', 'suspicious_activity']);
        }
    }
}
