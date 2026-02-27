<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\Guardian\GuardianProfileResource;
use App\Interfaces\Guardian\GuardianAuthRepositoryInterface;
use App\Services\Upload\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private readonly GuardianAuthRepositoryInterface $guardianAuthRepository
    ) {}

    /**
     * Guardian Login
     * POST /api/v1/guardian/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $login = $request->getLoginIdentifier();
            $user = $this->guardianAuthRepository->findGuardianByLogin($login);

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            if (!$user->is_active) {
                return ApiResponse::error('Your account is deactivated. Please contact admin.', 401);
            }

            // Check if user has guardian role
            if (!$user->hasRole('guardian')) {
                return ApiResponse::error('Access denied. Guardian account required.', 403);
            }

            $token = $this->guardianAuthRepository->createToken($user, $request->device_name ?? 'guardian_app');
            
            // Token expiration based on remember_me
            $expiresAt = $request->boolean('remember_me') ? now()->addDays(30) : now()->addDays(7);

            // Load guardian profile with students
            $user->load(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel']);

            return ApiResponse::success([
                'user' => new GuardianProfileResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toISOString(),
            ], 'Login successful');
        } catch (\Exception $e) {
            return ApiResponse::error('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Forgot Password - Send OTP
     * POST /api/v1/guardian/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        try {
            $result = $this->guardianAuthRepository->createPasswordResetOtp($request->identifier);

            return ApiResponse::success($result, 'OTP sent successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Resend OTP
     * POST /api/v1/guardian/auth/resend-otp
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        try {
            $result = $this->guardianAuthRepository->resendOtp($request->identifier);

            return ApiResponse::success($result, 'OTP resent successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Verify OTP
     * POST /api/v1/guardian/auth/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        try {
            $resetToken = $this->guardianAuthRepository->verifyOtp($request->identifier, $request->otp);

            return ApiResponse::success([
                'reset_token' => $resetToken,
            ], 'OTP verified successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Reset Password
     * POST /api/v1/guardian/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->guardianAuthRepository->resetPassword($request->reset_token, $request->password);

            return ApiResponse::success(null, 'Password reset successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Guardian Logout
     * POST /api/v1/guardian/auth/logout
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
     * Get Guardian Profile
     * GET /api/v1/guardian/auth/profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel']);

            return ApiResponse::success([
                'user' => new GuardianProfileResource($user),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve profile', 500);
        }
    }

    /**
     * Update Guardian Profile
     * PUT /api/v1/guardian/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'phone' => 'sometimes|string|unique:users,phone,' . $request->user()->id,
            'photo' => 'sometimes|string', // Base64 encoded image
        ]);

        try {
            $user = $request->user();
            $data = $request->only(['name', 'email', 'phone']);

            // Handle photo upload if provided
            if ($request->has('photo') && $request->photo) {
                $data['profile_photo_path'] = app(FileUploadService::class)->storeOptimizedBase64Image(
                    $request->photo,
                    'profiles',
                    'public',
                    'guardian_' . $user->id
                );
            }

            $user->update($data);
            $user->load(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel']);

            return ApiResponse::success([
                'user' => new GuardianProfileResource($user),
            ], 'Profile updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Change guardian password
     * POST /api/v1/guardian/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!$user || !Hash::check($request->input('current_password'), $user->password)) {
            return ApiResponse::error('Current password is incorrect', 400);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return ApiResponse::success(null, 'Password updated successfully');
    }

    /**
     * Get Guardian's Students
     * GET /api/v1/guardian/students
     */
    public function students(Request $request): JsonResponse
    {
        try {
            $students = $this->guardianAuthRepository->getGuardianStudents($request->user());

            $formattedStudents = $students->map(function ($student) {
                // Check if student is a class leader (single leader, male leader, or female leader)
                $isClassLeader = false;
                if ($student->classModel) {
                    $isClassLeader = $student->classModel->class_leader_id === $student->id 
                                  || $student->classModel->male_class_leader_id === $student->id 
                                  || $student->classModel->female_class_leader_id === $student->id;
                }

                return [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? 'N/A',
                    'student_id' => $student->student_identifier ?? $student->student_id,
                    'grade' => $student->grade?->name ?? 'N/A',
                    'section' => $student->classModel?->section ?? 'N/A',
                    'profile_image' => $student->photo_path ? asset($student->photo_path) : null,
                    'relationship' => $student->pivot->relationship ?? null,
                    'is_primary' => $student->pivot->is_primary ?? false,
                    'is_class_leader' => $isClassLeader ? 1 : 0,
                ];
            });

            return ApiResponse::success($formattedStudents);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve students: ' . $e->getMessage(), 500);
        }
    }
}
