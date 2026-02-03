<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedPermissions();
    }

    public function test_admin_can_create_user_with_role_and_active_status(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Teacher One',
                'email' => 'teacher@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => [RoleEnum::TEACHER->value],
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'teacher@example.com',
            'is_active' => true,
        ]);

        $user = User::where('email', 'teacher@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole(RoleEnum::TEACHER->value));
    }

    public function test_admin_can_deactivate_and_activate_user_and_revoke_tokens_on_deactivate(): void
    {
        $admin = $this->adminUser();
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::STAFF->value);
        $user->createToken('test-token');

        $this->actingAs($admin)
            ->post(route('users.deactivate', $user))
            ->assertRedirect(route('users.index'));

        $this->assertFalse($user->fresh()->is_active);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);

        $this->actingAs($admin)
            ->post(route('users.activate', $user))
            ->assertRedirect(route('users.index'));

        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_admin_can_reset_password_and_tokens_are_revoked(): void
    {
        $admin = $this->adminUser();
        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $user->assignRole(RoleEnum::STAFF->value);
        $user->createToken('test-token');

        $this->actingAs($admin)
            ->post(route('users.reset-password', $user), [
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

    private function seedRoles(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::firstOrCreate(
                ['name' => $role->value, 'guard_name' => 'web'],
                ['name' => $role->value, 'guard_name' => 'web']
            );
        }
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);
        $admin->givePermissionTo(['view users', 'create users', 'update users', 'delete users']);

        return $admin;
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'view users',
            'create users',
            'update users',
            'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['name' => $permission, 'guard_name' => 'web']
            );
        }
    }
}
