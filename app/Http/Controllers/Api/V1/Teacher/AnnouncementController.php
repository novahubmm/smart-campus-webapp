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
        $month = $request->query('month');
        $year = $request->query('year');

        $query = \App\Models\Event::query();

        // Optional filtering by month and year
        if ($month && $year) {
            $query->whereMonth('start_date', $month)
                  ->whereYear('start_date', $year);
        }

        $events = $query->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'desc')
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
                'status' => $event->calculated_status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'month' => $month ? (int) $month : null,
                'year' => $year ? (int) $year : null,
                'total' => $events->count(),
                'events' => $data,
            ],
        ]);
    }

    /**
     * Get event detail
     */
    public function eventDetail(Request $request, string $id): JsonResponse
    {
        $event = \App\Models\Event::with(['category', 'attachments', 'responses'])->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
            ], 404);
        }

        // Check if teacher has responded to this event and get their response
        $userResponse = \App\Models\EventResponse::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        // Map status to response format
        $statusToResponse = [
            'going' => 'yes',
            'not_going' => 'no',
            'maybe' => 'maybe',
        ];

        // Get response counts
        $responseCounts = [
            'yes' => $event->responses->where('status', 'going')->count(),
            'maybe' => $event->responses->where('status', 'maybe')->count(),
            'no' => $event->responses->where('status', 'not_going')->count(),
        ];

        // Format images
        $images = $event->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'url' => url('storage/' . $attachment->file_path),
                'thumbnail_url' => url('storage/' . $attachment->file_path),
            ];
        })->values()->toArray();

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
                'created_at' => $event->created_at?->format('Y-m-d\TH:i:s\Z'),
                'status' => $event->calculated_status,
                'has_attendance' => true,
                'user_response' => $userResponse ? ($statusToResponse[$userResponse->status] ?? null) : null,
                'response_counts' => $responseCounts,
                'images' => $images,
            ],
        ]);
    }

    /**
     * Record event attendance response
     */
    public function recordAttendance(Request $request, string $eventId): JsonResponse
    {
        $request->validate([
            'attending' => 'required|in:yes,no,maybe',
        ]);

        $event = \App\Models\Event::find($eventId);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
            ], 404);
        }

        // Map attending values to status
        $statusMap = [
            'yes' => 'going',
            'no' => 'not_going',
            'maybe' => 'maybe',
        ];

        // Create or update attendance response
        \App\Models\EventResponse::updateOrCreate(
            [
                'event_id' => $eventId,
                'user_id' => auth()->id(),
            ],
            [
                'status' => $statusMap[$request->attending],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Response recorded',
        ]);
    }
}
