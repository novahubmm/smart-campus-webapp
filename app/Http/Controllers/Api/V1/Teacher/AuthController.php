<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\DTOs\Auth\LoginData;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Teacher\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\Teacher\TeacherProfileResource;
use App\Interfaces\Teacher\TeacherAuthRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly TeacherAuthRepositoryInterface $teacherAuthRepository
    ) {}

    /**
     * Teacher Login
     * POST /api/v1/teacher/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $login = $request->getLoginIdentifier();
            $user = $this->teacherAuthRepository->findTeacherByLogin($login);

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            if (!$user->is_active) {
                return ApiResponse::error('Your account is deactivated. Please contact admin.', 401);
            }

            // Check if user has teacher role
            if (!$user->hasRole('teacher')) {
                return ApiResponse::error('Access denied. Teacher account required.', 403);
            }

            $token = $this->teacherAuthRepository->createToken($user, $request->device_name ?? 'teacher_app');
            $expiresAt = now()->addDays(30);

            return ApiResponse::success([
                'user' => new TeacherProfileResource($user->load('teacherProfile.department')),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toISOString(),
            ], 'Login successful');
        } catch (\Exception $e) {
            return ApiResponse::error('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Teacher Logout
     * POST /api/v1/teacher/auth/logout
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
     * Get Teacher Profile
     * GET /api/v1/teacher/auth/profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('teacherProfile.department');

            return ApiResponse::success([
                'user' => new TeacherProfileResource($user),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve profile', 500);
        }
    }

    /**
     * Change teacher password
     * POST /api/v1/teacher/auth/change-password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !Hash::check($request->input('current_password'), $user->password)) {
            return ApiResponse::error('Current password is incorrect', 400);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return ApiResponse::success(null, 'Password updated successfully');
    }
}
