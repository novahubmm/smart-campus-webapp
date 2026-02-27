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
            'active' => Event::ongoing()->count(),
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
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('events/attachments', 'public');

        $event->attachments()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
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

    public function destroyAttachment(EventAttachment $attachment): RedirectResponse
    {
        \Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', __('Attachment removed.'));
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
