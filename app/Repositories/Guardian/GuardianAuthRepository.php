<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianAuthRepositoryInterface;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuardianAuthRepository implements GuardianAuthRepositoryInterface
{
    public function findGuardianByLogin(string $login): ?User
    {
        return User::where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('phone', $login);
            })
            ->whereHas('guardianProfile')
            ->first();
    }

    public function createToken(User $user, string $deviceName): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function getGuardianStudents(User $user): Collection
    {
        $guardianProfile = $user->guardianProfile;
        
        if (!$guardianProfile) {
            return new Collection();
        }

        return $guardianProfile->students()
            ->with(['user', 'grade', 'classModel'])
            ->get();
    }

    public function createPasswordResetOtp(string $identifier): array
    {
        $user = $this->findGuardianByLogin($identifier);
        
        if (!$user) {
            throw new \Exception('No account found with this email or phone number.');
        }

        // Generate 6-digit OTP and token
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpToken = Str::random(64);
        $expiresIn = 300; // 5 minutes

        // Store OTP in password_reset_tokens table
        PasswordResetToken::updateOrCreate(
            ['email' => $user->email],
            [
                'token' => Hash::make($otpToken),
                'otp_code' => Hash::make($otpCode),
                'dev_otp_plain' => $otpCode,
                'otp_expires_at' => now()->addSeconds($expiresIn),
                'otp_resent_at' => null,
                'created_at' => now(),
            ]
        );

        // Mask the identifier for response
        $maskedIdentifier = $this->maskIdentifier($identifier, $user);

        // TODO: Send OTP via SMS/Email
        // For development, we'll log it
        \Log::info("Guardian OTP for {$identifier}: {$otpCode}");

        return [
            'otp_sent_to' => $maskedIdentifier,
            'otp_token' => $otpToken,
            'expires_in' => $expiresIn,
            // Include OTP in development mode only
            'dev_otp' => config('app.debug') ? $otpCode : null,
        ];
    }

    public function resendOtp(string $identifier): array
    {
        $user = $this->findGuardianByLogin($identifier);
        
        if (!$user) {
            throw new \Exception('No account found with this email or phone number.');
        }

        // Find existing reset token
        $resetToken = PasswordResetToken::where('email', $user->email)
            ->where('created_at', '>', now()->subMinutes(15))
            ->first();

        if (!$resetToken) {
            throw new \Exception('No active OTP session found. Please start the forgot password process again.');
        }

        // Check cooldown (60 seconds)
        if ($resetToken->otp_resent_at && $resetToken->otp_resent_at->addSeconds(60)->isFuture()) {
            $cooldownRemaining = $resetToken->otp_resent_at->addSeconds(60)->diffInSeconds(now());
            throw new \Exception("Please wait {$cooldownRemaining} seconds before requesting a new OTP.");
        }

        // Generate new 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpToken = Str::random(64);
        $expiresIn = 300; // 5 minutes

        // Update OTP in password_reset_tokens table
        $resetToken->update([
            'token' => Hash::make($otpToken),
            'otp_code' => Hash::make($otpCode),
            'dev_otp_plain' => $otpCode,
            'otp_expires_at' => now()->addSeconds($expiresIn),
            'otp_resent_at' => now(),
        ]);

        // Mask the identifier for response
        $maskedIdentifier = $this->maskIdentifier($identifier, $user);

        // TODO: Send OTP via SMS/Email
        // For development, we'll log it
        \Log::info("Guardian OTP resent for {$identifier}: {$otpCode}");

        return [
            'otp_sent_to' => $maskedIdentifier,
            'otp_token' => $otpToken,
            'expires_in' => $expiresIn,
            'cooldown' => 60,
            // Include OTP in development mode only
            'dev_otp' => config('app.debug') ? $otpCode : null,
        ];
    }

    public function verifyOtp(string $identifier, string $otp): ?string
    {
        $user = $this->findGuardianByLogin($identifier);
        
        if (!$user) {
            throw new \Exception('No account found with this email or phone number.');
        }

        // Find the password reset token
        $resetToken = PasswordResetToken::where('email', $user->email)
            ->whereNotNull('otp_code')
            ->where('otp_expires_at', '>', now())
            ->first();

        if (!$resetToken) {
            throw new \Exception('OTP has expired. Please request a new one.');
        }

        // Verify OTP code
        if (!Hash::check($otp, $resetToken->otp_code)) {
            throw new \Exception('Invalid OTP. Please try again.');
        }

        // OTP verified, generate final reset token
        $finalResetToken = Str::random(64);

        $resetToken->update([
            'token' => Hash::make($finalResetToken),
            'otp_code' => null,
            'dev_otp_plain' => null,
            'otp_expires_at' => null,
            'created_at' => now(),
        ]);

        return $finalResetToken;
    }

    public function resetPassword(string $resetToken, string $password): bool
    {
        // Find the password reset token (valid for 15 minutes)
        $tokenRecord = PasswordResetToken::where('created_at', '>', now()->subMinutes(15))
            ->whereNull('otp_code')
            ->get()
            ->first(function ($record) use ($resetToken) {
                return Hash::check($resetToken, $record->token);
            });

        if (!$tokenRecord) {
            throw new \Exception('Reset token has expired. Please start over.');
        }

        $user = User::where('email', $tokenRecord->email)->first();
        
        if (!$user) {
            throw new \Exception('User not found.');
        }

        $user->update([
            'password' => Hash::make($password),
        ]);

        // Delete the reset token
        $tokenRecord->delete();

        // Revoke all existing tokens
        $this->revokeTokens($user);

        return true;
    }

    private function maskIdentifier(string $identifier, User $user): string
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $user->email);
            $name = $parts[0];
            $domain = $parts[1];
            $masked = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 2, 3)) . '@' . $domain;
            return $masked;
        }

        // Phone number
        $phone = $user->phone ?? $identifier;
        return substr($phone, 0, 3) . str_repeat('*', max(strlen($phone) - 6, 4)) . substr($phone, -3);
    }
}
