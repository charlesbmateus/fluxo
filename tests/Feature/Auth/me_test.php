<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated user can fetch their profile', function () {
    $user = User::factory()->clientRole()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me');

    $response
        ->assertStatus(200)
        ->assertJsonFragment([
            'id'    => $user->id,
            'email'=> $user->email,
            'role' => 'client',
        ]);
});

test('unauthenticated user cannot access me endpoint', function () {
    $this->getJson('/api/v1/me')
        ->assertStatus(401);
});
