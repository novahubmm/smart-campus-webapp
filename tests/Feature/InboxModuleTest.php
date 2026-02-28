<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\GuardianProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\InboxMessage;
use App\Models\Role;
use App\Models\Permission;

class InboxModuleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        if (!Role::where('name', 'system_admin')->exists()) {
            Role::create(['name' => 'system_admin']);
            Role::create(['name' => 'guardian']);
            Role::create(['name' => 'teacher']);
        }

        if (!Permission::where('name', 'manage announcements')->exists()) {
            Permission::create(['name' => 'manage announcements']);
        }
    }

    public function test_admin_can_view_inbox_index()
    {
        $admin = User::factory()->create();
        $admin->assignRole('system_admin');

        $response = $this->actingAs($admin)->get('/inbox');

        $response->assertStatus(200);
        $response->assertViewIs('inbox.index');
    }

    public function test_guardian_can_send_inbox_message_via_api()
    {
        $guardianUser = User::factory()->create();
        $guardianUser->assignRole('guardian');
        $guardianProfile = GuardianProfile::create([
            'user_id' => $guardianUser->id,
            'relationship_to_student' => 'Parent'
        ]);

        $studentUser = User::factory()->create();
        $studentProfile = StudentProfile::create([
            'user_id' => $studentUser->id,
            'student_id' => 'STU123',
            'admission_number' => 'ADM123',
            'status' => 'active'
        ]);

        $guardianProfile->students()->attach($studentProfile->id);

        $response = $this->actingAs($guardianUser, 'sanctum')->postJson('/api/v1/guardian/inbox', [
            'student_profile_id' => $studentProfile->id,
            'subject' => 'Test Complaint',
            'category' => 'complaint',
            'priority' => 'high',
            'body' => 'This is a test message body.'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('inbox_messages', [
            'subject' => 'Test Complaint'
        ]);
        $this->assertDatabaseHas('inbox_message_replies', [
            'body' => 'This is a test message body.'
        ]);
    }

    public function test_admin_can_reply_to_inbox_message()
    {
        $admin = User::factory()->create();
        $admin->assignRole('system_admin');

        $guardianUser = User::factory()->create();
        $guardianProfile = GuardianProfile::create([
            'user_id' => $guardianUser->id,
            'relationship_to_student' => 'Parent'
        ]);

        $inbox = InboxMessage::create([
            'guardian_profile_id' => $guardianProfile->id,
            'subject' => 'Query about fees',
            'category' => 'general',
            'priority' => 'medium',
            'status' => 'unread'
        ]);

        $response = $this->actingAs($admin)->post("/inbox/{$inbox->id}/reply", [
            'body' => 'This is an admin reply.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Reply sent successfully.');

        $this->assertDatabaseHas('inbox_message_replies', [
            'inbox_message_id' => $inbox->id,
            'body' => 'This is an admin reply.',
            'sender_type' => get_class($admin),
            'sender_id' => $admin->id
        ]);
    }

    public function test_admin_can_assign_inbox_message()
    {
        $admin = User::factory()->create();
        $admin->assignRole('system_admin');

        $guardianUser = User::factory()->create();
        $guardianProfile = GuardianProfile::create([
            'user_id' => $guardianUser->id,
            'relationship_to_student' => 'Parent'
        ]);

        $teacherUser = User::factory()->create();
        $teacherProfile = TeacherProfile::create([
            'user_id' => $teacherUser->id,
            'employment_status' => 'full_time',
            'staff_id' => 'T123',
            'status' => 'active',
        ]);

        $inbox = InboxMessage::create([
            'guardian_profile_id' => $guardianProfile->id,
            'subject' => 'Needs Assignment',
            'status' => 'unread'
        ]);

        $response = $this->actingAs($admin)->post("/inbox/{$inbox->id}/assign", [
            'teacher_profile_id' => $teacherProfile->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('inbox_messages', [
            'id' => $inbox->id,
            'assigned_to_type' => TeacherProfile::class,
            'assigned_to_id' => $teacherProfile->id,
            'status' => 'assigned'
        ]);
    }
}
