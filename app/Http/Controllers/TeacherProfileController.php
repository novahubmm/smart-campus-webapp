<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\TeacherProfile\TeacherProfileStoreData;
use App\DTOs\TeacherProfile\TeacherProfileUpdateData;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Requests\TeacherProfile\TeacherProfileStoreRequest;
use App\Http\Requests\TeacherProfile\TeacherProfileUpdateRequest;
use App\Models\Department;
use App\Models\FreePeriodActivityType;
use App\Models\TeacherFreePeriodActivity;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\TeacherProfileService;
use App\Traits\LogsActivity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherProfileController extends Controller
{
    use AuthorizesRequests, LogsActivity;

    public function __construct(private readonly TeacherProfileService $teacherProfileService) {}

    public function index(Request $request): View
    {
        $this->authorize(PermissionEnum::VIEW_DEPARTMENTS_PROFILES->value);

        $filters = [
            'search' => $request->string('search')->toString(),
            'department_id' => $request->string('department_id')->toString(),
            'status' => $request->string('status')->toString(),
            'active' => $request->string('active')->toString(),
        ];

        $profiles = $this->teacherProfileService->paginate($filters);
        $totals = $this->teacherProfileService->totals();
        $departments = Department::orderBy('name')->get();

        return view('teacher-profiles.index', compact('profiles', 'totals', 'filters', 'departments'));
    }

    public function create(): View
    {
        $this->authorize(PermissionEnum::MANAGE_TEACHER_PROFILES->value);

        $departments = Department::orderBy('name')->get();

        return view('teacher-profiles.create', compact('departments'));
    }

    public function store(TeacherProfileStoreRequest $request): RedirectResponse
    {
        $this->authorize(PermissionEnum::MANAGE_TEACHER_PROFILES->value);

        $data = TeacherProfileStoreData::from($request->validated());
        $profile = $this->teacherProfileService->store($data);

        $this->logCreate('TeacherProfile', $profile->id ?? '', $request->validated()['employee_id'] ?? null);

        return redirect()->route('teacher-profiles.index')->with('success', __('Teacher profile created successfully.'));
    }

    public function show(TeacherProfile $teacherProfile): View
    {
        $this->authorize(PermissionEnum::VIEW_DEPARTMENTS_PROFILES->value);

        $teacherProfile->load('user', 'department');

        return view('teacher-profiles.show', [
            'profile' => $teacherProfile,
        ]);
    }

    public function edit(TeacherProfile $teacherProfile): View
    {
        $this->authorize(PermissionEnum::MANAGE_TEACHER_PROFILES->value);

        $teacherProfile->load('user', 'department');
        $departments = Department::orderBy('name')->get();

        return view('teacher-profiles.edit', [
            'profile' => $teacherProfile,
            'departments' => $departments,
        ]);
    }

    public function update(TeacherProfileUpdateRequest $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize(PermissionEnum::MANAGE_TEACHER_PROFILES->value);

        $data = TeacherProfileUpdateData::from($teacherProfile, $request->validated());
        $this->teacherProfileService->update($data);

        $this->logUpdate('TeacherProfile', $teacherProfile->id, $teacherProfile->employee_id);

        return redirect()->route('teacher-profiles.index')->with('success', __('Teacher profile updated successfully.'));
    }

    public function activities(Request $request, TeacherProfile $teacherProfile): View
    {
        $this->authorize(PermissionEnum::VIEW_DEPARTMENTS_PROFILES->value);

        $teacherProfile->load('user', 'department');

        $filters = [
            'start_date' => $request->string('start_date')->toString(),
            'end_date' => $request->string('end_date')->toString(),
            'activity_type' => $request->string('activity_type')->toString(),
        ];

        $query = TeacherFreePeriodActivity::where('teacher_profile_id', $teacherProfile->id)
            ->with('activityType')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->orderBy('created_at', 'desc');

        // Only apply date filter if both start_date and end_date are provided
        if ($filters['start_date'] && $filters['end_date']) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        } elseif ($filters['start_date']) {
            $query->where('date', '>=', $filters['start_date']);
        } elseif ($filters['end_date']) {
            // If only end_date is provided, show only that specific date
            $query->whereDate('date', $filters['end_date']);
        }

        if ($filters['activity_type']) {
            $query->whereHas('activityType', fn($q) => $q->where('code', $filters['activity_type']));
        }

        $activities = $query->paginate(10)->withQueryString();

        // Get summary stats
        $summaryQuery = TeacherFreePeriodActivity::where('teacher_profile_id', $teacherProfile->id);

        // Apply same date filters as main query
        if ($filters['start_date'] && $filters['end_date']) {
            $summaryQuery->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        } elseif ($filters['start_date']) {
            $summaryQuery->where('date', '>=', $filters['start_date']);
        } elseif ($filters['end_date']) {
            // If only end_date is provided, show only that specific date
            $summaryQuery->whereDate('date', $filters['end_date']);
        }

        // Apply activity type filter to summary if specified
        if ($filters['activity_type']) {
            $summaryQuery->whereHas('activityType', fn($q) => $q->where('code', $filters['activity_type']));
        }

        $summary = [
            'total_activities' => $summaryQuery->count(),
            'total_minutes' => $summaryQuery->sum('duration_minutes'),
            'by_type' => TeacherFreePeriodActivity::where('teacher_profile_id', $teacherProfile->id)
                ->when($filters['start_date'] && $filters['end_date'], function($q) use ($filters) {
                    $q->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
                })
                ->when($filters['start_date'] && !$filters['end_date'], function($q) use ($filters) {
                    $q->where('date', '>=', $filters['start_date']);
                })
                ->when($filters['end_date'] && !$filters['start_date'], function($q) use ($filters) {
                    // If only end_date is provided, show only that specific date
                    $q->whereDate('date', $filters['end_date']);
                })
                ->when($filters['activity_type'], function($q) use ($filters) {
                    $q->whereHas('activityType', fn($query) => $query->where('code', $filters['activity_type']));
                })
                ->with('activityType')
                ->get()
                ->groupBy(function($activity) {
                    return $activity->activityType?->localized_label ?? 'Unknown';
                })
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'minutes' => $group->sum('duration_minutes'),
                    'color' => $group->first()->activityType?->color,
                ])
                ->sortByDesc('count'),
        ];

        $activityTypes = FreePeriodActivityType::active()->ordered()->get();

        return view('teacher-profiles.activities', [
            'profile' => $teacherProfile,
            'activities' => $activities,
            'filters' => $filters,
            'summary' => $summary,
            'activityTypes' => $activityTypes,
        ]);
    }
}
