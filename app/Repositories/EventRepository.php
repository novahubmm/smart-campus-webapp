<?php

namespace App\Repositories;

use App\DTOs\Event\EventData;
use App\DTOs\Event\EventFilterData;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EventRepository implements EventRepositoryInterface
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $filter = $filters instanceof EventFilterData ? $filters : EventFilterData::from($filters);

        $now = now()->toDateString();
        $nowTime = now()->toTimeString();

        $query = Event::query()->with('category');

        $driver = \DB::getDriverName();
        if ($driver === 'sqlite') {
            $query->orderByRaw('ABS(strftime("%s", start_date) - strftime("%s", "now")) ASC');
        } else {
            $query->orderByRaw('ABS(DATEDIFF(start_date, CURDATE())) ASC');
        }

        $query->orderBy('start_time', 'asc');

        if ($filter->category_id) {
            $query->where('event_category_id', $filter->category_id);
        }

        $this->applyStatusFilter($query, $filter->status);
        $this->applyPeriodFilter($query, $filter->period);
        $this->applyMonthFilter($query, $filter->month);

        return $query->paginate(15);
    }

    public function calendar(array $filters = []): Collection
    {
        $filter = $filters instanceof EventFilterData ? $filters : EventFilterData::from($filters);

        $query = Event::query()->with('category')->orderBy('start_date')->orderBy('start_time');

        if ($filter->category_id) {
            $query->where('event_category_id', $filter->category_id);
        }

        $this->applyStatusFilter($query, $filter->status);
        $this->applyPeriodFilter($query, $filter->period);
        $this->applyMonthFilter($query, $filter->month);

        return $query->get()->map(function (Event $event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'type' => $event->type,
                'venue' => $event->venue,
                'status' => $event->status,
                'category' => $event->category?->only(['id', 'name', 'color', 'icon', 'slug']),
            ];
        });
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->fresh('category');
    }

    public function delete(Event $event): void
    {
        $event->delete();
    }

    private function applyStatusFilter($query, ?string $status): void
    {
        if (!$status || $status === 'all') {
            return;
        }

        match ($status) {
            'upcoming' => $query->upcoming(),
            'active' => $query->ongoing(),
            'completed' => $query->completed(),
            'inactive' => $query->completed(),
            default => null,
        };
    }

    private function applyPeriodFilter($query, ?string $period): void
    {
        if (!$period || $period === 'all') {
            return;
        }

        $today = Carbon::today();

        match ($period) {
            'today' => $query->whereDate('start_date', $today),
            'this_week' => $query->whereBetween('start_date', [$today->clone()->startOfWeek(), $today->clone()->endOfWeek()]),
            'this_month' => $query->whereBetween('start_date', [$today->clone()->startOfMonth(), $today->clone()->endOfMonth()]),
            'next_month' => $query->whereBetween('start_date', [$today->clone()->startOfMonth()->addMonth(), $today->clone()->addMonth()->endOfMonth()]),
            'this_year' => $query->whereYear('start_date', $today->year),
            default => null,
        };
    }

    private function applyMonthFilter($query, ?string $month): void
    {
        if (!$month) {
            return;
        }

        try {
            $date = Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception $e) {
            return;
        }

        $query->whereBetween('start_date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()]);
    }
}
