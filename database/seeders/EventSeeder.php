<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Create event categories first
        $categories = [
            ['name' => 'Academic', 'slug' => 'academic', 'color' => '#3b82f6', 'icon' => 'fas fa-graduation-cap'],
            ['name' => 'Sports', 'slug' => 'sports', 'color' => '#22c55e', 'icon' => 'fas fa-futbol'],
            ['name' => 'Cultural', 'slug' => 'cultural', 'color' => '#a855f7', 'icon' => 'fas fa-music'],
            ['name' => 'Meeting', 'slug' => 'meeting', 'color' => '#f59e0b', 'icon' => 'fas fa-users'],
            ['name' => 'Holiday', 'slug' => 'holiday', 'color' => '#ef4444', 'icon' => 'fas fa-calendar-day'],
        ];

        foreach ($categories as $category) {
            EventCategory::firstOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, ['status' => true])
            );
        }

        // Get category IDs
        $academicCategory = EventCategory::where('slug', 'academic')->first();
        $sportsCategory = EventCategory::where('slug', 'sports')->first();
        $culturalCategory = EventCategory::where('slug', 'cultural')->first();
        $meetingCategory = EventCategory::where('slug', 'meeting')->first();

        // Create sample events
        $events = [
            [
                'title' => 'Parent-Teacher Conference',
                'description' => 'Annual parent-teacher meeting to discuss student progress',
                'event_category_id' => $meetingCategory->id,
                'type' => 'meeting',
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(5),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'venue' => 'Main Hall',
                'status' => 'upcoming',
            ],
            [
                'title' => 'Cultural Festival',
                'description' => 'Annual cultural celebration with performances and exhibitions',
                'event_category_id' => $culturalCategory->id,
                'type' => 'cultural',
                'start_date' => now()->addDays(8),
                'end_date' => now()->addDays(8),
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'venue' => 'Auditorium',
                'status' => 'upcoming',
            ],
            [
                'title' => 'Football Championship',
                'description' => 'Inter-class football tournament finals',
                'event_category_id' => $sportsCategory->id,
                'type' => 'sports',
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(10),
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'venue' => 'Sports Ground',
                'status' => 'upcoming',
            ],
            [
                'title' => 'Science Exhibition',
                'description' => 'Student science projects showcase',
                'event_category_id' => $academicCategory->id,
                'type' => 'academic',
                'start_date' => now()->addDays(12),
                'end_date' => now()->addDays(12),
                'start_time' => '09:00:00',
                'end_time' => '15:00:00',
                'venue' => 'Science Lab',
                'status' => 'upcoming',
            ],
            [
                'title' => 'Annual Sports Day',
                'description' => 'School-wide sports competition',
                'event_category_id' => $sportsCategory->id,
                'type' => 'sports',
                'start_date' => now()->addDays(14),
                'end_date' => now()->addDays(14),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'venue' => 'Main Ground',
                'status' => 'upcoming',
            ],
        ];

        foreach ($events as $event) {
            $schedules = [
                [
                    'date' => $event['start_date']->toDateString(),
                    'start_time' => substr($event['start_time'], 0, 5),
                    'end_time' => substr($event['end_time'], 0, 5),
                    'label' => 'Event Day',
                    'description' => $event['description']
                ]
            ];

            $event['schedules'] = $schedules;

            Event::firstOrCreate(
                ['title' => $event['title'], 'start_date' => $event['start_date']],
                $event
            );
        }
    }
}
