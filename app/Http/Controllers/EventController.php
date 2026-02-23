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
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly EventService $service) {}

    public function index(Request $request): View
    {
        $filter = EventFilterData::from($request->all());
        $events = $this->service->list($filter);
        $categories = EventCategory::orderBy('name')->get();

        // Stats
        $today = now()->toDateString();
        $stats = [
            'total' => Event::count(),
            'upcoming' => Event::where('status', 'upcoming')->count(),
            'ongoing' => Event::where('status', 'ongoing')->count(),
            'completed' => Event::where('status', 'completed')->count(),
            'result' => Event::where('status', 'result')->count(),
        ];

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
}
