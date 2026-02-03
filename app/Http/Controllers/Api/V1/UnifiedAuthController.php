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

            // Determine user role and handle accordingly
            if ($user->hasRole('teacher')) {
                return $this->handleTeacherLogin($user, $request);
            } elseif ($user->hasRole('guardian')) {
                return $this->handleGuardianLogin($user, $request);
            } else {
                return ApiResponse::error('Access denied. Teacher or Guardian account required.', 403);
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
                'password' => Hash::make($request->new_password)
            ]);

            return ApiResponse::success(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to change password', 500);
        }
    }
}