<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        // If already authenticated as an admin, redirect straight to dashboard
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle an admin authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Validate login input
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'password.required' => 'Password is required.',
        ]);

        $remember = $request->boolean('remember');

        // 2. Attempt authentication using Laravel built-in password verification
        if (Auth::attempt($credentials, $remember)) {
            // Check authorization: only is_admin = true allowed
            if (!Auth::user()->is_admin) {
                // Reject normal user, flush session, and return generic message
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'These credentials do not match our records.',
                ])->onlyInput('email');
            }

            // 3. Regenerate session upon successful login (prevents session fixation attacks)
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Signed in successfully. Welcome back, ' . Auth::user()->name . '!');
        }

        // 4. Return generic invalid credentials message
        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the admin user out securely.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been safely logged out of the Admin Portal.');
    }
}

