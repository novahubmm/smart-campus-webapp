<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Event;

interface EventRepositoryInterface
{
    public function list(array $filters = []): LengthAwarePaginator;

    public function calendar(array $filters = []): Collection;

    public function create(array $data): Event;

    public function update(Event $event, array $data): Event;

    public function delete(Event $event): void;
}
