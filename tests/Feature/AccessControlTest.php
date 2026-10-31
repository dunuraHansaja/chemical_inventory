<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->assertSee('images/inventroo-icon.png')
            ->assertSee('Welcome Back')
            ->assertSee('name="remember"', false)
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
            ->assertSee('data-profile-open', false)
            ->assertSee('Profile details')
            ->assertSee('Sign out')
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

    public function test_user_can_update_own_profile_without_changing_role_or_account_status(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $roleId = $user->role_id;
        $this->actingAs($user);

        $this->from('/dashboard')->put('/profile', [
            'name' => 'Updated Profile',
            'email' => 'updated-profile@example.test',
            'password' => 'updated-secure-password',
            'password_confirmation' => 'updated-secure-password',
            'avatar' => UploadedFile::fake()->image('avatar.png'),
        ])->assertRedirect('/dashboard')
            ->assertSessionHas('success', 'Your profile details were updated successfully.');

        $user->refresh();
        $this->assertSame('Updated Profile', $user->name);
        $this->assertSame('updated-profile@example.test', $user->email);
        $this->assertSame($roleId, $user->role_id);
        $this->assertTrue($user->is_active);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('updated-secure-password', $user->password));
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);

        $this->get('/dashboard')->assertOk()
            ->assertSee(Storage::disk('public')->url($user->avatar_path));
    }

    public function test_profile_update_rejects_an_email_used_by_another_account(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $this->from('/dashboard')->put('/profile', [
            'name' => 'Changed Name',
            'email' => $otherUser->email,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect('/dashboard')
            ->assertSessionHasErrorsIn('profile', ['email']);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('data-profile-invalid', false)
            ->assertSee('The email has already been taken.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function test_profile_update_rejects_non_image_avatar_uploads(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->from('/dashboard')->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'avatar' => UploadedFile::fake()->create('avatar.txt', 10, 'text/plain'),
        ])->assertRedirect('/dashboard')
            ->assertSessionHasErrorsIn('profile', ['avatar']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'avatar_path' => null]);
        $this->assertSame([], Storage::disk('public')->allFiles('avatars'));
    }

    public function test_profile_avatar_replaces_the_previous_uploaded_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->from('/dashboard')->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'avatar' => UploadedFile::fake()->image('first-avatar.png'),
        ])->assertRedirect('/dashboard');

        $user->refresh();
        $previousAvatar = $user->avatar_path;

        $this->from('/dashboard')->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'avatar' => UploadedFile::fake()->image('second-avatar.jpg'),
        ])->assertRedirect('/dashboard');

        $user->refresh();
        $this->assertNotSame($previousAvatar, $user->avatar_path);
        Storage::disk('public')->assertMissing($previousAvatar);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_administrator_can_create_user_account_with_avatar(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $role = Role::query()->firstOrCreate(['name' => 'Operator'], ['permissions' => ['materials.view']]);

        $this->get('/settings/access/users/create')->assertOk()
            ->assertSee('Profile avatar')
            ->assertSee('name="avatar"', false);

        $this->post('/settings/access/users', [
            'name' => 'Operator Jane',
            'email' => 'operator-jane@example.test',
            'password' => 'secure-operator-password',
            'password_confirmation' => 'secure-operator-password',
            'role_id' => $role->id,
            'is_active' => '1',
            'avatar' => UploadedFile::fake()->image('jane.png'),
        ])->assertRedirect('/settings/access/users');

        $user = User::query()->where('email', 'operator-jane@example.test')->firstOrFail();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);

        $this->get('/settings/access/users')->assertOk()
            ->assertSee($user->avatar_url);
    }

    public function test_administrator_can_update_and_remove_user_avatar(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $role = Role::query()->firstOrCreate(['name' => 'Staff'], ['permissions' => ['materials.view']]);

        $avatarPath = UploadedFile::fake()->image('existing-avatar.png')->store('avatars', 'public');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'avatar_path' => $avatarPath,
        ]);

        $this->get("/settings/access/users/{$user->id}/edit")->assertOk()
            ->assertSee('Change avatar')
            ->assertSee('Remove current avatar')
            ->assertSee($user->avatar_url);

        // Update with new avatar
        $this->put("/settings/access/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $role->id,
            'is_active' => '1',
            'avatar' => UploadedFile::fake()->image('replacement-avatar.jpg'),
        ])->assertRedirect('/settings/access/users');

        $user->refresh();
        $this->assertNotSame($avatarPath, $user->avatar_path);
        Storage::disk('public')->assertMissing($avatarPath);
        Storage::disk('public')->assertExists($user->avatar_path);

        $currentAvatar = $user->avatar_path;

        // Remove avatar
        $this->put("/settings/access/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $role->id,
            'is_active' => '1',
            'remove_avatar' => '1',
        ])->assertRedirect('/settings/access/users');

        $user->refresh();
        $this->assertNull($user->avatar_path);
        Storage::disk('public')->assertMissing($currentAvatar);
    }

    public function test_administrator_user_creation_validates_avatar_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $role = Role::query()->firstOrCreate(['name' => 'Auditor'], ['permissions' => ['movements.view']]);

        $this->from('/settings/access/users/create')->post('/settings/access/users', [
            'name' => 'Invalid Avatar User',
            'email' => 'invalid-avatar@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'role_id' => $role->id,
            'is_active' => '1',
            'avatar' => UploadedFile::fake()->create('malicious.pdf', 100, 'application/pdf'),
        ])->assertRedirect('/settings/access/users/create')
            ->assertSessionHasErrors('avatar');

        $this->assertDatabaseMissing('users', ['email' => 'invalid-avatar@example.test']);
    }
}
