<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventAnnouncementSetupRequest;
use App\DTOs\EventAnnouncement\EventAnnouncementSetupData;
use App\Models\Setting;
use App\Services\EventAnnouncementService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventAnnouncementSetupController extends Controller
{
    use LogsActivity;

    public function __construct(
        private readonly EventAnnouncementService $service
    ) {}

    public function index(): View
    {
        $setting = $this->service->getSetup();
        $eventCategories = $this->service->getExistingCategoriesLower();
        $customCategories = $this->service->getCustomCategories();

        return view('events.setup', [
            'setting' => $setting,
            'eventCategories' => $eventCategories,
            'customCategories' => $customCategories,
        ]);
    }

    public function store(EventAnnouncementSetupRequest $request): RedirectResponse
    {
        $setupData = EventAnnouncementSetupData::from($request->validated());

        $this->service->saveSetup($setupData);

        $this->logActivity('setup_complete', 'EventAnnouncementSetup', null, 'Completed events & announcements setup');

        // Check if all setup steps are completed
        $setting = Setting::first();
        $allSetupCompleted = $setting
            && $setting->setup_completed_school_info
            && $setting->setup_completed_academic
            && $setting->setup_completed_event_and_announcements
            && $setting->setup_completed_time_table_and_attendance
            && $setting->setup_completed_finance;

        if ($allSetupCompleted) {
            return redirect()
                ->route('dashboard')
                ->with('success', __('Events setup completed successfully!'));
        }

        return redirect()
            ->route('setup.overview')
            ->with('success', __('Events setup completed successfully! Please complete the remaining setup steps.'));
    }
}
