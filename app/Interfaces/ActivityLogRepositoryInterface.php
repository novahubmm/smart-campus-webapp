<?php

namespace App\Interfaces;

use App\DTOs\ActivityLog\ActivityLogFilterData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActivityLogRepositoryInterface
{
    public function list(ActivityLogFilterData $filter): LengthAwarePaginator;

    public function getStats(ActivityLogFilterData $filter): array;

    public function log(string $action, ?string $userId = null, ?string $modelType = null, ?string $modelId = null, ?string $description = null, ?array $properties = null): void;
}
