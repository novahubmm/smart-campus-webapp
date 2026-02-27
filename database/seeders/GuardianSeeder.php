<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\GuardianProfile;
use App\Models\StudentProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuardianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Guardians...');

        // Create 5 guardians
        for ($i = 1; $i <= 5; $i++) {
            $email = "guardian{$i}@smartcampusedu.com";

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "Guardian {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone' => '09' . rand(100000000, 999999999),
                ]
            );

            // Ensure password is set to 'password' even if user existed
            $user->update([
                'password' => Hash::make('password'),
            ]);

            if (!$user->hasRole('guardian')) {
                $user->assignRole('guardian');
            }

            $guardianProfile = GuardianProfile::firstOrCreate(
                ['user_id' => $user->id],
                GuardianProfile::factory()->make(['user_id' => $user->id])->toArray()
            );

            // Assign 1-3 random students to this guardian
            $students = StudentProfile::inRandomOrder()->limit(rand(1, 3))->get();

            foreach ($students as $student) {
                // Check if relationship already exists
                $exists = DB::table('guardian_student')
                    ->where('guardian_profile_id', $guardianProfile->id)
                    ->where('student_profile_id', $student->id)
                    ->exists();

                if (!$exists) {
                    DB::table('guardian_student')->insert([
                        'guardian_profile_id' => $guardianProfile->id,
                        'student_profile_id' => $student->id,
                        'relationship' => ['Father', 'Mother', 'Guardian'][rand(0, 2)],
                        'is_primary' => rand(0, 1) == 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update student profile with guardian details as father/mother/contact
                    // This is a simplification; ideally we'd check gender/relationship
                    $student->update([
                        'father_name' => $user->name,
                        'father_phone_no' => $user->phone,
                        'father_occupation' => $guardianProfile->occupation,
                        'emergency_contact_phone_no' => $user->phone,
                    ]);
                }
            }
        }

        $this->command->info('Guardians seeded successfully!');
    }
}
