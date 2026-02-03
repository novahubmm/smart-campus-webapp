<?php

namespace App\Http\Controllers;

use App\DTOs\Finance\FinanceSetupData;
use App\Http\Requests\FinanceSetupRequest;
use App\Models\Grade;
use App\Models\Setting;
use App\Services\FinanceService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FinanceSetupController extends Controller
{
    use LogsActivity;

    public function __construct(
        private readonly FinanceService $service,
    ) {}

    public function index(): View
    {
        $setting = $this->service->getSetting();
        $expenseCategories = $this->service->getExpenseCategories();
        $grades = Grade::orderBy('level')->get();

        return view('finance.finance-setup', [
            'setting' => $setting,
            'expenseCategories' => $expenseCategories,
            'grades' => $grades,
        ]);
    }

    public function store(FinanceSetupRequest $request): RedirectResponse
    {
        $data = FinanceSetupData::from($request->validated());

        $this->service->saveSetup($data);

        $this->logActivity('setup_complete', 'FinanceSetup', null, 'Completed finance setup');

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
                ->with('success', __('Finance setup completed successfully!'));
        }

        return redirect()
            ->route('setup.overview')
            ->with('success', __('Finance setup completed successfully! Please complete the remaining setup steps.'));
    }
}
