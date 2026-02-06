<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function __invoke(): View|RedirectResponse
    {
        $this->authorize('access dashboard');

        $user = auth()->user();
        $roles = $user->roles->pluck('name')->toArray();
        
        // Always redirect guardian and teacher roles to PWA
        // Priority: guardian > teacher
        if (in_array('guardian', $roles)) {
            return redirect()->route('guardian-pwa.home');
        } elseif (in_array('teacher', $roles)) {
            return redirect()->route('teacher-pwa.dashboard');
        }

        // Admin and other roles see regular dashboard
        $dashboardData = $this->dashboardService->getDashboardData();

        return view('dashboard', $dashboardData->toArray());
    }
}
