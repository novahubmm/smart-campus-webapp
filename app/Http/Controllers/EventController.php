<?php

namespace App\Http\Controllers;

use App\DTOs\Event\EventData;
use App\DTOs\Event\EventFilterData;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Services\EventService;
use App\Traits\LogsActivity;
use App\Models\EventAttachment;
use App\Models\EventPoll;
use App\Models\EventPollOption;
use App\Models\EventResponse;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly EventService $service)
    {
    }

    public function index(Request $request): View
    {
        $filter = EventFilterData::from($request->all());
        $events = $this->service->list($filter);
        $categories = EventCategory::orderBy('name')->get();
        $grades = \App\Models\Grade::orderBy('level')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();

        // Stats
        $stats = [
            'total' => Event::count(),
            'upcoming' => Event::upcoming()->count(),
            'ongoing' => Event::ongoing()->count(),
            'completed' => Event::completed()->count(),
        ];
        $today = now()->toDateString();

        $month = $filter->month ?: now()->format('Y-m');
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception $e) {
            $monthDate = now()->startOfMonth();
            $month = $monthDate->format('Y-m');
        }
        $monthEvents = $this->service->calendar(new EventFilterData(
            category_id: $filter->category_id,
            status: $filter->status,
            period: null,
            month: $month
        ));

        $calendarDays = collect(range(1, $monthDate->daysInMonth))->map(function ($day) use ($monthDate, $monthEvents) {
            $date = $monthDate->copy()->day($day)->toDateString();
            return [
                'date' => $date,
                'label' => $monthDate->copy()->day($day)->format('M j'),
                'events' => $monthEvents->filter(fn($event) => $event['start_date']->toDateString() === $date),
            ];
        });

        return view('events.index', [
            'events' => $events,
            'categories' => $categories,
            'grades' => $grades,
            'departments' => $departments,
            'filter' => $filter,
            'stats' => $stats,
            'monthDate' => $monthDate,
            'calendarDays' => $calendarDays,
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = EventData::from($request->validated(), $request->user()?->id);

        $event = $this->service->create($data);

        $this->logCreate('Event', $event->id, $request->validated()['title'] ?? null);

        // Send notification to guardians
        try {
            $guardianNotificationService = app(\App\Services\GuardianNotificationService::class);
            $guardianNotificationService->sendEventNotification(
                $event->id,
                $event->title,
                strip_tags($event->description ?? ''),
                $event->start_date->format('Y-m-d'),
                $event->end_date->format('Y-m-d')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send guardian event notification', [
                'error' => $e->getMessage(),
                'event_id' => $event->id,
            ]);
        }

        return redirect()->route('events.index')->with('status', __('Event created.'));
    }

    public function show(Event $event): View
    {
        $grades = \App\Models\Grade::orderBy('level')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();

        return view('events.show', [
            'event' => $event->load(['category', 'organizer', 'polls.options.votes', 'polls.creator', 'attachments', 'responses']),
            'grades' => $grades,
            'departments' => $departments,
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $data = EventData::from($request->validated(), $request->user()?->id);

        $this->service->update($event, $data);

        $this->logUpdate('Event', $event->id, $event->title);

        return redirect()->route('events.index')->with('status', __('Event updated.'));
    }

    public function destroy(Event $event): RedirectResponse
    {
        $eventId = $event->id;
        $eventTitle = $event->title;

        $this->service->delete($event);

        $this->logDelete('Event', $eventId, $eventTitle);

        return redirect()->route('events.index')->with('status', __('Event removed.'));
    }

    public function respond(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:going,not_going',
        ]);

        EventResponse::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => auth()->id()],
            ['status' => $request->status]
        );

        return back()->with('status', __('Response recorded.'));
    }

    public function upload(Request $request, Event $event)
    {
        $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg|max:6144', // Only PNG/JPG, max 6MB per file
        ]);

        // Check total attachment count
        if ($event->attachments()->count() >= 30) {
            return response()->json([
                'success' => false,
                'message' => __('events.Maximum 30 files allowed')
            ], 422);
        }

        $file = $request->file('file');
        
        // Optimize image to reduce memory usage
        $optimizedPath = $this->optimizeAndStoreImage($file);

        $event->attachments()->create([
            'file_path' => $optimizedPath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => \Storage::disk('public')->size($optimizedPath),
            'uploaded_by' => auth()->id(),
        ]);

        // Force garbage collection to free memory
        gc_collect_cycles();

        return response()->json(['success' => true]);
    }

    private function optimizeAndStoreImage($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $storagePath = storage_path('app/public/events/attachments');
        
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $fullPath = $storagePath . '/' . $filename;

        // Load image based on type
        $image = match(strtolower($extension)) {
            'jpg', 'jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'png' => imagecreatefrompng($file->getRealPath()),
            default => throw new \Exception('Unsupported image type')
        };

        if (!$image) {
            throw new \Exception('Failed to load image');
        }

        // Get original dimensions
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // Resize if larger than 1920px on longest side (for 2GB RAM server)
        $maxDimension = 1920;
        if ($originalWidth > $maxDimension || $originalHeight > $maxDimension) {
            if ($originalWidth > $originalHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int)(($maxDimension / $originalWidth) * $originalHeight);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int)(($maxDimension / $originalHeight) * $originalWidth);
            }

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG
            if (strtolower($extension) === 'png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            imagedestroy($image);
            $image = $resized;
        }

        // Save optimized image
        if (strtolower($extension) === 'png') {
            imagepng($image, $fullPath, 6); // Compression level 6 (balance between size and quality)
        } else {
            imagejpeg($image, $fullPath, 80); // 80% quality for JPG
        }

        imagedestroy($image);

        return 'events/attachments/' . $filename;
    }

    public function storePoll(Request $request, Event $event): RedirectResponse
    {
        if ($event->calculated_status !== 'upcoming') {
            return back()->with('error', __('Polls can only be created for upcoming events.'));
        }

        $request->validate([
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
        ]);

        $poll = $event->polls()->create([
            'question' => $request->question,
            'created_by' => auth()->id(),
            'is_active' => true,
            'expires_at' => now()->addHours(24),
        ]);

        foreach ($request->options as $optionText) {
            $poll->options()->create(['option_text' => $optionText]);
        }

        return back()->with('status', __('Poll created.'));
    }

    public function destroyAttachment(EventAttachment $attachment)
    {
        // Check permission
        if (auth()->id() !== $attachment->uploaded_by && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => __('events.Unauthorized')
            ], 403);
        }

        // Delete file from storage
        \Storage::disk('public')->delete($attachment->file_path);
        
        // Delete database record
        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => __('events.Image deleted successfully')
        ]);
    }

    public function vote(EventPollOption $option)
    {
        $poll = $option->poll;

        if (!$poll->is_currently_active) {
            return response()->json(['message' => 'Poll is closed or expired.'], 403);
        }

        if ($poll->event->calculated_status !== 'upcoming') {
            return response()->json(['message' => 'Polls are only available for upcoming events.'], 403);
        }

        $isOrganizer = auth()->id() === $poll->event->organized_by;
        $isAdmin = auth()->user()->hasRole('admin');

        if ($isAdmin || $isOrganizer) {
            return response()->json(['message' => 'Admins and organizers cannot vote.'], 403);
        }

        // Check if user already voted in this poll
        $existingVote = \App\Models\EventPollVote::whereHas('option', function ($q) use ($poll) {
            $q->where('poll_id', $poll->id);
        })->where('user_id', auth()->id())->first();

        if ($existingVote) {
            return response()->json(['message' => 'Already voted.'], 403);
        }

        $option->votes()->create([
            'user_id' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function togglePoll(EventPoll $poll)
    {
        $poll->update(['is_active' => !$poll->is_active]);

        return response()->json(['success' => true]);
    }
}
