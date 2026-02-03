<?php

namespace App\Repositories;

use App\DTOs\EventAnnouncement\EventAnnouncementSetupData;
use App\Interfaces\EventAnnouncementRepositoryInterface;
use App\Models\EventCategory;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EventAnnouncementRepository implements EventAnnouncementRepositoryInterface
{
    public function firstOrCreateSetting(): Setting
    {
        return Setting::firstOrCreate([]);
    }

    public function updateSetup(Setting $setting, EventAnnouncementSetupData $data): Setting
    {
        $setting->fill([
            'setup_completed_event_and_announcements' => true,
        ]);

        $setting->save();

        return $setting->fresh();
    }

    public function getCategoryNames(): Collection
    {
        return EventCategory::query()
            ->orderBy('name')
            ->pluck('name');
    }

    public function syncCategories(array $names): void
    {
        $cleanNames = collect($names)
            ->map(fn(string $name) => trim($name))
            ->filter()
            ->unique(fn($name) => Str::lower($name))
            ->values();

        $keptIds = [];

        foreach ($cleanNames as $name) {
            $existing = EventCategory::withTrashed()
                ->whereRaw('lower(name) = ?', [Str::lower($name)])
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->name = $name;
                $existing->slug = Str::slug($name);
                $existing->status = true;
                $existing->save();
                $keptIds[] = $existing->id;
                continue;
            }

            $new = EventCategory::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => true,
            ]);
            $keptIds[] = $new->id;
        }

        EventCategory::whereNotIn('id', $keptIds)->update(['status' => false]);
        EventCategory::whereNotIn('id', $keptIds)->delete();
    }
}
