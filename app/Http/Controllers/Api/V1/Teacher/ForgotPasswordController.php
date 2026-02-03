<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Step 1: Verify Identifier (Phone/Email)
     * POST /api/v1/teacher/forgot-password/verify-identifier
     */
    public function verifyIdentifier(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'identifier' => 'required|string',
                'identifier_type' => 'required|in:phone,email',
            ]);

            $identifier = $request->input('identifier');
            $identifierType = $request->input('identifier_type');

            // Find user by phone or email
            $query = User::query();
            if ($identifierType === 'email') {
                $query->where('email', $identifier);
            } else {
                $query->where('phone', $identifier);
            }

            $user = $query->first();

            if (!$user) {
                return ApiResponse::error('No account found with this ' . $identifierType, 404, [
                    'identifier' => ['No account found with this identifier']
                ]);
            }

            // Generate verification token
            $verificationToken = Str::random(64);

            // Store token in password_reset_tokens table
            PasswordResetToken::updateOrCreate(
                ['email' => $user->email],
                [
                    'token' => Hash::make($verificationToken),
                    'created_at' => now(),
                ]
            );

            // Mask the identifier for security
            $maskedIdentifier = $this->maskIdentifier($identifier, $identifierType);

            return ApiResponse::success([
                'user_id' => $user->id,
                'masked_identifier' => $maskedIdentifier,
                'identifier_type' => $identifierType,
                'verification_token' => $verificationToken,
            ], 'Identifier verified successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to verify identifier: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Step 2: Verify NRC Number
     * POST /api/v1/teacher/forgot-password/verify-nrc
     */
    public function verifyNrc(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'verification_token' => 'required|string',
                'nrc' => 'required|string',
            ]);

            $verificationToken = $request->input('verification_token');
            $nrc = $request->input('nrc');

            // Find the password reset token
            $resetToken = PasswordResetToken::where('created_at', '>', now()->subMinutes(15))->first();

            if (!$resetToken || !Hash::check($verificationToken, $resetToken->token)) {
                return ApiResponse::error('Invalid or expired verification token', 400, [
                    'verification_token' => ['Token is invalid or has expired']
                ]);
            }

            // Find user and verify NRC
            $user = User::where('email', $resetToken->email)->first();

            if (!$user || $user->nrc !== $nrc) {
                return ApiResponse::error('NRC number does not match our records', 400, [
                    'nrc' => ['NRC number does not match the registered account']
                ]);
            }

            // Generate OTP
            $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpToken = Str::random(64);
       
            // Store OTP token
            $resetToken->update([
                'token' => Hash::make($otpToken),
                'otp_code' => Hash::make($otpCode),
                'dev_otp_plain' => $otpCode,
                'otp_expires_at' => now()->addMinutes(5),
                'created_at' => now(),
            ]);

            // In production, send OTP via SMS/Email
            // For development, we'll log it
            \Log::info("OTP for {$user->email}: {$otpCode}");

            $maskedDestination = $this->maskIdentifier(
                $user->phone ?? $user->email,
                $user->phone ? 'phone' : 'email'
            );

            return ApiResponse::success([
                'otp_sent' => true,
                'otp_expires_in' => 300,
                'masked_destination' => $maskedDestination,
                'otp_token' => $otpToken,
                // For development only - remove in production
                'dev_otp' => $otpCode,
            ], 'NRC verified successfully. OTP sent to your phone/email');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to verify NRC: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Step 3: Verify OTP Code
     * POST /api/v1/teacher/forgot-password/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'otp_token' => 'required|string',
                'otp_code' => 'required|string|size:6',
            ]);

            $otpToken = $request->input('otp_token');
            $otpCode = $request->input('otp_code');

            // Find the password reset token
            $resetToken = PasswordResetToken::whereNotNull('otp_code')
                ->where('otp_expires_at', '>', now())
                ->first();

            if (!$resetToken || !Hash::check($otpToken, $resetToken->token)) {
                return ApiResponse::error('Invalid or expired OTP token', 400, [
                    'otp_token' => ['Token is invalid or has expired']
                ]);
            }

            // Verify OTP code
            if (!Hash::check($otpCode, $resetToken->otp_code)) {
                return ApiResponse::error('Invalid OTP code', 400, [
                    'otp_code' => ['The OTP code is incorrect']
                ], ['attempts_remaining' => 2]);
            }

            // Generate reset token
            $finalResetToken = Str::random(64);

            $resetToken->update([
                'token' => Hash::make($finalResetToken),
                'otp_code' => null,
                'otp_expires_at' => null,
                'created_at' => now(),
            ]);

            return ApiResponse::success([
                'reset_token' => $finalResetToken,
                'expires_in' => 600,
            ], 'OTP verified successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to verify OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Resend OTP
     * POST /api/v1/teacher/forgot-password/resend-otp
     */
    public function resendOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'otp_token' => 'required|string',
            ]);

            $otpToken = $request->input('otp_token');

            // Find the password reset token
            $resetToken = PasswordResetToken::where('created_at', '>', now()->subMinutes(15))->first();

            if (!$resetToken || !Hash::check($otpToken, $resetToken->token)) {
                return ApiResponse::error('Invalid or expired OTP token', 400, [
                    'otp_token' => ['Token is invalid or has expired']
                ]);
            }

            // Check cooldown (60 seconds)
            if ($resetToken->otp_resent_at && $resetToken->otp_resent_at->addSeconds(60)->isFuture()) {
                $cooldownRemaining = $resetToken->otp_resent_at->addSeconds(60)->diffInSeconds(now());
                return ApiResponse::error('Please wait before requesting a new OTP', 400, null, [
                    'cooldown_remaining' => $cooldownRemaining
                ]);
            }

            $user = User::where('email', $resetToken->email)->first();

            // Generate new OTP
            $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $newOtpToken = Str::random(64);

            $resetToken->update([
                'token' => Hash::make($newOtpToken),
                'otp_code' => Hash::make($otpCode),
                'dev_otp_plain' => $otpCode,
                'otp_expires_at' => now()->addMinutes(5),
                'otp_resent_at' => now(),
            ]);

            // In production, send OTP via SMS/Email
            \Log::info("Resent OTP for {$user->email}: {$otpCode}");

            $maskedDestination = $this->maskIdentifier(
                $user->phone ?? $user->email,
                $user->phone ? 'phone' : 'email'
            );

            return ApiResponse::success([
                'otp_sent' => true,
                'otp_expires_in' => 300,
                'masked_destination' => $maskedDestination,
                'new_otp_token' => $newOtpToken,
                'cooldown' => 60,
                // For development only
                'dev_otp' => $otpCode,
            ], 'OTP resent successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to resend OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Step 4: Reset Password
     * POST /api/v1/teacher/forgot-password/reset
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'reset_token' => 'required|string',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    // 'regex:/[A-Z]/',
                    // 'regex:/[0-9]/',
                ],
            ], [
                'password.min' => 'Password must be at least 8 characters',
                'password.regex' => 'Password must contain at least one uppercase letter and one number',
                'password.confirmed' => 'Passwords do not match',
            ]);

            $resetToken = $request->input('reset_token');

            // Find the password reset token (valid for 10 minutes)
            $tokenRecord = PasswordResetToken::where('created_at', '>', now()->subMinutes(10))->first();

            if (!$tokenRecord || !Hash::check($resetToken, $tokenRecord->token)) {
                return ApiResponse::error('Invalid or expired reset token', 400, [
                    'reset_token' => ['Token is invalid or has expired. Please start over']
                ]);
            }

            // Find and update user password
            $user = User::where('email', $tokenRecord->email)->first();

            if (!$user) {
                return ApiResponse::error('User not found', 404);
            }

            $user->update([
                'password' => Hash::make($request->input('password')),
            ]);

            // Delete the reset token
            $tokenRecord->delete();

            return ApiResponse::success([
                'password_reset' => true,
            ], 'Password reset successfully. Please login with your new password');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to reset password: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mask identifier for security
     */
    private function maskIdentifier(string $identifier, string $type): string
    {
        if ($type === 'email') {
            $parts = explode('@', $identifier);
            $name = $parts[0];
            $domain = $parts[1] ?? '';
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 4)) . substr($name, -2);
            return $maskedName . '@' . $domain;
        }

        // Phone
        $length = strlen($identifier);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        return substr($identifier, 0, 3) . str_repeat('*', $length - 6) . substr($identifier, -3);
    }
}
