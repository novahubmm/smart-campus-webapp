<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions from PermissionEnum
        $permissions = collect(PermissionEnum::cases())->map(function ($permission) {
            return Permission::firstOrCreate(
                ['name' => $permission->value],
                ['id' => (string) Str::uuid(), 'guard_name' => 'web']
            );
        });

        // Create roles from RoleEnum
        $systemAdminRole = Role::firstOrCreate(['name' => RoleEnum::SYSTEM_ADMIN->value], ['id' => (string) Str::uuid(), 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value], ['id' => (string) Str::uuid(), 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => RoleEnum::STAFF->value], ['id' => (string) Str::uuid(), 'guard_name' => 'web']);
        $teacherRole = Role::firstOrCreate(['name' => RoleEnum::TEACHER->value], ['id' => (string) Str::uuid(), 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => RoleEnum::STUDENT->value], ['id' => (string) Str::uuid(), 'guard_name' => 'web']);
        $guardianRole = Role::firstOrCreate(['name' => RoleEnum::GUARDIAN->value], ['id' => (string) Str::uuid(), 'guard_name' => 'web']);

        // Assign all permissions to system_admin and admin
        $systemAdminRole->permissions()->sync($permissions->pluck('id'));
        $adminRole->permissions()->sync($permissions->pluck('id'));

        // Assign scoped permissions to staff/teacher/student/guardian
        $staffRole->permissions()->sync(
            $permissions->whereIn('name', [
                PermissionEnum::ACCESS_DASHBOARD->value,
                PermissionEnum::VIEW_USERS->value,
            ])->pluck('id')
        );

        $teacherRole->permissions()->sync(
            $permissions->where('name', PermissionEnum::ACCESS_DASHBOARD->value)->pluck('id')
        );

        $studentRole->permissions()->sync(
            $permissions->where('name', PermissionEnum::ACCESS_DASHBOARD->value)->pluck('id')
        );

        $guardianRole->permissions()->sync(
            $permissions->where('name', PermissionEnum::ACCESS_DASHBOARD->value)->pluck('id')
        );

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
