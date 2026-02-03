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
            'school_email' => ['nullable', 'email', 'max:255'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'school_website' => ['nullable', 'string', 'max:255'],
            'school_about_us' => ['nullable', 'string', 'max:2000'],
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
