<?php

namespace App\Http\Controllers;

use App\DTOs\ActivityLog\ActivityLogFilterData;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(private readonly ActivityLogService $service) {}

    public function index(Request $request): View
    {
        $filter = ActivityLogFilterData::from($request->all());
        $logs = $this->service->list($filter);
        $stats = $this->service->getStats($filter);

        $actionTypes = [
            'login' => __('Login'),
            'logout' => __('Logout'),
            'failed_login' => __('Failed Login'),
            'create' => __('Create'),
            'update' => __('Update'),
            'delete' => __('Delete'),
            'view' => __('View'),
            'password_change' => __('Password Change'),
            'profile_update' => __('Profile Update'),
        ];

        return view('activity-logs.index', [
            'logs' => $logs,
            'stats' => $stats,
            'filter' => $filter,
            'actionTypes' => $actionTypes,
        ]);
    }
}
