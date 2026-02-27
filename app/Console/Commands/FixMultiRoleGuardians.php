<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\GuardianProfile;
use App\Models\StudentProfile;
use App\Enums\RoleEnum;
use Illuminate\Console\Command;

class FixMultiRoleGuardians extends Command
{
    protected $signature = 'fix:multi-role-guardians';
    protected $description = 'Fix users who are linked as guardians but missing guardian role or profile';

    public function handle()
    {
        $this->info('Checking for users linked as guardians...');

        // Find all users who are linked to students via guardian_student pivot table
        // but don't have guardian role or profile
        $usersNeedingFix = collect();

        // Get all guardian profile IDs from the pivot table
        $guardianProfileIds = \DB::table('guardian_student')
            ->distinct()
            ->pluck('guardian_profile_id');

        $this->info("Found {$guardianProfileIds->count()} guardian profile IDs in guardian_student table");

        foreach ($guardianProfileIds as $guardianProfileId) {
            $guardianProfile = GuardianProfile::find($guardianProfileId);
            
            if (!$guardianProfile) {
                $this->warn("Guardian profile {$guardianProfileId} not found");
                continue;
            }

            $user = $guardianProfile->user;
            
            if (!$user) {
                $this->warn("User not found for guardian profile {$guardianProfileId}");
                continue;
            }

            // Check if user has guardian role
            if (!$user->hasRole(RoleEnum::GUARDIAN->value)) {
                $usersNeedingFix->push([
                    'user' => $user,
                    'needs_role' => true,
                    'needs_profile' => false,
                ]);
            }
        }

        // Also check for users who have guardian role but no profile
        $guardianUsers = User::role(RoleEnum::GUARDIAN->value)->get();
        foreach ($guardianUsers as $user) {
            if (!$user->guardianProfile) {
                $usersNeedingFix->push([
                    'user' => $user,
                    'needs_role' => false,
                    'needs_profile' => true,
                ]);
            }
        }

        if ($usersNeedingFix->isEmpty()) {
            $this->info('No users need fixing!');
            return 0;
        }

        $this->info("Found {$usersNeedingFix->count()} users needing fixes");

        foreach ($usersNeedingFix as $item) {
            $user = $item['user'];
            
            $this->line("Processing: {$user->name} ({$user->email})");

            if ($item['needs_role']) {
                $user->assignRole(RoleEnum::GUARDIAN->value);
                $this->info("  ✓ Assigned guardian role");
            }

            if ($item['needs_profile']) {
                GuardianProfile::create([
                    'user_id' => $user->id,
                ]);
                $this->info("  ✓ Created guardian profile");
            }
        }

        $this->info('All users fixed successfully!');
        return 0;
    }
}
