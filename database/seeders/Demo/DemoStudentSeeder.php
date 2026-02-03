<?php

namespace Database\Seeders\Demo;

use Illuminate\Support\Facades\DB;

class DemoStudentSeeder extends DemoBaseSeeder
{
    public function run(array $classes): array
    {
        $studentProfiles = $this->createStudents($classes);
        $this->createGuardians($studentProfiles);

        return $studentProfiles;
    }

    private function createStudents(array $classes): array
    {
        $this->command->info('Creating Students (1,170)...');

        $studentProfiles = [];
        $studentNumber = 1;
        $studentRole = \Spatie\Permission\Models\Role::where('name', 'student')->first();

        if (!$studentRole) {
            throw new \Exception('Student role not found. Please run RolePermissionSeeder first.');
        }

        foreach ($classes as $class) {
            // Ensure 15 male and 15 female students per class
            $genders = array_merge(array_fill(0, 15, 'male'), array_fill(0, 15, 'female'));
            shuffle($genders); // Randomize the order

            for ($i = 0; $i < 30; $i++) {
                $gender = $genders[$i];
                $studentId = 'STU-' . date('Y') . '-' . str_pad($studentNumber, 4, '0', STR_PAD_LEFT);

                // Calculate appropriate age based on grade level
                $baseAge = 6 + $class->grade->level; // Grade 0 = age 6, Grade 1 = age 7, etc.
                $ageVariation = rand(-1, 1); // Allow ±1 year variation
                $actualAge = max(5, $baseAge + $ageVariation); // Minimum age 5
                $dob = \Carbon\Carbon::now()->subYears($actualAge)->subDays(rand(0, 365));

                // Determine grade category for profile image
                $gradeCategory = $this->getGradeCategory($class->grade->level);
                $profileImage = $this->getRandomProfileImage($gradeCategory, $gender);

                // Use Eloquent model to ensure proper UUID handling
                $user = \App\Models\User::create([
                    'name' => $this->generateUniqueName($gender),
                    'email' => "student{$studentNumber}@smartcampusedu.com",
                    'password' => $this->getHashedPassword(),
                    'is_active' => true,
                ]);

                $user->assignRole('student');

                $studentProfile = \App\Models\StudentProfile::create([
                    'user_id' => $user->id,
                    'student_id' => $studentId,
                    'student_identifier' => $this->generateEmployeeId('STU'),
                    'class_id' => $class->id,
                    'grade_id' => $class->grade_id,
                    'date_of_joining' => $this->getSchoolOpenDate(),
                    'gender' => $gender,
                    'dob' => $dob,
                    'address' => $this->generateRandomAddress(),
                    'photo_path' => $profileImage,
                    'status' => 'active',
                    // Add some additional student details
                    'blood_type' => $this->getRandomBloodType(),
                    'ethnicity' => 'Myanmar',
                    'religious' => $this->getRandomReligion(),
                ]);

                $studentProfiles[] = (object) [
                    'id' => $studentProfile->id,
                    'user_id' => $user->id,
                    'class_id' => $class->id,
                    'address' => $studentProfile->address,
                    'grade' => $class->grade,
                    'gender' => $gender,
                ];

                $studentNumber++;
            }

            if ($studentNumber % 300 === 1) {
                $this->command->info("  Created " . ($studentNumber - 1) . " students...");
            }
        }

        $this->command->info("  Created " . ($studentNumber - 1) . " students total.");
        return $studentProfiles;
    }

