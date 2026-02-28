<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestService;
use App\Models\SchoolClass;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly LeaveRequestService $service) {}

    public function index(): View
    {
        $classes = SchoolClass::with('grade')
            ->orderBy('name')
            ->get()
            ->map(fn($class) => [
                'id' => $class->id,
                'name' => $class->name,
                'grade' => $class->grade?->name,
            ]);

        return view('leave.requests.index', [
            'classes' => $classes,
            'today' => now()->toDateString(),
            'initialTab' => request('tab', 'staff'),
        ]);
    }

    public function apply(): View
    {
        $user = auth()->user();
        $userType = $this->resolveUserType($user);

        return view('leave.requests.apply', [
            'user' => $user,
            'userType' => $userType,
            'today' => now()->toDateString(),
        ]);
    }

    public function applyForOther(): View
    {
        $this->authorize('manage leave requests');

        return view('leave.requests.apply-for-other', [
            'today' => now()->toDateString(),
        ]);
    }

    public function staffPending(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:teacher,staff'],
        ]);

        $data = $this->service->staffPending($validated);

        return response()->json(['data' => $data]);
    }

    public function staffHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:teacher,staff'],
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:approved,rejected'],
        ]);

        $data = $this->service->staffHistory($validated);

        return response()->json(['data' => $data]);
    }

    public function studentPending(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'string'],
        ]);

        $data = $this->service->studentPending($validated);

        return response()->json(['data' => $data]);
    }

    public function studentHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:approved,rejected'],
        ]);

        $data = $this->service->studentHistory($validated);

        return response()->json(['data' => $data]);
    }

    public function myHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $data = $this->service->myHistory($request->user()->id, $validated);

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leave_type' => ['required', 'in:sick,casual,emergency,other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $userType = $this->resolveUserType($user);

        if (!$userType) {
            return response()->json(['message' => 'Profile not set for leave requests'], 422);
        }

        $payload = array_merge($validated, [
            'user_id' => $user->id,
            'user_type' => $userType,
        ]);

        $data = $this->service->submit($payload);

        $this->logCreate('LeaveRequest', $data['id'] ?? $user->id, "Leave request: {$validated['leave_type']}");

        return response()->json(['data' => $data]);
    }

    public function storeForOther(Request $request): JsonResponse
    {
        $this->authorize('manage leave requests');

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'user_type' => ['required', 'in:teacher,staff,student'],
            'leave_type' => ['required', 'in:sick,casual,emergency,other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = User::with(['teacherProfile', 'staffProfile', 'studentProfile'])->findOrFail($validated['user_id']);

        if (!$this->userMatchesType($user, $validated['user_type'])) {
            return response()->json(['message' => 'Selected user does not match the chosen role.'], 422);
        }

        $payload = array_merge($validated, [
            'user_id' => $user->id,
        ]);

        $data = $this->service->submit($payload);

        $this->logCreate('LeaveRequest', $data['id'] ?? $user->id, "Leave request for other: {$validated['leave_type']}");

        return response()->json(['data' => $data]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $this->authorize('manage leave requests');

        $validated = $request->validate([
            'role' => ['required', 'in:teacher,staff,student'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = $validated['search'] ?? '';

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $query = User::query()->with(['teacherProfile', 'staffProfile', 'studentProfile']);

        if ($validated['role'] === 'teacher') {
            $query->where('is_active', true)
                  ->whereHas('teacherProfile', function ($q) {
                      $q->where('status', 'active');
                  });
        } elseif ($validated['role'] === 'staff') {
            $query->where('is_active', true)
                  ->whereHas('staffProfile', function ($q) {
                      $q->where('status', 'active');
                  });
        } else {
            $query->whereHas('studentProfile', function ($q) {
                $q->where('status', 'active');
            });
        }

        $query->where(function ($q) use ($search, $validated) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");

            if ($validated['role'] === 'teacher') {
                $q->orWhereHas('teacherProfile', function ($profile) use ($search) {
                    $profile->where('status', 'active')
                        ->where('employee_id', 'like', "%{$search}%");
                });
            } elseif ($validated['role'] === 'staff') {
                $q->orWhereHas('staffProfile', function ($profile) use ($search) {
                    $profile->where('status', 'active')
                        ->where('employee_id', 'like', "%{$search}%");
                });
            } else {
                $q->orWhereHas('studentProfile', function ($profile) use ($search) {
                    $profile->where('status', 'active')
                        ->where(function ($sq) use ($search) {
                            $sq->where('student_id', 'like', "%{$search}%")
                                ->orWhere('student_identifier', 'like', "%{$search}%");
                        });
                });
            }
        });

        $users = $query->limit(10)->get()->map(function (User $user) use ($validated) {
            $identifier = null;

            if ($validated['role'] === 'teacher') {
                $identifier = $user->teacherProfile?->employee_id;
            } elseif ($validated['role'] === 'staff') {
                $identifier = $user->staffProfile?->employee_id;
            } else {
                $identifier = $user->studentProfile?->student_id
                    ?? $user->studentProfile?->student_identifier;
            }

            return [
                'id' => $user->id,
                'name' => $user->name ?? '—',
                'email' => $user->email ?? '—',
                'phone' => $user->phone ?? '—',
                'identifier' => $identifier ?? '—',
            ];
        });

        return response()->json($users);
    }

    public function userHistory(Request $request): JsonResponse
    {
        $this->authorize('manage leave requests');

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'date' => ['nullable', 'date'],
        ]);

        $data = $this->service->myHistory($validated['user_id'], $validated);

        return response()->json(['data' => $data]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This leave request has already been processed.',
            ], 422);
        }

        DB::transaction(function () use ($leaveRequest, $validated) {
            // Update leave request status
            $leaveRequest->update([
                'status' => 'approved',
                'admin_remarks' => $validated['remarks'] ?? null,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Deduct from leave balance (only for staff/teachers, not students)
            if (in_array($leaveRequest->user_type, ['teacher', 'staff'])) {
                $leaveBalance = LeaveBalance::getOrCreateForUser(
                    $leaveRequest->user_id,
                    $leaveRequest->leave_type
                );
                $leaveBalance->deductDays($leaveRequest->total_days);
            }
        });

        $this->logActivity('approve', 'LeaveRequest', $leaveRequest->id, "Approved leave request");

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully.',
        ]);
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This leave request has already been processed.',
            ], 422);
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'admin_remarks' => $validated['remarks'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->logActivity('reject', 'LeaveRequest', $leaveRequest->id, "Rejected leave request");

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected.',
        ]);
    }

    private function resolveUserType($user): ?string
    {
        if ($user?->teacherProfile) {
            return 'teacher';
        }
        if ($user?->staffProfile) {
            return 'staff';
        }
        if ($user?->studentProfile) {
            return 'student';
        }
        return null;
    }

    private function userMatchesType(User $user, string $type): bool
    {
        return match ($type) {
            'teacher' => (bool) $user->teacherProfile,
            'staff' => (bool) $user->staffProfile,
            'student' => (bool) $user->studentProfile,
            default => false,
        };
    }
}
