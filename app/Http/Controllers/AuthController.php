<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        return User::query()->exists()
            ? view('auth.login')
            : redirect()->route('setup');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->landingRoute()));
    }

    public function showSetup(): View|RedirectResponse
    {
        return User::query()->exists()
            ? redirect()->route('login')
            : view('auth.setup');
    }

    public function setup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data) {
            if (User::query()->exists()) {
                return null;
            }

            $claimedSetup = DB::table('inventory_settings')->insertOrIgnore([
                'key' => 'administrator_initialized',
                'value' => 'true',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($claimedSetup === 0) {
                return null;
            }

            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => Role::query()->where('is_system', true)->value('id'),
                'is_active' => true,
            ]);
        });

        if (! $user) {
            return redirect()->route('login');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
