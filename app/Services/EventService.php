<?php

namespace App\Services;

use App\DTOs\Event\EventData;
use App\DTOs\Event\EventFilterData;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Support\Collection;

class EventService
{
    public function __construct(private readonly EventRepositoryInterface $repository) {}

    public function list(EventFilterData $filter): Collection
    {
        return $this->repository->list([
            'category_id' => $filter->category_id,
            'status' => $filter->status,
            'period' => $filter->period,
            'month' => $filter->month,
        ]);
    }

    public function calendar(EventFilterData $filter): Collection
    {
        return $this->repository->calendar([
            'category_id' => $filter->category_id,
            'status' => $filter->status,
            'period' => $filter->period,
            'month' => $filter->month,
        ]);
    }

    public function create(EventData $data): Event
    {
        return $this->repository->create($data->toArray());
    }

    public function update(Event $event, EventData $data): Event
    {
        return $this->repository->update($event, $data->toArray());
    }

    public function delete(Event $event): void
    {
        $this->repository->delete($event);
    }
}
