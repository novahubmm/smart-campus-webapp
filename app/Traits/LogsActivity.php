<?php

namespace App\Traits;

use App\Services\ActivityLogService;

trait LogsActivity
{
    /**
     * Log an activity
     */
    protected function logActivity(
        string $action,
        ?string $modelType = null,
        ?string $modelId = null,
        ?string $description = null,
        ?array $properties = null
    ): void {
        app(ActivityLogService::class)->log(
            $action,
            auth()->id(),
            $modelType,
            $modelId,
            $description,
            $properties
        );
    }

    /**
     * Log a create action
     */
    protected function logCreate(string $modelType, string $modelId, ?string $name = null, ?array $properties = null): void
    {
        $description = $name ? "Created {$modelType}: {$name}" : "Created {$modelType}";
        $this->logActivity('create', $modelType, $modelId, $description, $properties);
    }

    /**
     * Log an update action
     */
    protected function logUpdate(string $modelType, string $modelId, ?string $name = null, ?array $properties = null): void
    {
        $description = $name ? "Updated {$modelType}: {$name}" : "Updated {$modelType}";
        $this->logActivity('update', $modelType, $modelId, $description, $properties);
    }

    /**
     * Log a delete action
     */
    protected function logDelete(string $modelType, string $modelId, ?string $name = null, ?array $properties = null): void
    {
        $description = $name ? "Deleted {$modelType}: {$name}" : "Deleted {$modelType}";
        $this->logActivity('delete', $modelType, $modelId, $description, $properties);
    }

    /**
     * Log a view action
     */
    protected function logView(string $modelType, string $modelId, ?string $name = null): void
    {
        $description = $name ? "Viewed {$modelType}: {$name}" : "Viewed {$modelType}";
        $this->logActivity('view', $modelType, $modelId, $description);
    }
}
