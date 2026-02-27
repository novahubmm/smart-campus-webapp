<?php

namespace Database\Seeders\Demo;

use App\Models\Announcement;
use App\Models\AnnouncementType;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;

class DemoEventSeeder extends DemoBaseSeeder
{
    public function run(User $adminUser): void
    {
        $categories = $this->createEventCategories();
        $this->createEvents($adminUser, $categories);
        $this->createAnnouncements($adminUser);
    }

    private function createEventCategories(): array
    {
        $this->command->info('Creating Event Categories...');

        $categories = [
            'holiday' => ['name' => 'Holiday', 'color' => '#F59E0B'],
            'meeting' => ['name' => 'Meeting', 'color' => '#10B981'],
            'sports' => ['name' => 'Sports', 'color' => '#8B5CF6'],
            'cultural' => ['name' => 'Cultural', 'color' => '#EC4899'],
            'academic' => ['name' => 'Academic', 'color' => '#3B82F6'],
        ];

        $result = [];
        foreach ($categories as $slug => $data) {
            $result[$slug] = EventCategory::firstOrCreate(
                ['slug' => $slug],
                ['name' => $data['name'], 'color' => $data['color'], 'status' => true]
            );
        }

        return $result;
    }

    private function createEvents(User $adminUser, array $categories): void
    {
        $this->command->info('Creating Events (11)...');

        $lastMonday = $this->getSchoolOpenDate();
        $thisMonday = $lastMonday->copy()->addWeek();
        $nextMonday = $thisMonday->copy()->addWeek();

        $events = [
            ['title' => 'School Opening Ceremony', 'type' => 'holiday', 'date' => $lastMonday, 'venue' => 'School Auditorium'],
            ['title' => 'Staff Orientation Meeting', 'type' => 'meeting', 'date' => $lastMonday->copy()->addDay(), 'venue' => 'Conference Room'],
            ['title' => 'Welcome Assembly', 'type' => 'cultural', 'date' => $lastMonday->copy()->addDays(2), 'venue' => 'School Auditorium'],
            ['title' => 'Parent-Teacher Meeting', 'type' => 'meeting', 'date' => $lastMonday->copy()->addDays(3), 'venue' => 'School Hall'],
            ['title' => 'Morning Exercise Program Launch', 'type' => 'sports', 'date' => $lastMonday->copy()->addDays(4), 'venue' => 'Sports Ground'],
            ['title' => 'First Week Review Meeting', 'type' => 'meeting', 'date' => $thisMonday, 'venue' => 'Conference Room'],
            ['title' => 'Department Heads Meeting', 'type' => 'meeting', 'date' => $thisMonday->copy()->addDay(), 'venue' => 'Conference Room'],
            ['title' => 'Cultural Day Celebration', 'type' => 'cultural', 'date' => $thisMonday->copy()->addDays(2), 'venue' => 'School Auditorium'],
            ['title' => 'Christmas Eve Celebration', 'type' => 'holiday', 'date' => $this->getToday(), 'venue' => 'School Hall'],
            ['title' => 'Christmas Day', 'type' => 'holiday', 'date' => $this->getToday()->copy()->addDay(), 'venue' => 'School'],
            ['title' => 'Annual Sports Day', 'type' => 'sports', 'date' => $nextMonday, 'venue' => 'Sports Ground'],
        ];

        foreach ($events as $eventData) {
            $isMultiDay = $eventData['title'] === 'Annual Sports Day';
            $schedules = [];

            if ($isMultiDay) {
                // Annual Sports Day - 2 Days
                $schedules = [
                    [
                        'date' => $eventData['date']->toDateString(),
                        'start_time' => '08:00',
                        'end_time' => '17:00',
                        'label' => 'Day 1: Track & Field',
                        'description' => 'Morning track events and afternoon field competitions.'
                    ],
                    [
                        'date' => $eventData['date']->copy()->addDay()->toDateString(),
                        'start_time' => '09:00',
                        'end_time' => '14:00',
                        'label' => 'Day 2: Finals & Ceremony',
                        'description' => 'Final matches and award distribution.'
                    ]
                ];
                $endDate = $eventData['date']->copy()->addDay();
            } else {
                $schedules = [
                    [
                        'date' => $eventData['date']->toDateString(),
                        'start_time' => '09:00',
                        'end_time' => '16:00',
                        'label' => 'Event Day',
                        'description' => ''
                    ]
                ];
                $endDate = $eventData['date'];
            }

            Event::firstOrCreate(
                ['title' => $eventData['title']],
                [
                    'description' => "Description for {$eventData['title']}",
                    'event_category_id' => $categories[$eventData['type']]->id,
                    'type' => $eventData['type'],
                    'start_date' => $eventData['date'],
                    'end_date' => $endDate,
                    'start_time' => $schedules[0]['start_time'],
                    'end_time' => $schedules[count($schedules) - 1]['end_time'],
                    'venue' => $eventData['venue'],
                    'organized_by' => $adminUser->id,
                    'schedules' => $schedules,
                    'status' => 'upcoming',
                ]
            );
        }
    }

    private function createAnnouncements(User $adminUser): void
    {
        $this->command->info('Creating Announcements (15)...');

        $announcementTypes = AnnouncementType::all()->keyBy('slug');

        $announcements = [
            ['title' => 'Emergency Contact Update Required', 'type' => 'urgent', 'priority' => 'high'],
            ['title' => 'Safety Guidelines Reminder', 'type' => 'urgent', 'priority' => 'high'],
            ['title' => 'Welcome Assembly Notice', 'type' => 'event', 'priority' => 'medium'],
            ['title' => 'Sports Day Registration Open', 'type' => 'event', 'priority' => 'medium'],
            ['title' => 'Cultural Day Invitation', 'type' => 'event', 'priority' => 'medium'],
            ['title' => 'Christmas Celebration Announcement', 'type' => 'event', 'priority' => 'low'],
            ['title' => 'Monthly Exam Schedule Released', 'type' => 'academic', 'priority' => 'high'],
            ['title' => 'Homework Submission Policy', 'type' => 'academic', 'priority' => 'medium'],
            ['title' => 'Library Hours Extended', 'type' => 'academic', 'priority' => 'low'],
            ['title' => 'Study Materials Available', 'type' => 'academic', 'priority' => 'medium'],
            ['title' => 'Christmas Holiday Notice', 'type' => 'holiday', 'priority' => 'high'],
            ['title' => 'New Year Schedule', 'type' => 'holiday', 'priority' => 'medium'],
            ['title' => 'Winter Break Announcement', 'type' => 'holiday', 'priority' => 'medium'],
            ['title' => 'Staff Meeting Notice', 'type' => 'meeting', 'priority' => 'medium'],
            ['title' => 'PTA Meeting Invitation', 'type' => 'meeting', 'priority' => 'medium'],
        ];

        $dayOffset = 0;
        foreach ($announcements as $data) {
            $publishDate = $this->getSchoolOpenDate()->copy()->addDays($dayOffset);
            if ($publishDate->gt($this->getToday())) {
                $publishDate = $this->getToday();
            }

            Announcement::firstOrCreate(
                ['title' => $data['title']],
                [
                    'content' => "This is the content for: {$data['title']}. Please read carefully.",
                    'announcement_type_id' => $announcementTypes[$data['type']]->id ?? null,
                    'priority' => $data['priority'],
                    'target_roles' => ['teacher', 'student', 'guardian'],
                    'publish_date' => $publishDate,
                    'is_published' => true,
                    'status' => true,
                    'created_by' => $adminUser->id,
                ]
            );

            $dayOffset = ($dayOffset + 1) % 7;
        }
    }
}
