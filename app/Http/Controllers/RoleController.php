<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()->withCount('users')->orderByDesc('is_system')->orderBy('name')->paginate(20);

        return view('settings.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('settings.roles.form', [
            'role' => new Role,
            'permissionGroups' => PermissionCatalog::groups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Role::create($data);

        return redirect()->route('settings.roles.index')
            ->with('success_title', 'Role Created')
            ->with('success', "The role “{$data['name']}” was created successfully.");
    }

    public function edit(Role $role): View
    {
        return view('settings.roles.form', [
            'role' => $role,
            'permissionGroups' => PermissionCatalog::groups(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'The system administrator role cannot be changed.');

        $data = $this->validated($request, $role);
        $role->update($data);

        return redirect()->route('settings.roles.index')
            ->with('success_title', 'Role Updated')
            ->with('success', "The role “{$role->name}” was updated successfully.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'The system administrator role cannot be deleted.');

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'This role is assigned to one or more user accounts. Reassign those accounts before deleting it.']);
        }

        $role->delete();

        return redirect()->route('settings.roles.index')
            ->with('warning', "The role “{$role->name}” was deleted.");
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role?->id)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::all())],
        ]);

        $permissions = array_values(array_unique($data['permissions'] ?? []));
        if ($permissions === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'permissions' => 'Select at least one permission for this role.',
            ]);
        }

        $missingViews = PermissionCatalog::missingViewPermissions($permissions);
        if ($missingViews !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'permissions' => 'Grant these view permissions alongside the selected actions: '.implode(', ', $missingViews).'.',
            ]);
        }

        return [
            'name' => $data['name'],
            'permissions' => $permissions,
        ];
    }
}
