<?php

namespace App\Services;

use App\DTOs\ActivityLog\ActivityLogFilterData;
use App\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    public function __construct(private readonly ActivityLogRepositoryInterface $repository) {}

    public function list(ActivityLogFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->list($filter);
    }

    public function getStats(ActivityLogFilterData $filter): array
    {
        return $this->repository->getStats($filter);
    }

    public function log(string $action, ?string $userId = null, ?string $modelType = null, ?string $modelId = null, ?string $description = null, ?array $properties = null): void
    {
        $this->repository->log($action, $userId, $modelType, $modelId, $description, $properties);
    }
}
