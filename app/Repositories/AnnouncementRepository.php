<?php

namespace App\Repositories;

use App\DTOs\Announcement\AnnouncementData;
use App\DTOs\Announcement\AnnouncementFilterData;
use App\Interfaces\AnnouncementRepositoryInterface;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnnouncementRepository implements AnnouncementRepositoryInterface
{
    public function list(AnnouncementFilterData $filter, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Announcement::query()->with(['creator', 'announcementType'])->orderByDesc('created_at')->orderByDesc('publish_date');

        if ($filter->type && $filter->type !== 'all') {
            $query->where('type', $filter->type);
        }

        if ($filter->priority && $filter->priority !== 'all') {
            $query->where('priority', $filter->priority);
        }

        if ($filter->role && $filter->role !== 'all') {
            $query->whereJsonContains('target_roles', $filter->role);
        }

        // Filter by target (grade:id or dept:id format)
        if ($filter->target) {
            if (str_starts_with($filter->target, 'grade:')) {
                $gradeId = substr($filter->target, 6);
                $query->where(function ($q) use ($gradeId) {
                    // Must target teacher or guardian roles AND have the specific grade
                    $q->where(function ($subQ) use ($gradeId) {
                        $subQ->whereJsonContains('target_roles', 'teacher')
                             ->orWhereJsonContains('target_roles', 'guardian');
                    })->where(function ($subQ) use ($gradeId) {
                        $subQ->whereJsonContains('target_grades', $gradeId)
                             ->orWhereJsonContains('target_grades', 'all');
                    });
                });
            } elseif (str_starts_with($filter->target, 'dept:')) {
                $deptId = substr($filter->target, 5);
                $query->where(function ($q) use ($deptId) {
                    // Must target staff role AND have the specific department
                    $q->whereJsonContains('target_roles', 'staff')
                      ->where(function ($subQ) use ($deptId) {
                          $subQ->whereJsonContains('target_departments', $deptId)
                               ->orWhereJsonContains('target_departments', 'all');
                      });
                });
            }
        }

        $this->applyStatusFilter($query, $filter->status);
        $this->applyPeriodFilter($query, $filter->period);

        return $query->paginate($perPage);
    }

    public function create(AnnouncementData $data): Announcement
    {
        return Announcement::create($data->toArray());
    }

    public function update(Announcement $announcement, AnnouncementData $data): Announcement
    {
        $announcement->update($data->toArray());

        return $announcement->fresh('creator');
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }

    private function applyStatusFilter($query, ?string $status): void
    {
        if (!$status || $status === 'all') {
            return;
        }

        if ($status === 'published') {
            $query->where('is_published', true)->where('status', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        } elseif ($status === 'active') {
            $today = Carbon::today();
            $query->where('status', true)
                ->where('is_published', true)
                ->where('publish_date', '<=', $today);
        }
    }

    private function applyPeriodFilter($query, ?string $period): void
    {
        if (!$period || $period === 'all') {
            return;
        }

        $today = Carbon::today();

        match ($period) {
            'today' => $query->whereDate('publish_date', $today),
            'this_week' => $query->whereBetween('publish_date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]),
            'this_month' => $query->whereBetween('publish_date', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]),
            'next_month' => $query->whereBetween('publish_date', [$today->copy()->startOfMonth()->addMonth(), $today->copy()->addMonth()->endOfMonth()]),
            default => null,
        };
    }
}
