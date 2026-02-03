<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Department\DepartmentStoreData;
use App\DTOs\Department\DepartmentUpdateData;
use App\Http\Requests\Department\DepartmentStoreRequest;
use App\Http\Requests\Department\DepartmentUpdateRequest;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\DepartmentService;
use App\Traits\LogsActivity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    use AuthorizesRequests, LogsActivity;

    public function __construct(private readonly DepartmentService $departmentService) {}

    public function index(Request $request): View
    {
        $this->authorize('view departments and profiles');

        $status = $request->string('status')->toString();

        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => in_array($status, ['active', 'inactive'], true) ? $status : null,
        ];

        $departments = $this->departmentService->paginate($filters);
        $totals = $this->departmentService->totals();

        return view('departments.index', compact('departments', 'totals'));
    }

    public function store(DepartmentStoreRequest $request): RedirectResponse
    {
        $data = DepartmentStoreData::from($request->validated());
        $department = $this->departmentService->store($data);

        $this->logCreate('Department', $department->id ?? '', $request->validated()['name'] ?? null);

        return redirect()->route('departments.index')
            ->with('success', __('Department created successfully'));
    }

    public function show(Department $department): View
    {
        $this->authorize('view departments and profiles');

        $department->load(['staffProfiles.user', 'teacherProfiles.user']);
        
        // Get paginated members
        $members = $department->allMembersPaginated(10);

        return view('departments.show', compact('department', 'members'));
    }

    public function edit(Department $department): View
    {
        $this->authorize('manage departments');

        $department->load(['staffProfiles.user', 'teacherProfiles.user']);
        
        // Get paginated members
        $members = $department->allMembersPaginated(10);

        return view('departments.edit', compact('department', 'members'));
    }

    public function update(DepartmentUpdateRequest $request, Department $department): RedirectResponse
    {
        $data = DepartmentUpdateData::from($department, $request->validated());
        $this->departmentService->update($data);

        $this->logUpdate('Department', $department->id, $department->name);

        return redirect()->route('departments.index', ['highlight' => $department->id])
            ->with('success', __('Department updated successfully'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('manage departments');

        $departmentId = $department->id;
        $departmentName = $department->name;

        $this->departmentService->delete($department);

        $this->logDelete('Department', $departmentId, $departmentName);

        return redirect()->route('departments.index')
            ->with('success', __('Department deleted successfully'));
    }

    public function searchMembers(Request $request): JsonResponse
    {
        // $this->authorize('view departments and profiles');

        $search = $request->get('search', '');
        $departmentId = $request->get('department_id');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        // Search staff profiles
        $staffQuery = StaffProfile::with('user')
            ->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orWhere('employee_id', 'LIKE', "%{$search}%");

        // Search teacher profiles
        $teacherQuery = TeacherProfile::with('user')
            ->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orWhere('employee_id', 'LIKE', "%{$search}%");

        // If department_id is provided, exclude members already in that department
        if ($departmentId) {
            $staffQuery->where(function ($query) use ($departmentId) {
                $query->whereNull('department_id')
                      ->orWhere('department_id', '!=', $departmentId);
            });
            
            $teacherQuery->where(function ($query) use ($departmentId) {
                $query->whereNull('department_id')
                      ->orWhere('department_id', '!=', $departmentId);
            });
        }

        $staff = $staffQuery->limit(10)->get()->map(function ($profile) {
            return [
                'id' => $profile->id,
                'type' => 'staff',
                'name' => $profile->user->name,
                'email' => $profile->user->email,
                'employee_id' => $profile->employee_id,
                'position' => $profile->position,
                'current_department' => $profile->department ? $profile->department->name : null,
            ];
        });

        $teachers = $teacherQuery->limit(10)->get()->map(function ($profile) {
            return [
                'id' => $profile->id,
                'type' => 'teacher',
                'name' => $profile->user->name,
                'email' => $profile->user->email,
                'employee_id' => $profile->employee_id,
                'position' => $profile->position,
                'current_department' => $profile->department ? $profile->department->name : null,
            ];
        });

        $results = $staff->concat($teachers)->take(20);

        return response()->json($results);
    }

    public function addMember(Request $request, Department $department): JsonResponse
    {
        $this->authorize('manage departments');

        $request->validate([
            'member_id' => 'required|string',
            'member_type' => 'required|in:staff,teacher',
        ]);

        $memberId = $request->get('member_id');
        $memberType = $request->get('member_type');

        try {
            if ($memberType === 'staff') {
                $profile = StaffProfile::findOrFail($memberId);
            } else {
                $profile = TeacherProfile::findOrFail($memberId);
            }

            $profile->update(['department_id' => $department->id]);

            $this->logCreate('Department-Member Assignment', $memberId, ($profile->user->name ?? 'Member') . ' to ' . $department->name);

            return response()->json([
                'success' => true,
                'message' => __('Member added to department successfully'),
                'member' => [
                    'id' => $profile->id,
                    'type' => $memberType,
                    'name' => $profile->user->name,
                    'email' => $profile->user->email,
                    'employee_id' => $profile->employee_id,
                    'position' => $profile->position,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to add member to department')
            ], 400);
        }
    }

    public function removeMember(Request $request, Department $department): JsonResponse
    {
        $this->authorize('manage departments');

        $request->validate([
            'member_id' => 'required|string',
            'member_type' => 'required|in:staff,teacher',
        ]);

        $memberId = $request->get('member_id');
        $memberType = $request->get('member_type');

        try {
            if ($memberType === 'staff') {
                $profile = StaffProfile::where('id', $memberId)
                    ->where('department_id', $department->id)
                    ->firstOrFail();
            } else {
                $profile = TeacherProfile::where('id', $memberId)
                    ->where('department_id', $department->id)
                    ->firstOrFail();
            }

            $memberName = $profile->user->name ?? 'Member';

            $profile->update(['department_id' => null]);

            $this->logDelete('Department-Member Assignment', $memberId, $memberName . ' from ' . $department->name);

            return response()->json([
                'success' => true,
                'message' => __('Member removed from department successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to remove member from department')
            ], 400);
        }
    }
}
