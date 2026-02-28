<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InboxMessage;
use App\Models\InboxMessageReply;
use App\Models\GuardianProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Str;

class DemoInboxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, get some profiles to work with
        $guardians = GuardianProfile::with('user')->take(3)->get();
        if ($guardians->isEmpty()) {
            for ($i = 0; $i < 3; $i++) {
                $user = User::factory()->create();
                $user->assignRole('guardian');

                $studentUser = User::factory()->create();
                $studentProfile = StudentProfile::create([
                    'id' => Str::uuid(),
                    'user_id' => $studentUser->id,
                    'student_id' => 'STD' . rand(1000, 9999),
                    'admission_number' => 'ADM' . rand(1000, 9999),
                    'status' => 'active'
                ]);

                $guardian = GuardianProfile::create([
                    'id' => Str::uuid(),
                    'user_id' => $user->id,
                    'relationship_to_student' => 'Parent'
                ]);

                $guardian->students()->attach($studentProfile->id);
            }
            $guardians = GuardianProfile::with('user')->take(3)->get();
        }

        $teachers = TeacherProfile::with('user')->take(2)->get();
        if ($teachers->isEmpty()) {
            for ($i = 0; $i < 2; $i++) {
                $user = User::factory()->create();
                $user->assignRole('teacher');

                TeacherProfile::create([
                    'id' => Str::uuid(),
                    'user_id' => $user->id,
                    'employment_status' => 'full_time',
                    'staff_id' => 'TER' . rand(100, 999),
                    'status' => 'active',
                ]);
            }
            $teachers = TeacherProfile::with('user')->take(2)->get();
        }

        $adminUsers = User::role('system_admin')->take(1)->get();
        if ($adminUsers->isEmpty()) {
            $adminUser = User::factory()->create();
            $adminUser->assignRole('system_admin');
            $adminUsers = User::role('system_admin')->take(1)->get();
        }

        $admin = $adminUsers->first();
        $teacher = $teachers->first();

        // Data samples to generate
        $samples = [
            [
                'subject' => 'Inquiry regarding late fee submission',
                'category' => 'general',
                'priority' => 'medium',
                'status' => 'resolved',
                'replies' => [
                    ['is_admin' => false, 'body' => 'Hello, I wanted to know if there is a penalty for submitting the tuition fee 3 days late due to a banking issue on my end.'],
                    ['is_admin' => true, 'body' => 'Dear Parent, thank you for reaching out. There is no penalty if you submit it within the 7-day grace period. You are good to go.'],
                    ['is_admin' => false, 'body' => 'Thank you for the quick response!'],
                ]
            ],
            [
                'subject' => 'Bullying concern in Grade 5',
                'category' => 'complaint',
                'priority' => 'high',
                'status' => 'assigned',
                'assigned_to' => $teacher,
                'replies' => [
                    ['is_admin' => false, 'body' => 'My son mentioned that he is being teased during the lunch break by some older students. Can this be looked into?'],
                    ['is_admin' => true, 'body' => 'We take this very seriously. I am assigning this to the class teacher to investigate immediately.'],
                ]
            ],
            [
                'subject' => 'Absence due to family emergency',
                'category' => 'health',
                'priority' => 'high',
                'status' => 'read',
                'replies' => [
                    ['is_admin' => false, 'body' => 'I would like to inform the school that my daughter will be absent for the next two days due to a sudden family emergency out of state.'],
                ]
            ],
            [
                'subject' => 'Question about upcoming Science Fair',
                'category' => 'academic',
                'priority' => 'low',
                'status' => 'unread',
                'replies' => [
                    ['is_admin' => false, 'body' => 'Could you please share the rubric for the upcoming science fair project? We want to make sure we are preparing correctly.'],
                ]
            ],
            [
                'subject' => 'Lost ID Card',
                'category' => 'general',
                'priority' => 'medium',
                'status' => 'unread',
                'replies' => [
                    ['is_admin' => false, 'body' => 'My child lost their ID card yesterday. How can we apply for a replacement?'],
                ]
            ]
        ];

        foreach ($samples as $index => $sample) {
            $guardian = $guardians[$index % count($guardians)];

            // Try to find a student for this guardian
            $student = $guardian->students()->first();

            // Create the main thread
            $message = InboxMessage::create([
                'id' => Str::uuid(),
                'guardian_profile_id' => $guardian->id,
                'student_profile_id' => $student ? $student->id : null,
                'subject' => $sample['subject'],
                'category' => $sample['category'],
                'priority' => $sample['priority'],
                'status' => $sample['status'],
                'assigned_to_type' => isset($sample['assigned_to']) ? TeacherProfile::class : null,
                'assigned_to_id' => isset($sample['assigned_to']) ? $sample['assigned_to']->id : null,
                'created_at' => now()->subDays(rand(1, 14))->addHours(rand(1, 24)),
                'updated_at' => now(),
            ]);

            // Add replies
            $replyTime = $message->created_at->copy();
            foreach ($sample['replies'] as $replyData) {
                $replyTime = $replyTime->addHours(rand(1, 5));

                InboxMessageReply::create([
                    'id' => Str::uuid(),
                    'inbox_message_id' => $message->id,
                    'sender_type' => $replyData['is_admin'] ? User::class : GuardianProfile::class,
                    'sender_id' => $replyData['is_admin'] ? $admin->id : $guardian->id,
                    'body' => $replyData['body'],
                    'is_read' => true,
                    'created_at' => $replyTime,
                    'updated_at' => $replyTime,
                ]);
            }
        }

        $this->command->info('Guardian Inbox data seeded successfully!');
    }
}
