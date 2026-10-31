<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->with('role')->orderBy('name')->paginate(20);

        return view('settings.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('settings.users.form', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            if (! $avatarPath) {
                throw new RuntimeException('Unable to save the uploaded avatar.');
            }
        }

        User::create([
            'avatar_path' => $avatarPath,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('settings.users.index')
            ->with('success_title', 'User Account Created')
            ->with('success', "The account for “{$data['name']}” was created successfully.");
    }

    public function edit(User $user): View
    {
        return view('settings.users.form', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $willRemainActiveAdmin = $request->boolean('is_active')
            && Role::query()->whereKey($data['role_id'])->where('is_system', true)->exists();

        if ($user->is_active && $user->role?->is_system && ! $willRemainActiveAdmin) {
            $activeAdmins = User::query()->where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('is_system', true))
                ->count();

            if ($activeAdmins <= 1) {
                throw ValidationException::withMessages([
                    'role_id' => 'At least one active administrator account must remain.',
                ]);
            }
        }

        if ($request->user()->is($user) && ! $request->boolean('is_active')) {
            throw ValidationException::withMessages([
                'is_active' => 'You cannot deactivate your own account.',
            ]);
        }

        $updates = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'is_active' => $request->boolean('is_active'),
        ];
        if (! empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }

        $previousAvatar = $user->avatar_path;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            if (! $avatarPath) {
                throw new RuntimeException('Unable to save the uploaded avatar.');
            }
            $updates['avatar_path'] = $avatarPath;
        } elseif ($request->boolean('remove_avatar')) {
            $updates['avatar_path'] = null;
        }

        $user->update($updates);

        if (array_key_exists('avatar_path', $updates) && $previousAvatar && $previousAvatar !== $updates['avatar_path']) {
            if (Storage::disk('public')->exists($previousAvatar)) {
                Storage::disk('public')->delete($previousAvatar);
            }
        }

        return redirect()->route('settings.users.index')
            ->with('success_title', 'User Account Updated')
            ->with('success', "The account for “{$user->name}” was updated successfully.");
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:10', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $updates = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        $previousAvatar = $user->avatar_path;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            if (! $avatarPath) {
                throw new RuntimeException('Unable to save the uploaded profile avatar.');
            }
            $updates['avatar_path'] = $avatarPath;
        }
        if (! empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }
        $user->update($updates);

        if (isset($updates['avatar_path']) && $previousAvatar && ! Storage::disk('public')->delete($previousAvatar)) {
            throw new RuntimeException('The previous profile avatar could not be removed.');
        }

        return redirect()->back()
            ->with('success_title', 'Profile Updated')
            ->with('success', 'Your profile details were updated successfully.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:10', 'confirmed'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'is_active' => ['required', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);
    }
}
