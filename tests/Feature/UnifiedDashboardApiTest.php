<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class UnifiedDashboardApiTest extends TestCase
{
    public function test_teacher_can_access_dashboard()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_guardian_can_access_dashboard()
    {
        $guardian = User::whereHas('roles', fn($q) => $q->where('name', 'guardian'))->first();
        Sanctum::actingAs($guardian);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_teacher_can_get_today_classes()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->getJson('/api/v1/dashboard/today');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_guardian_can_get_today_schedule()
    {
        $guardian = User::whereHas('roles', fn($q) => $q->where('name', 'guardian'))->first();
        Sanctum::actingAs($guardian);

        $response = $this->getJson('/api/v1/dashboard/today');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_teacher_can_get_stats()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->getJson('/api/v1/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_guardian_can_get_stats()
    {
        $guardian = User::whereHas('roles', fn($q) => $q->where('name', 'guardian'))->first();
        Sanctum::actingAs($guardian);

        $response = $this->getJson('/api/v1/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/v1/dashboard');
        $response->assertStatus(401);
    }
}
