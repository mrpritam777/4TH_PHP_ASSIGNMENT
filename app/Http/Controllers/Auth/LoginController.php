<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'email address',
            'password' => 'password',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user->canLogin()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        ActivityLog::record('login', 'Authentication', $user->id, "{$user->name} logged in", $request->ip());

        if ($user->isAdmin()) {
            $destination = route('admin.dashboard');
        } elseif ($user->isContractor()) {
            $destination = route('contractor.dashboard');
        } elseif ($user->isStaff()) {
            $destination = route('staff.tasks.index');
        } else {
            $destination = route('public.home');
        }

        return redirect()->intended($destination);
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out securely.');
    }
}
