<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended('/');
        }

        $demoUsers = User::take(6)->get();

        return view('auth.login', [
            'title' => 'Log Masuk - Filemai.my',
            'demoUsers' => $demoUsers,
        ]);
    }

    /**
     * Handle an incoming login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user && in_array($user->role, ['creator', 'admin'])) {
                return redirect()->intended(route('creator.dramas.episodes.create', ['drama' => 1]));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'Emel atau kata laluan tidak sah.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Quick 1-click login switcher for staging & demo testing.
     */
    public function demoLogin(User $user): RedirectResponse
    {
        Auth::login($user);
        request()->session()->regenerate();

        if (in_array($user->role, ['creator', 'admin'])) {
            return redirect()->intended(route('creator.dramas.episodes.create', ['drama' => 1]))
                ->with('success', "Log masuk sebagai {$user->name} ({$user->role})");
        }

        return redirect()->intended(route('home'))
            ->with('success', "Log masuk sebagai {$user->name}");
    }
}
