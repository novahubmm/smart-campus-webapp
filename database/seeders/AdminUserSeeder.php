<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\GuardianProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure core roles exist
        foreach (RoleEnum::values() as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
        }

        $roles = Role::pluck('id', 'name');

        // Create system admin user
        $systemAdmin = User::firstOrCreate(
            ['email' => 'sysadmin@smartcampusedu.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'System Administrator',
                'phone' => '09770000000',
                'nrc' => '0',
                'password' => Hash::make('password'),
            ]
        );
        $systemAdminRoleId = $roles[RoleEnum::SYSTEM_ADMIN->value] ?? null;
        if ($systemAdminRoleId) {
            $systemAdmin->roles()->sync([$systemAdminRoleId]);
        }

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@smartcampusedu.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin User',
                'phone' => '09770000001',
                'nrc' => '1',
                'password' => Hash::make('password'),
            ]
        );
        $adminRoleId = $roles[RoleEnum::ADMIN->value] ?? null;
        if ($adminRoleId) {
            $admin->roles()->sync([$adminRoleId]);
        }

        $this->command->info('System Admin: sysadmin@smartcampusedu.com / password');
        $this->command->info('Admin: admin@smartcampusedu.com / password');
    }
}
