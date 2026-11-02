<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login', [
            'title' => 'Log Masuk - Filemai.my',
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Sila masukkan alamat emel anda.',
            'email.email' => 'Format alamat emel tidak sah.',
            'password.required' => 'Sila masukkan kata laluan anda.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            $greeting = 'Selamat kembali, ' . $user->name . '!';
            
            // If creator and no specific intended URL was set
            if ($user->isCreator() && !$request->session()->has('url.intended')) {
                return redirect()->route('home')->with('success', $greeting);
            }

            return redirect()->intended(route('home'))->with('success', $greeting);
        }

        throw ValidationException::withMessages([
            'email' => 'Alamat emel atau kata laluan yang dimasukkan tidak tepat.',
        ]);
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $initialRole = in_array($request->query('role'), ['creator', 'viewer']) 
            ? $request->query('role') 
            : 'viewer';

        return view('auth.register', [
            'title' => 'Daftar Akaun Baharu - Filemai.my',
            'initialRole' => $initialRole,
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', 'string', 'in:viewer,creator'],
        ], [
            'name.required' => 'Sila masukkan nama penuh anda.',
            'name.min' => 'Nama mestilah sekurang-kurangnya 2 aksara.',
            'email.required' => 'Sila masukkan alamat emel anda.',
            'email.email' => 'Format emel tidak sah.',
            'email.unique' => 'Alamat emel ini sudah pun didaftarkan di Filemai.',
            'password.required' => 'Sila tetapkan kata laluan anda.',
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 6 aksara.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
        ]);

        $role = in_array($validated['role'] ?? 'viewer', ['viewer', 'creator']) 
            ? $validated['role'] 
            : 'viewer';

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'role' => $role,
            'subscription_status' => 'free',
            'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode(trim($validated['name'])) . '&background=E50914&color=fff',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'creator') {
            return redirect()->route('creator.dramas.episodes.create', ['drama' => 1])
                ->with('success', "Tahniah {$user->name}! Akaun Creator anda telah diaktifkan.");
        }

        return redirect()->route('home')
            ->with('success', "Selamat datang ke Filemai.my, {$user->name}! Akaun anda telah berjaya dicipta.");
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Anda telah berjaya log keluar.');
    }

    /**
     * Quick 1-click switcher for testing demo accounts in staging/local.
     */
    public function demoSwitch(Request $request, int|string $id): RedirectResponse
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('login')->with('error', 'Akaun demo tidak wujud.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $badge = ucfirst($user->role) . ($user->isPremium() ? ' (VIP)' : '');

        return redirect()->intended(route('home'))
            ->with('success', "Log masuk pantas sebagai {$user->name} [{$badge}].");
    }
}
