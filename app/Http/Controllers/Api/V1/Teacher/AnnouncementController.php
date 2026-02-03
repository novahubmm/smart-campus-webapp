<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Get announcements list
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $announcements = Announcement::with('announcementType')
            ->where('is_published', true)
            ->where('status', true)
            ->whereDate('publish_date', '<=', now())
            ->orderByDesc('publish_date')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $announcements->map(function ($announcement) {
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'content' => $announcement->content,
                'type' => $announcement->announcementType ? [
                    'name' => $announcement->announcementType->name,
                    'slug' => $announcement->announcementType->slug,
                    'icon' => $announcement->announcementType->icon,
                    'color' => $announcement->announcementType->color,
                ] : null,
                'priority' => $announcement->priority,
                'location' => $announcement->location,
                'publish_date' => $announcement->publish_date?->format('Y-m-d H:i'),
                'created_at' => $announcement->created_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'announcements' => $data,
                'pagination' => [
                    'current_page' => $announcements->currentPage(),
                    'last_page' => $announcements->lastPage(),
                    'per_page' => $announcements->perPage(),
                    'total' => $announcements->total(),
                ],
            ],
        ]);
    }

    /**
     * Get announcement detail
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $announcement = Announcement::with(['announcementType', 'event'])
            ->where('is_published', true)
            ->where('status', true)
            ->find($id);

        if (!$announcement) {
            return response()->json([
                'success' => false,
                'message' => 'Announcement not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'content' => $announcement->content,
                'type' => $announcement->announcementType ? [
                    'name' => $announcement->announcementType->name,
                    'slug' => $announcement->announcementType->slug,
                    'icon' => $announcement->announcementType->icon,
                    'color' => $announcement->announcementType->color,
                ] : null,
                'priority' => $announcement->priority,
                'location' => $announcement->location,
                'event' => $announcement->event ? [
                    'id' => $announcement->event->id,
                    'title' => $announcement->event->title,
                    'start_date' => $announcement->event->start_date?->format('Y-m-d'),
                    'end_date' => $announcement->event->end_date?->format('Y-m-d'),
                ] : null,
                'publish_date' => $announcement->publish_date?->format('Y-m-d H:i'),
                'created_at' => $announcement->created_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Get calendar events
     */
    public function calendarEvents(Request $request): JsonResponse
    {
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $events = \App\Models\Event::whereMonth('start_date', $month)
            ->whereYear('start_date', $year)
            ->orderBy('start_date')
            ->get();

        $data = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_date' => $event->start_date?->format('Y-m-d'),
                'end_date' => $event->end_date?->format('Y-m-d'),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'venue' => $event->venue,
                'category' => $event->category?->name,
                'is_all_day' => $event->is_all_day ?? false,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'month' => (int) $month,
                'year' => (int) $year,
                'events' => $data,
            ],
        ]);
    }

    /**
     * Get event detail
     */
    public function eventDetail(Request $request, string $id): JsonResponse
    {
        $event = \App\Models\Event::with('category')->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_date' => $event->start_date?->format('Y-m-d'),
                'end_date' => $event->end_date?->format('Y-m-d'),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'venue' => $event->venue,
                'category' => $event->category ? [
                    'id' => $event->category->id,
                    'name' => $event->category->name,
                ] : null,
                'is_all_day' => $event->is_all_day ?? false,
                'created_at' => $event->created_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
