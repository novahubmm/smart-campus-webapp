<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class UnifiedAuthApiTest extends TestCase
{
    use WithFaker;

    public function test_teacher_can_login_with_email()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $teacher->email,
            'password' => 'password',
            'device_name' => 'test_device'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'user_type',
                    'token',
                    'token_type',
                    'expires_at',
                    'permissions',
                    'roles'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user_type' => 'teacher'
                ]
            ]);
    }

    public function test_guardian_can_login_with_email()
    {
        $guardian = User::whereHas('roles', fn($q) => $q->where('name', 'guardian'))->first();
        
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $guardian->email,
            'password' => 'password',
            'device_name' => 'test_device'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'user_type',
                    'token',
                    'token_type',
                    'expires_at',
                    'permissions',
                    'roles'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user_type' => 'guardian'
                ]
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'invalid@example.com',
            'password' => 'wrongpassword',
            'device_name' => 'test_device'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->getJson('/api/v1/auth/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'user_type',
                    'permissions',
                    'roles'
                ]
            ]);
    }

    public function test_authenticated_user_can_change_password()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
    }

    public function test_change_password_fails_with_wrong_current_password()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect'
            ]);
    }

    public function test_authenticated_user_can_logout()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/v1/auth/profile');
        $response->assertStatus(401);
    }
}
