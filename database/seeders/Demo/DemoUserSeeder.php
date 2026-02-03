<?php

namespace Database\Seeders\Demo;

use App\Models\StaffProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Carbon\Carbon;

class DemoUserSeeder extends DemoBaseSeeder
{
    public function run(array $departments): array
    {
        $adminUser = $this->createAdminUser();
        $this->createKeyContacts($departments);
        $staffProfiles = $this->createStaff($departments);
        $teacherProfiles = $this->createTeachers($departments);

        return [
            'adminUser' => $adminUser,
            'staffProfiles' => $staffProfiles,
            'teacherProfiles' => $teacherProfiles,
        ];
    }

    private function createAdminUser(): User
    {
        $this->command->info('Creating Admin User...');

        $user = User::firstOrCreate(
            ['email' => 'admin@smartcampusedu.com'],
            ['name' => $this->generateUniqueName('male'), 'password' => $this->getHashedPassword(), 'is_active' => true]
        );
        $user->assignRole('admin');

        return $user;
    }

    private function createKeyContacts(array $departments): void
    {
        $this->command->info('Creating Key Contacts (4)...');

        $keyContacts = [
            ['email' => 'principal@smartcampusedu.com', 'position' => 'Principal', 'department' => 'MGT', 'gender' => 'male'],
            ['email' => 'viceprincipal@smartcampusedu.com', 'position' => 'Vice Principal', 'department' => 'MGT', 'gender' => 'female'],
            ['email' => 'finance@smartcampusedu.com', 'position' => 'Finance Manager', 'department' => 'FIN', 'gender' => 'female'],
            ['email' => 'operations@smartcampusedu.com', 'position' => 'Operations Manager', 'department' => 'MGT', 'gender' => 'male'],
        ];

        foreach ($keyContacts as $contact) {
            $user = User::firstOrCreate(
                ['email' => $contact['email']],
                ['name' => $this->generateUniqueName($contact['gender']), 'password' => $this->getHashedPassword(), 'is_active' => true]
            );
            $user->assignRole('staff');

            StaffProfile::firstOrCreate(['user_id' => $user->id], [
                'employee_id' => $this->generateEmployeeId('STF'),
                'position' => $contact['position'],
                'department_id' => $departments[$contact['department']]->id,
                'hire_date' => $this->getSchoolOpenDate()->copy()->subYears(rand(1, 5)),
                'basic_salary' => rand(500000, 1000000),
                'gender' => $contact['gender'],
                'dob' => Carbon::now()->subYears(rand(35, 55)),
                'phone_no' => '09' . rand(100000000, 999999999),
                'address' => 'Yangon, Myanmar',
                'status' => 'active',
            ]);
        }
    }

    private function createStaff(array $departments): array
    {
        $this->command->info('Creating Staff (10)...');

        $positions = ['Accountant', 'HR Officer', 'Admin Assistant', 'Receptionist', 'IT Support', 'Clerk', 'Secretary', 'Coordinator', 'Assistant', 'Officer'];
        $staffProfiles = [];

        for ($i = 1; $i <= 10; $i++) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $deptKey = rand(0, 1) ? 'FIN' : 'MGT';

            $user = User::create([
                'name' => $this->generateUniqueName($gender),
                'email' => "staff{$i}@smartcampusedu.com",
                'password' => $this->getHashedPassword(),
                'is_active' => true,
            ]);
            $user->assignRole('staff');

            $staffProfiles[] = StaffProfile::create([
                'user_id' => $user->id,
                'employee_id' => $this->generateEmployeeId('STF'),
                'position' => $positions[$i - 1],
                'department_id' => $departments[$deptKey]->id,
                'hire_date' => $this->getSchoolOpenDate()->copy()->subYears(rand(1, 3)),
                'basic_salary' => rand(300000, 500000),
                'gender' => $gender,
                'dob' => Carbon::now()->subYears(rand(25, 45)),
                'phone_no' => '09' . rand(100000000, 999999999),
                'address' => 'Yangon, Myanmar',
                'status' => 'active',
            ]);
        }

        return $staffProfiles;
    }

    private function createTeachers(array $departments): array
    {
        $this->command->info('Creating Teachers (78)...');

        $teacherProfiles = [];
        $qualifications = ['B.Ed', 'M.Ed', 'B.A', 'M.A', 'B.Sc', 'M.Sc', 'Ph.D'];

        for ($i = 1; $i <= 78; $i++) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $profileImage = $this->getRandomTeacherProfileImage($gender);

            $user = User::create([
                'name' => $this->generateUniqueName($gender),
                'email' => "teacher{$i}@smartcampusedu.com",
                'password' => $this->getHashedPassword(),
                'is_active' => true,
            ]);
            $user->assignRole('teacher');

            $teacherProfiles[] = TeacherProfile::create([
                'user_id' => $user->id,
                'employee_id' => $this->generateEmployeeId('TCH'),
                'position' => 'Teacher',
                'department_id' => $departments['TCH']->id,
                'hire_date' => $this->getSchoolOpenDate()->copy()->subYears(rand(1, 5)),
                'basic_salary' => rand(400000, 800000),
                'gender' => $gender,
                'dob' => Carbon::now()->subYears(rand(25, 50)),
                'phone_no' => '09' . rand(100000000, 999999999),
                'address' => 'Yangon, Myanmar',
                'qualification' => $qualifications[array_rand($qualifications)],
                'previous_experience_years' => rand(0, 15),
                'photo_path' => $profileImage,
                'status' => 'active',
            ]);
        }

        return $teacherProfiles;
    }

    /**
     * Get random profile image for teacher based on gender
     * Images are stored in public/images/teacher_images/{gender}/
     * and accessed via /images/teacher_images/{gender}/
     */
    private function getRandomTeacherProfileImage(string $gender): string
    {
        $imagePath = "images/teacher_images/{$gender}";
        $fullPath = public_path($imagePath);
        
        if (!is_dir($fullPath)) {
            return null;
        }

        $images = array_diff(scandir($fullPath), ['.', '..']);
        $images = array_filter($images, function($file) use ($fullPath) {
            return is_file($fullPath . '/' . $file) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
        });

        if (empty($images)) {
            return null;
        }

        $randomImage = $images[array_rand($images)];
        return "{$imagePath}/{$randomImage}";
    }
}
