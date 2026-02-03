<?php

use App\Models\User;
use App\Models\Permission;

it('lists students', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'students.view']));
    $token = $user->createToken('api')->plainTextToken;

    $res = $this->withToken($token)->getJson('/api/v1/students');
    $res->assertOk()->assertJsonPath('success', true);
});
