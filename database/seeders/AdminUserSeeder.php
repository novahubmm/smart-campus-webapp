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

        // Create staff user + profile
        $staff = User::firstOrCreate(
            ['email' => 'staff@smartcampusedu.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Staff User',
                'phone' => '09770000002',
                'nrc' => '12/ABC(N)123457',
                'password' => Hash::make('password'),
            ]
        );
        $staffRoleId = $roles[RoleEnum::STAFF->value] ?? null;
        if ($staffRoleId) {
            $staff->roles()->sync([$staffRoleId]);
        }
        StaffProfile::firstOrCreate(
            ['user_id' => $staff->id],
            [
                'id' => (string) Str::uuid(),
                'employee_id' => 'STF-001',
                'position' => 'Office Staff',
                'phone_no' => $staff->phone,
                'nrc' => $staff->nrc,
                'status' => 'active',
            ]
        );

        // Create teacher user + profile
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@smartcampusedu.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Teacher User',
                'phone' => '09770000003',
                'nrc' => '12/ABC(N)123458',
                'password' => Hash::make('password'),
            ]
        );
        $teacherRoleId = $roles[RoleEnum::TEACHER->value] ?? null;
        if ($teacherRoleId) {
            $teacher->roles()->sync([$teacherRoleId]);
        }
        TeacherProfile::firstOrCreate(
            ['user_id' => $teacher->id],
            [
                'id' => (string) Str::uuid(),
                'employee_id' => 'TCH-001',
                'position' => 'Homeroom Teacher',
                'phone_no' => $teacher->phone,
                'nrc' => $teacher->nrc,
                'status' => 'active',
            ]
        );

        // Create guardian user + profile
        $guardian = User::firstOrCreate(
            ['email' => 'guardian@smartcampusedu.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Guardian User',
                'phone' => '09770000004',
                'nrc' => '12/ABC(N)123459',
                'password' => Hash::make('password'),
            ]
        );
        $guardianRoleId = $roles[RoleEnum::GUARDIAN->value] ?? null;
        if ($guardianRoleId) {
            $guardian->roles()->sync([$guardianRoleId]);
        }
        $guardianProfile = GuardianProfile::firstOrCreate(
            ['user_id' => $guardian->id],
            [
                'id' => (string) Str::uuid(),
                'occupation' => 'Business',
                'address' => 'Yangon',
                'notes' => null,
            ]
        );

        // Create student user + profile linked to guardian
        $student = User::firstOrCreate(
            ['email' => 'student@smartcampusedu.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Student User',
                'phone' => '09770000005',
                'nrc' => '12/ABC(N)123460',
                'password' => Hash::make('password'),
            ]
        );
        $studentRoleId = $roles[RoleEnum::STUDENT->value] ?? null;
        if ($studentRoleId) {
            $student->roles()->sync([$studentRoleId]);
        }
        $studentProfile = StudentProfile::firstOrCreate(
            ['user_id' => $student->id],
            [
                'id' => (string) Str::uuid(),
                'student_identifier' => 'STD-001',
                'starting_grade_at_school' => 'G-01',
                'current_grade' => 'G-01',
                'current_class' => 'A',
                'status' => 'active',
            ]
        );

        if ($studentProfile && $guardianProfile) {
            $guardianProfile->students()->syncWithoutDetaching([
                $studentProfile->id => ['relationship' => 'Parent', 'is_primary' => true],
            ]);
        }

        $this->command->info('Admin, Staff, Teacher, Student, and Guardian users created successfully!');
        $this->command->info('Admin: admin@smartcampusedu.com / password');
        $this->command->info('Staff: staff@smartcampusedu.com / password');
        $this->command->info('Teacher: teacher@smartcampusedu.com / password');
        $this->command->info('Student: student@smartcampusedu.com / password');
        $this->command->info('Guardian: guardian@smartcampusedu.com / password');
    }
}
