<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Get authenticated user
        $user = Auth::user();
        $roles = $user->roles->pluck('name')->toArray();
        
        // Always redirect guardian and teacher roles to PWA
        // Priority: guardian > teacher (as per API user_type)
        if (in_array('guardian', $roles)) {
            return redirect()->route('guardian-pwa.home');
        } elseif (in_array('teacher', $roles)) {
            return redirect()->route('teacher-pwa.dashboard');
        }

        // Admin and other roles go to regular dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Force redirect to login page with success message
        return redirect()->route('login')->with('status', 'You have been logged out successfully.');
    }
}
