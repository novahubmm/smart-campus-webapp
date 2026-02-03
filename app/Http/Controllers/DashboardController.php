<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function __invoke(): View
    {
        $this->authorize('access dashboard');

        $dashboardData = $this->dashboardService->getDashboardData();


        return view('dashboard', $dashboardData->toArray());
    }
}
