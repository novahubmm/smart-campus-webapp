<?php

namespace App\DTOs\EventAnnouncement;

class EventAnnouncementSetupData
{
    public function __construct(
        public readonly array $eventCategories,
    ) {}

    public static function from(array $validated): self
    {
        // Get checkbox categories
        $checkboxCategories = $validated['event_categories'] ?? [];

        // Parse custom categories (comma-separated)
        $customCategoriesStr = $validated['custom_categories'] ?? '';
        $customCategories = collect(explode(',', $customCategoriesStr))
            ->map(fn(string $item) => trim($item))
            ->filter()
            ->all();

        // Merge and deduplicate
        $allCategories = collect($checkboxCategories)
            ->merge($customCategories)
            ->map(fn(string $item) => ucfirst(strtolower(trim($item))))
            ->unique()
            ->values()
            ->all();

        return new self(
            eventCategories: $allCategories,
        );
    }
}
