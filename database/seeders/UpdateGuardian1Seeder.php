<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateGuardian1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Updating Guardian 1 to have 2 students...');

        // Get guardian 1
        $guardianUser = User::where('email', 'guardian1@smartcampusedu.com')
            ->whereHas('guardianProfile')
            ->first();

        if (!$guardianUser) {
            $this->command->error('Guardian 1 not found!');
            return;
        }

        $guardianProfile = $guardianUser->guardianProfile;

        // Get student 35
        $studentUser = User::where('email', 'student35@smartcampusedu.com')
            ->whereHas('studentProfile')
            ->first();

        if (!$studentUser) {
            $this->command->error('Student 35 not found!');
            return;
        }

        $studentProfile = $studentUser->studentProfile;

        // Check if relationship already exists
        $exists = DB::table('guardian_student')
            ->where('guardian_profile_id', $guardianProfile->id)
            ->where('student_profile_id', $studentProfile->id)
            ->exists();

        if ($exists) {
            $this->command->warn('Relationship already exists!');
            return;
        }

        // Create the relationship
        DB::table('guardian_student')->insert([
            'guardian_profile_id' => $guardianProfile->id,
            'student_profile_id' => $studentProfile->id,
            'relationship' => 'parent',
            'is_primary' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update student 35's parent information to match guardian 1
        $studentProfile->update([
            'father_name' => $guardianProfile->user->name,
            'father_phone_no' => $guardianUser->phone ?? '09123456789',
            'father_occupation' => $guardianProfile->occupation ?? 'Business Owner',
            'emergency_contact_phone_no' => $guardianUser->phone ?? '09123456789',
        ]);

        $this->command->info('✅ Successfully added Student 35 to Guardian 1');
        $this->command->info("   Guardian: {$guardianUser->name} ({$guardianUser->email})");
        $this->command->info("   Student: {$studentUser->name} ({$studentUser->email})");
        $this->command->info("   Student Class: {$studentProfile->classModel->name}");
    }
}
