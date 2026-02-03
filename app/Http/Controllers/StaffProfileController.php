<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\StaffProfile\StaffProfileStoreData;
use App\DTOs\StaffProfile\StaffProfileUpdateData;
use App\Enums\PermissionEnum;
use App\Http\Requests\StaffProfile\StaffProfileStoreRequest;
use App\Http\Requests\StaffProfile\StaffProfileUpdateRequest;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Services\StaffProfileService;
use App\Traits\LogsActivity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffProfileController extends Controller
{
    use AuthorizesRequests, LogsActivity;

    public function __construct(private readonly StaffProfileService $staffProfileService) {}

    public function index(Request $request): View
    {
        $this->authorize(PermissionEnum::VIEW_DEPARTMENTS_PROFILES->value);

        $filters = [
            'search' => $request->string('search')->toString(),
            'department_id' => $request->string('department_id')->toString(),
            'status' => $request->string('status')->toString(),
            'active' => $request->string('active')->toString(),
        ];

        $profiles = $this->staffProfileService->paginate($filters);
        $totals = $this->staffProfileService->totals();
        $departments = Department::orderBy('name')->get();

        return view('staff-profiles.index', compact('profiles', 'totals', 'filters', 'departments'));
    }

    public function create(): View
    {
        $this->authorize(PermissionEnum::MANAGE_STAFF_PROFILES->value);

        $departments = Department::orderBy('name')->get();

        return view('staff-profiles.create', compact('departments'));
    }

    public function store(StaffProfileStoreRequest $request): RedirectResponse
    {
        $this->authorize(PermissionEnum::MANAGE_STAFF_PROFILES->value);

        $data = StaffProfileStoreData::from($request->validated());
        $profile = $this->staffProfileService->store($data);

        $this->logCreate('StaffProfile', $profile->id ?? '', $request->validated()['employee_id'] ?? null);

        return redirect()->route('staff-profiles.index')->with('success', __('Staff profile created successfully.'));
    }

    public function show(StaffProfile $staffProfile): View
    {
        $this->authorize(PermissionEnum::VIEW_DEPARTMENTS_PROFILES->value);
        $staffProfile->load('user', 'department');

        return view('staff-profiles.show', compact('staffProfile'));
    }

    public function edit(StaffProfile $staffProfile): View
    {
        $this->authorize(PermissionEnum::MANAGE_STAFF_PROFILES->value);

        $staffProfile->load('user', 'department');
        $departments = Department::orderBy('name')->get();

        return view('staff-profiles.edit', [
            'profile' => $staffProfile,
            'departments' => $departments,
        ]);
    }

    public function update(StaffProfileUpdateRequest $request, StaffProfile $staffProfile): RedirectResponse
    {
        $this->authorize(PermissionEnum::MANAGE_STAFF_PROFILES->value);

        $data = StaffProfileUpdateData::from($staffProfile, $request->validated());
        $this->staffProfileService->update($data);

        $this->logUpdate('StaffProfile', $staffProfile->id, $staffProfile->employee_id);

        return redirect()->route('staff-profiles.index')->with('success', __('Staff profile updated successfully.'));
    }
}
