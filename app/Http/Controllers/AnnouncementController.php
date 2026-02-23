<?php

namespace App\Http\Controllers;

use App\DTOs\Announcement\AnnouncementData;
use App\DTOs\Announcement\AnnouncementFilterData;
use App\Http\Requests\Announcement\StoreAnnouncementRequest;
use App\Http\Requests\Announcement\UpdateAnnouncementRequest;
use App\Jobs\SendAnnouncementNotifications;
use App\Models\Announcement;
use App\Models\AnnouncementType;
use App\Services\AnnouncementService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly AnnouncementService $service) {}

    public function index(Request $request): View
    {
        $filter = AnnouncementFilterData::from($request->all());
        $announcements = $this->service->list($filter);

        $participantRoles = ['teacher', 'staff', 'guardian'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        // Get announcement types from database
        $announcementTypes = AnnouncementType::getActive();

        // Get grades for teacher/guardian targeting
        $grades = \App\Models\Grade::orderBy('level')->get();

        // Get departments for staff targeting
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();

        // Stats
        $stats = [
            'total' => Announcement::count(),
            'published' => Announcement::where('is_published', true)->count(),
            'draft' => Announcement::where('is_published', false)->count(),
            'urgent' => Announcement::whereHas('announcementType', fn($q) => $q->where('slug', 'urgent'))->count(),
        ];

        return view('announcements.index', [
            'announcements' => $announcements,
            'filter' => $filter,
            'participantRoles' => $participantRoles,
            'announcementTypes' => $announcementTypes,
            'priorities' => $priorities,
            'grades' => $grades,
            'departments' => $departments,
            'stats' => $stats,
        ]);
    }

    public function show(Announcement $announcement): View
    {
        $announcement->load(['creator', 'announcementType']);

        // Get grades for display
        $grades = \App\Models\Grade::orderBy('level')->get();

        // Get departments for display
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        
        return view('announcements.show', [
            'announcement' => $announcement,
            'grades' => $grades,
            'departments' => $departments,
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        \Log::info('Announcement store called', ['target_roles' => $request->input('target_roles')]);

        $data = AnnouncementData::from($request->validated(), $request->user()?->id);

        // Handle publish date and time for immediate publishing
        if ($data->is_published && !$data->publish_date) {
            $payload = $request->validated();
            $payload['publish_date'] = now()->format('Y-m-d H:i:s');
            $data = AnnouncementData::from($payload, $request->user()?->id);
        } elseif ($data->publish_date && $request->has('publish_time')) {
            // Combine date and time for scheduled announcements
            $payload = $request->validated();
            $publishDateTime = $data->publish_date . ' ' . ($request->input('publish_time') ?: '00:00:00');
            $payload['publish_date'] = $publishDateTime;
            $data = AnnouncementData::from($payload, $request->user()?->id);
        }

        $announcement = $this->service->create($data);
        \Log::info('Announcement created', ['id' => $announcement->id]);

        $this->logCreate('Announcement', $announcement->id, $request->validated()['title'] ?? null);

        // Send push notifications if published
        if ($data->is_published && $request->has('target_roles')) {
            \Log::info('Sending push notifications', ['target_roles' => $request->input('target_roles')]);

            // Parse target grades and departments from JSON
            $targetGrades = json_decode($request->input('target_grades_json', '["all"]'), true) ?: ['all'];
            $targetDepartments = json_decode($request->input('target_departments_json', '["all"]'), true) ?: ['all'];

            $this->sendPushNotifications(
                $announcement, 
                $request->input('target_roles', []),
                $targetGrades,
                $targetDepartments
            );
            \Log::info('Push notifications sent');

            // Send to guardians if they are in target roles
            if (in_array('guardian', $request->input('target_roles', []))) {
                try {
                    $guardianNotificationService = app(\App\Services\GuardianNotificationService::class);
                    $guardianNotificationService->sendAnnouncementNotification(
                        $announcement->id,
                        $announcement->title,
                        strip_tags($announcement->content),
                        $targetGrades
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send guardian announcement notification', [
                        'error' => $e->getMessage(),
                        'announcement_id' => $announcement->id,
                    ]);
                }
            }
        }

        return redirect()->route('announcements.index')->with('status', __('Announcement saved.'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $wasPublished = $announcement->is_published;
        
        $data = AnnouncementData::from($request->validated(), $request->user()?->id);
        
        // Handle publish date and time for immediate publishing
        if ($data->is_published && !$data->publish_date) {
            $payload = $request->validated();
            $payload['publish_date'] = now()->format('Y-m-d H:i:s');
            $data = AnnouncementData::from($payload, $request->user()?->id);
        } elseif ($data->publish_date && $request->has('publish_time')) {
            // Combine date and time for scheduled announcements
            $payload = $request->validated();
            $publishDateTime = $data->publish_date . ' ' . ($request->input('publish_time') ?: '00:00:00');
            $payload['publish_date'] = $publishDateTime;
            $data = AnnouncementData::from($payload, $request->user()?->id);
        }

        $this->service->update($announcement, $data);

        $this->logUpdate('Announcement', $announcement->id, $announcement->title);

        // Send push notifications if just published (was draft, now published)
        if (!$wasPublished && $data->is_published && $request->has('target_roles')) {
            // Parse target grades and departments from JSON
            $targetGrades = json_decode($request->input('target_grades_json', '["all"]'), true) ?: ['all'];
            $targetDepartments = json_decode($request->input('target_departments_json', '["all"]'), true) ?: ['all'];
            
            $this->sendPushNotifications(
                $announcement->fresh(), 
                $request->input('target_roles', []),
                $targetGrades,
                $targetDepartments
            );
        }

        return redirect()->route('announcements.index')->with('status', __('Announcement updated.'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcementId = $announcement->id;
        $announcementTitle = $announcement->title;

        $this->service->delete($announcement);

        $this->logDelete('Announcement', $announcementId, $announcementTitle);

        return redirect()->route('announcements.index')->with('status', __('Announcement removed.'));
    }

    /**
     * Send push notifications to target roles (dispatched to queue for fast response)
     */
    private function sendPushNotifications(Announcement $announcement, array $targetRoles, array $targetGrades = ['all'], array $targetDepartments = ['all']): void
    {
        // Run synchronously for now (dispatchSync) - change to dispatch() if using queue worker
        SendAnnouncementNotifications::dispatchSync($announcement, $targetRoles, $targetGrades, $targetDepartments);
    }

}
