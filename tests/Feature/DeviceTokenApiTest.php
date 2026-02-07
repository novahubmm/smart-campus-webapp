<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class DeviceTokenApiTest extends TestCase
{
    public function test_teacher_can_register_device_token()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $response = $this->postJson('/api/v1/device-tokens', [
            'device_token' => 'test_fcm_token_' . uniqid(),
            'device_type' => 'ios',
            'device_name' => 'iPhone 15',
            'app_version' => '1.0.0'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_guardian_can_register_device_token()
    {
        $guardian = User::whereHas('roles', fn($q) => $q->where('name', 'guardian'))->first();
        Sanctum::actingAs($guardian);

        $response = $this->postJson('/api/v1/device-tokens', [
            'device_token' => 'test_fcm_token_' . uniqid(),
            'device_type' => 'android',
            'device_name' => 'Samsung Galaxy',
            'app_version' => '1.0.0'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_teacher_can_delete_device_token()
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->first();
        Sanctum::actingAs($teacher);

        $token = 'test_fcm_token_' . uniqid();

        // Register first
        $this->postJson('/api/v1/device-tokens', [
            'device_token' => $token,
            'device_type' => 'ios'
        ]);

        // Then delete
        $response = $this->deleteJson('/api/v1/device-tokens', [
            'device_token' => $token
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_unauthenticated_user_cannot_register_device_token()
    {
        $response = $this->postJson('/api/v1/device-tokens', [
            'device_token' => 'test_token',
            'device_type' => 'ios'
        ]);

        $response->assertStatus(401);
    }
}
