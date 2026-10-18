<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_uses_the_inventroo_brand_and_logo(): void
    {
        User::factory()->create();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in · INVENTROO')
            ->assertSee('images/inventroo-logo.png')
            ->assertDontSee('ChemStock');
    }

    public function test_administrator_can_create_roles_and_user_accounts(): void
    {
        $this->actingAs(User::factory()->create());

        $this->assertFileExists(public_path('images/inventroo-logo.png'));
        $this->assertFileExists(public_path('images/inventroo-icon.png'));
        $this->get('/dashboard')->assertOk()
            ->assertSee('INVENTROO')
            ->assertSee('images/inventroo-logo.png')
            ->assertDontSee('ChemStock');

        $this->get('/settings/access/roles')
            ->assertOk()
            ->assertSee('General Settings')
            ->assertSee('Roles &amp; Permissions', false)
            ->assertSee('User Accounts')
            ->assertSee('<details class="nav-settings"', false)
            ->assertSee(' open >', false)
            ->assertDontSee('settings-nav');

        $this->post('/settings/access/roles', [
            'name' => 'Stock viewer',
            'permissions' => ['dashboard.view', 'materials.view'],
        ])->assertRedirect('/settings/access/roles');

        $role = Role::query()->where('name', 'Stock viewer')->firstOrFail();
        $this->assertSame(['dashboard.view', 'materials.view'], $role->permissions);

        $this->post('/settings/access/users', [
            'name' => 'Stock User',
            'email' => 'stock-user@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'role_id' => $role->id,
            'is_active' => '1',
        ])->assertRedirect('/settings/access/users');

        $this->assertDatabaseHas('users', [
            'email' => 'stock-user@example.test',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_action_permissions_require_the_matching_module_view_permission(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from('/settings/access/roles/create')->post('/settings/access/roles', [
            'name' => 'Material creator',
            'permissions' => ['materials.create'],
        ])->assertRedirect('/settings/access/roles/create')
            ->assertSessionHasErrors('permissions');
    }

    public function test_settings_view_permission_does_not_expose_update_controls(): void
    {
        $role = Role::create([
            'name' => 'Settings reader',
            'permissions' => ['settings.view'],
        ]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $this->get('/settings')->assertOk()
            ->assertSee('disabled', false)
            ->assertDontSee('Save settings');
        $this->put('/settings', [
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'vat_rate' => 20,
        ])->assertForbidden();
    }

    public function test_role_permissions_are_enforced_and_navigation_hides_unavailable_modules(): void
    {
        $role = Role::create([
            'name' => 'Materials reader',
            'permissions' => ['dashboard.view', 'materials.view'],
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        $this->get('/dashboard')->assertOk();
        $this->get('/materials')->assertOk()
            ->assertSee('Materials')
            ->assertDontSee('Suppliers');
        $this->get('/materials/create')->assertForbidden();
        $this->get('/suppliers')->assertForbidden();
    }

    public function test_system_administrator_is_protected_and_last_admin_cannot_be_deactivated(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $systemRole = Role::query()->where('is_system', true)->firstOrFail();
        $this->put('/settings/access/roles/'.$systemRole->id, [
            'name' => 'Changed administrator',
            'permissions' => PermissionCatalog::all(),
        ])->assertForbidden();

        $this->put('/settings/access/users/'.$admin->id, [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'password_confirmation' => '',
            'role_id' => $systemRole->id,
            'is_active' => '0',
        ])->assertSessionHasErrors('role_id');

        User::factory()->create();
        $this->put('/settings/access/users/'.$admin->id, [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'password_confirmation' => '',
            'role_id' => $systemRole->id,
            'is_active' => '0',
        ])->assertSessionHasErrors('is_active');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_active' => true,
            'role_id' => $systemRole->id,
        ]);
    }
}