    private function createGuardians(array $studentProfiles): void
    {
        $this->command->info('Creating Guardians (1,170)...');

        $occupations = ['Business Owner', 'Teacher', 'Doctor', 'Engineer', 'Government Officer', 'Farmer', 'Merchant', 'Driver', 'Accountant', 'Lawyer'];
        $guardianNumber = 1;

        foreach ($studentProfiles as $studentProfile) {
            // Use opposite gender for variety, but sometimes same gender
            $guardianGender = $studentProfile->gender === 'male' ? 
                (rand(0, 2) === 0 ? 'male' : 'female') : 
                (rand(0, 2) === 0 ? 'female' : 'male');

            $guardianName = $this->generateUniqueName($guardianGender);
            $guardianPhone = $this->generateMyanmarPhoneNumber();

            $user = \App\Models\User::create([
                'name' => $guardianName,
                'email' => "guardian{$guardianNumber}@smartcampusedu.com",
                'password' => $this->getHashedPassword(),
                'is_active' => true,
            ]);

            $user->assignRole('guardian');

            $guardianProfile = \App\Models\GuardianProfile::create([
                'user_id' => $user->id,
                'occupation' => $occupations[array_rand($occupations)],
                'address' => $studentProfile->address,
            ]);

            // Update student profile with guardian information
            \App\Models\StudentProfile::where('id', $studentProfile->id)->update([
                'father_name' => $guardianGender === 'male' ? $guardianName : $this->generateUniqueName('male'),
                'mother_name' => $guardianGender === 'female' ? $guardianName : $this->generateUniqueName('female'),
                'father_phone_no' => $guardianGender === 'male' ? $guardianPhone : $this->generateMyanmarPhoneNumber(),
                'mother_phone_no' => $guardianGender === 'female' ? $guardianPhone : $this->generateMyanmarPhoneNumber(),
                'father_occupation' => $guardianGender === 'male' ? $guardianProfile->occupation : $occupations[array_rand($occupations)],
                'mother_occupation' => $guardianGender === 'female' ? $guardianProfile->occupation : $occupations[array_rand($occupations)],
                'emergency_contact_phone_no' => $guardianPhone,
            ]);

            DB::table('guardian_student')->insert([
                'guardian_profile_id' => $guardianProfile->id,
                'student_profile_id' => $studentProfile->id,
                'relationship' => 'parent',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $guardianNumber++;

            if ($guardianNumber % 300 === 1) {
                $this->command->info("  Created " . ($guardianNumber - 1) . " guardians...");
            }
        }

        $this->command->info("  Created " . ($guardianNumber - 1) . " guardians total.");
    }

    /**
     * Get grade category based on grade level
     */
    private function getGradeCategory(int $gradeLevel): string
    {
        return match (true) {
            $gradeLevel <= 4 => 'primary',
            $gradeLevel <= 8 => 'middle',
            default => 'high',
        };
    }

    /**
     * Get random profile image based on grade category and gender
     */
    private function getRandomProfileImage(string $gradeCategory, string $gender): string
    {
        $imagePath = "images/student_images/{$gradeCategory}/{$gender}";
        $publicPath = public_path($imagePath);
        
        if (!is_dir($publicPath)) {
            return 'images/student_default_profile.jpg';
        }

        $images = array_diff(scandir($publicPath), ['.', '..']);
        $images = array_filter($images, function($file) use ($publicPath) {
            return is_file($publicPath . '/' . $file) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
        });

        if (empty($images)) {
            return 'images/student_default_profile.jpg';
        }

        $randomImage = $images[array_rand($images)];
        return "{$imagePath}/{$randomImage}";
    }

    /**
     * Generate Myanmar phone number
     */
    private function generateMyanmarPhoneNumber(): string
    {
        $operators = ['09', '09'];
        $operator = $operators[array_rand($operators)];
        
        // Common Myanmar mobile prefixes
        $prefixes = ['25', '26', '40', '42', '43', '44', '45', '67', '68', '69', '77', '78', '79', '94', '95', '96', '97', '98', '99'];
        $prefix = $prefixes[array_rand($prefixes)];
        
        $number = $operator . $prefix . rand(100000, 999999);
        return $number;
    }

    /**
     * Generate random Myanmar address
     */
    private function generateRandomAddress(): string
    {
        $townships = [
            'Yangon' => ['Bahan', 'Dagon', 'Kamayut', 'Kyimyindaing', 'Lanmadaw', 'Latha', 'Mayangon', 'Mingala Taungnyunt', 'Pabedan', 'Sanchaung', 'Seikkan', 'Tamwe', 'Thaketa', 'Thingangyun', 'Yankin'],
            'Mandalay' => ['Aungmyethazan', 'Chanayethazan', 'Chanmyathazi', 'Mahaaungmye', 'Pyigyidagun'],
            'Naypyidaw' => ['Dekkhina', 'Ottarathiri', 'Pobbathiri', 'Pyinmana', 'Tatkon', 'Zabuthiri', 'Zeyathiri'],
        ];

        $city = array_rand($townships);
        $township = $townships[$city][array_rand($townships[$city])];
        $street = rand(1, 50) . 'th Street';
        $quarter = 'Quarter ' . rand(1, 10);

        return "{$street}, {$quarter}, {$township}, {$city}, Myanmar";
    }

    /**
     * Get random blood type
     */
    private function getRandomBloodType(): string
    {
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        return $bloodTypes[array_rand($bloodTypes)];
    }

    /**
     * Get random religion
     */
    private function getRandomReligion(): string
    {
        $religions = ['Buddhism', 'Christianity', 'Islam', 'Hinduism', 'Other'];
        $weights = [85, 8, 4, 2, 1]; // Approximate distribution in Myanmar
        
        $random = rand(1, 100);
        $cumulative = 0;
        
        for ($i = 0; $i < count($religions); $i++) {
            $cumulative += $weights[$i];
            if ($random <= $cumulative) {
                return $religions[$i];
            }
        }
        
        return 'Buddhism'; // Default fallback
    }
}
