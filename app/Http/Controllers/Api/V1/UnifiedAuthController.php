<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\Teacher\TeacherProfileResource;
use App\Http\Resources\Api\Guardian\GuardianProfileResource;
use App\Interfaces\Teacher\TeacherAuthRepositoryInterface;
use App\Interfaces\Guardian\GuardianAuthRepositoryInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UnifiedAuthController extends Controller
{
    public function __construct(
        private readonly TeacherAuthRepositoryInterface $teacherAuthRepository,
        private readonly GuardianAuthRepositoryInterface $guardianAuthRepository
    ) {}

    /**
     * Unified Login for both Teachers and Guardians
     * Supports multi-role users (users who are both teacher and guardian)
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $login = $request->getLoginIdentifier();
            
            // Try to find user by login identifier
            $user = User::where(function ($query) use ($login) {
                $query->where('email', $login)
                      ->orWhere('phone', $login)
                      ->orWhere('nrc', $login);
            })->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            if (!$user->is_active) {
                return ApiResponse::error('Your account is deactivated. Please contact admin.', 401);
            }

            // Check available roles
            $isTeacher = $user->hasRole('teacher');
            $isGuardian = $user->hasRole('guardian');

            if (!$isTeacher && !$isGuardian) {
                return ApiResponse::error('Access denied. Teacher or Guardian account required.', 403);
            }

            // Build available roles array
            $availableRoles = [];
            if ($isTeacher) $availableRoles[] = 'teacher';
            if ($isGuardian) $availableRoles[] = 'guardian';

            // Handle multi-role users
            if (count($availableRoles) > 1) {
                return $this->handleMultiRoleLogin($user, $request, $availableRoles);
            }

            // Handle single-role users
            if ($isTeacher) {
                return $this->handleTeacherLogin($user, $request);
            } else {
                return $this->handleGuardianLogin($user, $request);
            }

        } catch (\Exception $e) {
            return ApiResponse::error('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle teacher login
     */
    private function handleTeacherLogin(User $user, LoginRequest $request): JsonResponse
    {
        $token = $this->teacherAuthRepository->createToken($user, $request->device_name ?? 'unified_app');
        $expiresAt = now()->addDays(30);

        return ApiResponse::success([
            'user' => new TeacherProfileResource($user->load('teacherProfile.department')),
            'user_type' => 'teacher',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toISOString(),
            'requires_password_change' => $user->requiresPasswordChange(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames(),
        ], 'Login successful');
    }

    /**
     * Handle guardian login
     */
    private function handleGuardianLogin(User $user, LoginRequest $request): JsonResponse
    {
        $token = $this->guardianAuthRepository->createToken($user, $request->device_name ?? 'unified_app');
        
        // Token expiration based on remember_me
        $expiresAt = $request->boolean('remember_me') ? now()->addDays(30) : now()->addDays(7);

        // Load guardian profile with students
        $user->load(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel']);

        return ApiResponse::success([
            'user' => new GuardianProfileResource($user),
            'user_type' => 'guardian',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toISOString(),
            'requires_password_change' => $user->requiresPasswordChange(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames(),
        ], 'Login successful');
    }

    /**
     * Handle multi-role login (user has both teacher and guardian roles)
     * Returns tokens for both roles
     */
    private function handleMultiRoleLogin(User $user, LoginRequest $request, array $availableRoles): JsonResponse
    {
        $tokens = [];
        $userData = [];
        $expiresAt = now()->addDays(30);

        // Generate teacher token if user has teacher role
        if (in_array('teacher', $availableRoles)) {
            $teacherToken = $this->teacherAuthRepository->createToken($user, $request->device_name ?? 'unified_app');
            $tokens['teacher'] = $teacherToken;
            
            // Load teacher data
            $user->load('teacherProfile.department');
            $userData['teacher'] = new TeacherProfileResource($user);
        }

        // Generate guardian token if user has guardian role
        if (in_array('guardian', $availableRoles)) {
            $guardianToken = $this->guardianAuthRepository->createToken($user, $request->device_name ?? 'unified_app');
            $tokens['guardian'] = $guardianToken;
            
            // Load guardian data
            $user->load(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel']);
            $userData['guardian'] = new GuardianProfileResource($user);
        }

        // Determine default role (prefer guardian for multi-role users)
        $defaultRole = in_array('guardian', $availableRoles) ? 'guardian' : 'teacher';

        return ApiResponse::success([
            'user' => $userData[$defaultRole], // Return default role user data
            'user_data' => $userData, // All role-specific user data
            'user_type' => $defaultRole,
            'available_roles' => $availableRoles,
            'tokens' => $tokens,
            'token' => $tokens[$defaultRole], // Default token for backward compatibility
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toISOString(),
            'requires_password_change' => $user->requiresPasswordChange(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames(),
        ], 'Login successful');
    }

    /**
     * Unified Logout
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return ApiResponse::success(null, 'Logged out successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Logout failed', 500);
        }
    }

    /**
     * Get User Profile (works for both teachers and guardians)
     * GET /api/v1/auth/profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                $user->load('teacherProfile.department');
                return ApiResponse::success([
                    'user' => new TeacherProfileResource($user),
                    'user_type' => 'teacher',
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'roles' => $user->getRoleNames(),
                ]);
            } elseif ($user->hasRole('guardian')) {
                $user->load(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel']);
                return ApiResponse::success([
                    'user' => new GuardianProfileResource($user),
                    'user_type' => 'guardian',
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'roles' => $user->getRoleNames(),
                ]);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve profile', 500);
        }
    }

    /**
     * Change Password (works for both teachers and guardians)
     * POST /api/v1/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return ApiResponse::error('Current password is incorrect', 400);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
                'password_changed_at' => now(),
            ]);

            return ApiResponse::success(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to change password', 500);
        }
    }

    /**
     * Check available roles for current user
     * GET /api/v1/auth/available-roles
     */
    public function availableRoles(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $availableRoles = [];
            $roleData = [];

            if ($user->hasRole('teacher')) {
                $availableRoles[] = 'teacher';
                $user->load('teacherProfile.department');
                $roleData['teacher'] = [
                    'type' => 'teacher',
                    'data' => [
                        'teacher_id' => $user->teacherProfile?->teacher_id ?? null,
                        'department' => $user->teacherProfile?->department?->name ?? null,
                        'position' => $user->teacherProfile?->position ?? null,
                    ]
                ];
            }

            if ($user->hasRole('guardian')) {
                $availableRoles[] = 'guardian';
                $user->load(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel']);
                $students = $user->guardianProfile?->students->map(function ($student) {
                    return [
                        'name' => $student->user?->name ?? 'N/A',
                        'grade' => $student->grade?->name ?? 'N/A',
                        'section' => $student->classModel?->section ?? 'N/A',
                    ];
                }) ?? [];

                $roleData['guardian'] = [
                    'type' => 'guardian',
                    'data' => [
                        'students' => $students,
                        'student_count' => $students->count(),
                    ]
                ];
            }

            return ApiResponse::success([
                'available_roles' => $availableRoles,
                'role_data' => $roleData,
                'has_multiple_roles' => count($availableRoles) > 1,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve available roles', 500);
        }
    }

    /**
     * Switch role and get new token
     * POST /api/v1/auth/switch-role
     */
    public function switchRole(Request $request): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|in:teacher,guardian',
        ]);

        try {
            $user = $request->user();
            $targetRole = $request->role;

            // Verify user has the requested role
            if (!$user->hasRole($targetRole)) {
                return ApiResponse::error("You don't have access to {$targetRole} role", 403);
            }

            // Generate new token for the target role
            if ($targetRole === 'teacher') {
                $token = $this->teacherAuthRepository->createToken($user, $request->device_name ?? 'unified_app');
                $user->load('teacherProfile.department');
                $userData = new TeacherProfileResource($user);
            } else {
                $token = $this->guardianAuthRepository->createToken($user, $request->device_name ?? 'unified_app');
                $user->load(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel']);
                $userData = new GuardianProfileResource($user);
            }

            return ApiResponse::success([
                'user' => $userData,
                'user_type' => $targetRole,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toISOString(),
            ], "Switched to {$targetRole} role successfully");
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to switch role: ' . $e->getMessage(), 500);
        }
    }
}