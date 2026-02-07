<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class UnifiedNotificationApiTest extends TestCase
{
    public function test_teacher_can_get_notifications()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_guardian_can_get_notifications()
    {
        $guardian = User::whereHas('roles', fn($q) => $q->where('name', 'guardian'))->first();
        Sanctum::actingAs($guardian);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_teacher_can_get_unread_count()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->getJson('/api/v1/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_guardian_can_get_unread_count()
    {
        $guardian = User::whereHas('roles', fn($q) => $q->where('name', 'guardian'))->first();
        Sanctum::actingAs($guardian);

        $response = $this->getJson('/api/v1/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_teacher_can_get_notification_settings()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->getJson('/api/v1/notifications/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_guardian_can_get_notification_settings()
    {
        $guardian = User::whereHas('roles', fn($q) => $q->where('name', 'guardian'))->first();
        Sanctum::actingAs($guardian);

        $response = $this->getJson('/api/v1/notifications/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_unauthenticated_user_cannot_access_notifications()
    {
        $response = $this->getJson('/api/v1/notifications');
        $response->assertStatus(401);
    }
}
