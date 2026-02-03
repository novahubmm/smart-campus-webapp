<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordRecoveryController extends Controller
{
    public function createIdentifier()
    {
        session()->forget('password_reset_user_id');

        return view('auth.forgot-identifier');
    }

    public function storeIdentifier(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:191'],
        ]);

        $user = User::query()
            ->where('email', $data['identifier'])
            ->orWhere('phone', $data['identifier'])
            ->first();

        if (! $user) {
            return back()
                ->withErrors(['identifier' => __('We could not find an account with that email or phone number.')]);
        }

        session(['password_reset_user_id' => $user->id]);

        return redirect()->route('password.recovery.nrc');
    }

    public function createNrc()
    {
        if (! $this->pendingUser()) {
            return redirect()->route('password.recovery.identifier')
                ->withErrors(['identifier' => __('Please confirm your email or phone number first.')]);
        }

        return view('auth.forgot-nrc');
    }

    public function storeNrc(Request $request): RedirectResponse
    {
        $user = $this->pendingUser();

        if (! $user) {
            return redirect()->route('password.recovery.identifier')
                ->withErrors(['identifier' => __('Please confirm your email or phone number first.')]);
        }

        $data = $request->validate([
            'nrc' => ['required', 'string', 'max:191'],
        ]);

        if ($user->nrc !== $data['nrc']) {
            return back()->withErrors(['nrc' => __('NRC does not match our records.')]);
        }

        $otp = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'password_otp_code' => $otp,
            'password_otp_expires_at' => CarbonImmutable::now()->addMinutes(10),
        ])->save();

        session(['password_reset_user_id' => $user->id]);

        // Email/SMS delivery will be wired later; for now show the OTP entry form.
        return redirect()
            ->route('password.recovery.otp')
            ->with('status', __('If your details are correct, an OTP has been sent to your email or phone. It expires in :minutes minutes.', ['minutes' => 10]));
    }

    public function createOtp()
    {
        if (! $this->pendingUser()) {
            return redirect()->route('password.recovery.identifier')
                ->withErrors(['identifier' => __('Please confirm your email or phone number first.')]);
        }

        return view('auth.forgot-otp');
    }

    public function storeOtp(Request $request): RedirectResponse
    {
        $user = $this->pendingUser();

        if (! $user) {
            return redirect()->route('password.recovery.identifier')
                ->withErrors(['identifier' => __('Please confirm your email or phone number first.')]);
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if (! $user->password_otp_code || $user->password_otp_code !== $data['otp']) {
            return back()->withErrors(['otp' => __('Invalid code. Please check and try again.')]);
        }

        if (! $user->password_otp_expires_at || $user->password_otp_expires_at->isPast()) {
            return back()->withErrors(['otp' => __('This code has expired. Please restart recovery.')]);
        }

        // OTP verified, move to password reset step
        session(['password_reset_verified_user_id' => $user->id]);

        return redirect()->route('password.recovery.reset')
            ->with('status', __('OTP verified. Set your new password below.'));
    }

    public function createReset()
    {
        if (! $this->verifiedUser()) {
            return redirect()->route('password.recovery.identifier')
                ->withErrors(['identifier' => __('Please restart recovery.')]);
        }

        return view('auth.forgot-reset');
    }

    public function storeReset(Request $request): RedirectResponse
    {
        $user = $this->verifiedUser();

        if (! $user) {
            return redirect()->route('password.recovery.identifier')
                ->withErrors(['identifier' => __('Please restart recovery.')]);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->forceFill([
            'password' => bcrypt($data['password']),
            'password_otp_code' => null,
            'password_otp_expires_at' => null,
        ])->save();

        session()->forget('password_reset_user_id');
        session()->forget('password_reset_verified_user_id');

        return redirect()
            ->route('login')
            ->with('status', __('Password updated. Please sign in with your new password.'));
    }

    private function pendingUser(): ?User
    {
        $userId = session('password_reset_user_id');

        return $userId ? User::find($userId) : null;
    }

    private function verifiedUser(): ?User
    {
        $userId = session('password_reset_verified_user_id');

        return $userId ? User::find($userId) : null;
    }
}
