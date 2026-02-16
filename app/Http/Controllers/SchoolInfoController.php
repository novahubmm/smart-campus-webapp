<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\KeyContact;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SchoolInfoController extends Controller
{
    use AuthorizesRequests;
    use LogsActivity;

    public function __invoke(): View
    {
        $this->authorize('manage school settings');

        $setting = Setting::first();
        $contacts = KeyContact::where('setting_id', $setting?->id ?? '00000000-0000-0000-0000-000000000001')->orderByDesc('is_primary')->orderBy('name')->get();

        return view('settings.school-info', compact('setting', 'contacts'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage school settings');

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_code' => ['nullable', 'string', 'max:50'],
            'school_name_mm' => ['nullable', 'string', 'max:255'],
            'school_email' => ['nullable', 'email', 'max:255'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'school_website' => ['nullable', 'string', 'max:255'],
            'school_about_us' => ['nullable', 'string', 'max:2000'],
            'school_about_us_mm' => ['nullable', 'string', 'max:2000'],
            'established_year' => ['nullable', 'integer', 'min:1800', 'max:' . date('Y')],
            'motto' => ['nullable', 'string', 'max:500'],
            'motto_mm' => ['nullable', 'string', 'max:500'],
            'vision' => ['nullable', 'string', 'max:2000'],
            'vision_mm' => ['nullable', 'string', 'max:2000'],
            'mission' => ['nullable', 'string', 'max:2000'],
            'mission_mm' => ['nullable', 'string', 'max:2000'],
            'values' => ['nullable', 'array'],
            'values.*' => ['string', 'max:255'],
            'values_mm' => ['nullable', 'array'],
            'values_mm.*' => ['string', 'max:255'],
            'pass_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'average_attendance' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_twitter' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],
            'social_linkedin' => ['nullable', 'string', 'max:255'],
            'accreditations' => ['nullable', 'array'],
            'accreditations.*.name' => ['required_with:accreditations', 'string', 'max:255'],
            'accreditations.*.name_mm' => ['nullable', 'string', 'max:255'],
            'accreditations.*.year' => ['nullable', 'integer', 'min:1800', 'max:' . date('Y')],
            'accreditations.*.certificate_url' => ['nullable', 'string', 'max:500'],
        ]);

        $setting = Setting::firstOrCreate(['id' => '00000000-0000-0000-0000-000000000001']);

        $setting->update($validated + ['setup_completed_school_info' => true]);

        $this->logUpdate('SchoolInfo', $setting->id, $validated['school_name']);

        // Check if all setup steps are completed
        $setting->refresh();
        $allSetupCompleted = $setting->setup_completed_school_info
            && $setting->setup_completed_academic
            && $setting->setup_completed_event_and_announcements
            && $setting->setup_completed_time_table_and_attendance
            && $setting->setup_completed_finance;

        if ($allSetupCompleted) {
            return redirect()->route('settings.school-info')
                ->with('success', __('School information updated successfully.'));
        }

        return redirect()->route('setup.overview')
            ->with('success', __('School information updated successfully. Please complete the remaining setup steps.'));
    }

    public function updateWorkingHours(Request $request): RedirectResponse
    {
        $this->authorize('manage school settings');

        $validated = $request->validate([
            'office_working_days' => ['nullable', 'array'],
            'office_working_days.*' => ['integer', 'between:1,7'],
            'office_start_time' => ['nullable', 'date_format:H:i'],
            'office_end_time' => ['nullable', 'date_format:H:i', 'after:office_start_time'],
            'office_break_duration_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'required_working_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'allow_early_checkout' => ['nullable', 'boolean'],
            'late_arrival_grace_minutes' => ['nullable', 'integer', 'min:0', 'max:60'],
            'track_overtime' => ['nullable', 'boolean'],
        ]);

        $setting = Setting::firstOrCreate(['id' => '00000000-0000-0000-0000-000000000001']);

        // Convert checkboxes to proper values
        $validated['allow_early_checkout'] = $request->has('allow_early_checkout');
        $validated['track_overtime'] = $request->has('track_overtime');

        $setting->update($validated);

        $this->logUpdate('WorkingHours', $setting->id, 'Working hours settings');

        return redirect()->route('settings.school-info')
            ->with('success', __('Working hours settings updated successfully.'));
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $this->authorize('manage school settings');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $setting = Setting::firstOrCreate(['id' => '00000000-0000-0000-0000-000000000001']);

        if (!empty($validated['is_primary'])) {
            KeyContact::where('setting_id', $setting->id)->update(['is_primary' => false]);
        }

        KeyContact::create($validated + ['setting_id' => $setting->id]);

        $this->logCreate('KeyContact', $setting->id, $validated['name']);

        return redirect()->route('settings.school-info')->with('success', __('Key contact added.'));
    }

    public function updateContact(Request $request, KeyContact $contact): RedirectResponse
    {
        $this->authorize('manage school settings');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        if (!empty($validated['is_primary'])) {
            KeyContact::where('setting_id', $contact->setting_id)->update(['is_primary' => false]);
        }

        $contact->update($validated);

        $this->logUpdate('KeyContact', $contact->id, $contact->name);

        return redirect()->route('settings.school-info')->with('success', __('Key contact updated.'));
    }

    public function destroyContact(KeyContact $contact): RedirectResponse
    {
        $this->authorize('manage school settings');

        $contactName = $contact->name;
        $contactId = $contact->id;
        $contact->delete();

        $this->logDelete('KeyContact', $contactId, $contactName);

        return redirect()->route('settings.school-info')->with('success', __('Key contact removed.'));
    }
}
